<?php

return [
    'role' => [
        'headquarters' => 'headquarters',
        'production' => 'production',
        'admin' => 'admin',
        'design' => 'design',
        'finance' => 'finance',
    ],

    'status' => [
        'deleted' => 0,
        'actived' => 1,
        'pending' => 2,
        'rejected' => 3,
        'saw' => 4,
        'open' => 5,
        'block' => 6
    ],

    'activity' => [
        'logged' => 'User logged in',
        'logout' => 'User logged out',
        'user' => [
            'add' => 'User added',
            'update' => 'User updated',
            'invite_via_email' => 'User invited via email',
            'invite_via_telegram' => 'User invited via telegram'
        ],
        'director' => [
            'add' => 'Director added',
            'update' => 'Director updated',
            'pending' => 'Director approval request sent',
            'pending_update' => 'Director update approval request sent'
        ],
        'company' => [
            'add' => 'Company added',
            'updated' => 'Company updated',
        ],

        'codes' => [
            'user_add' => 1,
            'user_update' => 2,
            'director_add' => 3,
            'director_update' => 4,
            'company_add' => 5,
            'company_update' => 6,
            'user_invite_via_email' => 7,
            'user_invite_via_telegram' => 8,
            'director_pending' => 9,
            'director_pending_update' => 10,
        ],

        'codes_link' => [
            1 => 'departments',
            2 => 'departments',
            3 => 'directors',
            4 => 'directors',
            5 => 'companies',
            6 => 'companies',
            7 => 'departments',
            8 => 'departments',
            9 => 'directors',
            10 => 'directors'
        ],
    ],

    'invite' => [
        'telegram' => 1,
        'email' => 2,
    ],

    'errors' => [
        'exsist' => 'Data exsist.'
    ],
];
