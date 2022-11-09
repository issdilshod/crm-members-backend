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
            /*[
                'permission_name' => 'Director update',
                'alias' => Config::get('common.permission.director.update')
            ],*/
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
            /*[
                'permission_name' => 'Director reject',
                'alias' => Config::get('common.permission.director.reject')
            ],*/
            [
                'permission_name' => 'Director card view',
                'alias' => Config::get('common.permission.director.view')
            ],
            // company
            [
                'permission_name' => 'Company card save (without approve)',
                'alias' => Config::get('common.permission.company.store')
            ],
            /*[
                'permission_name' => 'Company update',
                'alias' => Config::get('common.permission.company.update')
            ],*/
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
            /*[
                'permission_name' => 'Company reject',
                'alias' => Config::get('common.permission.company.reject')
            ],*/
            [
                'permission_name' => 'Company card view',
                'alias' => Config::get('common.permission.company.view')
            ],
            // future websites
            [
                'permission_name' => 'Future website card save (without approve)',
                'alias' => Config::get('common.permission.websites_future.store')
            ],
            /*[
                'permission_name' => 'Future websites update',
                'alias' => Config::get('common.permission.websites_future.update')
            ],*/
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
            /*[
                'permission_name' => 'Future websites reject',
                'alias' => Config::get('common.permission.websites_future.reject')
            ],*/
            [
                'permission_name' => 'Future website card view',
                'alias' => Config::get('common.permission.websites_future.view')
            ],
            // virtual office
            [
                'permission_name' => 'Virtual office card save (without approve)',
                'alias' => Config::get('common.permission.virtual_office.store')
            ],
            /*[
                'permission_name' => 'Virtual Office update',
                'alias' => Config::get('common.permission.virtual_office.update')
            ],*/
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
            /*[
                'permission_name' => 'Virtual office reject',
                'alias' => Config::get('common.permission.virtual_office.reject')
            ],*/
            [
                'permission_name' => 'Virtual office card view',
                'alias' => Config::get('common.permission.virtual_office.view')
            ],
            // future company
            [
                'permission_name' => 'Future company card save (without approve)',
                'alias' => Config::get('common.permission.future_company.store')
            ],
            /*[
                'permission_name' => 'Future Company update',
                'alias' => Config::get('common.permission.future_company.update')
            ],*/
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
            /*[
                'permission_name' => 'Future company reject',
                'alias' => Config::get('common.permission.future_company.reject')
            ],*/
            [
                'permission_name' => 'Future company card view',
                'alias' => Config::get('common.permission.future_company.view')
            ],
            // chat
            [
                'permission_name' => 'Chat store', // add/update
                'alias' => Config::get('common.permission.chat.store')
            ],
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
