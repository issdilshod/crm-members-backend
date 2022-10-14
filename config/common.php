<?php

return [
    'role' => [
        'headquarters' => 'headquarters',
        'production' => 'production',
        'admin' => 'admin',
        'design' => 'design',
        'finance' => 'finance',
    ],

    'permission' => [
        'director' => [
            'store' => 'director_store',
            'update' => 'director_update',
            'delete' => 'director_delete',
            'save' => 'director_save', // pending add/update
            'accept' => 'director_accept',
            'reject' => 'director_reject',
            'view' => 'director_view'
        ],
        'company' => [
            'store' => 'company_store',
            'update' => 'company_update',
            'delete' => 'company_delete',
            'save' => 'company_save', // pending add/update
            'accept' => 'company_accept',
            'reject' => 'company_reject',
            'view' => 'company_view'
        ],
    ],

    'session' => [
        'token_deadline' => 7, // expires in days
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
            'invite_via_telegram' => 'User invited via telegram',
            'register' => 'User sent request to register',
            'reject' => 'User register rejected',
            'accept' => 'User register accepted'
        ],
        'director' => [
            'add' => 'Director {name} card added',
            'update' => 'Director {name} card updated',
            'pending' => 'Director {name} card approval request sent',
            'pending_update' => 'Director {name} card update approval request sent',
            'reject' => 'Director {name} card rejected',
            'accept' => 'Director {name} card accepted',
        ],
        'company' => [
            'add' => 'Company {name} card added',
            'updated' => 'Company {name} card updated',
            'pending' => 'Company {name} card approval request sent',
            'pending_update' => 'Company {name} card update approval request sent',
            'reject' => 'Company {name} card rejected',
            'accept' => 'Company {name} card accepted',
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
            'director_reject' => 11,
            'director_accept' => 12,
            'company_pending' => 13,
            'company_pending_update' => 14,
            'company_reject' => 15,
            'company_accept' => 16,
            'user_register' => 17,
            'user_reject' => 18,
            'user_accept' => 19,
        ],

        'codes_link' => [
            1 => 'departments/user',
            2 => 'departments/user',
            3 => 'directors',
            4 => 'directors',
            5 => 'companies',
            6 => 'companies',
            7 => 'departments',
            8 => 'departments',
            9 => 'directors',
            10 => 'directors',
            11 => 'directors',
            12 => 'directors',
            13 => 'companies',
            14 => 'companies',
            15 => 'companies',
            16 => 'companies',
            17 => 'departments/user',
            18 => '',
            19 => 'departments/user',
        ],
    ],

    'invite' => [
        'telegram' => 1,
        'email' => 2,
    ],

    'errors' => [
        'exsist' => 'Data exsist.',
        'invalid_login' => 'Invalid username or password',
    ],
];
