<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;

    public bool $site_active;

    public int $commission_fee;

    public int $platform_fee;

    public static function group(): string
    {
        return 'general';
    }
}
