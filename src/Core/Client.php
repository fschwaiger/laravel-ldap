<?php

namespace Fschwaiger\Ldap\Core;

use Symfony\Component\Ldap\Entry as SymfonyEntry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface as SymfonyClient;

class Client
{
    /**
     * Underlying ldap client.
     *
     * @var SymfonyClient
     */
    protected $ldap;

    /**
     * Since this client is used as a singleton, the performance
     * can be increased by not binding on every single request
     * but automatically and on the first call to find(One).
     *
     * @var boolean
     */
    protected static $connected = false;

    /**
     * Create a new ldap client wrapping around the symfony ldap
     * component. See LdapServiceProvider for instantiation.
     */
    public function __construct(SymfonyClient $ldap)
    {
        $this->ldap = $ldap;
    }

    /**
     * Find ldap entries within the given dn for the specified query.
     * This function returns a laravel collection to ease iteration.
     * You can call this function without calling bind() before, it
     * will then use your credentials from ldap.php or .env files.
     *
     * @param string $dn The directory DN to search.
     * @param string $query The ldap search query, see ext_ldap documentation.
     *
     * @return \Illuminate\Support\Collection
     */
    public function find($dn, $query)
    {
        static::$connected or $this->bind();

        $entries = $this->ldap->query($dn, $query)->execute()->toArray();
        return collect($entries)->map(function (SymfonyEntry $entry) {
            return new Entry($entry);
        });
    }

    /**
     * Same as find($dn, $query), but returns only the first search
     * result or null if the result is an empty set. Parameters
     * are identical to find().
     *
     * @param string $dn The directory DN to search.
     * @param string $query The ldap search query, see ext_ldap documentation.
     *
     * @return Entry
     */
    public function findOne($dn, $query)
    {
        return $this->find($dn, $query)->first();
    }

    /**
     * Bind (login) to the ldap server using username and password. Since ldap
     * requires a DN to bind, the username can be transformed into a valid
     * DN by postfixing it, ie. 'username@domain.tld'. If you already
     * have a DN, use ['dn' => ..., 'password' => ...] instead.
     *
     * @param array $credentials Either ['username' => ..., 'password' => ...]
     *                               or [   'dn'    => ..., 'password' => ...]
     *
     * @return boolean Success state.
     */
    public function bind(array $credentials = null)
    {
        $credentials = $credentials ?: config('ldap.bind_credentials');

        $username = array_key_exists('dn', $credentials) ? $credentials['dn'] : $credentials['username'] . config('ldap.domain_suffix');
        $password = $credentials['password'];

        try {
            $this->ldap->bind($username, $password);
            return static::$connected = true;
        } catch (ConnectionException $credentialsAreInvalid) {
            return false;
        }
    }
}
