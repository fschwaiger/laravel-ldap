<?php

namespace Fschwaiger\Ldap\Providers;

use Auth;
use Gate;
use Fschwaiger\Ldap\Console\Commands\ImportGroups;
use Fschwaiger\Ldap\Console\Commands\ImportUser;
use Fschwaiger\Ldap\Console\Commands\ShowUser;
use Fschwaiger\Ldap\Core\Client as LdapClient;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Ldap\Ldap as SymfonyLdapClient;

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
            ImportGroups::class,
            ImportUser::class,
            ShowUser::class,
        ]);
        
        $this->publishes([
            __DIR__ . '/../../config/' => config_path(),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../migrations/' => database_path('migrations')
        ], 'migrations');
        
        $this->app->singleton('ldap', function ($app) {
            return new LdapClient(SymfonyLdapClient::create('ext_ldap', $app['config']['ldap.options']));
        });
    }
}
