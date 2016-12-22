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

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function retrieveById($identifier)
    {
        return $this->createModel()->newQuery()->find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        return $this->createModel()->newQuery()->whereRememberToken($token)->find($identifier);
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->remember_token = $token;
        $user->save();
    }

    public function retrieveByCredentials(array $credentials)
    {
        Artisan::call('ldap:import-user', [ 'username' => $credentials['username'] ]);
        return $this->createModel()->newQuery()->whereUsername($credentials['username'])->first();
    }

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