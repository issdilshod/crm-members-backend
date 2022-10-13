<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Resources\Director\DirectorResource;
use App\Models\Director\Director;
use App\Models\Helper\Address;
use App\Models\Helper\Email;
use App\Models\Helper\File;
use App\Policies\PermissionPolicy;
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
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.view'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

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
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function store(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.store'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'first_name' => 'required',
            'middle_name' => '',
            'last_name' => 'required',
            'date_of_birth' => 'required',
            'ssn_cpn' => 'required',
            'company_association' => '',
            'phone_type' => 'required',
            'phone_number' => 'required',
            // addresses
            'address.dl_address' => 'array',

            'address.credit_home_address' => 'array',
            // emails
            'emails' => 'array',

            'user_uuid' => 'string'
        ]);

        $check = [];

        if (isset($validated['emails'])){
            $tmpCheck = $this->emailService->check($validated['emails']);
            $check = array_merge($check, $tmpCheck);
        }

        if (isset($validated['address']['dl_address'])){
            $tmpCheck = $this->addressService->check($validated['address']['dl_address'], 'dl_address');
            $check = array_merge($check, $tmpCheck);
        }
        if (isset($validated['address']['credit_home_address'])){
            $tmpCheck = $this->addressService->check($validated['address']['credit_home_address'], 'credit_home_address');
            $check = array_merge($check, $tmpCheck);
        }

        $tmpCheck = $this->directorService->check($validated);
        $check = array_merge($check, $tmpCheck);
        
        // exsist
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
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
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Request $request, Director $director)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            if ($request->user_uuid!=$director->user_uuid){ // if creator not them
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

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
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function update(Request $request, Director $director)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.update'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'first_name' => 'required',
            'middle_name' => '',
            'last_name' => 'required',
            'date_of_birth' => 'required',
            'ssn_cpn' => 'required',
            'company_association' => '',
            'phone_type' => 'required',
            'phone_number' => 'required',
            // addresses
            'address.dl_address' => 'array',

            'address.credit_home_address' => 'array',
            // emails
            'emails' => 'array',
            // files to delete by uuid
            'files_to_delete' => 'array',

            'user_uuid' => 'string'
        ]);

        $check = [];

        if (isset($validated['emails'])){
            $tmpCheck = $this->emailService->check_ignore($validated['emails'], $director->uuid);
            $check = array_merge($check, $tmpCheck);
        }

        if (isset($validated['address']['dl_address'])){
            $tmpCheck = $this->addressService->check_ignore($validated['address']['dl_address'], $director->uuid, 'dl_address');
            $check = array_merge($check, $tmpCheck);
        }
        if (isset($validated['address']['credit_home_address'])){
            $tmpCheck = $this->addressService->check_ignore($validated['address']['credit_home_address'], $director->uuid, 'credit_home_address');
            $check = array_merge($check, $tmpCheck);
        }

        $tmpCheck = $this->directorService->check_ignore($validated, $director->uuid);
        $check = array_merge($check, $tmpCheck);
        
        // exsist
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

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
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(Request $request, Director $director)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.delete'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
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
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function search(Request $request, $search)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.view'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

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
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function pending(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.save'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'first_name' => 'required',
            'middle_name' => '',
            'last_name' => 'required',
            'date_of_birth' => 'required',
            'ssn_cpn' => 'required',
            'company_association' => '',
            'phone_type' => 'required',
            'phone_number' => 'required',
            // addresses
            'address.dl_address' => 'array',

            'address.credit_home_address' => 'array',
            
            // emails
            'emails' => 'array',

            'user_uuid' => 'string'
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
      *         path="/api/director-pending-update/{uuid}",
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
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function pending_update(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.save'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'first_name' => 'required',
            'middle_name' => '',
            'last_name' => 'required',
            'date_of_birth' => 'required',
            'ssn_cpn' => 'required',
            'company_association' => '',
            'phone_type' => 'required',
            'phone_number' => 'required',

            // addresses
            'address.dl_address' => 'array',

            'address.credit_home_address' => 'array',
            // emails
            'emails' => 'array',

            'user_uuid' => 'string'
        ]);

        $director = $this->directorService->pending_update($uuid, $validated);

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

    /**     @OA\PUT(
      *         path="/api/director-accept/{uuid}",
      *         operationId="accept_director",
      *         tags={"Director"},
      *         summary="Accept director (not working on swagger)",
      *         description="Accept director",
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
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function accept(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.accept'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }
    
        $validated = $request->validate([
            'first_name' => 'required',
            'middle_name' => '',
            'last_name' => 'required',
            'date_of_birth' => 'required',
            'ssn_cpn' => 'required',
            'company_association' => '',
            'phone_type' => 'required',
            'phone_number' => 'required',
            // addresses
            'address.dl_address' => 'array',

            'address.credit_home_address' => 'array',

            // emails
            'emails' => 'array',

            // files to delete by uuid
            'files_to_delete' => 'array',
        ]);

        $director = Director::where('uuid', $uuid)->first();

        $check = [];

        if (isset($validated['emails'])){
            $tmpCheck = $this->emailService->check_ignore($validated['emails'], $director->uuid);
            $check = array_merge($check, $tmpCheck);
        }

        /*if (isset($validated['address']['dl_address'])){
            $tmpCheck = $this->addressService->check_ignore($validated['address']['dl_address'], $director->uuid, 'dl_address');
            $check = array_merge($check, $tmpCheck);
        }
        if (isset($validated['address']['credit_home_address'])){
            $tmpCheck = $this->addressService->check_ignore($validated['address']['credit_home_address'], $director->uuid, 'credit_home_address');
            $check = array_merge($check, $tmpCheck);
        }*/

        $tmpCheck = $this->directorService->check_ignore($validated, $director->uuid);
        $check = array_merge($check, $tmpCheck);
        
        // exsist
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $director = $this->directorService->accept($director, $validated, $request->user_uuid);

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

    /**     @OA\PUT(
      *         path="/api/director-reject/{uuid}",
      *         operationId="reject_director",
      *         tags={"Director"},
      *         summary="Reject director",
      *         description="Reject director",
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
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function reject(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.reject'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $this->directorService->reject($uuid, $request->user_uuid);
    }

    /**     @OA\GET(
      *         path="/api/director-user",
      *         operationId="list_director_by_user",
      *         tags={"Director"},
      *         summary="List of director by user",
      *         description="List of director by user",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function by_user(Request $request)
    {
        $directors = $this->directorService->by_user($request->user_uuid);
        return $directors;
    }

    /**     @OA\GET(
      *         path="/api/director-permission",
      *         operationId="director_permission",
      *         tags={"Director"},
      *         summary="Get director permission of user",
      *         description="Get director permission of user",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function permission(Request $request)
    {
        $permissions = [];

        // permission
        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.view'))){
            $permissions[] = Config::get('common.permission.director.view');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.store'))){
            $permissions[] = Config::get('common.permission.director.store');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.update'))){
            $permissions[] = Config::get('common.permission.director.update');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.save'))){
            $permissions[] = Config::get('common.permission.director.save');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.delete'))){
            $permissions[] = Config::get('common.permission.director.delete');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.accept'))){
            $permissions[] = Config::get('common.permission.director.accept');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.reject'))){
            $permissions[] = Config::get('common.permission.director.reject');
        }

        return $permissions;
    }

    /**     @OA\GET(
      *         path="/api/director-list/{search?}",
      *         operationId="director_list_search",
      *         tags={"Director"},
      *         summary="Get director list and search",
      *         description="Get director list and search",
      *             @OA\Parameter(
      *                 name="search",
      *                 in="path",
      *                 description="director full name",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="text"
      *                 )
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function director_list($search = '')
    {
        $directors = $this->directorService->director_list($search);
        return $directors;
    }

}
