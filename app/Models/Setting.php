<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = Cache::remember('setting:' . $key, 3600, function () use ($key) {
            $row = static::find($key);
            return $row?->value;
        });
        return $value !== null ? $value : $default;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        Cache::forget('setting:' . $key);
    }
}
