<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;
use Livewire\WithFileUploads;

class UpdateTeamLogo implements UpdatesTeamNames
{
    use WithFileUploads;

    /**
     * Validate and update the given team's name.
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @return void
     */
    public function update($user, $team, array $input)
    {
        ray('TEST2');
        //Gate::forUser($user)->authorize('update', $team);

        Validator::make($input, [
            'logo' => ['required', 'string', 'max:255'],
        ])->validateWithBag('updateTeamLogo');

        $input['logo']->store('logos');

        ray($input);

        $team->forceFill([
            'logo' => $input['logo'],
        ])->save();
    }
}
