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
