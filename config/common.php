<?php

return [
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
        ]
    ],

    'invite' => [
        'telegram' => 1,
        'email' => 2,
    ],
];
