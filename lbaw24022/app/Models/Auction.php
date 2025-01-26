<?php

namespace App\Models;

use App\Http\Controllers\ImageController;
use App\Observers\AuctionObserver;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;


#[ObservedBy([AuctionObserver::class])]
class Auction extends Model
{
    public $timestamps = false;
    protected $table = 'auction';


    protected $casts = [
        'attribute_values' => 'array',
        'end_time' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'description',
        'location',
        'end_time',
        'minimum_bid',
        'auction_state',
        'category_id',
        'attribute_values',
        'evaluation_requested',
        'evaluation',
        'expert',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function attributeValues()
    {
        return $this->attribute_values;
    }

    public function expertUser(): BelongsTo
    {
        return $this->belongsTo(Expert::class, 'expert');
    }

    public static function evaluationRequests($perPage)
    {
        return self::where('evaluation_requested', true)
            ->where('auction_state', 'Active')
            ->whereNull('evaluation')
            ->orderBy('end_time', 'asc')
            ->paginate($perPage);
    }


    // Report Auction
    public function reports(): HasMany
    {
        return $this->hasMany(ReportAuction::class, 'auction');
    }

    public function isReportedBy(int $userId): bool
    {
        return $this->reports()->where('reporter', $userId)->exists();
    }


    // Followers - pivot table

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follow_auction', 'auction', 'follower');
    }

    public function minimumBid()
    {
        // Return the minimum bid value directly from the auction's attribute
        return $this->minimum_bid;
    }

