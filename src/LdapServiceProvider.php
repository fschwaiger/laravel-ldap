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
        Auth::provider('ldap', function ($app, $config) {
            return new LdapUserProvider($config['model']);
        });
        
        collect(config('privileges'))->each(function ($dns, $role) {
            Gate::define($role, function ($user) use ($dns) {
                return $user->isMemberOfAny($dns);
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
            __DIR__ . '/../config/' => config_path(),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../migrations/' => database_path('migrations')
        ], 'migrations');
        
        $this->app->singleton('ldap', function ($app) {
            return new Client(SymfonyClient::create('ext_ldap', $app['config']['ldap.options']));
        });
    }
}
