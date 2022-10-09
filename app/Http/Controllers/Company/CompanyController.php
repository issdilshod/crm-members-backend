<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\Company\CompanyResource;
use App\Models\Company\Company;
use App\Models\Helper\Address;
use App\Models\Helper\BankAccount;
use App\Models\Helper\BankAccountSecurity;
use App\Models\Helper\Email;
use App\Models\Helper\File;
use App\Policies\PermissionPolicy;
use App\Services\Company\CompanyService;
use App\Services\Helper\AddressService;
use App\Services\Helper\BankAccountService;
use App\Services\Helper\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class CompanyController extends Controller
{

    private $companyService;
    private $emailService;
    private $addressService;
    private $bankAccountService;

    public function __construct()
    {
        $this->companyService = new CompanyService();
        $this->emailService = new EmailService();
        $this->addressService = new AddressService();
        $this->bankAccountService = new BankAccountService();
    }

    /**     @OA\GET(
      *         path="/api/company",
      *         operationId="list_company",
      *         tags={"Company"},
      *         summary="List of company",
      *         description="List of company",
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.view'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
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
      *                         required={"user_uuid", "legal_name", "sic_code_uuid", "director_uuid", "incorporation_state_uuid", "incorporation_state_name", "doing_business_in_state_uuid", "doing_business_in_state_name", "ein", "business_number", "business_number_type", "voip_provider", "voip_login", "voip_password", "business_mobile_number_provider", "business_mobile_number_login", "business_mobile_number_password", "website", "db_report_number", "address[street_address]", "address[address_line_2]", "address[city]", "address[state]", "address[postal]", "address[country]", "emails[hosting_uuid]", "emails[email]", "emails[password]", "emails[phone]"},
      *                         @OA\Property(property="user_uuid", type="text"),
      *                         @OA\Property(property="legal_name", type="text"),
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="director_uuid", type="text"),
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
      *                         @OA\Property(property="business_mobile_number_provider", type="text"),
      *                         @OA\Property(property="business_mobile_number_login", type="text"),
      *                         @OA\Property(property="business_mobile_number_password", type="text"),
      *
      *                         @OA\Property(property="website", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *
      *                         @OA\Property(property="address[street_address]", type="text"),
      *                         @OA\Property(property="address[address_line_2]", type="text"),
      *                         @OA\Property(property="address[city]", type="text"),
      *                         @OA\Property(property="address[state]", type="text"),
      *                         @OA\Property(property="address[postal]", type="text"),
      *                         @OA\Property(property="address[country]", type="text"),
      *
      *                         @OA\Property(property="emails[hosting_uuid]", type="text"),
      *                         @OA\Property(property="emails[email]", type="text"),
      *                         @OA\Property(property="emails[password]", type="text"),
      *                         @OA\Property(property="emails[phone]", type="text"),
      *
      *                         @OA\Property(property="bank_account[name]", type="text"),
      *                         @OA\Property(property="bank_account[website]", type="text"),
      *                         @OA\Property(property="bank_account[username]", type="text"),
      *                         @OA\Property(property="bank_account[password]", type="text"),
      *                         @OA\Property(property="bank_account[account_number]", type="text"),
      *                         @OA\Property(property="bank_account[routing_number]", type="text"),
      *
      *                         @OA\Property(property="bank_account_security[][question]", type="text"),
      *                         @OA\Property(property="bank_account_security[][answer]", type="text"),
      *
      *                         @OA\Property(property="files[incorporation_state][]", type="file", format="binary"),
      *                         @OA\Property(property="files[doing_business_in_state][]", type="file", format="binary"),
      *                         @OA\Property(property="files[company_ein][]", type="file", format="binary"),
      *                         @OA\Property(property="files[db_report][]", type="file", format="binary")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function store(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.store'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'legal_name' => 'required|string',
            'sic_code_uuid' => 'required|string',
            'director_uuid' => 'required|string',
            'incorporation_state_uuid' => 'required|string',
            'incorporation_state_name' => 'required|string',
            'doing_business_in_state_uuid' => 'required|string',
            'doing_business_in_state_name' => 'required|string',
            'ein' => 'required|string',
            
            // numbers
            'business_number' => 'required|string',
            'business_number_type' => 'required|string',
            'voip_provider' => 'required|string',
            'voip_login' => 'required|string',
            'voip_password' => 'required|string',
            'business_mobile_number_provider' => 'required|string',
            'business_mobile_number_login' => 'required|string',
            'business_mobile_number_password' => 'required|string',

            'website' => 'required|string',
            'db_report_number' => 'required|string',

            // addresses
            'address.street_address' => 'required|string',
            'address.address_line_2' => 'required|string',
            'address.city' => 'required|string',
            'address.state' => 'required|string',
            'address.postal' => 'required|string',
            'address.country' => 'required|string',

            // emails
            'emails.hosting_uuid' => 'required|string',
            'emails.email' => 'required|string',
            'emails.password' => 'required|string',
            'emails.phone' => 'required|string',

            // bank account
            'bank_account' => 'array',

            // bank account security
            'bank_account_security' => 'array',

            'user_uuid' => 'string'
        ]);

        $check = [];

        $tmpCheck = $this->emailService->check($validated['emails']);
        $check = array_merge($check, $tmpCheck);

        $tmpCheck = $this->addressService->check($validated['address']);
        $check = array_merge($check, $tmpCheck);

        if (isset($validated['bank_account'])){
            $tmpCheck = $this->bankAccountService->check($validated['bank_account']);
            $check = array_merge($check, $tmpCheck);
        }
        
        $tmpCheck = $this->companyService->check($validated);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $company = $this->companyService->create($validated);

        // email
        $validated['emails']['entity_uuid'] = $company['uuid'];
        $this->emailService->create($validated['emails']);

        // bank account & account sercurity
        if (isset($validated['bank_account'])){
            $validated['bank_account']['entity_uuid'] = $company['uuid'];
            $bank_account = $this->bankAccountService->create($validated['bank_account']);

            // security
            if (isset($validated['bank_account_security'])){
                foreach ($validated['bank_account_security'] AS $key => $value):
                    $value['entity_uuid'] = $bank_account['uuid'];
                    BankAccountSecurity::create($value);
                endforeach;
            }
        }

        // address
        $validated['address']['address_parent'] = '';
        $validated['address']['entity_uuid'] = $company['uuid'];
        $this->addressService->create($validated['address']);

        #region Files upload (if exsist)

        if ($request->has('files')){
            $files = $request->file('files');
            foreach ($files AS $key => $value):
                $tmp_file = $value;
                $file_parent = $key;

                foreach ($tmp_file AS $key2 => $value2):
                    $file = new File();
                    $file->user_uuid = $validated['user_uuid'];
                    $file->entity_uuid = $company['uuid'];
                    $file->file_name = Str::uuid()->toString() . '.' . $value2->getClientOriginalExtension();
                    $file->file_path = $file->file_name;
                    $file->file_parent = $file_parent;
                    $value2->move('uploads', $file->file_path);
                    $file->save();
                endforeach;
            endforeach;
        }

        #endregion

        return new CompanyResource($company);
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Company $company)
    {
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
      *                         @OA\Property(property="user_uuid", type="text"),
      *                         @OA\Property(property="legal_name", type="text"),
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="director_uuid", type="text"),
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
      *                         @OA\Property(property="business_mobile_number_provider", type="text"),
      *                         @OA\Property(property="business_mobile_number_login", type="text"),
      *                         @OA\Property(property="business_mobile_number_password", type="text"),
      *
      *                         @OA\Property(property="website", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *
      *                         @OA\Property(property="address[street_address]", type="text"),
      *                         @OA\Property(property="address[address_line_2]", type="text"),
      *                         @OA\Property(property="address[city]", type="text"),
      *                         @OA\Property(property="address[state]", type="text"),
      *                         @OA\Property(property="address[postal]", type="text"),
      *                         @OA\Property(property="address[country]", type="text"),
      *
      *                         @OA\Property(property="emails[0][hosting_uuid]", type="text"),
      *                         @OA\Property(property="emails[0][email]", type="text"),
      *                         @OA\Property(property="emails[0][password]", type="text"),
      *                         @OA\Property(property="emails[0][phone]", type="text"),
      *
      *                         @OA\Property(property="bank_account[name]", type="text"),
      *                         @OA\Property(property="bank_account[website]", type="text"),
      *                         @OA\Property(property="bank_account[username]", type="text"),
      *                         @OA\Property(property="bank_account[password]", type="text"),
      *                         @OA\Property(property="bank_account[account_number]", type="text"),
      *                         @OA\Property(property="bank_account[routing_number]", type="text"),
      *
      *                         @OA\Property(property="bank_account_security[][question]", type="text"),
      *                         @OA\Property(property="bank_account_security[][answer]", type="text"),
      *
      *                         @OA\Property(property="bank_account_security_to_delete[]", type="text"),
      *
      *                         @OA\Property(property="files[incorporation_state][]", type="file", format="binary"),
      *                         @OA\Property(property="files[doing_business_in_state][]", type="file", format="binary"),
      *                         @OA\Property(property="files[company_ein][]", type="file", format="binary"),
      *                         @OA\Property(property="files[db_report][]", type="file", format="binary"),
      *
      *                         @OA\Property(property="files_to_delete[]", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function update(Request $request, Company $company)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.update'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'legal_name' => 'required|string',
            'sic_code_uuid' => 'required|string',
            'director_uuid' => 'required|string',
            'incorporation_state_uuid' => 'required|string',
            'incorporation_state_name' => 'required|string',
            'doing_business_in_state_uuid' => 'required|string',
            'doing_business_in_state_name' => 'required|string',
            'ein' => 'required|string',
            
            // numbers
            'business_number' => 'required|string',
            'business_number_type' => 'required|string',
            'voip_provider' => 'required|string',
            'voip_login' => 'required|string',
            'voip_password' => 'required|string',
            'business_mobile_number_provider' => 'required|string',
            'business_mobile_number_login' => 'required|string',
            'business_mobile_number_password' => 'required|string',

            'website' => 'required|string',
            'db_report_number' => 'required|string',

            // addresses
            'address.street_address' => 'required|string',
            'address.address_line_2' => 'required|string',
            'address.city' => 'required|string',
            'address.state' => 'required|string',
            'address.postal' => 'required|string',
            'address.country' => 'required|string',

            // emails
            'emails' => 'required|array',

            // bank account
            'bank_account' => 'array',

            // bank account security
            'bank_account_security' => 'array',

            // bank account security to delete
            'bank_account_security_to_delete' => 'array',

            // files to delete
            'files_to_delete' => 'array',

            'user_uuid' => 'string'
        ]);

        $check = [];

        $tmpCheck = $this->emailService->check_ignore($validated['emails'], $company->uuid);
        $check = array_merge($check, $tmpCheck);

        $tmpCheck = $this->addressService->check_ignore($validated['address'], $company->uuid);
        $check = array_merge($check, $tmpCheck);

        if (isset($validated['bank_account'])){
            $tmpCheck = $this->bankAccountService->check_ignore($validated['bank_account'], $company->uuid);
            $check = array_merge($check, $tmpCheck);
        }
        
        $tmpCheck = $this->companyService->check_ignore($validated, $company->uuid);
        $check = array_merge($check, $tmpCheck);
        
        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $company = $this->companyService->update($company, $validated);

        // email
        $email = Email::where('entity_uuid', $company['uuid']);
        $email->update($validated['emails']);

        // address
        $address = Address::where('entity_uuid', $company['uuid']);
        $address->update($validated['address']);

        // bank account & security
        if (isset($validated['bank_account'])){
            $bank_account = BankAccount::where('entity_uuid', $company['uuid'])->first();
            if ($bank_account!=null){
                $bank_account->update($validated['bank_account']);
            }else{
                $validated['bank_account']['entity_uuid'] = $company['uuid'];
                $bank_account = BankAccount::create($validated['bank_account']);
            }

            // security delete
            if (isset($validated['bank_account_security_to_delete'])){
                foreach($validated['bank_account_security_to_delete'] AS $key => $value):
                    $bank_account_security = BankAccountSecurity::where('uuid', $value);
                    $bank_account_security->update(['status' => Config::get('common.status.deleted')]);
                endforeach;
            }

            // security
            if (isset($validated['bank_account_security'])){
                foreach ($validated['bank_account_security'] AS $key => $value):
                    $value['entity_uuid'] = $bank_account['uuid'];
                    $bank_account_security = BankAccountSecurity::find($value);
                    if (!$bank_account_security->count()){
                        BankAccountSecurity::create($value);
                    }else{
                        $bank_account_security->update($value);
                    }
                endforeach;
            }

        }

        #region Files delete (if exsist)

        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                if ($value!=null){
                    $file = File::find($value);
                    $file->update(['status'=> Config::get('common.status.deleted')]);
                }
            endforeach;
        }

        #endregion

        #region Files upload (if exsist)

        if ($request->has('files')){
            $files = $request->file('files');
            foreach ($files AS $key => $value):
                $tmp_file = $value;
                $file_parent = $key;

                foreach ($tmp_file AS $key2 => $value2):
                    $file = new File();
                    $file->user_uuid = $validated['user_uuid'];
                    $file->entity_uuid = $company['uuid'];
                    $file->file_name = Str::uuid()->toString() . '.' . $value2->getClientOriginalExtension();
                    $file->file_path = $file->file_name;
                    $file->file_parent = $file_parent;
                    $value2->move('uploads', $file->file_path);
                    $file->save();
                endforeach;
            endforeach;
        }

        #endregion

        return new CompanyResource($company);
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(Request $request, Company $company)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.delete'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $this->companyService->delete($company);
    }

    /**     @OA\GET(
      *         path="/api/company-search/{search}",
      *         operationId="get_company_search",
      *         tags={"Company"},
      *         summary="Get company search",
      *         description="Get company search",
      *             @OA\Parameter(
      *                 name="search",
      *                 in="path",
      *                 description="company search",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="text"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function search(Request $request, $search)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.view'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $companies = $this->companyService->search($search);
        return $companies;
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
      *                         required={},
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function pending(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.save'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'legal_name' => 'required|string',
            'sic_code_uuid' => '',
            'director_uuid' => '',
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
            'business_mobile_number_provider' => '',
            'business_mobile_number_login' => '',
            'business_mobile_number_password' => '',

            'website' => '',
            'db_report_number' => '',

            // addresses
            'address.street_address' => '',
            'address.address_line_2' => '',
            'address.city' => '',
            'address.state' => '',
            'address.postal' => '',
            'address.country' => '',

            // emails
            'emails.hosting_uuid' => 'required',
            'emails.email' => '',
            'emails.password' => '',
            'emails.phone' => '',

            // bank account
            'bank_account' => 'array',

            // bank account security
            'bank_account_security' => 'array',

            'user_uuid' => 'string'
        ]);

        $company = $this->companyService->pending($validated);

        // email
        $validated['emails']['entity_uuid'] = $company['uuid'];
        $this->emailService->create($validated['emails']);

        //address
        $validated['address']['address_parent'] = '';
        $validated['address']['entity_uuid'] = $company['uuid'];
        $this->addressService->create($validated['address']);

        // bank account & security
        if (isset($validated['bank_account'])){
            $validated['bank_account']['entity_uuid'] = $company['uuid'];
            $bank_account = $this->bankAccountService->create($validated['bank_account']);

            // security
            if (isset($validated['bank_account_security'])){
                foreach ($validated['bank_account_security'] AS $key => $value):
                    $value['entity_uuid'] = $bank_account['uuid'];
                    BankAccountSecurity::create($value);
                endforeach;
            }
        }

        #region Files upload (if exsist)

        if ($request->has('files')){
            $files = $request->file('files');
            foreach ($files AS $key => $value):
                foreach ($value AS $key1 => $value1):
                    if ($key1=='back' || $key1=='front'){
                        $tmp_file = $value1;
                        $file_parent = $key . '/' . $key1;
                    }else{
                        $tmp_file = $value;
                        $file_parent = $key;
                    }
                    foreach ($tmp_file AS $key2 => $value2):
                        $file = new File();
                        $file->user_uuid = $validated['user_uuid'];
                        $file->entity_uuid = $company['uuid'];
                        $file->file_name = Str::uuid()->toString() . '.' . $value2->getClientOriginalExtension();
                        $file->file_path = $file->file_name;
                        $file->file_parent = $file_parent;
                        $value2->move('uploads', $file->file_path);
                        $file->save();
                    endforeach;
                endforeach;
            endforeach;
        }

        #endregion

        return new CompanyResource($company);
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
      *                         required={},
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function pending_update(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.save'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'legal_name' => 'required|string',
            'sic_code_uuid' => '',
            'director_uuid' => '',
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
            'business_mobile_number_provider' => '',
            'business_mobile_number_login' => '',
            'business_mobile_number_password' => '',

            'website' => '',
            'db_report_number' => '',

            // addresses
            'address.street_address' => '',
            'address.address_line_2' => '',
            'address.city' => '',
            'address.state' => '',
            'address.postal' => '',
            'address.country' => '',

            // emails
            'emails.hosting_uuid' => 'required|string',
            'emails.email' => '',
            'emails.password' => '',
            'emails.phone' => '',

            // bank account
            'bank_account' => 'array',

            // bank account security
            'bank_account_security' => 'array',

            'user_uuid' => 'string'
        ]);

        $company = $this->companyService->pending_update($uuid, $validated);

        // email
        if (isset($validated['emails'])){
            $email = Email::where('entity_uuid', $company['uuid']);   
            $email->update($validated['emails']);
        }

        // address
        if (isset($validated['address'])){
            $address = Address::where('entity_uuid', $company['uuid']);
            $address->update($validated['address']);
        }

        // bank account & security
        if (isset($validated['bank_account'])){
            $bank_account = BankAccount::where('entity_uuid', $company['uuid'])->first();
            if ($bank_account!=null){
                $bank_account->update($validated['bank_account']);
            }else{
                $validated['bank_account']['entity_uuid'] = $company['uuid'];
                $bank_account = BankAccount::create($validated['bank_account']);
            }

            // security delete
            if (isset($validated['bank_account_security_to_delete'])){
                foreach($validated['bank_account_security_to_delete'] AS $key => $value):
                    $bank_account_security = BankAccountSecurity::where('uuid', $value);
                    $bank_account_security->update(['status' => Config::get('common.status.deleted')]);
                endforeach;
            }

            // security
            if (isset($validated['bank_account_security'])){
                foreach ($validated['bank_account_security'] AS $key => $value):
                    $value['entity_uuid'] = $bank_account['uuid'];
                    $bank_account_security = BankAccountSecurity::find($value);
                    if (!$bank_account_security->count()){
                        BankAccountSecurity::create($value);
                    }else{
                        $bank_account_security->update($value);
                    }
                endforeach;
            }

        }

        #region Files delete (if exsist)

        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                if ($value!=null){
                    $file = File::find($value);
                    $file->update(['status'=> 0]);
                }
            endforeach;
        }

        #endregion

        #region Files upload (if exsist)

        if ($request->has('files')){
            $files = $request->file('files');
            foreach ($files AS $key => $value):
                foreach ($value AS $key1 => $value1):
                    if ($key1=='back' || $key1=='front'){
                        $tmp_file = $value1;
                        $file_parent = $key . '/' . $key1;
                    }else{
                        $tmp_file = $value;
                        $file_parent = $key;
                    }
                    foreach ($tmp_file AS $key2 => $value2):
                        $file = new File();
                        $file->user_uuid = $validated['user_uuid'];
                        $file->entity_uuid = $company['uuid'];
                        $file->file_name = Str::uuid()->toString() . '.' . $value2->getClientOriginalExtension();
                        $file->file_path = $file->file_name;
                        $file->file_parent = $file_parent;
                        $value2->move('uploads', $file->file_path);
                        $file->save();
                    endforeach;
                endforeach;
            endforeach;
        }

        #endregion

        return new CompanyResource($company);
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
      *                         required={},
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function accept(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.accept'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }
    
        $validated = $request->validate([
            'legal_name' => 'required|string',
            'sic_code_uuid' => 'required|string',
            'director_uuid' => 'required|string',
            'incorporation_state_uuid' => 'required|string',
            'incorporation_state_name' => 'required|string',
            'doing_business_in_state_uuid' => 'required|string',
            'doing_business_in_state_name' => 'required|string',
            'ein' => 'required|string',
            
            // numbers
            'business_number' => 'required|string',
            'business_number_type' => 'required|string',
            'voip_provider' => 'required|string',
            'voip_login' => 'required|string',
            'voip_password' => 'required|string',
            'business_mobile_number_provider' => 'required|string',
            'business_mobile_number_login' => 'required|string',
            'business_mobile_number_password' => 'required|string',

            'website' => 'required|string',
            'db_report_number' => 'required|string',

            // addresses
            'address.street_address' => 'required|string',
            'address.address_line_2' => 'required|string',
            'address.city' => 'required|string',
            'address.state' => 'required|string',
            'address.postal' => 'required|string',
            'address.country' => 'required|string',

            // emails
            'emails' => 'required|array',

            // bank account
            'bank_account' => 'array',

            // bank account security
            'bank_account_security' => 'array',

            // bank account security to delete
            'bank_account_security_to_delete' => 'array',

            // files to delete
            'files_to_delete' => 'array'
        ]);

        $company = Company::where('uuid', $uuid)->first();

        $check = [];

        $tmpCheck = $this->emailService->check_ignore($validated['emails'], $company->uuid);
        $check = array_merge($check, $tmpCheck);

        $tmpCheck = $this->addressService->check_ignore($validated['address'], $company->uuid);
        $check = array_merge($check, $tmpCheck);

        $tmpCheck = $this->companyService->check_ignore($validated, $company->uuid);
        $check = array_merge($check, $tmpCheck);
        
        // exsist
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $company = $this->companyService->accept($company, $validated, $request->user_uuid);

        $email = Email::where('entity_uuid', $company['uuid']);
        $email->update($validated['emails']);

        $address = Address::where('entity_uuid', $company['uuid']);
        $address->update($validated['address']);

        // bank account & security
        if (isset($validated['bank_account'])){
            $bank_account = BankAccount::where('entity_uuid', $company['uuid'])->first();
            if ($bank_account!=null){
                $bank_account->update($validated['bank_account']);
            }else{
                $validated['bank_account']['entity_uuid'] = $company['uuid'];
                $bank_account = BankAccount::create($validated['bank_account']);
            }

            // security delete
            if (isset($validated['bank_account_security_to_delete'])){
                foreach($validated['bank_account_security_to_delete'] AS $key => $value):
                    $bank_account_security = BankAccountSecurity::where('uuid', $value);
                    $bank_account_security->update(['status' => Config::get('common.status.deleted')]);
                endforeach;
            }

            // security
            if (isset($validated['bank_account_security'])){
                foreach ($validated['bank_account_security'] AS $key => $value):
                    $value['entity_uuid'] = $bank_account['uuid'];
                    $bank_account_security = BankAccountSecurity::find($value);
                    if (!$bank_account_security->count()){
                        BankAccountSecurity::create($value);
                    }else{
                        $bank_account_security->update($value);
                    }
                endforeach;
            }

        }

        #region Files delete (if exsist)

        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                if ($value!=null){
                    $file = File::find($value);
                    $file->update(['status'=> 0]);
                }
            endforeach;
        }

        #endregion

        #region Files upload (if exsist)

        if ($request->has('files')){
            $files = $request->file('files');
            foreach ($files AS $key => $value):
                foreach ($value AS $key1 => $value1):
                    if ($key1=='back' || $key1=='front'){
                        $tmp_file = $value1;
                        $file_parent = $key . '/' . $key1;
                    }else{
                        $tmp_file = $value;
                        $file_parent = $key;
                    }
                    foreach ($tmp_file AS $key2 => $value2):
                        $file = new File();
                        $file->user_uuid = $validated['user_uuid'];
                        $file->entity_uuid = $company['uuid'];
                        $file->file_name = Str::uuid()->toString() . '.' . $value2->getClientOriginalExtension();
                        $file->file_path = $file->file_name;
                        $file->file_parent = $file_parent;
                        $value2->move('uploads', $file->file_path);
                        $file->save();
                    endforeach;
                endforeach;
            endforeach;
        }

        #endregion

        return new CompanyResource($company);
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function reject(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.reject'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $this->companyService->reject($uuid, $request->user_uuid);
    }
}
