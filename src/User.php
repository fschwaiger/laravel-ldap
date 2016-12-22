<?php

namespace Fschwaiger\Ldap;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['ldap_guid', 'ldap_dn', 'username', 'name', 'email'];

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function isMemberOfAny(array $dns)
    {
        return $this->groups()->whereIn('ldap_dn', $dns)->exists();
    }
}
