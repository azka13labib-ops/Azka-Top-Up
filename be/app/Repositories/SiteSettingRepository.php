<?php

namespace App\Repositories;

use App\Contracts\Interfaces\data\SiteSettingInterface;
use App\Models\SiteSetting;

class SiteSettingRepository implements SiteSettingInterface
{
    public function get(string $key, $default = null)
    {
        $setting = SiteSetting::where('key', $key)->first();
        return $setting ? json_decode($setting->value, true) ?? $setting->value : $default;
    }

    public function set(string $key, $value): bool
    {
        $encodedValue = is_array($value) || is_object($value) ? json_encode($value) : $value;
        $setting = SiteSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $encodedValue]
        );
        return $setting->exists;
    }

    public function all(): array
    {
        return SiteSetting::pluck('value', 'key')->map(function ($value) {
            return json_decode($value, true) ?? $value;
        })->toArray();
    }
}
