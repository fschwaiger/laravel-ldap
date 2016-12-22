<?php

namespace Fschwaiger\Ldap;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['guid', 'dn', 'username', 'name', 'email', 'imported_at'];

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function isMemberOfAny(array $groupDns)
    {
        return $this->groups()->whereIn('dn', $groupDns)->exists();
    }
}
