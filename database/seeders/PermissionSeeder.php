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
                'alias' => Config::get('common.permission.director.store')
            ],
            [
                'permission_name' => 'Director card delete',
                'alias' => Config::get('common.permission.director.delete')
            ],
            [
                'permission_name' => 'Director card save',
                'alias' => Config::get('common.permission.director.save')
            ],
            [
                'permission_name' => 'Director card save (cards not belong to user)',
                'alias' => Config::get('common.permission.director.pre_save')
            ],
            [
                'permission_name' => 'Director card accept & reject',
                'alias' => Config::get('common.permission.director.accept')
            ],
            [
                'permission_name' => 'Director cards view',
                'alias' => Config::get('common.permission.director.view')
            ],
            [
                'permission_name' => 'Director card access',
                'alias' => Config::get('common.permission.director.access')
            ],
            [
                'permission_name' => 'Director card files download',
                'alias' => Config::get('common.permission.director.download')
            ],
            // company
            [
                'permission_name' => 'Company card save (without approve)',
                'alias' => Config::get('common.permission.company.store')
            ],
            [
                'permission_name' => 'Company card delete',
                'alias' => Config::get('common.permission.company.delete')
            ],
            [
                'permission_name' => 'Company card save',
                'alias' => Config::get('common.permission.company.save')
            ],
            [
                'permission_name' => 'Company card save (cards not belong to user)',
                'alias' => Config::get('common.permission.company.pre_save')
            ],
            [
                'permission_name' => 'Company card accept & reject',
                'alias' => Config::get('common.permission.company.accept')
            ],
            [
                'permission_name' => 'Company cards view',
                'alias' => Config::get('common.permission.company.view')
            ],
            [
                'permission_name' => 'Company card access',
                'alias' => Config::get('common.permission.company.access')
            ],
            [
                'permission_name' => 'Company card files download',
                'alias' => Config::get('common.permission.company.download')
            ],
            // future websites
            [
                'permission_name' => 'Future website card save (without approve)',
                'alias' => Config::get('common.permission.websites_future.store')
            ],
            [
                'permission_name' => 'Future website card delete',
                'alias' => Config::get('common.permission.websites_future.delete')
            ],
            [
                'permission_name' => 'Future website card save',
                'alias' => Config::get('common.permission.websites_future.save')
            ],
            [
                'permission_name' => 'Future websites card accept & reject',
                'alias' => Config::get('common.permission.websites_future.accept')
            ],
            [
                'permission_name' => 'Future website card view',
                'alias' => Config::get('common.permission.websites_future.view')
            ],
            // virtual office
            [
                'permission_name' => 'Virtual office card save (without approve)',
                'alias' => Config::get('common.permission.virtual_office.store')
            ],
            [
                'permission_name' => 'Virtual office card delete',
                'alias' => Config::get('common.permission.virtual_office.delete')
            ],
            [
                'permission_name' => 'Virtual office card save',
                'alias' => Config::get('common.permission.virtual_office.save')
            ],
            [
                'permission_name' => 'Virtual office card accept & reject',
                'alias' => Config::get('common.permission.virtual_office.accept')
            ],
            [
                'permission_name' => 'Virtual office card view',
                'alias' => Config::get('common.permission.virtual_office.view')
            ],
            // future company
            [
                'permission_name' => 'Future company card save (without approve)',
                'alias' => Config::get('common.permission.future_company.store')
            ],
            [
                'permission_name' => 'Future company card delete',
                'alias' => Config::get('common.permission.future_company.delete')
            ],
            [
                'permission_name' => 'Future company card save',
                'alias' => Config::get('common.permission.future_company.save')
            ],
            [
                'permission_name' => 'Future company card accept & reject',
                'alias' => Config::get('common.permission.future_company.accept')
            ],
            [
                'permission_name' => 'Future company card view',
                'alias' => Config::get('common.permission.future_company.view')
            ],
            // chat
            [
                'permission_name' => 'Chat store', // add/update
                'alias' => Config::get('common.permission.chat.store')
            ],
            // task
            [
                'permission_name' => 'Task store', // add/update
                'alias' => Config::get('common.permission.task.store')
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
