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
            $department_list = [
                [
                    'department_name' => 'Headquarters',
                    'alias' => 'headquarters'
                ],
                [
                    'department_name' => 'Production',
                    'alias' => 'production'
                ],
                [
                    'department_name' => 'Admin',
                    'alias' => 'admin'
                ],
                [
                    'department_name' => 'Design',
                    'alias' => 'design'
                ],
                [
                    'department_name' => 'Finance',
                    'alias' => 'finance'
                ],
            ];
            foreach($department_list AS $key => $value):
                $value['sort'] = ($key+1);
                Department::create($value);
            endforeach;
        }
    }
}
