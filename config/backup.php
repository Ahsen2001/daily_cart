<?php

return [
    'disk' => env('BACKUP_DISK', 'local'),
    'directory' => env('BACKUP_DIRECTORY', 'backups'),
    'retention_count' => (int) env('BACKUP_RETENTION_COUNT', 10),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
    'encryption_key' => env('BACKUP_ENCRYPTION_KEY') ?: env('APP_KEY'),
];