    // AutoBid
    public function autoBids(): HasMany
    {
        return $this->hasMany(AutoBid::class, 'auction');
    }
    // Bid
    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class, 'auction');
    }

    public function getHighestBids(int $nBids)
    {
        return $this->bids()->orderBy('value', 'desc')->take($nBids)->get();
    }

    public function highestBidValue()
    {
        return $this->bids()->max('value');
    }

    public function participants()
    {
        return $this->bids()->distinct('bidder')->count('bidder');
    }

    public function winner()
    {
        $highestBid = $this->getHighestBids(1)->first();
        return $highestBid ? $highestBid->user : null;
    }

    // Advertisement
    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class, 'auction_id');
    }

    public function activeLastAdvertisement()
    {
        if ($this->advertised) {
            return $this->advertisements()->orderBy('end_time', 'desc')->first();
        } else {
            return null;
        }
    }


    // Message
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'auction');
    }
    // Image
    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'auction');
    }
    // User follows auction
    public function isFollowedBy(int $userId): bool
    {
        return $this->followers()->where('follow_auction.follower', $userId)->exists();
    }

    public function scopeAdvertised($query)
    {
        return $query->where('advertised', true);
    }
    public function getImages()
    {
        return ImageController::auctionGet($this->id);
    }


    public function minBidValid()
    {
        // Get the current highest bid for the auction
        $currentHighestBid = $this->highestBidValue();


        // Get the site config for the minimal bid interval
        $siteConfig = SiteConfig::getSiteConfig();

        // If there is no bid, we get the minimum_bid
        $currentBid = $currentHighestBid ? $currentHighestBid + $siteConfig->minimal_bid_interval : $this->minimum_bid;


        // Calculate the minimum valid bid
        return $currentBid;
    }

    public static function getTotalValue($auctionsQuery)
    {
        return $auctionsQuery->join('bid', 'auction.id', '=', 'bid.auction')
            ->selectRaw('SUM(bid.value) as total')
            ->whereRaw('bid.value = (SELECT MAX(b.value) FROM bid AS b WHERE b.auction = auction.id)')
            ->value('total') ?? 0;
    }


    public function scopeSearch(
        Builder $query,
        ?string $search = null,
        ?string $location = null,
        ?Category $category = null,
        ?float $fromBid = null,
        ?float $toBid = null,
        ?array $attributes = []
    ): Builder {
        // Full-text search for auction name and description
        $query->when($search, function ($query, $search) {
            return $query->whereRaw("to_tsvector('english', name || ' ' || description) @@ plainto_tsquery('english', ?)", [$search]);
        });

        // Full-text search for location
        $query->when($location, function ($query, $location) {
            return $query->whereRaw("to_tsvector('english', location) @@ plainto_tsquery('english', ?)", [$location]);
        });

        // Filter by category
        $query->when($category, function ($query) use ($category) {
            return $query->where('category_id', $category->id);
        });

        // Filter by attributes
        $query->when($attributes, function ($query) use ($category, $attributes) {
            foreach ($attributes as $attributeName => $value) {
                $attribute = collect($category->attribute_list)->firstWhere('name', $attributeName) ?? null;
                $attributeType = $attribute['type'] ?? null;
                $attributeOptions = $attribute['options'] ?? [];

                if ($value) {
                    switch ($attributeType) {
                        case 'enum':
                            if (!in_array($value, $attributeOptions)) {
                                throw new \InvalidArgumentException("Invalid option for {$attributeName}.");
                            }
                            $query->whereJsonContains("attribute_values->$attributeName", $value);
                            break;
                        case 'float':
                            if (isset($value['from']) && !is_numeric($value['from'])) {
                                throw new \InvalidArgumentException("Invalid value for {$attributeName} 'from', must be a float.");
                            }

                            if (isset($value['to']) && !is_numeric($value['to'])) {
                                throw new \InvalidArgumentException("Invalid value for {$attributeName} 'to', must be a float.");
                            }

                            $from = isset($value['from']) ? (float) $value['from'] : null;
                            $to = isset($value['to']) ? (float) $value['to'] : null;

                            /*

                            select count(*) as aggregate from "auction" 
                            where "auction_state" = 'Active' 
                            and "category_id" = 2 
                            and ("attribute_values"->'color')::jsonb @> '"black"' 
                            and "attribute_values"->>'weight' <= 15
                            */

                            if ($from !== null && $to !== null) {
                                $query->whereRaw('(attribute_values->>?::TEXT)::NUMERIC BETWEEN ? AND ?', [$attributeName, $from, $to]);
                            } elseif ($from !== null) {
                                $query->whereRaw('(attribute_values->>?::TEXT)::NUMERIC >= ?', [$attributeName, $from]);
                            } elseif ($to !== null) {
                                $query->whereRaw('(attribute_values->>?::TEXT)::NUMERIC <= ?', [$attributeName, $to]);
                            }
                            break;
                        case 'int':
                            if (isset($value['from']) && !is_numeric($value['from'])) {
                                throw new \InvalidArgumentException("Invalid value for {$attributeName} 'from', must be an integer.");
                            }

                            if (isset($value['to']) && !is_numeric($value['to'])) {
                                throw new \InvalidArgumentException("Invalid value for {$attributeName} 'to', must be an integer.");
                            }

                            $from = isset($value['from']) ? (int) $value['from'] : null;
                            $to = isset($value['to']) ? (int) $value['to'] : null;

                            if ($from !== null && $to !== null) {
                                $query->whereRaw('(attribute_values->>?::TEXT)::NUMERIC BETWEEN ? AND ?', [$attributeName, $from, $to]);
                            } elseif ($from !== null) {
                                $query->whereRaw('(attribute_values->>?::TEXT)::NUMERIC >= ?', [$attributeName, $from]);
                            } elseif ($to !== null) {
                                $query->whereRaw('(attribute_values->>?::TEXT)::NUMERIC <= ?', [$attributeName, $to]);
                            }
                            break;
                        case 'string':
                            if (strlen($value) > 255) {
                                throw new \InvalidArgumentException("Text value for {$attributeName} cannot exceed 255 characters.");
                            }
                            $query->where("attribute_values->$attributeName", 'like', "%{$value}%");
                            break;
                    }
                }
            }
        });

        // Filter by bids (fromBid, toBid)
        $query->when($fromBid || $toBid, function ($query) use ($fromBid, $toBid) {
            return $query->where(function ($query) use ($fromBid, $toBid) {
                $query->whereHas('bids', function ($query) use ($fromBid, $toBid) {
                    if (!is_null($fromBid) && !is_null($toBid)) {
                        $query->whereBetween('value', [$fromBid, $toBid]);
                    } elseif (!is_null($fromBid)) {
                        $query->where('value', '>=', $fromBid);
                    } elseif (!is_null($toBid)) {
                        $query->where('value', '<=', $toBid);
                    }
                })
                    ->orWhere(function ($query) use ($fromBid, $toBid) {
                        $query->whereDoesntHave('bids') // No bids exist
                            ->when(!is_null($fromBid) || !is_null($toBid), function ($query) use ($fromBid, $toBid) {
                                if (!is_null($fromBid) && !is_null($toBid)) {
                                    $query->whereBetween('minimum_bid', [$fromBid, $toBid]);
                                } elseif (!is_null($fromBid)) {
                                    $query->where('minimum_bid', '>=', $fromBid);
                                } elseif (!is_null($toBid)) {
                                    $query->where('minimum_bid', '<=', $toBid);
                                }
                            });
                    });
            });
        });

        return $query;
    }
}
