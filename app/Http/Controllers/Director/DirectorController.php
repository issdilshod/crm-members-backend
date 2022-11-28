<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Resources\Director\DirectorResource;
use App\Models\Director\Director;
use App\Policies\PermissionPolicy;
use App\Services\Director\DirectorService;
use App\Services\Helper\AddressService;
use App\Services\Helper\EmailService;
use App\Services\Helper\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class DirectorController extends Controller
{

    private $directorService;
    private $addressService;
    private $emailService;
    private $fileService;

    public function __construct()
    {
        $this->directorService = new DirectorService();
        $this->addressService = new AddressService();
        $this->emailService = new EmailService();
        $this->fileService = new FileService();
    }

    /**     @OA\GET(
      *         path="/api/director",
      *         operationId="list_director",
      *         tags={"Director"},
      *         summary="List of director",
      *         description="List of director",
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $directors = $this->directorService->all();
        return $directors;
    }

    /**     @OA\POST(
      *         path="/api/director",
      *         operationId="post_director",
      *         tags={"Director"},
      *         summary="Add director",
      *         description="Add director",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"first_name", "last_name", "date_of_birth", "ssn_cpn", "phone_type", "phone_number"},
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="middle_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="date_of_birth", type="string", format="date"),
      *                         @OA\Property(property="ssn_cpn", type="text"),
      *                         @OA\Property(property="company_association", type="text"),
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
      *
      *                         @OA\Property(property="files[]", type="text")
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
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
            'addresses' => 'array',

            // emails
            'emails' => 'array',

            // files
            'files' => 'array',

            'user_uuid' => 'string'
        ]);

        $check = [];

        /*if (isset($validated['emails'])){
            $tmpCheck = $this->emailService->check($validated['emails']);
            $check = array_merge($check, $tmpCheck);
        }*/

        $tmpCheck = $this->directorService->check($validated);
        $check = array_merge($check, $tmpCheck);
        
        // exsist
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $director = $this->directorService->create($validated);

        // emails
        if (isset($validated['emails'])){
            foreach ($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $this->emailService->save($value);
            endforeach;
        }

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $this->addressService->save($value);
            endforeach;
        }

        // files
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $director->uuid], $value['uuid']);
            endforeach;
        }

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
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Request $request, Director $director)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.access'))){
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
      *         summary="Update director",
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
      *                         required={"first_name", "last_name", "date_of_birth", "ssn_cpn", "phone_type", "phone_number"},
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="middle_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="date_of_birth", type="string", format="date"),
      *                         @OA\Property(property="ssn_cpn", type="text"),
      *                         @OA\Property(property="company_association", type="text"),
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
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
    public function update(Request $request, Director $director)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
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
            'addresses' => 'array',

            // emails
            'emails' => 'array',

            // files & files to delete by uuid
            'files' => 'array',
            'files_to_delete' => 'array'
        ]);

        $check = [];

        /*if (isset($validated['emails'])){
            $tmpCheck = $this->emailService->check_ignore($validated['emails'], $director->uuid);
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

        $director = $this->directorService->update($director, $validated, $request->user_uuid);

        // emails
        if (isset($validated['emails'])){
            foreach ($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $this->emailService->save($value);
            endforeach;
        }

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $this->addressService->save($value);
            endforeach;
        }

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // file to upload
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $director->uuid], $value['uuid']);
            endforeach;
        }

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
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(Request $request, Director $director)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.delete'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
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
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function search(Request $request, $search)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $directors = $this->directorService->search($search);
        return $directors;
    }

    /**     @OA\POST(
      *         path="/api/director-pending",
      *         operationId="pending_director",
      *         tags={"Director"},
      *         summary="Pending director",
      *         description="Pending director",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"first_name", "last_name", "date_of_birth", "ssn_cpn", "phone_type", "phone_number"},
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="middle_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="date_of_birth", type="string", format="date"),
      *                         @OA\Property(property="ssn_cpn", type="text"),
      *                         @OA\Property(property="company_association", type="text"),
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
      *
      *                         @OA\Property(property="files[]", type="text")
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
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
            'addresses' => 'array',
            
            // emails
            'emails' => 'array',

            // files
            'files' => 'array',

            'user_uuid' => 'string'
        ]);

        $check = [];

        /*if (isset($validated['emails'])){
            $tmpCheck = $this->emailService->check($validated['emails']);
            $check = array_merge($check, $tmpCheck);
        }*/

        $tmpCheck = $this->directorService->check($validated);
        $check = array_merge($check, $tmpCheck);
        
        // exsist
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $director = $this->directorService->pending($validated);

        // emails
        if (isset($validated['emails'])){
            foreach ($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $value['status'] = Config::get('common.status.pending');
                $this->emailService->save($value);
            endforeach;
        }

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $value['status'] = Config::get('common.status.pending');
                $this->addressService->save($value);
            endforeach;
        }

        // files to upload
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $director->uuid], $value['uuid']);
            endforeach;
        }

        return new DirectorResource($director);
    }

    /**     @OA\PUT(
      *         path="/api/director-pending-update/{uuid}",
      *         operationId="pending_update_director",
      *         tags={"Director"},
      *         summary="Pending update director",
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
      *                         required={"first_name", "last_name", "date_of_birth", "ssn_cpn", "phone_type", "phone_number"},
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="middle_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="date_of_birth", type="string", format="date"),
      *                         @OA\Property(property="ssn_cpn", type="text"),
      *                         @OA\Property(property="company_association", type="text"),
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
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
        $director = Director::where('uuid', $uuid)->first();

        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            } else{
                if ($director->user_uuid!=$request->user_uuid){
                    if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.pre_save'))){
                        return response()->json([ 'data' => 'Not Authorized' ], 403);
                    }
                }
            }
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
            'addresses' => 'array',

            // emails
            'emails' => 'array',

            // files
            'files' => 'array',
            'files_to_delete' => 'array',
        ]);

        $check = [];

        /*if (isset($validated['emails'])){
            $tmpCheck = $this->emailService->check_ignore($validated['emails'], $director->uuid);
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

        $director = $this->directorService->pending_update($uuid, $validated, $request->user_uuid);

        // emails
        if (isset($validated['emails'])){
            foreach ($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $value['status'] = Config::get('common.status.pending');
                $this->emailService->save($value);
            endforeach;
        }

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $value['status'] = Config::get('common.status.pending');
                $this->addressService->save($value);
            endforeach;
        }

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // file to upload
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $director->uuid], $value['uuid']);
            endforeach;
        }

        return new DirectorResource($director);
    }

    /**     @OA\PUT(
      *         path="/api/director-accept/{uuid}",
      *         operationId="accept_director",
      *         tags={"Director"},
      *         summary="Accept director",
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
      *                         required={"first_name", "last_name", "date_of_birth", "ssn_cpn", "phone_type", "phone_number"},
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="middle_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="date_of_birth", type="string", format="date"),
      *                         @OA\Property(property="ssn_cpn", type="text"),
      *                         @OA\Property(property="company_association", type="text"),
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.accept'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
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
            'addresses' => 'array',

            // emails
            'emails' => 'array',

            // files & files to delete by uuid
            'files' => 'array',
            'files_to_delete' => 'array',
        ]);

        $director = Director::where('uuid', $uuid)->first();

        $check = [];

        /*if (isset($validated['emails'])){
            $tmpCheck = $this->emailService->check_ignore($validated['emails'], $director->uuid);
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

        // emails
        if (isset($validated['emails'])){
            foreach ($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $value['status'] = Config::get('common.status.actived');
                $this->emailService->save($value);
            endforeach;
        }
        
        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $value['status'] = Config::get('common.status.actived');
                $this->addressService->save($value);
            endforeach;
        }
           
        // files to delete
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files to upload
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $director->uuid], $value['uuid']);
            endforeach;
        }

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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.acccept'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
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
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function by_user(Request $request)
    {
        //$directors = $this->directorService->by_user($request->user_uuid, '');
        //return $directors;
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
      *             @OA\Response(response=403, description="Not Autorized"),
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

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.access'))){
            $permissions[] = Config::get('common.permission.director.access');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.store'))){
            $permissions[] = Config::get('common.permission.director.store');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.save'))){
            $permissions[] = Config::get('common.permission.director.save');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.pre_save'))){
            $permissions[] = Config::get('common.permission.director.pre_save');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.delete'))){
            $permissions[] = Config::get('common.permission.director.delete');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.accept'))){
            $permissions[] = Config::get('common.permission.director.accept');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.download'))){
            $permissions[] = Config::get('common.permission.director.download');
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

    /**     @OA\PUT(
      *         path="/api/director-override/{uuid}",
      *         operationId="override_director",
      *         tags={"Director"},
      *         summary="Override director (not working on swagger)",
      *         description="Override director",
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
      *                         @OA\Property(property="addresses", type="text"),
      *
      *                         @OA\Property(property="emails[]", type="text"),
      *
      *                         @OA\Property(property="files[]", type="text"),
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
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

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
            'addresses' => 'array',

            // emails
            'emails' => 'array',

            // files & files to delete by uuid
            'files' => 'array',
            'files_to_delete' => 'array',
        ]);

        $director = Director::where('uuid', $uuid)->first();

        $director = $this->directorService->accept($director, $validated, $request->user_uuid, true);

        // emails
        if (isset($validated['emails'])){
            foreach ($validated['emails'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $value['status'] = Config::get('common.status.actived');
                $this->emailService->save($value);
            endforeach;
        }

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $director->uuid;
                $value['status'] = Config::get('common.status.actived');
                $this->addressService->save($value);
            endforeach;
        }
        
        // files to delete
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files to upload
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $director->uuid], $value['uuid']);
            endforeach;
        }

        return new DirectorResource($director);
    }

}
