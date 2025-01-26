<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    public $timestamps = false;
    protected $table = 'category';

    protected $casts = [
        // 'attributes' => 'array',
        'attribute_list' => 'array',
    ];

    protected $fillable = ['name', 'attribute_list'];

    // Expert
    public function experts(): BelongsToMany
    {
        return $this->belongsToMany(
            Expert::class,
            'expert_category',
            'category_id',
            'expert'
        );
    }

    // Auction
    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class, 'category_id');
    }

    // checks if attribute is valid returning it's value if it is
    public function checkAttribute($name, $value)
    {
        foreach ($this->attribute_list ?? [] as $attribute) {
            if ($attribute['name'] === $name) {

                $type = $attribute['type'];

                if ($type == "enum" && $value < count($attribute['options']) && $value >= 0) {
                    // Considering $value as Int
                    return $attribute['options'][$value];
                } elseif ($type == "float" && is_numeric($value)) {
                    return $value;
                } elseif ($type == "int" && filter_var($value, FILTER_VALIDATE_INT) !== false) {
                    return $value;
                } elseif ($type == "price" && is_numeric($value) && preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
                    return $value;
                } elseif ($type == "string" && is_string($value) && !empty($value)) {
                    return $value;
                }
            }
        }
        return null;
    }
}
