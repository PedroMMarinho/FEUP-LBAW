<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteConfig extends Model
{
    public $timestamps = false;
    protected $table = 'site_config';

    protected $fillable = [
        'minimal_bid_interval',
        'subscribe_price_plan_a',
        'subscribe_price_plan_b',
        'subscribe_price_plan_c',
        'ad_price',
        'discounted_ad_price',
    ];

    // Static method to get the site configuration
    public static function getSiteConfig()
    {
        return self::first();  // Retrieve the first record
    }

}
