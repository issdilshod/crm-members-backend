<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyResource;
use App\Models\API\Address;
use App\Models\API\BankAccount;
use App\Models\API\BankAccountSecurity;
use App\Models\API\Company;
use App\Models\API\Email;
use App\Models\API\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**     @OA\Get(
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
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index()
    {
        //
        $company = Company::where('status', '=', '1')->paginate(20);
        return CompanyResource::collection($company);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
      *                         required={"user_uuid", "legal_name", "sic_code_uuid", "director_uuid", "incorporation_state_uuid", "incorporation_state_name", "doing_business_in_state_uuid", "doing_business_in_state_name", "ein", "phone_type", "phone_number", "website", "db_report_number", "address[street_address]", "address[address_line_2]", "address[city]", "address[state]", "address[postal]", "address[country]", "emails[0][hosting_uuid]", "emails[0][email]", "emails[0][password]", "emails[0][phone]", "bank_account[name]", "bank_account[website]", "bank_account[username]", "bank_account[password]", "bank_account[account_number]", "bank_account[routing_number]"},
      *                         @OA\Property(property="user_uuid", type="text"),
      *                         @OA\Property(property="legal_name", type="text"),
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="director_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_name", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_name", type="text"),
      *                         @OA\Property(property="ein", type="text"),
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
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
      *                         @OA\Property(property="emails[][hosting_uuid]", type="text"),
      *                         @OA\Property(property="emails[][email]", type="text"),
      *                         @OA\Property(property="emails[][password]", type="text"),
      *                         @OA\Property(property="emails[][phone]", type="text"),
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
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function store(Request $request)
    {
        #region Validate

        $validated = $request->validate([
            'user_uuid' => 'required|string',
            'legal_name' => 'required|string',
            'sic_code_uuid' => 'required|string',
            'director_uuid' => 'required|string',
            'incorporation_state_uuid' => 'required|string',
            'incorporation_state_name' => 'required|string',
            'doing_business_in_state_uuid' => 'required|string',
            'doing_business_in_state_name' => 'required|string',
            'ein' => 'required|string',
            'phone_type' => 'required|string',
            'phone_number' => 'required|string',
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
            'emails' => 'array',

            // bank account
            'bank_account.name' => 'required|string',
            'bank_account.website' => 'required|string',
            'bank_account.username' => 'required|string',
            'bank_account.password' => 'required|string',
            'bank_account.account_number' => 'required|string',
            'bank_account.routing_number' => 'required|string',

            // bank account security
            'bank_account_security' => 'array'
        ]);

        #endregion

        #region Check exsist models

        $result_check = [];
        // Check Email
        foreach($validated['emails'] AS $key => $value):
            $result_check['emails'] = Email::where('email', $value['email'])
                                        ->where('hosting_uuid', $value['hosting_uuid'])
                                        ->orWhere('phone', $value['phone'])
                                        ->first();
            if ($result_check['emails']!=null){
                break;
            }
        endforeach;

        // Check Address
        $result_check['address'] = Address::where('street_address', $validated['address']['street_address'])
                                            ->where('address_line_2', $validated['address']['address_line_2'])
                                            ->where('city',$validated['address']['city'])
                                            ->where('postal', $validated['address']['postal'])
                                            ->first();

        // Check Company
        $result_check['company'] = Company::where('legal_name', $validated['legal_name'])
                                                ->orWhere('director_uuid', $validated['director_uuid'])
                                                ->orWhere('ein', $validated['ein'])
                                                ->orWhere('phone_number', $validated['phone_number'])
                                                ->orWhere('website', $validated['website'])
                                                ->orWhere('db_report_number', $validated['db_report_number'])
                                                ->first();

        // Check Bank Account
        $result_check['bank_account'] = BankAccount::where('name', $validated['bank_account']['name'])
                                                    ->where('username', $validated['bank_account']['username'])
                                                    ->orWhere('account_number', $validated['bank_account']['account_number'])
                                                    ->orWhere('routing_number', $validated['bank_account']['routing_number'])
                                                    ->first();

        $exsist = false;
        foreach ($result_check AS $key => $value):
            if ($value != null){
                $exsist = true;
                break;
            }
        endforeach;

        if ($exsist){
            return response()->json([
                        'data' => $result_check,
                    ], 409);
        }

        #endregion

        $company = Company::create($validated);

        #region Email add

        foreach($validated['emails'] AS $key => $value):
            $value['entity_uuid'] = $company['uuid'];
            Email::create($value);
        endforeach;

        #endregion

        #region Bank account & bank account security (if exsist) add

        $validated['bank_account']['entity_uuid'] = $company['uuid'];
        $bank_account = BankAccount::create($validated['bank_account']);

        if (isset($validated['bank_account_security'])){
            foreach ($validated['bank_account_security'] AS $key => $value):
                $value['entity_uuid'] = $bank_account['uuid'];
                BankAccountSecurity::create($value);
            endforeach;
        }

        #endregion

        #region Address add

        $address = new Address($validated['address']);
        $address->address_parent = '';
        $address->entity_uuid = $company['uuid'];
        $address->save();

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
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Company $company)
    {
        //
        return new CompanyResource($company);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        //
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
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
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
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function update(Request $request, Company $company)
    {
        #region Validate

        $validated = $request->validate([
            'user_uuid' => 'string',
            'legal_name' => 'string',
            'sic_code_uuid' => 'string',
            'director_uuid' => 'string',
            'incorporation_state_uuid' => 'string',
            'incorporation_state_name' => 'string',
            'doing_business_in_state_uuid' => 'string',
            'doing_business_in_state_name' => 'string',
            'ein' => 'string',
            'phone_type' => 'string',
            'phone_number' => 'string',
            'website' => 'string',
            'db_report_number' => 'string',

            // addresses
            'address.street_address' => 'string',
            'address.address_line_2' => 'string',
            'address.city' => 'string',
            'address.state' => 'string',
            'address.postal' => 'string',
            'address.country' => 'string',

            // emails
            'emails' => 'array',

            // bank account
            'bank_account.name' => 'string',
            'bank_account.website' => 'string',
            'bank_account.username' => 'string',
            'bank_account.password' => 'string',
            'bank_account.account_number' => 'string',
            'bank_account.routing_number' => 'string',

            // files to delete
            'files_to_delete' => 'array',

            // bank account security
            'bank_account_security' => 'array',

            // bank account security to delete
            'bank_account_security_to_delete' => 'array'
        ]);

        #endregion

        #region Check exsist models

        $result_check = [];
        // Check Email
        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $result_check['emails'] = Email::where('entity_uuid', '!=', $company['uuid'])
                                                ->where(function($query) use ($value){
                                                        $query->where('email', $value['email'])
                                                                ->where('hosting_uuid', $value['hosting_uuid'])
                                                                ->orWhere('phone', $value['phone']);
                                                })
                                                ->first();
                if ($result_check['emails']!=null){
                    break;
                }
            endforeach;
        }

        // Check Address
        if (isset($validated['address'])){
            $result_check['address'] = Address::where('entity_uuid', '!=', $company['uuid'])
                                                ->where(function($query) use ($validated){
                                                        $query->where('street_address', $validated['address']['street_address'])
                                                                ->where('address_line_2', $validated['address']['address_line_2'])
                                                                ->where('city',$validated['address']['city'])
                                                                ->where('postal', $validated['address']['postal']);
                                                })
                                                ->first();
        }

        // Check Company
        if (isset($validated['legal_name'])){
            $result_check['company'] = Company::where('uuid', '!=', $company['uuid'])
                                                ->where(function($query) use ($validated){
                                                        $query->where('legal_name', $validated['legal_name'])
                                                                ->orWhere('director_uuid', $validated['director_uuid'])
                                                                ->orWhere('ein', $validated['ein'])
                                                                ->orWhere('phone_number', $validated['phone_number'])
                                                                ->orwhere('website', $validated['website'])
                                                                ->orWhere('db_report_number', $validated['db_report_number']);
                                                })
                                                ->first();
        }

        // Check Bank Account
        if (isset($validated['bank_account'])){
            $result_check['bank_account'] = BankAccount::where('entity_uuid', '!=', $company['uuid'])
                                                        ->where(function($query) use ($validated){
                                                                $query->where('name', $validated['bank_account']['name'])
                                                                        ->where('username', $validated['bank_account']['username'])
                                                                        ->orwhere('account_number', $validated['bank_account']['account_number'])
                                                                        ->orWhere('routing_number', $validated['bank_account']['routing_number']);
                                                        })
                                                        ->first();
        }

        $exsist = false;
        foreach ($result_check AS $key => $value):
            if ($value != null){
                $exsist = true;
                break;
            }
        endforeach;

        if ($exsist){
            return response()->json([
                        'data' => $result_check,
                    ], 409);
        }

        #endregion

        $company->update($validated);

        #region Email update

        if (isset($validated['emails'])){
            foreach($validated['emails'] AS $key => $value):
                $email = Email::where('entity_uuid', $company['uuid']);
                $email->update($value);
            endforeach;
        }

        #endregion

        #region Bank account & bank account security (delete/update) update

        $bank_account = BankAccount::where('entity_uuid', $company['uuid'])->first();
        if (isset($validated['bank_account'])){
            $bank_account->update($validated['bank_account']);
        }

        if (isset($validated['bank_account_security_to_delete'])){
            foreach($validated['bank_account_security_to_delete'] AS $key => $value):
                $bank_account_security = BankAccountSecurity::where('uuid', $value);
                $bank_account_security->update(['status' => '0']);
            endforeach;
        }

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

        #endregion

        #region Address update

        if (isset($validated['address'])){
            $address = Address::where('entity_uuid', $company['uuid']);
            $address->update($validated['address']);
        }

        #endregion

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        //
    }
}
