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
            'group' => 'director', // group name
            'store' => 'director_store', // add/update
            'delete' => 'director_delete',
            'save' => 'director_save', // pending add/update
            'pre_save' => 'director_pre_save',
            'accept' => 'director_accept', // accept/reject
            'view' => 'director_view',
            'access' => 'director_access',
            'download' => 'director_download'
        ],
        'company' => [
            'group' => 'company', // group name
            'store' => 'company_store', // add/update
            'delete' => 'company_delete',
            'save' => 'company_save', // pending add/update
            'pre_save' => 'company_pre_save',
            'accept' => 'company_accept', // accept/reject
            'view' => 'company_view',
            'access' => 'company_access',
            'download' => 'company_download'
        ],
        'future_website' => [
            'group' => 'future_website', // group name
            'store' => 'future_website_store', // add/update
            'delete' => 'future_website_delete',
            'save' => 'future_website_save', // pending add/update
            'accept' => 'future_website_accept', // accept/reject
            'view' => 'future_website_view'
        ],
        'virtual_office' => [
            'group' => 'virtual_office', // group name
            'store' => 'virtual_office_store', // add/update
            'delete' => 'virtual_office_delete',
            'save' => 'virtual_office_save', // pending add/update
            'accept' => 'virtual_office_accept', // accept/reject
            'view' => 'virtual_office_view'
        ],
        'future_company' => [
            'group' => 'future_company', // group name
            'store' => 'future_company_store', // add/update
            'delete' => 'future_company_delete',
            'save' => 'future_company_save', // pending add/update
            'accept' => 'future_company_accept', // accept/reject
            'view' => 'future_company_view'
        ],
        'chat' => [
            'group' => 'chat', // group name
            'store' => 'chat_store' // add/update
        ],
        'task' => [
            'group' => 'task', // group name
            'store' => 'task_store' // add/update
        ],
        'contact' => [
            'group' => 'contact', // group name
            'store' => 'contact_store', // add/update
            'delete' => 'contact_delete',
            'save' => 'contact_save', // pending add/update
            'accept' => 'contact_accept', // accept/reject
            'view' => 'contact_view'
        ],
    ],

    'chat' => [
        'type' => [
            'private' => 1,
            'group' => 2
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

    'task_progress' => [
        'todo' => 1,
        'doing' => 2,
        'approve' => 3,
        'rejected' => 4,
        'completed' => 5
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
            'override' => 'Director {name} card override',
        ],
        'company' => [
            'add' => 'Company {name} card added',
            'updated' => 'Company {name} card updated',
            'pending' => 'Company {name} card approval request sent',
            'pending_update' => 'Company {name} card update approval request sent',
            'reject' => 'Company {name} card rejected',
            'accept' => 'Company {name} card accepted',
            'override' => 'Company {name} card override',
        ],
        'future_website' => [
            'add' => 'Future website {link} card added',
            'updated' => 'Future website {link} card updated',
            'pending' => 'Future website {link} card approval request sent',
            'pending_update' => 'Future website {link} card update approval request sent',
            'reject' => 'Future website {link} card rejected',
            'accept' => 'Future website {link} card accepted',
        ],
        'virtual_office' => [
            'add' => 'Virtual Office {name} card added',
            'updated' => 'Virtual Office {name} card updated',
            'pending' => 'Virtual Office {name} card approval request sent',
            'pending_update' => 'Virtual Office {name} card update approval request sent',
            'reject' => 'Virtual Office {name} card rejected',
            'accept' => 'Virtual Office {name} card accepted',
        ],
        'future_company' => [
            'add' => 'Future company {name} card added',
            'updated' => 'Future company {name} card updated',
            'pending' => 'Future company {name} card approval request sent',
            'pending_update' => 'Future company {name} card update approval request sent',
            'reject' => 'Future company {name} card rejected',
            'accept' => 'Future company {name} card accepted',
        ],
        'chat' => [
            'add' => 'Chat {name} created',
            'updated' => 'Chat {name} updated'
        ],
        'task' => [
            'add' => 'Task {name} created',
            'update' => 'Task {name} updated',
            'to_progress' => 'Task {name} submitted', 
            'approve' =>  'Task {name} approved',
            'reject' => 'Task {name} rejected',
        ],
        'contact' => [
            'add' => 'Contact {name} card added',
            'updated' => 'Contact {name} card updated',
            'pending' => 'Contact {name} card approval request sent',
            'pending_update' => 'Contact {name} card update approval request sent',
            'reject' => 'Contact {name} card rejected',
            'accept' => 'Contact {name} card accepted',
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

            'future_website_add' => 20,
            'future_website_update' => 21,
            'future_website_pending' => 22,
            'future_website_pending_update' => 23,
            'future_website_accept' => 24,
            'future_website_reject' => 25,

            'virtual_office_add' => 26,
            'virtual_office_update' => 27,
            'virtual_office_pending' => 28,
            'virtual_office_pending_update' => 29,
            'virtual_office_accept' => 30,
            'virtual_office_reject' => 31,

            'future_company_add' => 32,
            'future_company_update' => 33,
            'future_company_pending' => 34,
            'future_company_pending_update' => 35,
            'future_company_accept' => 36,
            'future_company_reject' => 37,

            'chat_add' => 38,
            'chat_update' => 39,

            'task_add' => 40,
            'task_update' => 41,
            'task_to_progress' => 42,
            'task_reject' => 43,
            'task_approve' => 44,

            'contact_add' => 45,
            'contact_update' => 46,
            'contact_pending' => 47,
            'contact_pending_update' => 48,
            'contact_accept' => 49,
            'contact_reject' => 50,
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

            20 => 'future-websites',
            21 => 'future-websites',
            22 => 'future-websites',
            23 => 'future-websites',
            24 => 'future-websites',
            25 => 'future-websites',

            26 => 'virtual-offices',
            27 => 'virtual-offices',
            28 => 'virtual-offices',
            29 => 'virtual-offices',
            30 => 'virtual-offices',
            31 => 'virtual-offices',

            32 => 'future-companies',
            33 => 'future-companies',
            34 => 'future-companies',
            35 => 'future-companies',
            36 => 'future-companies',
            37 => 'future-companies',

            38 => '?section=chat&part=chats&uuid=',
            39 => '?section=chat&part=chats&uuid=',

            40 => '?section=task&uuid=',
            41 => '?section=task&uuid=',
            42 => '?section=task&uuid=',
            43 => '?section=task&uuid=',
            44 => '?section=task&uuid=',

            45 => 'contacts',
            46 => 'contacts',
            47 => 'contacts',
            48 => 'contacts',
            49 => 'contacts',
            50 => 'contacts',
        ],
    ],

    'invite' => [
        'telegram' => 1,
        'email' => 2,
    ],

    'errors' => [
        'exsist' => 'Data exists',
        'invalid_login' => 'Invalid username or password',
    ],
];
