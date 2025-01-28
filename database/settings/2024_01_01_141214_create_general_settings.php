<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'Fleepness');
        $this->migrator->add('general.site_active', true);
        $this->migrator->add('general.commission_fee', 20);
        $this->migrator->add('general.platform_fee', 4);
    }
};
