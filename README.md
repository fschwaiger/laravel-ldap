# Simple LDAP Integration for Laravel 5

This module provides a simple user provider for Laravel's auth system
that connects to an LDAP server, imports users and groups and then
maps the group identifiers to privilege roles in a config file.

This extension is originally written for use in a couple of webservices
for Fachschaft Maschinenbau e.V., but is generic enough to be used in
a variety of other projects as well. My focus here is to really keep
the logic as simple as possible, this is why I stripped all unnecessary
properties and methods.


## Installation

First, import the package into your laravel project via composer:

```console
composer require fschwaiger/laravel-ldap
```

Second, copy and paste this line to add its service provider to `config/app.php`:

```php
Fschwaiger\Ldap\LdapServiceProvider::class,
```

Last, publish the config files and migrations to your project with
`php artisan vendor:publish`. This will create the following files:

```console
config/ldap.php
config/privileges.php
database/migrations/2016_12_17_000000_extend_users_table.php
database/migrations/2016_12_17_100000_create_groups_table.php
database/migrations/2016_12_17_200000_create_group_user_table.php
```

## Integration

To tell Laravel to use this module for providing users, go to `config/auth.php`
and replace the user provider driver `eloquent` for `ldap`:

```php
'providers' => [
    'users' => [
        'driver' => 'ldap',
        'model' => App\User::class,
    ],
],
```

To connect to your directory server, edit the file `config/ldap.php` to match
your setup. Simply follow the instructions present in the config file.

Finally, edit your `.env` file to include:

```ini
LDAP_USERNAME=binduser
LDAP_PASSWORD=bindpass
```

If you did not modify the default migration for the `users` table, migration
should work out of the box. Else make sure you review the migration changes first.

```console
php artisan migrate
```

Once the setup is complete, the following command should import all your user groups.

```console
php artisan ldap:import-groups
```


## Importing Users and Groups

With above setup, login should now use the LDAP server instead of the local
database to authenticate users. This imports unknown users on their first successful
login on-the-fly. Note that importing users __does NOT import groups automatically!__
This means that you need to run `ldap:import-users` every time your groups change.

__Option A:__ Import manually after changes with `php artisan ldap:import-groups`.
This is good enough for mostly static directory structures. If your groups change
more often, check out Option B.

__Option B:__ Schedule the import in `app/Console/Kernel.php`:

```php
/**
    * Define the application's command schedule.
    *
    * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
    * @return void
    */
protected function schedule(Schedule $schedule)
{
    $schedule->command('ldap:import-groups')->daily();
}
```


## Authorize Actions with Group Privileges

The privileges you define in `config/privileges.php` are automatically mapped to the
authorization gate, so that you can check for them in all parts of your application.

In code:

```php
Gate::authorize('privilege')
// or
$allowed = $user->can('privilege')
```

Using middleware:

```php
$this->middleware('can:privilege')
// or
Route::get('action', ['middleware' => 'can:privilege', 'uses' => 'MyController@action'])
```

In Blade files:

```php
@can('privilege')
    ...
@endcan
```
