<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Access Control List Roles
    |--------------------------------------------------------------------------
    |
    | Grants privileges to users belonging to the specified groups. You can
    | authorize actions by the 'can:privilege' middleware or directly via
    | the Gate facade. The mapping is 'privilege' => [ list of DNs ].
    |
    */

    'admin' => [
        'CN=Administrators,OU=Groups,DC=domain,DC=tld',
        'CN=Super Users,OU=Groups,DC=domain,DC=tld',
    ],
];
