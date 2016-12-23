<?php

namespace Fschwaiger\Ldap;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Minimal configuration required for this ldap authentication
 * extension to work. You can extend the model by subclassing.
 */
class User extends Authenticatable
{
    protected $fillable = [ 'username', 'name', 'email' ];

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function isMemberOfAny(array $groupDns)
    {
        return $this->groups()->whereIn('dn', $groupDns)->exists();
    }
}
