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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class DirectorController extends Controller
{
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
    public function index(DirectorService $directorService)
    {
        $directors = $directorService->getDirectors();

        return DirectorResource::collection($directors);
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
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function store(Request $request)
    {
        #region Validate

        $validated = $request->validate([
            'user_uuid' => 'string',
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

            'user_uuid' => 'string'
        ]);

        #endregion

        #region Check exsist data

        $check = [];

        #region Check Email

        if (isset($validated['emails'])){
            // Hosting & Email
            $check['hosting_email'] = Email::select('hosting_uuid', 'email')
                                                ->where('status', Config::get('common.status.actived'))
                                                ->where('hosting_uuid', $validated['emails']['hosting_uuid'])
                                                ->where('email', $validated['emails']['email'])->first();
            if ($check['hosting_email']!=null){
                $check['hosting_email'] = $check['hosting_email']->toArray();
                foreach ($check['hosting_email'] AS $key => $value):
                    $check['emails.'.$key] = '~Exsist~';
                endforeach;
            }
            unset($check['hosting_email']);

            // Phone
            $check['phone'] = Email::select('phone')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('phone', $validated['emails']['phone'])->first();
            if ($check['phone']!=null){
                $check['phone'] = $check['phone']->toArray();
                foreach ($check['phone'] AS $key => $value):
                    $check['emails.'.$key] = '~Exsist~';
                endforeach;
            }
            unset($check['phone']);
        }

        #endregion

        #region Check Address

        if (isset($validated['address'])){
            foreach ($validated['address'] AS $key => $value):
                $check[$key] = Address::select('street_address', 'address_line_2', 'city', 'postal')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where(function($query) use ($value){
                                                $query->where('street_address', $value['street_address'])
                                                        ->where('address_line_2', $value['address_line_2'])
                                                        ->where('city', $value['city'])
                                                        ->where('postal', $value['postal']);
                                        })->first();
                if ($check[$key]!=null){
                    $check[$key] = $check[$key]->toArray();
                    foreach ($check[$key] AS $key1 => $value1):
                        $check['address.'.$key.'.'.$key1] = '~Exsist~';
                    endforeach;
                }
                unset($check[$key]);                
            endforeach;
        }

        #endregion

        #region Check Director

        // Names
        $check['names'] = Director::select('first_name', 'middle_name', 'last_name')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('first_name', $validated['first_name'])
                                        ->where('middle_name', (isset($validated['middle_name'])?$validated['middle_name']:''))
                                        ->where('last_name', $validated['last_name'])
                                        ->first();
        if ($check['names']!=null){
            $check['names'] = $check['names']->toArray();
            foreach ($check['names'] AS $key => $value):
                $check[$key] = '~Exsist~';
            endforeach;
        }
        unset($check['names']);

        // Ssn
        $check['ssn'] = Director::select('ssn_cpn')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('ssn_cpn', $validated['ssn_cpn'])
                                        ->first();
        if ($check['ssn']!=null){
            $check['ssn'] = $check['ssn']->toArray();
            foreach ($check['ssn'] AS $key => $value):
                $check[$key] = '~Exsist~';
            endforeach;
        }
        unset($check['ssn']);

        // Company 
        $check['company_association'] = Director::select('company_association')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('company_association', $validated['company_association'])
                                        ->first();
        if ($check['company_association']!=null){
            $check['company_association'] = $check['company_association']->toArray();
            foreach ($check['company_association'] AS $key => $value):
                $check[$key] = '~Exsist~';
            endforeach;
        }
        unset($check['company_association']);

        // Phone
        $check['phone_number'] = Director::select('phone_number')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('phone_number', $validated['phone_number'])
                                        ->first();
        if ($check['phone_number']!=null){
            $check['phone_number'] = $check['phone_number']->toArray();
            foreach ($check['phone_number'] AS $key => $value):
                $check[$key] = '~Exsist~';
            endforeach;
        }
        unset($check['phone_number']);

        #endregion

        if (count($check)>0){
            return response()->json([
                        'data' => $check,
                    ], 409);
        }

        #endregion

        $director = Director::create($validated);

        #region Email add

        $validated['emails']['entity_uuid'] = $director['uuid'];
        Email::create($validated['emails']);

        #endregion

        #region Address add

        foreach ($validated['address'] AS $key => $value){
            $address = new Address($validated['address'][$key]);
            $address->address_parent = $key;
            $address->entity_uuid = $director['uuid'];
            $address->save();
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
            'description' => Config::get('common.activity.director.add'),
            'changes' => json_encode($validated),
            'action_code' => Config::get('common.activity.codes.director_add'),
            'status' => Config::get('common.status.actived')
        ]);

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
        return new DirectorResource($director);
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
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function update(Request $request, Director $director)
    {
        #region Validate

        $validated = $request->validate([
            'user_uuid' => 'string',
            'first_name' => 'string',
            'middle_name' => '',
            'last_name' => 'string',
            'date_of_birth' => 'date',
            'ssn_cpn' => 'string',
            'company_association' => 'string',
            'phone_type' => 'string',
            'phone_number' => 'string',
            // addresses
            'address.dl_address.street_address' => 'string',
            'address.dl_address.address_line_2' => 'string',
            'address.dl_address.city' => 'string',
            'address.dl_address.state' => 'string',
            'address.dl_address.postal' => 'string',
            'address.dl_address.country' => 'string',

            'address.credit_home_address.street_address' => 'string',
            'address.credit_home_address.address_line_2' => 'string',
            'address.credit_home_address.city' => 'string',
            'address.credit_home_address.state' => 'string',
            'address.credit_home_address.postal' => 'string',
            'address.credit_home_address.country' => 'string',
            // emails
            'emails.hosting_uuid' => 'string',
            'emails.email' => 'string',
            'emails.password' => 'string',
            'emails.phone' => 'string',
            // files to delete by uuid
            'files_to_delete' => 'array',

            'user_uuid' => 'string'
        ]);

        #endregion

        #region Check exsist data

        $check = [];

        #region Check Email

        if (isset($validated['emails'])){
            // Hosting & Email
            $check['hosting_email'] = Email::select('hosting_uuid', 'email')
                                                ->where('entity_uuid', '!=', $director['uuid'])
                                                ->where('status', Config::get('common.status.actived'))
                                                ->where('hosting_uuid', $validated['emails']['hosting_uuid'])
                                                ->where('email', $validated['emails']['email'])->first();
            if ($check['hosting_email']!=null){
                $check['hosting_email'] = $check['hosting_email']->toArray();
                foreach ($check['hosting_email'] AS $key => $value):
                    $check['emails.'.$key] = '~Exsist~';
                endforeach;
            }
            unset($check['hosting_email']);

            // Phone
            $check['phone'] = Email::select('phone')
                                        ->where('entity_uuid', '!=', $director['uuid'])
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('phone', $validated['emails']['phone'])->first();
            if ($check['phone']!=null){
                $check['phone'] = $check['phone']->toArray();
                foreach ($check['phone'] AS $key => $value):
                    $check['emails.'.$key] = '~Exsist~';
                endforeach;
            }
            unset($check['phone']);
        }

        #endregion

        #region Check Address

        if (isset($validated['address'])){
            foreach ($validated['address'] AS $key => $value):
                $check[$key] = Address::select('street_address', 'address_line_2', 'city', 'postal')
                                        ->where('entity_uuid', '!=', $director['uuid'])
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where(function($query) use ($value){
                                                $query->where('street_address', $value['street_address'])
                                                        ->where('address_line_2', $value['address_line_2'])
                                                        ->where('city', $value['city'])
                                                        ->where('postal', $value['postal']);
                                        })->first();
                if ($check[$key]!=null){
                    $check[$key] = $check[$key]->toArray();
                    foreach ($check[$key] AS $key1 => $value1):
                        $check['address.'.$key.'.'.$key1] = '~Exsist~';
                    endforeach;
                }
                unset($check[$key]);                
            endforeach;
        }

        #endregion

        #region Check Director

        // Names
        $check['names'] = Director::select('first_name', 'middle_name', 'last_name')
                                        ->where('uuid', '!=', $director['uuid'])
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('first_name', $validated['first_name'])
                                        ->where('middle_name', (isset($validated['middle_name'])?$validated['middle_name']:''))
                                        ->where('last_name', $validated['last_name'])
                                        ->first();
        if ($check['names']!=null){
            $check['names'] = $check['names']->toArray();
            foreach ($check['names'] AS $key => $value):
                $check[$key] = '~Exsist~';
            endforeach;
        }
        unset($check['names']);

        // Ssn
        $check['ssn'] = Director::select('ssn_cpn')
                                        ->where('uuid', '!=', $director['uuid'])
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('ssn_cpn', $validated['ssn_cpn'])
                                        ->first();
        if ($check['ssn']!=null){
            $check['ssn'] = $check['ssn']->toArray();
            foreach ($check['ssn'] AS $key => $value):
                $check[$key] = '~Exsist~';
            endforeach;
        }
        unset($check['ssn']);

        // Company 
        $check['company_association'] = Director::select('company_association')
                                        ->where('uuid', '!=', $director['uuid'])
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('company_association', $validated['company_association'])
                                        ->first();
        if ($check['company_association']!=null){
            $check['company_association'] = $check['company_association']->toArray();
            foreach ($check['company_association'] AS $key => $value):
                $check[$key] = '~Exsist~';
            endforeach;
        }
        unset($check['company_association']);

        // Phone
        $check['phone_number'] = Director::select('phone_number')
                                        ->where('uuid', '!=', $director['uuid'])
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('phone_number', $validated['phone_number'])
                                        ->first();
        if ($check['phone_number']!=null){
            $check['phone_number'] = $check['phone_number']->toArray();
            foreach ($check['phone_number'] AS $key => $value):
                $check[$key] = '~Exsist~';
            endforeach;
        }
        unset($check['phone_number']);

        #endregion

        if (count($check)>0){
            return response()->json([
                        'data' => $check,
                    ], 409);
        }

        #endregion

        $director->update($validated);

        #region Email update (if exsist)

        if (isset($validated['emails'])){
            $email = Email::where('entity_uuid', $director['uuid']);
            $email->update($validated['emails']);
        }

        #endregion

        #region Address update (if exsist)

        if (isset($validated['address'])){
            foreach ($validated['address'] AS $key => $value){
                $address = Address::where('entity_uuid', $director['uuid'])
                                    ->where('address_parent', $key);
                $address->update($validated['address'][$key]);
            }
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
    public function destroy(Director $director, DirectorService $directorService)
    {
        $directorService->deleteDirector($director);
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
    public function search($search, DirectorService $directorService)
    {
        $directors = $directorService->searchDirector($search);

        return DirectorResource::collection($directors);
    }
}