<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemMaintenanceController extends Controller
{
    public function index(): View
    {
        $disk = Storage::disk(config('backup.disk'));
        $directory = config('backup.directory');
        $disk->makeDirectory($directory);

        $backups = collect($disk->files($directory))
            ->filter(fn ($path) => str_ends_with($path, '.dcbackup'))
            ->map(fn ($path) => [
                'path' => $path,
                'name' => basename($path),
                'size' => $disk->size($path),
                'modified' => $disk->lastModified($path),
            ])
            ->sortByDesc('modified')
            ->values();

        return view('admin.maintenance.index', compact('backups'));
    }

    public function backup(DatabaseBackupService $backups): RedirectResponse
    {
        $backups->create();

        return back()->with('status', 'Encrypted database backup created.');
    }

    public function download(string $file): StreamedResponse
    {
        abort_unless(str_ends_with($file, '.dcbackup'), 404);

        $disk = Storage::disk(config('backup.disk'));
        $path = trim((string) config('backup.directory'), '/').'/'.basename($file);
        abort_unless($disk->exists($path), 404);

        return $disk->download($path, basename($path), ['Content-Type' => 'application/octet-stream']);
    }

    public function clearCompiled(): RedirectResponse
    {
        Artisan::call('optimize:clear');

        return back()->with('status', 'Application, route, config, event, and view caches cleared.');
    }
}
