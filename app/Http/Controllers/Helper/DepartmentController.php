<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helper\DepartmentResource;
use App\Models\Helper\Department;
use App\Policies\PermissionPolicy;
use App\Services\Helper\DepartmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class DepartmentController extends Controller
{

    private $departmentService;

    public function __construct()
    {
        $this->departmentService = new DepartmentService();
    }

    /**     @OA\GET(
      *         path="/api/department",
      *         operationId="list_department",
      *         tags={"Helper"},
      *         summary="List of department",
      *         description="List of department",
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
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $departments = $this->departmentService->getDepartments();
        return DepartmentResource::collection($departments);
    }

    /**     @OA\GET(
      *         path="/api/department/{uuid}",
      *         operationId="get_department",
      *         tags={"Helper"},
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
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Department $department)
    {
        return new DepartmentResource($department);
    }

    /**     @OA\DELETE(
      *         path="/api/department/{uuid}",
      *         operationId="delete_department",
      *         tags={"Helper"},
      *         summary="Delete department (not working)",
      *         description="Delete department (not working)",
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
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(Request $request, Department $department)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        //$this->departmentService->deleteDepartment($department);
    }
}
