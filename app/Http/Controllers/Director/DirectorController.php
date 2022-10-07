<?php

namespace App\Http\Controllers\Director;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Director\DirectorResource;
use App\Models\Account\Activity;
use App\Models\Director\Director;
use App\Models\Helper\Address;
use App\Models\Helper\Email;
use App\Models\Helper\File;
use App\Services\Director\DirectorService;
use App\Services\Helper\AddressService;
use App\Services\Helper\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class DirectorController extends Controller
{

    private $directorService;
    private $addressService;
    private $emailService;

    public function __construct()
    {
        $this->directorService = new DirectorService();
        $this->addressService = new AddressService();
        $this->emailService = new EmailService();
    }

    /**     @OA\GET(
      *         path="/api/director",
      *         operationId="list_director",
      *         tags={"Director"},
      *         summary="List of director",
      *         description="List of director",
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index()
    {
        $directors = $this->directorService->all();
        return $directors;
    }

    /**     @OA\POST(
      *         path="/api/director",
      *         operationId="post_director",
      *         tags={"Director"},
      *         summary="Add director (not working on swagger)",
      *         description="Add director",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"first_name", "middle_name", "last_name", "date_of_birth", "ssn_cpn", "company_association", "phone_type", "phone_number", "status", "address[dl_address][street_address]", "address[dl_address][address_line_2]", "address[dl_address][city]", "address[dl_address][state]", "address[dl_address][postal]", "address[dl_address][country]", "address[credit_home_address][street_address]", "address[credit_home_address][address_line_2]", "address[credit_home_address][city]", "address[credit_home_address][state]", "address[credit_home_address][postal]", "address[credit_home_address][country]", "emails[hosting_uuid]", "emails[email]", "emails[password]", "emails[phone]"},
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="middle_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="date_of_birth", type="string", format="date"),
      *                         @OA\Property(property="ssn_cpn", type="text"),
      *                         @OA\Property(property="company_association", type="text"),
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
      *
      *                         @OA\Property(property="address[dl_address][street_address]", type="text"),
      *                         @OA\Property(property="address[dl_address][address_line_2]", type="text"),
      *                         @OA\Property(property="address[dl_address][city]", type="text"),
      *                         @OA\Property(property="address[dl_address][state]", type="text"),
      *                         @OA\Property(property="address[dl_address][postal]", type="text"),
      *                         @OA\Property(property="address[dl_address][country]", type="text"),
      *
      *                         @OA\Property(property="address[credit_home_address][street_address]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][address_line_2]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][city]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][state]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][postal]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][country]", type="text"),
      *
      *                         @OA\Property(property="emails[hosting_uuid]", type="text"),
      *                         @OA\Property(property="emails[email]", type="text"),
      *                         @OA\Property(property="emails[password]", type="text"),
      *                         @OA\Property(property="emails[phone]", type="text"),
      *
      *                         @OA\Property(property="files[dl_upload][front]", type="file", format="binary"),
      *                         @OA\Property(property="files[dl_upload][back]", type="file", format="binary"),
      *                         @OA\Property(property="files[ssn_upload][front]", type="file", format="binary"),
      *                         @OA\Property(property="files[ssn_upload][back]", type="file", format="binary"),
      *                         @OA\Property(property="files[cpn_docs_upload]", type="file", format="binary")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authorized"),
      *             @OA\Response(response=403, description="Not Authentificated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'middle_name' => '',
            'last_name' => 'required|string',
            'date_of_birth' => 'required|date',
            'ssn_cpn' => 'required|string',
            'company_association' => 'required|string',
            'phone_type' => 'required|string',
            'phone_number' => 'required|string',
            // addresses
            'address.dl_address.street_address' => 'required|string',
            'address.dl_address.address_line_2' => 'required|string',
            'address.dl_address.city' => 'required|string',
            'address.dl_address.state' => 'required|string',
            'address.dl_address.postal' => 'required|string',
            'address.dl_address.country' => 'required|string',

            'address.credit_home_address.street_address' => 'required|string',
            'address.credit_home_address.address_line_2' => 'required|string',
            'address.credit_home_address.city' => 'required|string',
            'address.credit_home_address.state' => 'required|string',
            'address.credit_home_address.postal' => 'required|string',
            'address.credit_home_address.country' => 'required|string',
            // emails
            'emails.hosting_uuid' => 'required|string',
            'emails.email' => 'required|string',
            'emails.password' => 'required|string',
            'emails.phone' => 'required|string',

            'user_uuid' => 'string',
            'role_alias' => 'string'
        ]);

        $check = [];

        $tmpCheck = $this->emailService->check($validated['emails']);
        $check = array_merge($check, $tmpCheck);

        $tmpCheck = $this->addressService->check($validated['address']['dl_address'], 'dl_address');
        $check = array_merge($check, $tmpCheck);
        $tmpCheck = $this->addressService->check($validated['address']['credit_home_address'], 'credit_home_address');
        $check = array_merge($check, $tmpCheck);

        $tmpCheck = $this->directorService->check($validated);
        $check = array_merge($check, $tmpCheck);
        
        // exsist
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        // permission
        if ($validated['role_alias']!=Config::get('common.role.headquarters')){ // not headquarters 403
            return response()->json([
                'data' => 'Not Authentificated',
            ], 403);
        }

        $director = $this->directorService->create($validated);

        // email
        $validated['emails']['entity_uuid'] = $director['uuid'];
        $this->emailService->create($validated['emails']);

        //address
        $validated['address']['dl_address']['address_parent'] = 'dl_address';
        $validated['address']['dl_address']['entity_uuid'] = $director['uuid'];
        $this->addressService->create($validated['address']['dl_address']);
        $validated['address']['credit_home_address']['address_parent'] = 'credit_home_address';
        $validated['address']['credit_home_address']['entity_uuid'] = $director['uuid'];
        $this->addressService->create($validated['address']['credit_home_address']);


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
                        $file->entity_uuid = $director['uuid'];
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

        return new DirectorResource($director);
    }

    /**     @OA\GET(
      *         path="/api/director/{uuid}",
      *         operationId="get_director",
      *         tags={"Director"},
      *         summary="Get director",
      *         description="Get director",
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Director $director)
    {
        $director = $this->directorService->one($director);
        return $director;
    }

    /**     @OA\PUT(
      *         path="/api/director",
      *         operationId="update_director",
      *         tags={"Director"},
      *         summary="Update director (not working on swagger)",
      *         description="Update director",
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
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         required={},
      *                         @OA\Property(property="user_uuid", type="text"),
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="middle_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="date_of_birth", type="string", format="date"),
      *                         @OA\Property(property="ssn_cpn", type="text"),
      *                         @OA\Property(property="company_association", type="text"),
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
      *
      *                         @OA\Property(property="address[dl_address][street_address]", type="text"),
      *                         @OA\Property(property="address[dl_address][address_line_2]", type="text"),
      *                         @OA\Property(property="address[dl_address][city]", type="text"),
      *                         @OA\Property(property="address[dl_address][state]", type="text"),
      *                         @OA\Property(property="address[dl_address][postal]", type="text"),
      *                         @OA\Property(property="address[dl_address][country]", type="text"),
      *
      *                         @OA\Property(property="address[credit_home_address][street_address]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][address_line_2]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][city]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][state]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][postal]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][country]", type="text"),
      *
      *                         @OA\Property(property="emails[hosting_uuid]", type="text"),
      *                         @OA\Property(property="emails[email]", type="text"),
      *                         @OA\Property(property="emails[password]", type="text"),
      *                         @OA\Property(property="emails[phone]", type="text"),
      *
      *                         @OA\Property(property="files[dl_upload][front][]", type="file", format="binary"),
      *                         @OA\Property(property="files[dl_upload][back][]", type="file", format="binary"),
      *                         @OA\Property(property="files[ssn_upload][front][]", type="file", format="binary"),
      *                         @OA\Property(property="files[ssn_upload][back][]", type="file", format="binary"),
      *                         @OA\Property(property="files[cpn_docs_upload][]", type="file", format="binary"),
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
      *             @OA\Response(response=401, description="Not Authorized"),
      *             @OA\Response(response=403, description="Not Authentificated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function update(Request $request, Director $director)
    {
        $validated = $request->validate([
            'user_uuid' => 'required|string',
            'first_name' => 'required|string',
            'middle_name' => '',
            'last_name' => 'required|string',
            'date_of_birth' => 'required|date',
            'ssn_cpn' => 'required|string',
            'company_association' => 'required|string',
            'phone_type' => 'required|string',
            'phone_number' => 'required|string',
            // addresses
            'address.dl_address.street_address' => 'required|string',
            'address.dl_address.address_line_2' => 'required|string',
            'address.dl_address.city' => 'required|string',
            'address.dl_address.state' => 'required|string',
            'address.dl_address.postal' => 'required|string',
            'address.dl_address.country' => 'required|string',

            'address.credit_home_address.street_address' => 'required|string',
            'address.credit_home_address.address_line_2' => 'required|string',
            'address.credit_home_address.city' => 'required|string',
            'address.credit_home_address.state' => 'required|string',
            'address.credit_home_address.postal' => 'required|string',
            'address.credit_home_address.country' => 'required|string',
            // emails
            'emails.hosting_uuid' => 'required|string',
            'emails.email' => 'required|string',
            'emails.password' => 'required|string',
            'emails.phone' => 'required|string',
            // files to delete by uuid
            'files_to_delete' => 'array',

            'user_uuid' => 'string',
            'role_alias' => 'string'
        ]);

        $check = [];

        $tmpCheck = $this->emailService->check_ignore($validated['emails'], $director->uuid);
        $check = array_merge($check, $tmpCheck);

        $tmpCheck = $this->addressService->check_ignore($validated['address']['dl_address'], $director->uuid, 'dl_address');
        $check = array_merge($check, $tmpCheck);
        $tmpCheck = $this->addressService->check_ignore($validated['address']['credit_home_address'], $director->uuid, 'credit_home_address');
        $check = array_merge($check, $tmpCheck);

        $tmpCheck = $this->directorService->check_ignore($validated, $director->uuid);
        $check = array_merge($check, $tmpCheck);
        
        // exsist
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        // permission
        if ($validated['role_alias']!=Config::get('common.role.headquarters')){ // not headquarters 403
            return response()->json([
                'data' => 'Not Authentificated',
            ], 403);
        }

        $director = $this->directorService->update($director, $validated);

        $email = Email::where('entity_uuid', $director['uuid']);
        $email->update($validated['emails']);

        $address = Address::where('entity_uuid', $director['uuid'])
                                    ->where('address_parent', 'dl_address');
        $address->update($validated['address']['dl_address']);
        $address = Address::where('entity_uuid', $director['uuid'])
                                    ->where('address_parent', 'credit_home_address');
        $address->update($validated['address']['credit_home_address']);

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
                        $file->entity_uuid = $director['uuid'];
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

        // Activity log
        Activity::create([
            'user_uuid' => $validated['user_uuid'],
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.director.update'),
            'changes' => json_encode($validated),
            'action_code' => Config::get('common.activity.codes.director_update'),
            'status' => Config::get('common.status.actived')
        ]);

        return new DirectorResource($director);
    }

    /**     @OA\DELETE(
      *         path="/api/director/{uuid}",
      *         operationId="delete_director",
      *         tags={"Director"},
      *         summary="Delete director",
      *         description="Delete director",
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(Request $request, Director $director)
    {
        // permission
        if ($request->role_alias!=Config::get('common.role.headquarters')){
            return response()->json([
                'data' => 'Not Authentificated',
            ], 403);
        }

        $this->directorService->delete($director);
    }

    /**     @OA\GET(
      *         path="/api/director-search/{search}",
      *         operationId="get_director_search",
      *         tags={"Director"},
      *         summary="Get director search",
      *         description="Get director search",
      *             @OA\Parameter(
      *                 name="search",
      *                 in="path",
      *                 description="director search",
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
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function search($search)
    {
        $directors = $this->directorService->search($search);
        return $directors;
    }

    /**     @OA\POST(
      *         path="/api/director-pending",
      *         operationId="pending_director",
      *         tags={"Director"},
      *         summary="Pending director (not working on swagger)",
      *         description="Pending director",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={},
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="middle_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="date_of_birth", type="string", format="date"),
      *                         @OA\Property(property="ssn_cpn", type="text"),
      *                         @OA\Property(property="company_association", type="text"),
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
      *
      *                         @OA\Property(property="address[dl_address][street_address]", type="text"),
      *                         @OA\Property(property="address[dl_address][address_line_2]", type="text"),
      *                         @OA\Property(property="address[dl_address][city]", type="text"),
      *                         @OA\Property(property="address[dl_address][state]", type="text"),
      *                         @OA\Property(property="address[dl_address][postal]", type="text"),
      *                         @OA\Property(property="address[dl_address][country]", type="text"),
      *
      *                         @OA\Property(property="address[credit_home_address][street_address]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][address_line_2]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][city]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][state]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][postal]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][country]", type="text"),
      *
      *                         @OA\Property(property="emails[hosting_uuid]", type="text"),
      *                         @OA\Property(property="emails[email]", type="text"),
      *                         @OA\Property(property="emails[password]", type="text"),
      *                         @OA\Property(property="emails[phone]", type="text"),
      *
      *                         @OA\Property(property="files[dl_upload][front]", type="file", format="binary"),
      *                         @OA\Property(property="files[dl_upload][back]", type="file", format="binary"),
      *                         @OA\Property(property="files[ssn_upload][front]", type="file", format="binary"),
      *                         @OA\Property(property="files[ssn_upload][back]", type="file", format="binary"),
      *                         @OA\Property(property="files[cpn_docs_upload]", type="file", format="binary")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authorized"),
      *             @OA\Response(response=403, description="Not Authenticate"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function pending(Request $request)
    {
        $validated = $request->validate([
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'date_of_birth' => '',
            'ssn_cpn' => '',
            'company_association' => '',
            'phone_type' => '',
            'phone_number' => '',
            // addresses
            'address.dl_address.street_address' => '',
            'address.dl_address.address_line_2' => '',
            'address.dl_address.city' => '',
            'address.dl_address.state' => '',
            'address.dl_address.postal' => '',
            'address.dl_address.country' => '',

            'address.credit_home_address.street_address' => '',
            'address.credit_home_address.address_line_2' => '',
            'address.credit_home_address.city' => '',
            'address.credit_home_address.state' => '',
            'address.credit_home_address.postal' => '',
            'address.credit_home_address.country' => '',
            // emails
            'emails.hosting_uuid' => '',
            'emails.email' => '',
            'emails.password' => '',
            'emails.phone' => '',

            'user_uuid' => 'string',
            'role_alias' => 'string'
        ]);

        $director = $this->directorService->pending($validated);

        // email
        $validated['emails']['entity_uuid'] = $director['uuid'];
        $this->emailService->create($validated['emails']);

        //address
        $validated['address']['dl_address']['address_parent'] = 'dl_address';
        $validated['address']['dl_address']['entity_uuid'] = $director['uuid'];
        $this->addressService->create($validated['address']['dl_address']);
        $validated['address']['credit_home_address']['address_parent'] = 'credit_home_address';
        $validated['address']['credit_home_address']['entity_uuid'] = $director['uuid'];
        $this->addressService->create($validated['address']['credit_home_address']);

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
                        $file->entity_uuid = $director['uuid'];
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

        return new DirectorResource($director);
    }

    /**     @OA\PUT(
      *         path="/api/director-pending-update",
      *         operationId="pending_update_director",
      *         tags={"Director"},
      *         summary="Pending update director (not working on swagger)",
      *         description="Pending update director",
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
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         required={},
      *                         @OA\Property(property="user_uuid", type="text"),
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="middle_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="date_of_birth", type="string", format="date"),
      *                         @OA\Property(property="ssn_cpn", type="text"),
      *                         @OA\Property(property="company_association", type="text"),
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
      *
      *                         @OA\Property(property="address[dl_address][street_address]", type="text"),
      *                         @OA\Property(property="address[dl_address][address_line_2]", type="text"),
      *                         @OA\Property(property="address[dl_address][city]", type="text"),
      *                         @OA\Property(property="address[dl_address][state]", type="text"),
      *                         @OA\Property(property="address[dl_address][postal]", type="text"),
      *                         @OA\Property(property="address[dl_address][country]", type="text"),
      *
      *                         @OA\Property(property="address[credit_home_address][street_address]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][address_line_2]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][city]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][state]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][postal]", type="text"),
      *                         @OA\Property(property="address[credit_home_address][country]", type="text"),
      *
      *                         @OA\Property(property="emails[hosting_uuid]", type="text"),
      *                         @OA\Property(property="emails[email]", type="text"),
      *                         @OA\Property(property="emails[password]", type="text"),
      *                         @OA\Property(property="emails[phone]", type="text"),
      *
      *                         @OA\Property(property="files[dl_upload][front][]", type="file", format="binary"),
      *                         @OA\Property(property="files[dl_upload][back][]", type="file", format="binary"),
      *                         @OA\Property(property="files[ssn_upload][front][]", type="file", format="binary"),
      *                         @OA\Property(property="files[ssn_upload][back][]", type="file", format="binary"),
      *                         @OA\Property(property="files[cpn_docs_upload][]", type="file", format="binary"),
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
      *             @OA\Response(response=401, description="Not Authorized"),
      *             @OA\Response(response=403, description="Not Authenticate"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function pending_update(Request $request, $uuid)
    {
        $director = Director::where('uuid', $uuid)->get();
        
        $validated = $request->validate([
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'date_of_birth' => '',
            'ssn_cpn' => '',
            'company_association' => '',
            'phone_type' => '',
            'phone_number' => '',
            // addresses
            'address.dl_address.street_address' => '',
            'address.dl_address.address_line_2' => '',
            'address.dl_address.city' => '',
            'address.dl_address.state' => '',
            'address.dl_address.postal' => '',
            'address.dl_address.country' => '',

            'address.credit_home_address.street_address' => '',
            'address.credit_home_address.address_line_2' => '',
            'address.credit_home_address.city' => '',
            'address.credit_home_address.state' => '',
            'address.credit_home_address.postal' => '',
            'address.credit_home_address.country' => '',
            // emails
            'emails.hosting_uuid' => '',
            'emails.email' => '',
            'emails.password' => '',
            'emails.phone' => '',

            'user_uuid' => 'string',
            'role_alias' => 'string'
        ]);

        $director = $this->directorService->update($director, $validated);

        if (isset($validated['emails'])){
            $email = Email::where('entity_uuid', $director['uuid']);   
            $email->update($validated['emails']);
        }

        if (isset($validated['address']['dl_address'])){
            $address = Address::where('entity_uuid', $director['uuid'])
                                        ->where('address_parent', 'dl_address');
            $address->update($validated['address']['dl_address']);
        }
        if (isset($validated['address']['credit_home_address'])){
            $address = Address::where('entity_uuid', $director['uuid'])
                                        ->where('address_parent', 'credit_home_address');
            $address->update($validated['address']['credit_home_address']);
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
                        $file->entity_uuid = $director['uuid'];
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

        return new DirectorResource($director);
    }

}
