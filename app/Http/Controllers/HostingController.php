<?php

namespace App\Http\Controllers;

use App\Http\Resources\HostingResource;
use App\Models\API\Hosting;
use Illuminate\Http\Request;

class HostingController extends Controller
{
    /**     @OA\GET(
      *         path="/api/hosting",
      *         operationId="list_hosting",
      *         tags={"Helper"},
      *         summary="List of hosting",
      *         description="List of hosting",
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
        //
        return HostingResource::collection(Hosting::all()->where('status', 1));
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'host' => 'required|string|max:100'
        ]);
        return new HostingResource(Hosting::create($validated));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\API\Hosting  $hosting
     * @return \Illuminate\Http\Response
     */
    public function show(Hosting $hosting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\API\Hosting  $hosting
     * @return \Illuminate\Http\Response
     */
    public function edit(Hosting $hosting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\API\Hosting  $hosting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Hosting $hosting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\API\Hosting  $hosting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Hosting $hosting)
    {
        //
    }
}
