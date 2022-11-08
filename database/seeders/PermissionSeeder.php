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
                'permission_name' => 'Director store',
                'alias' => Config::get('common.permission.director.store')
            ],
            [
                'permission_name' => 'Director update',
                'alias' => Config::get('common.permission.director.update')
            ],
            [
                'permission_name' => 'Director delete',
                'alias' => Config::get('common.permission.director.delete')
            ],
            [
                'permission_name' => 'Director save',
                'alias' => Config::get('common.permission.director.save')
            ],
            [
                'permission_name' => 'Director save (cards not belong to user)',
                'alias' => Config::get('common.permission.director.pre_save')
            ],
            [
                'permission_name' => 'Director accept',
                'alias' => Config::get('common.permission.director.accept')
            ],
            [
                'permission_name' => 'Director reject',
                'alias' => Config::get('common.permission.director.reject')
            ],
            [
                'permission_name' => 'Director view',
                'alias' => Config::get('common.permission.director.view')
            ],
            // company
            [
                'permission_name' => 'Company store',
                'alias' => Config::get('common.permission.company.store')
            ],
            [
                'permission_name' => 'Company update',
                'alias' => Config::get('common.permission.company.update')
            ],
            [
                'permission_name' => 'Company delete',
                'alias' => Config::get('common.permission.company.delete')
            ],
            [
                'permission_name' => 'Company save',
                'alias' => Config::get('common.permission.company.save')
            ],
            [
                'permission_name' => 'Company save (cards not belong to user)',
                'alias' => Config::get('common.permission.company.pre_save')
            ],
            [
                'permission_name' => 'Company accept',
                'alias' => Config::get('common.permission.company.accept')
            ],
            [
                'permission_name' => 'Company reject',
                'alias' => Config::get('common.permission.company.reject')
            ],
            [
                'permission_name' => 'Company view',
                'alias' => Config::get('common.permission.company.view')
            ],
            // future websites
            [
                'permission_name' => 'Future websites store',
                'alias' => Config::get('common.permission.websites_future.store')
            ],
            [
                'permission_name' => 'Future websites update',
                'alias' => Config::get('common.permission.websites_future.update')
            ],
            [
                'permission_name' => 'Future websites delete',
                'alias' => Config::get('common.permission.websites_future.delete')
            ],
            [
                'permission_name' => 'Future websites save',
                'alias' => Config::get('common.permission.websites_future.save')
            ],
            [
                'permission_name' => 'Future websites accept',
                'alias' => Config::get('common.permission.websites_future.accept')
            ],
            [
                'permission_name' => 'Future websites reject',
                'alias' => Config::get('common.permission.websites_future.reject')
            ],
            [
                'permission_name' => 'Future websites view',
                'alias' => Config::get('common.permission.websites_future.view')
            ],
            // virtual office
            [
                'permission_name' => 'Virtual Office store',
                'alias' => Config::get('common.permission.virtual_office.store')
            ],
            [
                'permission_name' => 'Virtual Office update',
                'alias' => Config::get('common.permission.virtual_office.update')
            ],
            [
                'permission_name' => 'Virtual Office delete',
                'alias' => Config::get('common.permission.virtual_office.delete')
            ],
            [
                'permission_name' => 'Virtual Office save',
                'alias' => Config::get('common.permission.virtual_office.save')
            ],
            [
                'permission_name' => 'Virtual Office accept',
                'alias' => Config::get('common.permission.virtual_office.accept')
            ],
            [
                'permission_name' => 'Virtual Office reject',
                'alias' => Config::get('common.permission.virtual_office.reject')
            ],
            [
                'permission_name' => 'Virtual Office view',
                'alias' => Config::get('common.permission.virtual_office.view')
            ],
            // future company
            [
                'permission_name' => 'Future Company store',
                'alias' => Config::get('common.permission.future_company.store')
            ],
            [
                'permission_name' => 'Future Company update',
                'alias' => Config::get('common.permission.future_company.update')
            ],
            [
                'permission_name' => 'Future Company delete',
                'alias' => Config::get('common.permission.future_company.delete')
            ],
            [
                'permission_name' => 'Future Company save',
                'alias' => Config::get('common.permission.future_company.save')
            ],
            [
                'permission_name' => 'Future Company accept',
                'alias' => Config::get('common.permission.future_company.accept')
            ],
            [
                'permission_name' => 'Future Company reject',
                'alias' => Config::get('common.permission.future_company.reject')
            ],
            [
                'permission_name' => 'Future Company view',
                'alias' => Config::get('common.permission.future_company.view')
            ],
            // chat
            [
                'permission_name' => 'Chat store',
                'alias' => Config::get('common.permission.chat.store')
            ],
        ];
        foreach($permission_list AS $key => $value):
            $permission = Permission::where('status', Config::get('common.status.actived'))
                                        ->where('alias', $value['alias'])
                                        ->first();
            if ($permission==null){
                Permission::create($value);
            }
        endforeach;
    }
}
