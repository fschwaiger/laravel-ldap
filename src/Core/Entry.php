<?php

namespace Fschwaiger\Ldap\Core;

use Symfony\Component\Ldap\Entry as SymfonyLdapEntry;

class Entry implements \ArrayAccess
{
    private $wrappedEntry;

    public function __construct(SymfonyLdapEntry $wrappedEntry)
    {
        $this->wrappedEntry = $wrappedEntry;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'dn':
                return $this->wrappedEntry->getDn();
            default:
                $raw = $this->wrappedEntry->getAttribute($name);
                return $this->processAttribute($name, $raw);
        }
    }

    public function offsetExists($offset)
    {
        return $offset === 'dn' || $this->wrappedEntry->hasAttribute($offset);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        // unsupported
    }

    public function offsetUnset($offset)
    {
        // unsupported
    }

    public function getAttributes()
    {
        return collect($this->wrappedEntry->getAttributes());
    }

    private function processAttribute($name, $raw)
    {
        switch ($name) {
            case 'memberOf':
                return collect($raw);
            case 'objectGUID':
                return $this->formatGuid($raw[0]);
            default:
                return $raw[0];
        }
    }

    private function formatGuid($raw)
    {
        $hex  = unpack( "H*hex", $raw);
        $hex  = $hex["hex"];

        $hex1 = substr( $hex, -26, 2 ) . substr( $hex, -28, 2 ) . substr( $hex, -30, 2 ) . substr( $hex, -32, 2 );
        $hex2 = substr( $hex, -22, 2 ) . substr( $hex, -24, 2 );
        $hex3 = substr( $hex, -18, 2 ) . substr( $hex, -20, 2 );
        $hex4 = substr( $hex, -16, 4 );
        $hex5 = substr( $hex, -12, 12 );

        $guid = $hex1 . "-" . $hex2 . "-" . $hex3 . "-" . $hex4 . "-" . $hex5;

        return $guid;
    }
}