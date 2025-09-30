<?php

namespace App\Http\Livewire\Teams;

use App\Actions\Jetstream\UpdateTeamLogo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateTeamLogoForm extends Component
{
    use WithFileUploads;

    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [];

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;

        $this->state = $team->withoutRelations()->toArray();
    }

    /**
     * Update the team's name.
     *
     * @param  \Laravel\Jetstream\Contracts\UpdatesTeamNames  $updater
     * @return void
     */
    public function updateTeamLogo(UpdateTeamLogo $updater)
    {
        $this->resetErrorBag();

        //$this->state->validate([
        //  'logo' => 'image|max:1024', // 1MB Max
        // ]);

        $logo = $this->state['logo']->store('logos');

        $this->team->logo = $logo;
        $this->team->save();

        // $updater->update($this->user, $this->team, $this->state);

        $this->emit('saved');

        $this->emit('refresh-navigation-menu');
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('teams.update-team-logo-form');
    }
}
