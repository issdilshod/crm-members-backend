<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Models\Contact\Contact;
use App\Policies\PermissionPolicy;
use App\Services\Contact\ContactService;
use App\Services\Helper\AccountSecurityService;
use App\Services\Helper\FileService;
use App\Services\Helper\RejectReasonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ContactController extends Controller
{

    private $contactSerivce;
    private $rejectReasonService;
    private $accountSecurityService;
    private $fileService;

    public function __construct()
    {
        $this->contactSerivce = new ContactService();
        $this->rejectReasonService = new RejectReasonService();
        $this->accountSecurityService = new AccountSecurityService();
        $this->fileService = new FileService();
    }
    
    /**     @OA\GET(
         *         path="/api/contact",
        *         operationId="list_contact",
        *         tags={"Contact"},
        *         summary="List of contact",
        *         description="List of contact",
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $contacts = $this->contactSerivce->all();

        return $contacts;
    }
  
      /**     @OA\POST(
        *         path="/api/contact",
        *         operationId="post_contact",
        *         tags={"Contact"},
        *         summary="Add contact",
        *         description="Add contact",
        *             @OA\RequestBody(
        *                 @OA\JsonContent(),
        *                 @OA\MediaType(
        *                     mediaType="multipart/form-data",
        *                     @OA\Schema(
        *                         type="object",
        *                         required={},
        *                         
        *                         @OA\Property(property="first_name", type="text"),
        *                         @OA\Property(property="last_name", type="text"),
        *                         @OA\Property(property="email", type="text"),
        *                         @OA\Property(property="phone_number", type="text"),
        *                         @OA\Property(property="company_name", type="text"),
        *                         @OA\Property(property="company_phone_number", type="text"),
        *                         @OA\Property(property="company_email", type="text"),
        *                         @OA\Property(property="company_website", type="text"),
        *                         @OA\Property(property="online_account", type="text"),
        *                         @OA\Property(property="account_username", type="text"),
        *                         @OA\Property(property="account_password", type="text"),
        *                         @OA\Property(property="fax", type="text"),
        *                         @OA\Property(property="security_questions", type="text"),
        *                         @OA\Property(property="account_securities[]", type="text"),
        *                         @OA\Property(property="notes", type="text"),
        *
        *                         @OA\Property(property="files[]", type="text"),
        *                         @OA\Property(property="files_to_delete[]", type="text"),
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'first_name' => '', 
            'last_name' => '', 
            'email' => '', 
            'phone_number' => '', 
            'company_name' => '', 
            'company_phone_number' => '', 
            'company_email' => '', 
            'company_website' => '', 
            'online_account' => '', 
            'account_username' => '', 
            'account_password' => '', 
            'fax' => '', 
            'security_questions' => '', 
            'account_securities' => 'array', 
            'notes' => '', 

            // files
            'files' => 'array',
            'files_to_delete' => 'array',

            'user_uuid' => ''
        ]);

        $contact = $this->contactSerivce->create($validated);

        // account securities
        if (isset($validated['account_securities'])){
            foreach ($validated['account_securities'] AS $key => $value):
                $value['entity_uuid'] = $contact['uuid'];
                $this->accountSecurityService->save($value);
            endforeach;
        }

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $contact->uuid], $value['uuid']);
            endforeach;
        }

        return $contact;
    }
  
      /**     @OA\GET(
        *         path="/api/contact/{uuid}",
        *         operationId="get_contact",
        *         tags={"Contact"},
        *         summary="Get contact",
        *         description="Get contact",
        *             @OA\Parameter(
        *                 name="uuid",
        *                 in="path",
        *                 description="contact uuid",
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
    public function show(Request $request, Contact $contact)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $contact = $this->contactSerivce->one($contact);

        return $contact;
    }
  
      /**     @OA\PUT(
        *         path="/api/contact/{uuid}",
        *         operationId="update_contact",
        *         tags={"Contact"},
        *         summary="Update contact",
        *         description="Update contact",
        *             @OA\Parameter(
        *                 name="uuid",
        *                 in="path",
        *                 description="contact uuid",
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
        *                         
        *                         @OA\Property(property="first_name", type="text"),
        *                         @OA\Property(property="last_name", type="text"),
        *                         @OA\Property(property="email", type="text"),
        *                         @OA\Property(property="phone_number", type="text"),
        *                         @OA\Property(property="company_name", type="text"),
        *                         @OA\Property(property="company_phone_number", type="text"),
        *                         @OA\Property(property="company_email", type="text"),
        *                         @OA\Property(property="company_website", type="text"),
        *                         @OA\Property(property="online_account", type="text"),
        *                         @OA\Property(property="account_username", type="text"),
        *                         @OA\Property(property="account_password", type="text"),
        *                         @OA\Property(property="fax", type="text"),
        *                         @OA\Property(property="security_questions", type="text"),
        *                         @OA\Property(property="account_securities[]", type="text"),
        *                         @OA\Property(property="notes", type="text"),
        *
        *                         @OA\Property(property="files[]", type="text"),
        *                         @OA\Property(property="files_to_delete[]", type="text"),
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
    public function update(Request $request, Contact $contact)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'first_name' => '', 
            'last_name' => '', 
            'email' => '', 
            'phone_number' => '', 
            'company_name' => '', 
            'company_phone_number' => '', 
            'company_email' => '', 
            'company_website' => '', 
            'online_account' => '', 
            'account_username' => '', 
            'account_password' => '',
            'fax' => '',  
            'security_questions' => '', 
            'account_securities' => 'array', 

            // files
            'files' => 'array',
            'files_to_delete' => 'array',

            'notes' => '', 
        ]);

        $contact = $this->contactSerivce->update($contact, $validated, $request->user_uuid);

        // account securities
        if (isset($validated['account_securities'])){
            foreach ($validated['account_securities'] AS $key => $value):
                $value['entity_uuid'] = $contact['uuid'];
                $this->accountSecurityService->save($value);
            endforeach;
        }

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $contact->uuid], $value['uuid']);
            endforeach;
        }

        return $contact;
    }
  
      /**     @OA\DELETE(
        *         path="/api/contact/{uuid}",
        *         operationId="delete_contact",
        *         tags={"Contact"},
        *         summary="Delete contact",
        *         description="Delete contact",
        *             @OA\Parameter(
        *                 name="uuid",
        *                 in="path",
        *                 description="contact uuid",
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
    public function destroy(Request $request, Contact $contact)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.delete'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $this->contactSerivce->delete($contact);
    }
  
      /**     @OA\GET(
        *         path="/api/contact-search/{search}",
        *         operationId="get_contact_search",
        *         tags={"Contact"},
        *         summary="Get contact search",
        *         description="Get contact search",
        *             @OA\Parameter(
        *                 name="search",
        *                 in="path",
        *                 description="contact search",
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        // TODO: Need reailize search with all fields
        //$contact = $this->contact->search($search);

        //return $contact;
    }
  
      /**     @OA\POST(
        *         path="/api/contact-pending",
        *         operationId="pending_contact",
        *         tags={"Contact"},
        *         summary="Pending contact",
        *         description="Pending contact",
        *             @OA\RequestBody(
        *                 @OA\JsonContent(),
        *                 @OA\MediaType(
        *                     mediaType="multipart/form-data",
        *                     @OA\Schema(
        *                         type="object",
        *                         required={},
        *                         
        *                         @OA\Property(property="first_name", type="text"),
        *                         @OA\Property(property="last_name", type="text"),
        *                         @OA\Property(property="email", type="text"),
        *                         @OA\Property(property="phone_number", type="text"),
        *                         @OA\Property(property="company_name", type="text"),
        *                         @OA\Property(property="company_phone_number", type="text"),
        *                         @OA\Property(property="company_email", type="text"),
        *                         @OA\Property(property="company_website", type="text"),
        *                         @OA\Property(property="online_account", type="text"),
        *                         @OA\Property(property="account_username", type="text"),
        *                         @OA\Property(property="account_password", type="text"),
        *                         @OA\Property(property="fax", type="text"),
        *                         @OA\Property(property="security_questions", type="text"),
        *                         @OA\Property(property="account_securities[]", type="text"),
        *                         @OA\Property(property="notes", type="text"),
        *
        *                         @OA\Property(property="files[]", type="text"),
        *                         @OA\Property(property="files_to_delete[]", type="text"),
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'first_name' => '', 
            'last_name' => '', 
            'email' => '', 
            'phone_number' => '', 
            'company_name' => '', 
            'company_phone_number' => '', 
            'company_email' => '', 
            'company_website' => '', 
            'online_account' => '', 
            'account_username' => '', 
            'account_password' => '', 
            'fax' => '', 
            'security_questions' => '', 
            'account_securities' => 'array', 
            'notes' => '', 

            // files
            'files' => 'array',
            'files_to_delete' => 'array',

            'user_uuid' => ''
        ]);

        $contact = $this->contactSerivce->pending($validated);

        // account securities
        if (isset($validated['account_securities'])){
            foreach ($validated['account_securities'] AS $key => $value):
                $value['entity_uuid'] = $contact['uuid'];
                $this->accountSecurityService->save($value);
            endforeach;
        }

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $contact->uuid], $value['uuid']);
            endforeach;
        }

        return $contact;
    }
  
      /**     @OA\PUT(
        *         path="/api/contact-pending-update/{uuid}",
        *         operationId="pending_update_contact",
        *         tags={"Contact"},
        *         summary="Pending update contact",
        *         description="Pending update contact",
        *             @OA\Parameter(
        *                 name="uuid",
        *                 in="path",
        *                 description="contact uuid",
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
        *                         
        *                         @OA\Property(property="first_name", type="text"),
        *                         @OA\Property(property="last_name", type="text"),
        *                         @OA\Property(property="email", type="text"),
        *                         @OA\Property(property="phone_number", type="text"),
        *                         @OA\Property(property="company_name", type="text"),
        *                         @OA\Property(property="company_phone_number", type="text"),
        *                         @OA\Property(property="company_email", type="text"),
        *                         @OA\Property(property="company_website", type="text"),
        *                         @OA\Property(property="online_account", type="text"),
        *                         @OA\Property(property="account_username", type="text"),
        *                         @OA\Property(property="account_password", type="text"),
        *                         @OA\Property(property="fax", type="text"),
        *                         @OA\Property(property="security_questions", type="text"),
        *                         @OA\Property(property="account_securities[]", type="text"),
        *                         @OA\Property(property="notes", type="text"),
        *
        *                         @OA\Property(property="files[]", type="text"),
        *                         @OA\Property(property="files_to_delete[]", type="text"),
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
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'first_name' => '', 
            'last_name' => '', 
            'email' => '', 
            'phone_number' => '', 
            'company_name' => '', 
            'company_phone_number' => '', 
            'company_email' => '', 
            'company_website' => '', 
            'online_account' => '', 
            'account_username' => '', 
            'account_password' => '', 
            'fax' => '', 
            'security_questions' => '', 
            'account_securities' => 'array', 

            // files
            'files' => 'array',
            'files_to_delete' => 'array',

            'notes' => '', 

        ]);

        $contact = Contact::where('uuid', $uuid)->first();

        $contact = $this->contactSerivce->pending_update($contact, $validated, $request->user_uuid);

        // account securities
        if (isset($validated['account_securities'])){
            foreach ($validated['account_securities'] AS $key => $value):
                $value['entity_uuid'] = $contact['uuid'];
                $this->accountSecurityService->save($value);
            endforeach;
        }

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $contact->uuid], $value['uuid']);
            endforeach;
        }

        return $contact;
    }
  
      /**     @OA\PUT(
        *         path="/api/contact-accept/{uuid}",
        *         operationId="accept_contact",
        *         tags={"Contact"},
        *         summary="Accept contact",
        *         description="Accept contact",
        *             @OA\Parameter(
        *                 name="uuid",
        *                 in="path",
        *                 description="contact uuid",
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
        *                     
        *                         @OA\Property(property="first_name", type="text"),
        *                         @OA\Property(property="last_name", type="text"),
        *                         @OA\Property(property="email", type="text"),
        *                         @OA\Property(property="phone_number", type="text"),
        *                         @OA\Property(property="company_name", type="text"),
        *                         @OA\Property(property="company_phone_number", type="text"),
        *                         @OA\Property(property="company_email", type="text"),
        *                         @OA\Property(property="company_website", type="text"),
        *                         @OA\Property(property="online_account", type="text"),
        *                         @OA\Property(property="account_username", type="text"),
        *                         @OA\Property(property="account_password", type="text"),
        *                         @OA\Property(property="fax", type="text"),
        *                         @OA\Property(property="security_questions", type="text"),
        *                         @OA\Property(property="account_securities[]", type="text"),
        *                         @OA\Property(property="notes", type="text"),
        *
        *                         @OA\Property(property="files[]", type="text"),
        *                         @OA\Property(property="files_to_delete[]", type="text"),
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.accept'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'first_name' => '', 
            'last_name' => '', 
            'email' => '', 
            'phone_number' => '', 
            'company_name' => '', 
            'company_phone_number' => '', 
            'company_email' => '', 
            'company_website' => '', 
            'online_account' => '', 
            'account_username' => '', 
            'account_password' => '', 
            'fax' => '', 
            'security_questions' => '', 
            'account_securities' => 'array',  

            // files
            'files' => 'array',
            'files_to_delete' => 'array',

            'notes' => '',
        ]);

        $contact = Contact::where('uuid', $uuid)->first();

        $contact = $this->contactSerivce->accept($contact, $validated, $request->user_uuid);

        // account securities
        if (isset($validated['account_securities'])){
            foreach ($validated['account_securities'] AS $key => $value):
                $value['entity_uuid'] = $contact['uuid'];
                $this->accountSecurityService->save($value);
            endforeach;
        }

        // files to delete (first)
        if (isset($validated['files_to_delete'])){
            foreach ($validated['files_to_delete'] AS $key => $value):
                $this->fileService->delete($value);
            endforeach;
        }

        // files
        if (isset($validated['files'])){
            foreach ($validated['files'] AS $key => $value):
                $this->fileService->update(['entity_uuid' => $contact->uuid], $value['uuid']);
            endforeach;
        }

        return $contact;
    }
  
      /**     @OA\PUT(
        *         path="/api/contact-reject/{uuid}",
        *         operationId="reject_contact",
        *         tags={"Contact"},
        *         summary="Reject contact",
        *         description="Reject contact",
        *             @OA\Parameter(
        *                 name="uuid",
        *                 in="path",
        *                 description="contact uuid",
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
        *                         @OA\Property(property="description", type="text"),
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
    public function reject(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.accept'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'description' => ''
        ]);

        // reject reason
        if (isset($validated['description'])){
            $this->rejectReasonService->create([
                'entity_uuid' => $uuid,
                'description' => $validated['description']
            ]);
        }

        $this->contactSerivce->reject($uuid, $request->user_uuid);
    }
  
    /**     @OA\GET(
        *         path="/api/contact-permission",
        *         operationId="contact_permission",
        *         tags={"Contact"},
        *         summary="Get contact permission of user",
        *         description="Get contact permission of user",
        *             @OA\Response(response=200, description="Successfully"),
        *             @OA\Response(response=400, description="Bad request"),
        *             @OA\Response(response=401, description="Not Authenticated"),
        *     )
        */
    public function permission(Request $request)
    {
        $permissions = [];

        // permission
        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.view'))){
            $permissions[] = Config::get('common.permission.contact.view');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.store'))){
            $permissions[] = Config::get('common.permission.contact.store');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.save'))){
            $permissions[] = Config::get('common.permission.contact.save');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.delete'))){
            $permissions[] = Config::get('common.permission.contact.delete');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.accept'))){
            $permissions[] = Config::get('common.permission.contact.accept');
        }

        return $permissions;
    }

}
