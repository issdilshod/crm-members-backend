<?php

namespace App\Services;

use App\Models\API\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class CompanyService {

    /**
     * Return 20 last added companies
     * 
     * @return Company
     */
    public function getCompanies()
    {
        $companies = Company::orderBy('created_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->paginate(20);
        return $companies;
    }

    /**
     * Delete company by id
     * 
     * @return void
     */
    public function deleteCompany(Company $company)
    {
        $company->update(['status' => Config::get('common.status.deleted')]);
    }

    /**
     * Return result of search Company
     * 
     * @return Company
     */
    public function searchCompany($value)
    {
        $companies = Company::orderBy('created_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->where('legal_name', 'like', '%'.$value.'%')
                                ->paginate(20);
        return $companies;
    }

}