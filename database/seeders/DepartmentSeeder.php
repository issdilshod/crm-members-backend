<?php

namespace Database\Seeders;

use App\Models\Helper\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = Department::where('status', 1)->first();
        if ($departments==null){ // if database empty
            $department_list = ['Headquarters', 'Production', 'Admin', 'Design', 'Finance'];
            foreach($department_list AS $key => $value):
                Department::create(['department_name' => $value, 'sort' => ($key+1)]);
            endforeach;
        }
    }
}
