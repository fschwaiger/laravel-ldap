<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LDAP Server Connection
    |--------------------------------------------------------------------------
    |
    | Specify the connection parameters to your server. Override LDAP_HOST,
    | LDAP_USERNAME and LDAP_PASSWORD from your .env file. Setup your AD
    | general configuration here.
    |
    */

    // This suffix is appended to the username on bind operations.
    'domain_suffix' => '@domain.tld',

    // These options will be passed directly to the underlying symfony component.
    // Read https://symfony.com/doc/3.2/components/ldap.html for all options. 
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

    // Groups with DNs matching the following patterns will not be imported.
    'group_ignore_pattern' => '/$no groups are ignored for now/',

];