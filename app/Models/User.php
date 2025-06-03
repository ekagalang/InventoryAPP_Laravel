<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function stockMovements(): HasMany // Tambahkan return type hint
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Seorang User bisa membuat banyak Pengajuan Barang.
     */
    public function itemRequestsDiajukan(): HasMany
    {
        return $this->hasMany(ItemRequest::class, 'user_id');
    }

    /**
     * Seorang User bisa menyetujui/menolak banyak Pengajuan Barang.
     */
    public function itemRequestsDisetujui(): HasMany
    {
        return $this->hasMany(ItemRequest::class, 'approved_by');
    }

    /**
     * Seorang User bisa memproses banyak Pengajuan Barang.
     */
    public function itemRequestsDiproses(): HasMany
    {
        return $this->hasMany(ItemRequest::class, 'processed_by');
    }
}
