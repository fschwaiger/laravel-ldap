<?php

namespace Fschwaiger\Ldap\Providers;

use Symfony\Component\Ldap\Ldap as SymfonyLdap;
use Fschwaiger\Ldap\Console\Commands\SyncGroups;
use Fschwaiger\Ldap\Console\Commands\SyncUser;
use Fschwaiger\Ldap\Console\Commands\ShowUser;
use Illuminate\Support\ServiceProvider;
use Fschwaiger\Ldap\Core\Client;
use Auth;
use Gate;

class LdapServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Auth::provider('ldap', function ($app) {
            return $app->make(LdapUserProvider::class);
        });
        
        collect(config('privileges.grant'))->each(function ($dns, $role) {
            Gate::define($role, function ($user) use ($dns) {
                return $user->isMemberOfAny($dns);
            });
        });

        collect(config('privileges.deny'))->each(function ($dns, $role) {
            Gate::define($role, function ($user) use ($dns) {
                return ! $user->isMemberOfAny($dns);
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
        $this->commands([
            SyncGroups::class,
            SyncUser::class,
            ShowUser::class,
        ]);
        
        $this->publishes([
            __DIR__ . '/../../config/' => config_path(),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../migrations/' => database_path('migrations')
        ], 'migrations');
        
        $this->app->singleton('ldap', function ($app) {
            return new LdapClient(SymfonyLdap::create('ext_ldap', $app['config']['ldap.options']));
        });
    }
}
