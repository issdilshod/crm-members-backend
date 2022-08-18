<?php

namespace App\Http\Controllers;

use App\Http\Resources\SicCodeResource;
use App\Models\API\SicCode;
use Illuminate\Http\Request;

class SicCodeController extends Controller
{
    /**     @OA\Get(
      *         path="/api/sic_code",
      *         operationId="list_sic_code",
      *         tags={"Helper"},
      *         summary="List of sic code",
      *         description="List of sic code",
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
        return SicCodeResource::collection(SicCode::all()->where('status', '=', '1'));
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
            'code' => 'required|integer',
            'office' => 'required|string|max:100',
            'industry_title' => 'required|string|max:200',
            'status' => 'required|integer'
        ]);
        return new SicCodeResource(SicCode::create($validated));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\API\SicCode  $sicCode
     * @return \Illuminate\Http\Response
     */
    public function show(SicCode $sicCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\API\SicCode  $sicCode
     * @return \Illuminate\Http\Response
     */
    public function edit(SicCode $sicCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\API\SicCode  $sicCode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SicCode $sicCode)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\API\SicCode  $sicCode
     * @return \Illuminate\Http\Response
     */
    public function destroy(SicCode $sicCode)
    {
        //
    }
}
