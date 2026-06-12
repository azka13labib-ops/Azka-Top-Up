<?php

namespace App\Contracts\Interfaces\data;

interface SiteSettingInterface
{
    public function get(string $key, $default = null);
    public function set(string $key, $value): bool;
    public function all(): array;
}
