<?php

namespace App\Actions\Fortify;

use App\Helpers\SlugHelper;
use App\Mail\AccountCreated;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Contracts\AddsTeamMembers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public $companyName;

    /**
     * Create a newly registered user.
     *
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            //'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ];

        if (session('teamInvitation')) {
            $rules[] = ['company_name' => ['required', 'string', 'max:255']];
        }

        Validator::make($input, $rules)->validate();

        if (! session('teamInvitation')) {
            $this->companyName = $input['company_name'];
        }

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]), function (User $user) {

                $user->assignRole('user');

                // Get's all the invitations for this user and accept them
                $pendingInvitations = TeamInvitation::where('email', '=', $user->email)->get();

                if ($pendingInvitations && count($pendingInvitations) > 0) {
                    foreach ($pendingInvitations as $invitation) {
                        app(AddsTeamMembers::class)->add(
                            $invitation->team->owner,
                            $invitation->team,
                            $invitation->email,
                            $invitation->role
                        );

                        $invitation->delete();
                    }
                } else {
                    $this->createTeam($user);
                    //ray("Send Email");
                    request()->session()->flash('status', 'Your account has been created. Please login below.');
                    Mail::to($user)->send(new AccountCreated($user));
                }
            });
        });
    }

    /**
     * Create a personal team for the user.
     *
     * @return void
     */
    protected function createTeam(User $user)
    {
        $slug = SlugHelper::generate(Team::class, \Str::slug($this->companyName).'.'.preg_replace('(^https?://)', '', config('app.url')), 'hostname');

        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            //'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'name' => $this->companyName,
            'personal_team' => true,
            'hostname' => $slug,
            //'seats' => $this->numberOfSeats,
        ]));
    }
}
