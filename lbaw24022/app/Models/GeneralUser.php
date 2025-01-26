<?php

namespace App\Models;

use App\Http\Controllers\ImageController;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class GeneralUser extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    public $timestamps = false;
    protected $table = 'general_user';

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'google_id',
        'email_verified_at'
    ];

    // Specific roles
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'id');
    }

    public function expert(): HasOne
    {
        return $this->hasOne(Expert::class, 'id');
    }

    public function specificRole()
    {
        switch ($this->role) {
            case 'Regular User':
                return $this->user()->first();
            case 'Admin':
                return $this->admin()->first();
            case 'Expert':
                return $this->expert()->first();
            default:
                return null;
        }
    }
    public function isAdmin(): bool
    {
        return $this->role == 'Admin';
    }

    public function isExpert(): bool
    {
        return $this->role == 'Expert';
    }

    public function isGeneralUser(): bool
    {
        return $this->role == 'Regular User';
    }

    // Message
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'general_user_id');
    }

    // Notification
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'general_user_id');
    }

    // Image
    public function image(): HasOne
    {
        return $this->hasOne(Image::class, 'general_user_id');
    }

    public function getProfileImage()
    {
        return ImageController::getProfileImage($this->id);
    }

    public function scopeSearch(Builder $query, ?string $search = null)
    {
        // Apply the search condition if the search term is provided
        if ($search) {
            $query->whereRaw('to_tsvector(username || \' \' || email) @@ to_tsquery(?)', [$search]);
        }

        return $query;
    }
}
