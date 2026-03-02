<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public static function get(string $key, string $default = ''): string
    {
        $setting = static::where('key', $key)->first();

        return $setting ? (string) $setting->value : $default;
    }

    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)
            ->orderBy('key')
            ->get()
            ->keyBy('key')
            ->map(fn ($s) => $s->value)
            ->all();
    }
}
