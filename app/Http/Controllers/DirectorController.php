<?php

namespace App\Http\Controllers;

use App\Http\Resources\DirectorResource;
use App\Models\API\Director;
use Illuminate\Http\Request;

class DirectorController extends Controller
{
    /**     @OA\Get(
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
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index()
    {
        //
        $director = Director::where('status', '=', '1')->paginate(20);
        return DirectorResource::collection($director);
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
      *                         required={"user_uuid", "first_name", "middle_name", "last_name", "date_of_birth", "ssn_cpn", "company_association", "phone_type", "phone_number", "status"},
      *                         @OA\Property(property="user_uuid", type="text"),
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="middle_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="date_of_birth", type="string", format="date"),
      *                         @OA\Property(property="ssn_cpn", type="text"),
      *                         @OA\Property(property="company_association", type="text"),
      *                         @OA\Property(property="phone_type", type="text"),
      *                         @OA\Property(property="phone_number", type="text"),
      *                         @OA\Property(property="status", type="int"),
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
      *     )
      */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'user_uuid' => 'required|string',
            'first_name' => 'required|string',
            'middle_name' => 'required|string',
            'last_name' => 'required|string',
            'date_of_birth' => 'required|date',
            'ssn_cpn' => 'required|string',
            'company_association' => 'required|string',
            'phone_type' => 'required|string',
            'phone_number' => 'required|string',
            'status' => 'required|integer'
        ]);
        return new DirectorResource(Director::create($validated));
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
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Director $director)
    {
        //
        return new DirectorResource($director);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\API\Director  $director
     * @return \Illuminate\Http\Response
     */
    public function edit(Director $director)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\API\Director  $director
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Director $director)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\API\Director  $director
     * @return \Illuminate\Http\Response
     */
    public function destroy(Director $director)
    {
        //
    }
}
