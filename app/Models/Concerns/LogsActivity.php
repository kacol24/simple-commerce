<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity as SpatieLogsActivity;

trait LogsActivity
{
    use SpatieLogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                         ->useLogName('commerce')
                         ->logAll()
                         ->dontSubmitEmptyLogs()
                         ->logExcept(['updated_at']);
    }
}
