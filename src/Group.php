<?php

namespace Fschwaiger\Ldap;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [ 'name', 'email', 'ldap_guid', 'ldap_dn' ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
