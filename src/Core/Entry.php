<?php

namespace Fschwaiger\Ldap\Core;

use Symfony\Component\Ldap\Entry as SymfonyEntry;

class Entry implements \ArrayAccess
{
    /**
     * Underlying attribute container with ldap query results.
     *
     * @var \Symfony\Component\Ldap\Entry
     */
    private $wrappedEntry;

    /**
     * This entry wraps around the symfony ldap query result
     * entry, creating a more convenient interface to work
     * with Laravel.
     * 
     * @param \Symfony\Component\Ldap\Entry $wrappedEntry
     */
    public function __construct(SymfonyEntry $wrappedEntry)
    {
        $this->wrappedEntry = $wrappedEntry;
    }

    /**
     * Allows object style access to wrapped properties.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'dn':       return $this->wrappedEntry->getDn();
            case 'memberOf': return collect($this->wrappedEntry->getAttribute('memberOf'));
            default:         return $this->wrappedEntry->getAttribute($name)[0];
        }
    }

    /**
     * Allows array style access to wrapped properties. Required for
     * Laravel collection pluck('attribute') functionality.
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetExists($offset)
    {
        return $offset === 'dn' || $this->wrappedEntry->hasAttribute($offset);
    }

    /**
     * Allows array style access to wrapped properties. Required for
     * Laravel collection pluck('attribute') functionality.
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * This class is read-only, method has no effect.
     */
    public function offsetSet($offset, $value)
    {
        // unsupported
    }

    /**
     * This class is read-only, method has no effect.
     */
    public function offsetUnset($offset)
    {
        // unsupported
    }
}