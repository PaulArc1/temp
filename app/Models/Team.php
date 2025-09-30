<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Subscription;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;
use Spark\Billable;
use Spatie\Onboard\Concerns\GetsOnboarded;

class Team extends JetstreamTeam implements \Spatie\Onboard\Concerns\Onboardable
{
    use HasFactory;
    use Billable;
    use GetsOnboarded;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personal_team' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
        'hostname',
        'seats',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    public function stripeEmail(): string|null
    {
        return $this->owner->email;
    }

    public function getCountUsersAttribute()
    {
        return $this->users()->count() + 1;
    }

    public function subscription()
    {
        return $this->hasMany(Subscription::class, 'team_id', 'id')->orderBy('created_at', 'desc')->first();
    }
}
