<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Policies\PermissionPolicy;
use App\Services\Company\CompanyIncorporationService;
use App\Services\Company\CompanyService;
use App\Services\Helper\AddressService;
use App\Services\Helper\BankAccountService;
use App\Services\Helper\EmailService;
use App\Services\Helper\FileService;
use App\Services\Helper\RegisterAgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class CompanyController extends Controller
{

    private $companyService;
    private $emailService;
    private $addressService;
    private $bankAccountService;
    private $fileService;
    private $registerAgentService;
    private $companyIncorporationService;

    public function __construct()
    {
        $this->companyService = new CompanyService();
        $this->emailService = new EmailService();
        $this->addressService = new AddressService();
        $this->bankAccountService = new BankAccountService();
        $this->fileService = new FileService();
        $this->registerAgentService = new RegisterAgentService();
        $this->companyIncorporationService = new CompanyIncorporationService();
    }

    /**     @OA\GET(
      *         path="/api/company",
      *         operationId="list_company",
      *         tags={"Company"},
      *         summary="List of company",
      *         description="List of company",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $companies = $this->companyService->all();
        return $companies;
    }

    /**     @OA\POST(
      *         path="/api/company",
      *         operationId="post_company",
      *         tags={"Company"},
      *         summary="Add company (not working on swagger)",
      *         description="Add company",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"legal_name", "director_uuid", "ein", "db_report_number"},
      *                         
      *                         @OA\Property(property="legal_name", type="text"),
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="director_uuid", type="text"),
      *                         @OA\Property(property="incorporation_date", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_name", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_name", type="text"),
      *                         @OA\Property(property="ein", type="text"),
      *
      *                         @OA\Property(property="business_number", type="text"),
      *                         @OA\Property(property="business_number_type", type="text"),
      *                         @OA\Property(property="voip_provider", type="text"),
      *                         @OA\Property(property="voip_login", type="text"),
      *                         @OA\Property(property="voip_password", type="text"),
      *                         @OA\Property(property="business_mobile_number", type="text"),
      *                         @OA\Property(property="business_mobile_number_type", type="text"),
      *
      *                         @OA\Property(property="business_mobile_provider", type="text"),
      *                         @OA\Property(property="business_mobile_website", type="text"),
      *                         @OA\Property(property="business_mobile_login", type="text"),
      *                         @OA\Property(property="business_mobile_password", type="text"),
      *
      *                         @OA\Property(property="card_on_file", type="text"),
      *                         @OA\Property(property="card_last_four_digit", type="text"),
      *                         @OA\Property(property="card_holder_name", type="text"),
      *
      *                         @OA\Property(property="website", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
      *
      *                         @OA\Property(property="bank_account[]", type="text"),
      *
      *                         @OA\Property(property="register_agents[]", type="text"),
      *
      *                         @OA\Property(property="incorporations[]", type="text"),
      *
      *                         @OA\Property(property="files[]", type="text"),
      *
      *                         @OA\Property(property="files_to_delete[]", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function store(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'legal_name' => 'required',
            'sic_code_uuid' => '',
            'director_uuid' => 'required',
            'incorporation_date' => '',
            'incorporation_state_uuid' => '',
            'incorporation_state_name' => '',
            'doing_business_in_state_uuid' => '',
            'doing_business_in_state_name' => '',
            'ein' => 'required',
            
            // numbers
            'business_number' => '',
            'business_number_type' => '',
            'voip_provider' => '',
            'voip_login' => '',
            'voip_password' => '',
            'business_mobile_number' => '',
            'business_mobile_number_type' => '',

            'business_mobile_provider' => '',
            'business_mobile_website' => '',
            'business_mobile_login' => '',
            'business_mobile_password' => '',

            'card_on_file' => '',
            'card_last_four_digit' => '',
            'card_holder_name' => '',

            'website' => '',
            'db_report_number' => 'required',

            // addresses
            'addresses' => 'array',

            // emails
            'emails' => 'array',

            // bank account
            'bank_account' => 'array',

            // register agents
            'register_agents' => 'array',

            // incorporations
            'incorporations' => 'array',

            // files
            'files' => 'array',
            'files_to_delete' => 'array',

            'user_uuid' => 'string'
        ]);

        // check
        $check = [];

        // emails check
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $tmpCheck = $this->emailService->check($value, $key, '', 'companies');
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        // addresses check
        if (isset($validated['addresses'])){
            foreach($validated['addresses'] AS $key => $value):
                $tmpCheck = $this->addressService->check($value, $key);
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        // bank account check
        if (isset($validated['bank_account'])){
            $tmpCheck = $this->bankAccountService->check($validated['bank_account']);
            $check = array_merge($check, $tmpCheck);
        }
        
        // company check
        $tmpCheck = $this->companyService->check($validated);
        $check = array_merge($check, $tmpCheck);

        if (count($check)>0){
            return response()->json(['data' => $check], 409);
        }

        // create
        $company = $this->companyService->create($validated);

        // emails
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->emailService->save($value);
            endforeach;
        }

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->addressService->save($value);
            endforeach;
        }

        // addresses to delete
        if (isset($validated['addresses_to_delete'])){
            foreach ($validated['addresses_to_delete'] AS $key => $value):
                $this->addressService->delete($value);
            endforeach;
        }

        // bank account
        $validated['bank_account']['entity_uuid'] = $company->uuid;
        $this->bankAccountService->save($validated['bank_account']);

        // register agent
        if (isset($validated['register_agents'])){
            foreach ($validated['register_agents'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->registerAgentService->save($value);
            endforeach;
        }

        // incorporations
        if (isset($validated['incorporations'])){
            foreach ($validated['incorporations'] as $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->companyIncorporationService->save($value);
            endforeach;
        }

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files to upload
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $company->uuid], $value['uuid']);
            endforeach;
        }

        return $company;
    }

    /**     @OA\GET(
      *         path="/api/company/{uuid}",
      *         operationId="get_company",
      *         tags={"Company"},
      *         summary="Get company",
      *         description="Get company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Request $request, Company $company)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.access'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $company = $this->companyService->one($company);
        return $company;
    }

    /**     @OA\PUT(
      *         path="/api/company",
      *         operationId="update_company",
      *         tags={"Company"},
      *         summary="Update company (not working on swagger)",
      *         description="Update company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"legal_name", "director_uuid", "ein", "db_report_number"},
      *                         @OA\Property(property="legal_name", type="text"),
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="director_uuid", type="text"),
      *                         @OA\Property(property="incorporation_date", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_name", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_name", type="text"),
      *                         @OA\Property(property="ein", type="text"),
      *                         
      *                         @OA\Property(property="business_number", type="text"),
      *                         @OA\Property(property="business_number_type", type="text"),
      *                         @OA\Property(property="voip_provider", type="text"),
      *                         @OA\Property(property="voip_login", type="text"),
      *                         @OA\Property(property="voip_password", type="text"),
      *                         @OA\Property(property="business_mobile_number", type="text"),
      *                         @OA\Property(property="business_mobile_number_type", type="text"),
      *            
      *                         @OA\Property(property="business_mobile_provider", type="text"),
      *                         @OA\Property(property="business_mobile_website", type="text"),
      *                         @OA\Property(property="business_mobile_login", type="text"),
      *                         @OA\Property(property="business_mobile_password", type="text"),
      *
      *                         @OA\Property(property="card_on_file", type="text"),
      *                         @OA\Property(property="card_last_four_digit", type="text"),
      *                         @OA\Property(property="card_holder_name", type="text"),
      *
      *                         @OA\Property(property="website", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
      *                         @OA\Property(property="address_to_delete", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
      *                         @OA\Property(property="emails_to_delete[]", type="text"),
      *
      *                         @OA\Property(property="bank_account[]", type="text"),
      *
      *                         @OA\Property(property="register_agents[]", type="text"),
      *
      *                         @OA\Property(property="incorporations[]", type="text"),
      *
      *                         @OA\Property(property="files[]", type="text"),
      *
      *                         @OA\Property(property="files_to_delete[]", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function update(Request $request, Company $company)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'legal_name' => 'required',
            'sic_code_uuid' => '',
            'director_uuid' => 'required',
            'incorporation_date' => '',
            'incorporation_state_uuid' => '',
            'incorporation_state_name' => '',
            'doing_business_in_state_uuid' => '',
            'doing_business_in_state_name' => '',
            'ein' => 'required',
            
            // numbers
            'business_number' => '',
            'business_number_type' => '',
            'voip_provider' => '',
            'voip_login' => '',
            'voip_password' => '',
            'business_mobile_number' => '',
            'business_mobile_number_type' => '',
            
            'business_mobile_provider' => '',
            'business_mobile_website' => '',
            'business_mobile_login' => '',
            'business_mobile_password' => '',

            'card_on_file' => '',
            'card_last_four_digit' => '',
            'card_holder_name' => '',

            'website' => '',
            'db_report_number' => 'required',

            // addresses
            'addresses' => 'array',
            'address_to_delete' => '',

            // emails
            'emails' => 'array',
            'emails_to_delete' => 'array',

            // bank account
            'bank_account' => 'array',

            // register agent
            'register_agents' => 'array',

            // incorporations
            'incorporations' => 'array',

            // files to delete
            'files' => 'array',
            'files_to_delete' => 'array'
        ]);

        // check
        $check = [];

        // emails check
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $tmpCheck = $this->emailService->check($value, $key, $company->uuid, 'companies');
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        // addresses check
        if (isset($validated['addresses'])){
            foreach($validated['addresses'] AS $key => $value):
                $tmpCheck = $this->addressService->check($value, $key, $company->uuid);
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        // bank account check
        if (isset($validated['bank_account'])){
            $tmpCheck = $this->bankAccountService->check($validated['bank_account'], $company->uuid);
            $check = array_merge($check, $tmpCheck);
        }
        
        // company check
        $tmpCheck = $this->companyService->check($validated, $company->uuid);
        $check = array_merge($check, $tmpCheck);
        
        if (count($check)>0){
            return response()->json(['data' => $check], 409);
        }

        // update
        $company = $this->companyService->update($company, $validated, $request->user_uuid);

        // emails to delete
        if (isset($validated['emails_to_delete'])){
            foreach($validated['emails_to_delete'] AS $key => $value):
                $this->emailService->delete($value);
            endforeach;
        }

        // emails
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $value['status'] = Config::get('common.status.actived');
                $this->emailService->save($value);
            endforeach;
        }

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->addressService->save($value);
            endforeach;
        }

        // address to delete
        if (isset($validated['address_to_delete'])){
            $this->addressService->delete($validated['address_to_delete']);
        }

        // register agent
        if (isset($validated['register_agents'])){
            foreach ($validated['register_agents'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->registerAgentService->save($value);
            endforeach;
        }

        // incorporations
        if (isset($validated['incorporations'])){
            foreach ($validated['incorporations'] as $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->companyIncorporationService->save($value);
            endforeach;
        }

        // bank account
        $validated['bank_account']['entity_uuid'] = $company->uuid;
        $this->bankAccountService->save($validated['bank_account']);

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files to upload
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $company->uuid], $value['uuid']);
            endforeach;
        }

        return $company;
    }

    /**     @OA\DELETE(
      *         path="/api/company/{uuid}",
      *         operationId="delete_company",
      *         tags={"Company"},
      *         summary="Delete company",
      *         description="Delete company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(Request $request, Company $company)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.delete'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $this->companyService->delete($company);
    }

    /**     @OA\POST(
      *         path="/api/comapny-pending",
      *         operationId="pending_company",
      *         tags={"Company"},
      *         summary="Pending company (not working on swagger)",
      *         description="Pending company",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"legal_name", "director_uuid", "ein", "db_report_number"},
      *                         @OA\Property(property="legal_name", type="text"),
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="director_uuid", type="text"),
      *                         @OA\Property(property="incorporation_date", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_name", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_name", type="text"),
      *                         @OA\Property(property="ein", type="text"),
      *                         
      *                         @OA\Property(property="business_number", type="text"),
      *                         @OA\Property(property="business_number_type", type="text"),
      *                         @OA\Property(property="voip_provider", type="text"),
      *                         @OA\Property(property="voip_login", type="text"),
      *                         @OA\Property(property="voip_password", type="text"),
      *                         @OA\Property(property="business_mobile_number", type="text"),
      *                         @OA\Property(property="business_mobile_number_type", type="text"),
      *
      *                         @OA\Property(property="business_mobile_provider", type="text"),
      *                         @OA\Property(property="business_mobile_website", type="text"),
      *                         @OA\Property(property="business_mobile_login", type="text"),
      *                         @OA\Property(property="business_mobile_password", type="text"),
      *
      *                         @OA\Property(property="card_on_file", type="text"),
      *                         @OA\Property(property="card_last_four_digit", type="text"),
      *                         @OA\Property(property="card_holder_name", type="text"),
      *
      *                         @OA\Property(property="website", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
      *
      *                         @OA\Property(property="bank_account[]", type="text"),
      *
      *                         @OA\Property(property="register_agents[]", type="text"),
      *
      *                         @OA\Property(property="incorporations[]", type="text"),
      *
      *                         @OA\Property(property="files[]", type="text"),
      *
      *                         @OA\Property(property="files_to_delete[]", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function pending(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'legal_name' => 'required',
            'sic_code_uuid' => '',
            'director_uuid' => 'required',
            'incorporation_date' => '',
            'incorporation_state_uuid' => '',
            'incorporation_state_name' => '',
            'doing_business_in_state_uuid' => '',
            'doing_business_in_state_name' => '',
            'ein' => 'required',
            
            // numbers
            'business_number' => '',
            'business_number_type' => '',
            'voip_provider' => '',
            'voip_login' => '',
            'voip_password' => '',
            'business_mobile_number' => '',
            'business_mobile_number_type' => '',
            
            'business_mobile_provider' => '',
            'business_mobile_website' => '',
            'business_mobile_login' => '',
            'business_mobile_password' => '',

            'card_on_file' => '',
            'card_last_four_digit' => '',
            'card_holder_name' => '',

            'website' => '',
            'db_report_number' => 'required',

            // addresses
            'addresses' => 'array',

            // emails
            'emails' => 'array',

            // bank account
            'bank_account' => 'array',

            // register agents
            'register_agents' => 'array',

            // incorporations
            'incorporations' => 'array',

            // files
            'files' => 'array',
            'files_to_delete' => 'array',

            'user_uuid' => 'string'
        ]);

        // check
        $check = [];

        // emails check
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $tmpCheck = $this->emailService->check($value, $key, '', 'companies');
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        // addresses check
        if (isset($validated['addresses'])){
            foreach($validated['addresses'] AS $key => $value):
                $tmpCheck = $this->addressService->check($value, $key);
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        // bank account check
        if (isset($validated['bank_account'])){
            $tmpCheck = $this->bankAccountService->check($validated['bank_account']);
            $check = array_merge($check, $tmpCheck);
        }
        
        // company check
        $tmpCheck = $this->companyService->check($validated);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json(['data' => $check], 409);
        }

        // create
        $company = $this->companyService->pending($validated);

        // emails
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $value['status'] = Config::get('common.status.pending');
                $this->emailService->save($value);
            endforeach;
        }

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $value['status'] = Config::get('common.status.pending');
                $this->addressService->save($value);
            endforeach;
        }

        // bank account
        $validated['bank_account']['entity_uuid'] = $company->uuid;
        $validated['bank_account']['status'] = Config::get('common.status.pending');
        $this->bankAccountService->save($validated['bank_account']);

        // register agent
        if (isset($validated['register_agents'])){
            foreach ($validated['register_agents'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->registerAgentService->save($value);
            endforeach;
        }

        // incorporations
        if (isset($validated['incorporations'])){
            foreach ($validated['incorporations'] as $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->companyIncorporationService->save($value);
            endforeach;
        }

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files to upload
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $company->uuid], $value['uuid']);
            endforeach;
        }

        return $company;
    }
  
    /**     @OA\PUT(
      *         path="/api/company-pending-update/{uuid}",
      *         operationId="pending_update_company",
      *         tags={"Company"},
      *         summary="Pending update company (not working on swagger)",
      *         description="Pending update company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"legal_name", "director_uuid", "ein", "db_report_number"},
      *                         @OA\Property(property="legal_name", type="text"),
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="director_uuid", type="text"),
      *                         @OA\Property(property="incorporation_date", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_name", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_name", type="text"),
      *                         @OA\Property(property="ein", type="text"),
      *                         
      *                         @OA\Property(property="business_number", type="text"),
      *                         @OA\Property(property="business_number_type", type="text"),
      *                         @OA\Property(property="voip_provider", type="text"),
      *                         @OA\Property(property="voip_login", type="text"),
      *                         @OA\Property(property="voip_password", type="text"),
      *                         @OA\Property(property="business_mobile_number", type="text"),
      *                         @OA\Property(property="business_mobile_number_type", type="text"),
      *
      *                         @OA\Property(property="business_mobile_provider", type="text"),
      *                         @OA\Property(property="business_mobile_website", type="text"),
      *                         @OA\Property(property="business_mobile_login", type="text"),
      *                         @OA\Property(property="business_mobile_password", type="text"),
      *
      *                         @OA\Property(property="card_on_file", type="text"),
      *                         @OA\Property(property="card_last_four_digit", type="text"),
      *                         @OA\Property(property="card_holder_name", type="text"),
      *
      *                         @OA\Property(property="website", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
      *                         @OA\Property(property="address_to_delete", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
      *                         @OA\Property(property="emails_to_delete[]", type="text"),
      *
      *                         @OA\Property(property="bank_account[]", type="text"),
      *
      *                         @OA\Property(property="register_agents[]", type="text"),
      *
      *                         @OA\Property(property="incorporations[]", type="text"),
      *
      *                         @OA\Property(property="files[]", type="text"),
      *
      *                         @OA\Property(property="files_to_delete[]", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function pending_update(Request $request, $uuid)
    {
        $company = Company::where('uuid', $uuid)->first();

        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            } else{
                if ($company->user_uuid!=$request->user_uuid){
                    if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.pre_save'))){
                        return response()->json([ 'data' => 'Not Authorized' ], 403);
                    }
                }
            }
        }

        $validated = $request->validate([
            'legal_name' => 'required',
            'sic_code_uuid' => '',
            'director_uuid' => 'required',
            'incorporation_date' => '',
            'incorporation_state_uuid' => '',
            'incorporation_state_name' => '',
            'doing_business_in_state_uuid' => '',
            'doing_business_in_state_name' => '',
            'ein' => 'required',
            
            // numbers
            'business_number' => '',
            'business_number_type' => '',
            'voip_provider' => '',
            'voip_login' => '',
            'voip_password' => '',
            'business_mobile_number' => '',
            'business_mobile_number_type' => '',
            
            'business_mobile_provider' => '',
            'business_mobile_website' => '',
            'business_mobile_login' => '',
            'business_mobile_password' => '',

            'card_on_file' => '',
            'card_last_four_digit' => '',
            'card_holder_name' => '',

            'website' => '',
            'db_report_number' => 'required',

            // addresses
            'addresses' => 'array',
            'address_to_delete' => '',

            // emails
            'emails' => 'array',
            'emails_to_delete' => 'array',

            // bank account
            'bank_account' => 'array',

            // register agents
            'register_agents' => 'array',

            // incorporations
            'incorporations' => 'array',

            // files
            'files' => 'array',
            'files_to_delete' => 'array',
        ]);

        // check
        $check = [];

        // emails check
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $tmpCheck = $this->emailService->check($value, $key, $company->uuid, 'companies');
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        // addresses check
        if (isset($validated['addresses'])){
            foreach($validated['addresses'] AS $key => $value):
                $tmpCheck = $this->addressService->check($value, $key, $company->uuid);
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        // bank account check
        if (isset($validated['bank_account'])){
            $tmpCheck = $this->bankAccountService->check($validated['bank_account'], $company->uuid);
            $check = array_merge($check, $tmpCheck);
        }

        // company
        $tmpCheck = $this->companyService->check($validated, $company->uuid);
        $check = array_merge($check, $tmpCheck);
        
        if (count($check)>0){
            return response()->json(['data' => $check], 409);
        }

        // update
        $company = $this->companyService->pending_update($uuid, $validated, $request->user_uuid);

        // emails to delete
        if (isset($validated['emails_to_delete'])){
            foreach($validated['emails_to_delete'] AS $key => $value):
                $this->emailService->delete($value);
            endforeach;
        }

        // emails
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $value['status'] = Config::get('common.status.pending');
                $this->emailService->save($value);
            endforeach;
        }

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $value['status'] = Config::get('common.status.pending');
                $this->addressService->save($value);
            endforeach;
        }

        // addresses to delete
        if (isset($validated['address_to_delete'])){
            $this->addressService->delete($validated['address_to_delete']);
        }

        // bank account
        $validated['bank_account']['entity_uuid'] = $company->uuid;
        $validated['bank_account']['status'] = Config::get('common.status.pending');
        $this->bankAccountService->save($validated['bank_account']);

        // register agent
        if (isset($validated['register_agents'])){
            foreach ($validated['register_agents'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->registerAgentService->save($value);
            endforeach;
        }

        // incorporations
        if (isset($validated['incorporations'])){
            foreach ($validated['incorporations'] as $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->companyIncorporationService->save($value);
            endforeach;
        }

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files to upload
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $company->uuid], $value['uuid']);
            endforeach;
        }

        return $company;
    }
  
    /**     @OA\PUT(
      *         path="/api/company-accept",
      *         operationId="accept_company",
      *         tags={"Company"},
      *         summary="Accept company (not working on swagger)",
      *         description="Accept company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"legal_name", "director_uuid", "ein", "db_report_number"},
      *                         @OA\Property(property="legal_name", type="text"),
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="director_uuid", type="text"),
      *                         @OA\Property(property="incorporation_date", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_name", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_name", type="text"),
      *                         @OA\Property(property="ein", type="text"),
      *                         
      *                         @OA\Property(property="business_number", type="text"),
      *                         @OA\Property(property="business_number_type", type="text"),
      *                         @OA\Property(property="voip_provider", type="text"),
      *                         @OA\Property(property="voip_login", type="text"),
      *                         @OA\Property(property="voip_password", type="text"),
      *                         @OA\Property(property="business_mobile_number", type="text"),
      *                         @OA\Property(property="business_mobile_number_type", type="text"),
      *
      *                         @OA\Property(property="business_mobile_provider", type="text"),
      *                         @OA\Property(property="business_mobile_website", type="text"),
      *                         @OA\Property(property="business_mobile_login", type="text"),
      *                         @OA\Property(property="business_mobile_password", type="text"),
      *
      *                         @OA\Property(property="card_on_file", type="text"),
      *                         @OA\Property(property="card_last_four_digit", type="text"),
      *                         @OA\Property(property="card_holder_name", type="text"),
      *
      *                         @OA\Property(property="website", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
      *                         @OA\Property(property="address_to_delete", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
      *                         @OA\Property(property="emails_to_delete[]", type="text"),
      *
      *                         @OA\Property(property="bank_account[]", type="text"),
      *
      *                         @OA\Property(property="register_agents[]", type="text"),
      *
      *                         @OA\Property(property="incorporations[]", type="text"),
      *
      *                         @OA\Property(property="files[]", type="text"),
      *
      *                         @OA\Property(property="files_to_delete[]", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function accept(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.accept'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }
    
        $validated = $request->validate([
            'legal_name' => 'required',
            'sic_code_uuid' => '',
            'director_uuid' => 'required',
            'incorporation_date' => '',
            'incorporation_state_uuid' => '',
            'incorporation_state_name' => '',
            'doing_business_in_state_uuid' => '',
            'doing_business_in_state_name' => '',
            'ein' => 'required',
            
            // numbers
            'business_number' => '',
            'business_number_type' => '',
            'voip_provider' => '',
            'voip_login' => '',
            'voip_password' => '',
            'business_mobile_number' => '',
            'business_mobile_number_type' => '',
            
            'business_mobile_provider' => '',
            'business_mobile_website' => '',
            'business_mobile_login' => '',
            'business_mobile_password' => '',

            'card_on_file' => '',
            'card_last_four_digit' => '',
            'card_holder_name' => '',

            'website' => '',
            'db_report_number' => 'required',

            // addresses
            'addresses' => 'array',
            'address_to_delete' => '',

            // emails
            'emails' => 'array',
            'emails_to_delete' => 'array',

            // bank account
            'bank_account' => 'array',

            // register agents
            'register_agents' => 'array',

            // incorporations
            'incorporations' => 'array',

            // files to delete
            'files' => 'array',
            'files_to_delete' => 'array'
        ]);

        $company = Company::where('uuid', $uuid)->first();

        // check
        $check = [];

        // emails check
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $tmpCheck = $this->emailService->check($value, $key, $company->uuid, 'companies');
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        // addresses check
        if (isset($validated['addresses'])){
            foreach($validated['addresses'] AS $key => $value):
                $tmpCheck = $this->addressService->check($value, $key, $company->uuid);
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        // bank account check
        if (isset($validated['bank_account'])){
            $tmpCheck = $this->bankAccountService->check($validated['bank_account'], $company->uuid);
            $check = array_merge($check, $tmpCheck);
        }

        // company check
        $tmpCheck = $this->companyService->check($validated, $company->uuid);
        $check = array_merge($check, $tmpCheck);
        
        if (count($check)>0){
            return response()->json(['data' => $check], 409);
        }

        // update
        $company = $this->companyService->accept($company, $validated, $request->user_uuid);

        // emails to delete
        if (isset($validated['emails_to_delete'])){
            foreach($validated['emails_to_delete'] AS $key => $value):
                $this->emailService->delete($value);
            endforeach;
        }

        // emails
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $value['status'] = Config::get('common.status.actived');
                $this->emailService->save($value);
            endforeach;
        }

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $value['status'] = Config::get('common.status.actived');
                $this->addressService->save($value);
            endforeach;
        }

        // addresses to delete
        if (isset($validated['address_to_delete'])){
            $this->addressService->delete($validated['address_to_delete']);
        }

        // bank account
        $validated['bank_account']['entity_uuid'] = $company->uuid;
        $validated['bank_account']['status'] = Config::get('common.status.actived');
        $this->bankAccountService->save($validated['bank_account']);

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files to upload
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $company->uuid], $value['uuid']);
            endforeach;
        }

        return $company;
    }
  
    /**     @OA\PUT(
      *         path="/api/company-reject/{uuid}",
      *         operationId="reject_company",
      *         tags={"Company"},
      *         summary="Reject company",
      *         description="Reject company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function reject(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.accept'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $this->companyService->reject($uuid, $request->user_uuid);
    }

    /**     @OA\PUT(
      *         path="/api/company-override/{uuid}",
      *         operationId="override_company",
      *         tags={"Company"},
      *         summary="Override company (not working on swagger)",
      *         description="Override company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={},
      *                         @OA\Property(property="legal_name", type="text"),
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="director_uuid", type="text"),
      *                         @OA\Property(property="incorporation_date", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_name", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_name", type="text"),
      *                         @OA\Property(property="ein", type="text"),
      *                         
      *                         @OA\Property(property="business_number", type="text"),
      *                         @OA\Property(property="business_number_type", type="text"),
      *                         @OA\Property(property="voip_provider", type="text"),
      *                         @OA\Property(property="voip_login", type="text"),
      *                         @OA\Property(property="voip_password", type="text"),
      *                         @OA\Property(property="business_mobile_number", type="text"),
      *                         @OA\Property(property="business_mobile_number_type", type="text"),
      *
      *                         @OA\Property(property="business_mobile_provider", type="text"),
      *                         @OA\Property(property="business_mobile_website", type="text"),
      *                         @OA\Property(property="business_mobile_login", type="text"),
      *                         @OA\Property(property="business_mobile_password", type="text"),
      *
      *                         @OA\Property(property="card_on_file", type="text"),
      *                         @OA\Property(property="card_last_four_digit", type="text"),
      *                         @OA\Property(property="card_holder_name", type="text"),
      *
      *                         @OA\Property(property="website", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
      *                         @OA\Property(property="address_to_delete", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
      *                         @OA\Property(property="emails_to_delete[]", type="text"),
      *
      *                         @OA\Property(property="bank_account[]", type="text"),
      *
      *                         @OA\Property(property="register_agents[]", type="text"),
      *
      *                         @OA\Property(property="incorporations[]", type="text"),
      *
      *                         @OA\Property(property="files[]", type="text"),
      *
      *                         @OA\Property(property="files_to_delete[]", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function override(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.accept'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }
    
        $validated = $request->validate([
            'legal_name' => '',
            'sic_code_uuid' => '',
            'director_uuid' => '',
            'incorporation_date' => '',
            'incorporation_state_uuid' => '',
            'incorporation_state_name' => '',
            'doing_business_in_state_uuid' => '',
            'doing_business_in_state_name' => '',
            'ein' => '',
            
            // numbers
            'business_number' => '',
            'business_number_type' => '',
            'voip_provider' => '',
            'voip_login' => '',
            'voip_password' => '',
            'business_mobile_number' => '',
            'business_mobile_number_type' => '',
            
            'business_mobile_provider' => '',
            'business_mobile_website' => '',
            'business_mobile_login' => '',
            'business_mobile_password' => '',

            'card_on_file' => '',
            'card_last_four_digit' => '',
            'card_holder_name' => '',

            'website' => '',
            'db_report_number' => '',

            // addresses
            'addresses' => 'array',
            'address_to_delete' => '',

            // emails
            'emails' => 'array',
            'emails_to_delete' => 'array',

            // bank account
            'bank_account' => 'array',

            // register agents
            'register_agents' => 'array',
            
            // incorporations
            'incorporations' => 'array',

            // files to delete
            'files' => 'array',
            'files_to_delete' => 'array'
        ]);

        $company = Company::where('uuid', $uuid)->first();

        $company = $this->companyService->accept($company, $validated, $request->user_uuid, true);

        // emails to delete
        if (isset($validated['emails_to_delete'])){
            foreach($validated['emails_to_delete'] AS $key => $value):
                $this->emailService->delete($value);
            endforeach;
        }

        // emails
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $value['status'] = Config::get('common.status.actived');
                $this->emailService->save($value);
            endforeach;
        }

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $value['status'] = Config::get('common.status.actived');
                $this->addressService->save($value);
            endforeach;
        }

        // addresses to delete
        if (isset($validated['address_to_delete'])){
            $this->addressService->delete($validated['address_to_delete']);
        }

        // bank account
        $validated['bank_account']['entity_uuid'] = $company->uuid;
        $validated['bank_account']['status'] = Config::get('common.status.actived');
        $this->bankAccountService->save($validated['bank_account']);

        // register agent
        if (isset($validated['register_agents'])){
            foreach ($validated['register_agents'] AS $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->registerAgentService->save($value);
            endforeach;
        }

        // incorporations
        if (isset($validated['incorporations'])){
            foreach ($validated['incorporations'] as $key => $value):
                $value['entity_uuid'] = $company->uuid;
                $this->companyIncorporationService->save($value);
            endforeach;
        }

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files to upload
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $company->uuid], $value['uuid']);
            endforeach;
        }

        return $company;
    }

    /**     @OA\GET(
      *         path="/api/company-permission",
      *         operationId="company_permission",
      *         tags={"Company"},
      *         summary="Get company permission of user",
      *         description="Get company permission of user",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *     )
      */
    public function permission(Request $request)
    {
        $permissions = [];

        // permission
        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.view'))){
            $permissions[] = Config::get('common.permission.company.view');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.access'))){
            $permissions[] = Config::get('common.permission.company.access');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.store'))){
            $permissions[] = Config::get('common.permission.company.store');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.save'))){
            $permissions[] = Config::get('common.permission.company.save');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.pre_save'))){
            $permissions[] = Config::get('common.permission.company.pre_save');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.delete'))){
            $permissions[] = Config::get('common.permission.company.delete');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.accept'))){
            $permissions[] = Config::get('common.permission.company.accept');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.download'))){
            $permissions[] = Config::get('common.permission.company.download');
        }

        return $permissions;
    }

    /**     @OA\GET(
      *         path="/api/company-by-director/{uuid}",
      *         operationId="get_company_by_director",
      *         tags={"Company"},
      *         summary="Get company by director",
      *         description="Get company by director",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="director uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function by_director(Request $request, $uuid)
    {
        $company = Company::where('director_uuid', $uuid)
                            ->first(['legal_name']);
        return $company;
    }
 
}
