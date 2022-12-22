<?php

namespace Database\Seeders;

use App\Models\Account\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission_list = [
            // directors
            [
                'permission_name' => 'Director card save (without approve)',
                'alias' => Config::get('common.permission.director.store'),
                'group' => Config::get('common.permission.director.group')
            ],
            [
                'permission_name' => 'Director card delete',
                'alias' => Config::get('common.permission.director.delete'),
                'group' => Config::get('common.permission.director.group')
            ],
            [
                'permission_name' => 'Director card save',
                'alias' => Config::get('common.permission.director.save'),
                'group' => Config::get('common.permission.director.group')
            ],
            [
                'permission_name' => 'Director card save (cards not belong to user)',
                'alias' => Config::get('common.permission.director.pre_save'),
                'group' => Config::get('common.permission.director.group')
            ],
            [
                'permission_name' => 'Director card accept & reject',
                'alias' => Config::get('common.permission.director.accept'),
                'group' => Config::get('common.permission.director.group')
            ],
            [
                'permission_name' => 'Director cards view',
                'alias' => Config::get('common.permission.director.view'),
                'group' => Config::get('common.permission.director.group')
            ],
            [
                'permission_name' => 'Director card access',
                'alias' => Config::get('common.permission.director.access'),
                'group' => Config::get('common.permission.director.group')
            ],
            [
                'permission_name' => 'Director card files download',
                'alias' => Config::get('common.permission.director.download'),
                'group' => Config::get('common.permission.director.group')
            ],
            // company
            [
                'permission_name' => 'Company card save (without approve)',
                'alias' => Config::get('common.permission.company.store'),
                'group' => Config::get('common.permission.company.group')
            ],
            [
                'permission_name' => 'Company card delete',
                'alias' => Config::get('common.permission.company.delete'),
                'group' => Config::get('common.permission.company.group')
            ],
            [
                'permission_name' => 'Company card save',
                'alias' => Config::get('common.permission.company.save'),
                'group' => Config::get('common.permission.company.group')
            ],
            [
                'permission_name' => 'Company card save (cards not belong to user)',
                'alias' => Config::get('common.permission.company.pre_save'),
                'group' => Config::get('common.permission.company.group')
            ],
            [
                'permission_name' => 'Company card accept & reject',
                'alias' => Config::get('common.permission.company.accept'),
                'group' => Config::get('common.permission.company.group')
            ],
            [
                'permission_name' => 'Company cards view',
                'alias' => Config::get('common.permission.company.view'),
                'group' => Config::get('common.permission.company.group')
            ],
            [
                'permission_name' => 'Company card access',
                'alias' => Config::get('common.permission.company.access'),
                'group' => Config::get('common.permission.company.group')
            ],
            [
                'permission_name' => 'Company card files download',
                'alias' => Config::get('common.permission.company.download'),
                'group' => Config::get('common.permission.company.group')
            ],
            // future websites
            [
                'permission_name' => 'Future website card save (without approve)',
                'alias' => Config::get('common.permission.future_website.store'),
                'group' => Config::get('common.permission.future_website.group')
            ],
            [
                'permission_name' => 'Future website card delete',
                'alias' => Config::get('common.permission.future_website.delete'),
                'group' => Config::get('common.permission.future_website.group')
            ],
            [
                'permission_name' => 'Future website card save',
                'alias' => Config::get('common.permission.future_website.save'),
                'group' => Config::get('common.permission.future_website.group')
            ],
            [
                'permission_name' => 'Future websites card accept & reject',
                'alias' => Config::get('common.permission.future_website.accept'),
                'group' => Config::get('common.permission.future_website.group')
            ],
            [
                'permission_name' => 'Future website card view',
                'alias' => Config::get('common.permission.future_website.view'),
                'group' => Config::get('common.permission.future_website.group')
            ],
            // virtual office
            [
                'permission_name' => 'Virtual office card save (without approve)',
                'alias' => Config::get('common.permission.virtual_office.store'),
                'group' => Config::get('common.permission.virtual_office.group')
            ],
            [
                'permission_name' => 'Virtual office card delete',
                'alias' => Config::get('common.permission.virtual_office.delete'),
                'group' => Config::get('common.permission.virtual_office.group')
            ],
            [
                'permission_name' => 'Virtual office card save',
                'alias' => Config::get('common.permission.virtual_office.save'),
                'group' => Config::get('common.permission.virtual_office.group')
            ],
            [
                'permission_name' => 'Virtual office card accept & reject',
                'alias' => Config::get('common.permission.virtual_office.accept'),
                'group' => Config::get('common.permission.virtual_office.group')
            ],
            [
                'permission_name' => 'Virtual office card view',
                'alias' => Config::get('common.permission.virtual_office.view'),
                'group' => Config::get('common.permission.virtual_office.group')
            ],
            // future company
            [
                'permission_name' => 'Future company card save (without approve)',
                'alias' => Config::get('common.permission.future_company.store'),
                'group' => Config::get('common.permission.future_company.group')
            ],
            [
                'permission_name' => 'Future company card delete',
                'alias' => Config::get('common.permission.future_company.delete'),
                'group' => Config::get('common.permission.future_company.group')
            ],
            [
                'permission_name' => 'Future company card save',
                'alias' => Config::get('common.permission.future_company.save'),
                'group' => Config::get('common.permission.future_company.group')
            ],
            [
                'permission_name' => 'Future company card accept & reject',
                'alias' => Config::get('common.permission.future_company.accept'),
                'group' => Config::get('common.permission.future_company.group')
            ],
            [
                'permission_name' => 'Future company card view',
                'alias' => Config::get('common.permission.future_company.view'),
                'group' => Config::get('common.permission.future_company.group')
            ],
            // chat
            [
                'permission_name' => 'Chat store', // add/update
                'alias' => Config::get('common.permission.chat.store'),
                'group' => Config::get('common.permission.chat.group')
            ],
            // task
            [
                'permission_name' => 'Task store', // add/update
                'alias' => Config::get('common.permission.task.store'),
                'group' => Config::get('common.permission.task.group')
            ]
        ];
        Permission::query()->update(['status' => Config::get('common.status.deleted')]);
        foreach($permission_list AS $key => $value):
            $permission = Permission::where('alias', $value['alias'])
                                        ->first();
            if ($permission==null){
                Permission::create($value);
            }else{
                $value['status'] = Config::get('common.status.actived');
                $permission->update($value);
            }
        endforeach;
    }
}
