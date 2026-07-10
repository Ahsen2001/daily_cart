<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemMaintenanceController extends Controller
{
    private const BACKUP_DISK = 'local';
    private const BACKUP_DIR = 'backups';

    public function index(): View
    {
        Storage::disk(self::BACKUP_DISK)->makeDirectory(self::BACKUP_DIR);

        $backups = collect(Storage::disk(self::BACKUP_DISK)->files(self::BACKUP_DIR))
            ->filter(fn ($path) => str_ends_with($path, '.json'))
            ->map(fn ($path) => [
                'path' => $path,
                'name' => basename($path),
                'size' => Storage::disk(self::BACKUP_DISK)->size($path),
                'modified' => Storage::disk(self::BACKUP_DISK)->lastModified($path),
            ])
            ->sortByDesc('modified')
            ->values();

        return view('admin.maintenance.index', compact('backups'));
    }

    public function backup(): RedirectResponse
    {
        Storage::disk(self::BACKUP_DISK)->makeDirectory(self::BACKUP_DIR);

        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->reject(fn ($table) => in_array($table, ['cache', 'cache_locks', 'sessions'], true));

        $payload = [
            'created_at' => now()->toISOString(),
            'app' => config('app.name'),
            'tables' => [],
        ];

        foreach ($tables as $table) {
            $payload['tables'][$table] = [
                'columns' => Schema::getColumnListing($table),
                'rows' => DB::table($table)->get()->map(fn ($row) => (array) $row)->all(),
            ];
        }

        $path = self::BACKUP_DIR.'/dailycart-backup-'.now()->format('Ymd-His').'.json';
        Storage::disk(self::BACKUP_DISK)->put($path, json_encode($payload, JSON_PRETTY_PRINT));

        return back()->with('status', 'Database backup created.');
    }

    public function download(string $file): StreamedResponse
    {
        $path = self::BACKUP_DIR.'/'.basename($file);
        abort_unless(Storage::disk(self::BACKUP_DISK)->exists($path), 404);

        return Storage::disk(self::BACKUP_DISK)->download($path);
    }

    public function clearCompiled(): RedirectResponse
    {
        Artisan::call('optimize:clear');

        return back()->with('status', 'Application, route, config, event, and view caches cleared.');
    }
}
