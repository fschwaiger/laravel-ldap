<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LDAP Server Connection
    |--------------------------------------------------------------------------
    |
    | Specify the connection parameters to your server. Override LDAP_HOST,
    | LDAP_USERNAME and LDAP_PASSWORD from your .env file. Setup your AD
    | specific configuration here.
    |
    */

    // This suffix is appended to the username on bind operations.

    'domain_suffix' => '@domain.tld',

    // These are the connection settings for the FSMB LDAP

    'options' => [

        'host' => env('LDAP_HOST', 'adr.domain.tld'),
        'encryption' => 'ssl',

    ],

    // Service user credentials to sync AD data.

    'bind_credentials' => [

        'username' => env('LDAP_USERNAME', 'binduser'),
        'password' => env('LDAP_PASSWORD', ''),
        
    ],

    // These groups will be searched for user records.

    'user_folder_dns' => [

        'OU=Users,DC=domain,DC=tld',
        'OU=Admins,DC=domain,DC=tld',

    ],

    // All imported groups need to be in those folders.

    'group_folder_dns' => [

        'OU=Groups,DC=domain,DC=tld',

    ],
];