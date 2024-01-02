<?php

namespace Aqayepardakht\Ticket;

use Illuminate\Support\ServiceProvider;

class TicketServiceProvider extends ServiceProvider
{
   
    public function register()
    {
        //
    }

   
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    
        $this->publishes([
            __DIR__.'/../config/support.php' => config_path('support.php'),
        ], 'ticket-config');
        
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'ticket-migrations');
    }
}