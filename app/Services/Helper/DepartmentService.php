<?php

namespace App\Services\Helper;

use App\Models\Helper\Department;
use Illuminate\Support\Facades\Config;

class DepartmentService {

    /**
     * Return list of departments
     * 
     * @return Department
     */
    public function getDepartments()
    {
        $departments = Department::orderBy('sort', 'ASC')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->get();
        return $departments;
    }

    /**
     * Detele deprtment
     * 
     * @return void
     */
    public function deleteDepartment(Department $department)
    {
        $department->update(['status' => Config::get('common.status.deleted')]);
    }
}