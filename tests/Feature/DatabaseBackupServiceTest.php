<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseBackupServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_backups_are_streamed_encrypted_and_retention_limited(): void
    {
        Storage::fake('backup-test');
        config([
            'backup.disk' => 'backup-test',
            'backup.directory' => 'backups',
            'backup.encryption_key' => 'test-backup-encryption-key',
            'backup.retention_count' => 1,
            'backup.retention_days' => 30,
        ]);

        User::factory()->create(['email' => 'must-not-appear@example.com']);

        $path = app(DatabaseBackupService::class)->create();
        $contents = Storage::disk('backup-test')->get($path);

        $this->assertStringStartsWith('DCBK2', $contents);
        $this->assertStringNotContainsString('must-not-appear@example.com', $contents);
        $this->assertStringNotContainsString('"type":"row"', $contents);

        Storage::disk('backup-test')->put('backups/older-one.dcbackup', 'old');
        Storage::disk('backup-test')->put('backups/older-two.dcbackup', 'old');
        app(DatabaseBackupService::class)->prune();

        $this->assertCount(1, Storage::disk('backup-test')->files('backups'));
    }
}
