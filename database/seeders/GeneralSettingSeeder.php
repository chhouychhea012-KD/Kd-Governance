<?php

namespace Database\Seeders;

use App\Models\GeneralSetting;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'target-score',
                'name' => 'Target Score',
                'value' => '90',
            ],
            [
                'key' => 'kpi-score',
                'name' => 'KPI Score',
                'value' => '70',
            ],
            [
                'key' => 'competency-score',
                'name' => 'Competency Score',
                'value' => '30',
            ],
        ];

        foreach ($settings as $setting) {
            GeneralSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'name' => $setting['name'],
                    'value' => $setting['value'],
                ]
            );
        }
    }
}
