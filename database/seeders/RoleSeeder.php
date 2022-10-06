<?php

namespace Database\Seeders;

use App\Models\Helper\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = Role::where('status', 1)->first();
        if ($roles==null){ // if database empty
            $role_list = ['Headquarters', 'Production', 'Admin', 'Design', 'Finance'];
            $role_alias = ['headquarters', 'production', 'admin', 'design', 'finance'];
            foreach($role_list AS $key => $value):
                Role::create(['role_name' => $value, 'alias' => $role_alias[$key], 'sort' => ($key+1)]);
            endforeach;
        }
    }
}
