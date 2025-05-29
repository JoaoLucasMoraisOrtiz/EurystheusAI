<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Free user prompt limit
        SystemSetting::set(
            'free_user_prompt_limit',
            15,
            'integer',
            'Maximum number of prompts that free users can generate'
        );

        // Other system settings can be added here
        SystemSetting::set(
            'site_maintenance_mode',
            false,
            'boolean',
            'Enable maintenance mode for the entire site'
        );

        SystemSetting::set(
            'max_file_upload_size',
            10,
            'integer',
            'Maximum file upload size in MB'
        );

        SystemSetting::set(
            'default_currency',
            'BRL',
            'string',
            'Default currency for pricing'
        );

        $this->command->info('System settings initialized successfully.');
    }
}
