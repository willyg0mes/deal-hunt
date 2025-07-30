<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AppMetric extends Model
{
    protected $fillable = [
        'date',
        'metric_type',
        'metric_key',
        'value',
        'metadata'
    ];

    protected $casts = [
        'date' => 'date',
        'metadata' => 'array'
    ];

    public static function track($type, $key = null, $value = 1, $metadata = null)
    {
        $date = Carbon::today();
        
        $metric = self::firstOrNew([
            'date' => $date,
            'metric_type' => $type,
            'metric_key' => $key
        ]);

        $metric->value += $value;
        if ($metadata) {
            $metric->metadata = array_merge($metric->metadata ?? [], $metadata);
        }
        
        return $metric->save();
    }

    public static function getMetrics($type, $days = 30)
    {
        return self::where('metric_type', $type)
            ->where('date', '>=', Carbon::today()->subDays($days))
            ->orderBy('date')
            ->get();
    }
}
