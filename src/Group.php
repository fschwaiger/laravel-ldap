<?php

namespace Fschwaiger\Ldap;

use Illuminate\Database\Eloquent\Model;

/**
 * Minimal configuration required for this ldap authentication
 * extension to work. You can extend the model by subclassing.
 */
class Group extends Model
{
    protected $fillable = [ 'name', 'email', 'dn' ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
