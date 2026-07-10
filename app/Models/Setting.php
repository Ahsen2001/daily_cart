<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
    ];

    public static function values(array $defaults): array
    {
        return array_replace($defaults, static::query()
            ->whereIn('setting_key', array_keys($defaults))
            ->pluck('setting_value', 'setting_key')
            ->toArray());
    }

    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value ?? '']
            );
        }
    }
}
