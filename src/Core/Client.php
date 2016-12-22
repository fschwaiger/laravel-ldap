<?php

namespace Fschwaiger\Ldap\Core;

use Symfony\Component\Ldap\Entry as SymfonyLdapEntry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface as SymfonyLdap;

class Client
{
    protected $ldap;
    protected static $connected = false;

    public function __construct(SymfonyLdap $ldap)
    {
        $this->ldap = $ldap;
    }

    public function find($dn, $query)
    {
        static::$connected or $this->bind();

        $entries = $this->ldap->query($dn, $query)->execute()->toArray();
        return collect($entries)->map(function (SymfonyLdapEntry $entry) {
            return new Entry($entry);
        });
    }

    public function findOne($dn, $query)
    {
        return $this->find($dn, $query)->first();
    }

    public function bind(array $credentials = null)
    {
        $credentials = $credentials ?: config('ldap.bind_credentials');

        $username = $credentials['username'] . (str_contains($credentials['username'], '@') ? '' : config('ldap.domain_suffix'));
        $password = $credentials['password'];

        try {
            $this->ldap->bind($username, $password);
            static::$connected = true;
            return true;
        } catch (ConnectionException $credentialsAreInvalid) {
            return false;
        }
    }
}
