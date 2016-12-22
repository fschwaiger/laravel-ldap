<?php
namespace Fschwaiger\Ldap\Providers;

use Symfony\Component\Ldap\Exception\ConnectionException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Fschwaiger\Ldap\Core\Facade as Ldap;
use Fschwaiger\Ldap\Group;
use Fschwaiger\Ldap\User;
use Artisan;

class LdapUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        return User::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        return User::whereRememberToken($token)->find($identifier);
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->remember_token = $token;
        $user->save();
    }

    public function retrieveByCredentials(array $credentials)
    {
        Artisan::call('ldap:import-user', [ 'username' => $credentials['username'] ]);
        return User::whereUsername($credentials['username'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $hasCorrectCredentials = $credentials['username'] === $user->username;
        $avoidAnonymousLdapBind = $credentials['password'] !== '';

        return $hasCorrectCredentials && $avoidAnonymousLdapBind && Ldap::bind($credentials);
    }
}