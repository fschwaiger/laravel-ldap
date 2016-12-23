<?php

namespace Fschwaiger\Ldap;

use Auth;
use Gate;
use Fschwaiger\Ldap\Commands\ImportGroups;
use Fschwaiger\Ldap\Commands\ImportUser;
use Fschwaiger\Ldap\Commands\ShowUser;
use Fschwaiger\Ldap\Core\Client;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Ldap\Ldap as SymfonyClient;

class LdapServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // configure this provider in config/auth.php as your driver 'ldap'
        Auth::provider('ldap', function ($app, $config) {
            return new LdapUserProvider($config['model']);
        });
        
        // configure those roles in config/privileges.php
        collect(config('privileges'))->each(function ($groupDns, $privilege) {
            Gate::define($privilege, function ($user) use ($groupDns) {
                return $user->isMemberOfAny($groupDns);
            });
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // CLI commands provided by this extension
        $this->commands([
            ImportGroups::class, // ldap:import-groups
            ImportUser::class,   // ldap:import-user {username}
            ShowUser::class,     // ldap:show-user {username}
        ]);
        
        // run php artisan vendor:publish to import these files into your project
        $this->publishes([ __DIR__ . '/../config/' => config_path() ], 'config');
        $this->publishes([ __DIR__ . '/../migrations/' => database_path('migrations') ], 'migrations');
        
        // call app('ldap') to recieve the singleton instance
        $this->app->singleton('ldap', function ($app) {
            return new Client(SymfonyClient::create('ext_ldap', $app['config']['ldap.options']));
        });
    }
}
