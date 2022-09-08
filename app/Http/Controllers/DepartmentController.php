<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Models\API\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class DepartmentController extends Controller
{
    /**     @OA\GET(
      *         path="/api/department",
      *         operationId="list_department",
      *         tags={"Account"},
      *         summary="List of department",
      *         description="List of department",
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
        $departments = Department::orderBy('sort', 'ASC')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->get();
        return DepartmentResource::collection($departments);
    }

    public function store(Request $request)
    {
        /*$validated = $request->validate([
            'department_name' => 'required|string|max:100',
        ]);
        // TODO: Incrementing number sort
        return new DepartmentResource(Department::create($validated));*/
    }

    /**     @OA\GET(
      *         path="/api/department/{uuid}",
      *         operationId="get_department",
      *         tags={"Account"},
      *         summary="Get department",
      *         description="Get department",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="department uuid",
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
    public function show(Department $department)
    {
        return new DepartmentResource($department);
    }

    public function destroy(Department $department)
    {
    }
}
