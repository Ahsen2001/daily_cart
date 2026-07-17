<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseBackupService
{
    private const MAGIC = 'DCBK2';

    private const BLOCK_SIZE = 16;

    public function create(): string
    {
        $disk = Storage::disk(config('backup.disk'));
        $directory = trim((string) config('backup.directory'), '/');
        $disk->makeDirectory($directory);

        $path = $directory.'/dailycart-backup-'.now()->format('Ymd-His').'.dcbackup';
        $stream = fopen($disk->path($path), 'wb');

        if ($stream === false) {
            throw new RuntimeException('Unable to open the backup destination.');
        }

        try {
            $encryption = $this->beginEncryption($stream);
            $this->writeRecord($stream, $encryption, [
                'type' => 'metadata',
                'version' => 2,
                'created_at' => now()->toISOString(),
                'app' => config('app.name'),
            ]);

            foreach ($this->tables() as $table) {
                $columns = Schema::getColumnListing($table);
                $this->writeRecord($stream, $encryption, [
                    'type' => 'table',
                    'name' => $table,
                    'columns' => $columns,
                ]);

                foreach (DB::table($table)->orderBy($columns[0])->cursor() as $row) {
                    $this->writeRecord($stream, $encryption, [
                        'type' => 'row',
                        'table' => $table,
                        'data' => (array) $row,
                    ]);
                }
            }

            $this->writeRecord($stream, $encryption, ['type' => 'end']);
            $this->finishEncryption($stream, $encryption);
        } catch (\Throwable $exception) {
            fclose($stream);
            $disk->delete($path);

            throw $exception;
        }

        fclose($stream);
        $this->prune();

        return $path;
    }

    public function prune(): void
    {
        $disk = Storage::disk(config('backup.disk'));
        $directory = trim((string) config('backup.directory'), '/');
        $retentionDays = max(1, (int) config('backup.retention_days'));
        $retentionCount = max(1, (int) config('backup.retention_count'));
        $cutoff = now()->subDays($retentionDays)->getTimestamp();

        $files = collect($disk->files($directory))
            ->filter(fn (string $path) => str_ends_with($path, '.dcbackup'))
            ->sortByDesc(fn (string $path) => $disk->lastModified($path))
            ->values();

        $files->each(function (string $path, int $index) use ($disk, $cutoff, $retentionCount) {
            if ($index >= $retentionCount || $disk->lastModified($path) < $cutoff) {
                $disk->delete($path);
            }
        });
    }

    /** @return array<int, string> */
    private function tables(): array
    {
        return collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => (string) array_values((array) $row)[0])
            ->reject(fn (string $table) => in_array($table, ['cache', 'cache_locks', 'sessions'], true))
            ->values()
            ->all();
    }

    /** @return array{key: string, iv: string, buffer: string, hmac: \HashContext} */
    private function beginEncryption($stream): array
    {
        $keyMaterial = (string) config('backup.encryption_key');

        if (str_starts_with($keyMaterial, 'base64:')) {
            $keyMaterial = base64_decode(substr($keyMaterial, 7), true) ?: '';
        }

        if ($keyMaterial === '') {
            throw new RuntimeException('BACKUP_ENCRYPTION_KEY or APP_KEY must be configured.');
        }

        $encryptionKey = hash_hkdf('sha256', $keyMaterial, 32, 'dailycart-backup-encryption');
        $authenticationKey = hash_hkdf('sha256', $keyMaterial, 32, 'dailycart-backup-authentication');
        $iv = random_bytes(self::BLOCK_SIZE);
        $header = self::MAGIC.$iv;
        $hmac = hash_init('sha256', HASH_HMAC, $authenticationKey);
        hash_update($hmac, $header);
        fwrite($stream, $header);

        return [
            'key' => $encryptionKey,
            'iv' => $iv,
            'buffer' => '',
            'hmac' => $hmac,
        ];
    }

    private function writeRecord($stream, array &$encryption, array $record): void
    {
        $json = json_encode($record, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE)."\n";
        $encryption['buffer'] .= $json;
        $completeLength = intdiv(strlen($encryption['buffer']), self::BLOCK_SIZE) * self::BLOCK_SIZE;

        if ($completeLength === 0) {
            return;
        }

        $plaintext = substr($encryption['buffer'], 0, $completeLength);
        $encryption['buffer'] = substr($encryption['buffer'], $completeLength);
        $this->encryptAndWrite($stream, $encryption, $plaintext);
    }

    private function finishEncryption($stream, array &$encryption): void
    {
        $paddingLength = self::BLOCK_SIZE - (strlen($encryption['buffer']) % self::BLOCK_SIZE);
        $plaintext = $encryption['buffer'].str_repeat(chr($paddingLength), $paddingLength);
        $this->encryptAndWrite($stream, $encryption, $plaintext);
        fwrite($stream, hash_final($encryption['hmac'], true));
    }

    private function encryptAndWrite($stream, array &$encryption, string $plaintext): void
    {
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-cbc',
            $encryption['key'],
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $encryption['iv']
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Unable to encrypt the database backup.');
        }

        $encryption['iv'] = substr($ciphertext, -self::BLOCK_SIZE);
        hash_update($encryption['hmac'], $ciphertext);
        fwrite($stream, $ciphertext);
    }
}
