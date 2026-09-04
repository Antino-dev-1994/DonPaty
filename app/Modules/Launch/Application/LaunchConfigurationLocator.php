<?php

namespace App\Modules\Launch\Application;

use App\Modules\Launch\Domain\Models\LaunchConfiguration;

class LaunchConfigurationLocator
{
    public function execute(): LaunchConfiguration
    {
        return LaunchConfiguration::query()->firstOrCreate([], ['status' => 'draft', 'attestations' => []]);
    }
}
