<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Access Control List Roles
    |--------------------------------------------------------------------------
    |
    | Grants privileges to users belonging to the mapped group DNs. You can
    | authorize actions by the 'can:privilege' middleware or directly via
    | the Gate facade. If a privilege is granted, it cannot be denied.
    |
    */

    'grant' => [
        // 'privilege-identifier' => [ list of LDAP DNs ]

        'admin' => [
            'CN=Administrators,OU=Groups,DC=domain,DC=tld',
            'CN=Super-Users,OU=Groups,DC=domain,DC=tld',
        ],
    ],

    'deny' => [
        // 'privilege-identifier' => [ list of LDAP DNs ]
    ]
];
