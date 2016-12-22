<?php

namespace Fschwaiger\Ldap;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'email', 'guid', 'dn', 'imported_at'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
