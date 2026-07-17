<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property int|null $role_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $status
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $phone_verified_at
 * @property-read Customer|null $customer
 * @property-read Vendor|null $vendor
 * @property-read Rider|null $rider
 * @property-read Role|null $role
 */
class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes {
        assignRole as private assignSpatieRole;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'role_id',
        'email',
        'phone',
        'password',
        'status',
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
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if (! $user->wasChanged('role_id')) {
                return;
            }

            $role = $user->role_id ? $user->role()->first() : null;
            $user->syncRoles($role ? [$role] : []);
        });
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPrimaryRole(string ...$roles): bool
    {
        return $this->hasAnyRole($roles);
    }

    public function assignRole(...$roles)
    {
        $result = $this->assignSpatieRole(...$roles);
        $primaryRole = collect($roles)->flatten()->first();

        if ($primaryRole !== null) {
            $role = $primaryRole instanceof Role
                ? $primaryRole
                : Role::findByName((string) $primaryRole, $this->getDefaultGuardName());

            if ((int) $this->role_id !== (int) $role->id) {
                $this->forceFill(['role_id' => $role->id])->saveQuietly();
                $this->setRelation('role', $role);
            }
        }

        return $result;
    }

    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill(['phone_verified_at' => $this->freshTimestamp()])->save();
    }

    public function delete(): ?bool
    {
        return parent::delete();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasPrimaryRole('Super Admin');
    }

    public function isAdminUser(): bool
    {
        return $this->hasPrimaryRole('Admin', 'Super Admin');
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function rider(): HasOne
    {
        return $this->hasOne(Rider::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function supportTicketReplies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
