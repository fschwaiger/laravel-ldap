<?php
namespace Fschwaiger\Ldap\Providers;

use Artisan;
use Fschwaiger\Ldap\Core\Client as LdapClient;
use Fschwaiger\Ldap\Group;
use Fschwaiger\Ldap\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\Ldap\Exception\ConnectionException;

class LdapUserProvider implements UserProvider
{
    /**
     * Ldap client that performs the search and bind operations.
     *
     * @var Client
     */
    protected $ldap;

    /**
     * Constructor injects dependencies.
     */
    public function __construct(LdapClient $ldap)
    {
        $this->ldap = $ldap;
    }

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

        return $hasCorrectCredentials && $avoidAnonymousLdapBind && $this->ldap->bind($credentials);
    }
}