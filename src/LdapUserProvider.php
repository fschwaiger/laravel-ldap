<?php
namespace Fschwaiger\Ldap;

use Artisan;
use Fschwaiger\Ldap\Group;
use Fschwaiger\Ldap\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\Ldap\Exception\ConnectionException;

class LdapUserProvider implements UserProvider
{
    /**
     * The Eloquent user model.
     *
     * @var string
     */
    protected $model;

    /**
     * Instantiate the user provider with the eloquent model to use.
     *
     * @param string $model  Eloquent class name to provide.
     */
    public function __construct($model = User::class)
    {
        $this->model = $model;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return $this->createModel()->newQuery()->find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed   $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return $this->createModel()->newQuery()->whereRememberToken($token)->find($identifier);
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->remember_token = $token;
        $user->save();
    }

    /**
     * Retrieve a user by the given credentials. This triggers an
     * import from the ldap server.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        Artisan::call('ldap:import-user', [ 'username' => $credentials['username'] ]);
        return $this->createModel()->newQuery()->whereUsername($credentials['username'])->first();
    }

    /**
     * Validates a user against the given credentials. This tries to
     * bind to the ldap server with the specified credentials and
     * returns true if a connection could be established. This
     * also checks the password is non-empty, so anonymous
     * binding will not result in unauthorized logins.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $hasCorrectCredentials = $credentials['username'] === $user->username;
        $avoidAnonymousLdapBind = $credentials['password'] !== '';

        return $hasCorrectCredentials && $avoidAnonymousLdapBind && app('ldap')->bind($credentials);
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');
        return new $class;
    }
}