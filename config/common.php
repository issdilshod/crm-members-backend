<?php

return [
    'role' => [
        'general' => 'ab1c1795-0f45-4d05-a7db-d1bdcce1ba0d',
        'headquarters' => '88690813-1dc6-49ee-9496-55af93cc4b10',
        'product' => '3e191b6b-47a2-4b91-98ce-09db3ed147ae',
        'admin' => 'c92df6d5-b7e5-455f-801e-e1c8146cdb2b',
        'design' => 'e5972007-3aad-4ee1-9619-2a9e30c4fd9c',
        'finance' => '2b768d35-8624-429a-9783-7c506a391dd2',
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
        ],
    ],

    'invite' => [
        'telegram' => 1,
        'email' => 2,
    ],
];
