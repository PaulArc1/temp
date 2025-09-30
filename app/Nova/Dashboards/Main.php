<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Cards\Help;
use Laravel\Nova\Dashboards\Main as Dashboard;
use Orion\NovaGreeter\GreeterCard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            GreeterCard::make()
                ->user(name: (auth()->user()->name))
                ->avatar(url: 'https://ui-avatars.com/api/?size=300&color=7F9CF5&background=EBF4FF&name='.auth()->user()->name),

            //new Help,
        ];
    }
}
