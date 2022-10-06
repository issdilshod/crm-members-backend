<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helper\DepartmentResource;
use App\Models\Helper\Department;
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
    public function index(Request $request)
    {
        $role = $request->validate([
            'role_alias' => 'string'
        ]);

        if ($role['role_alias']!=Config::get('common.role.headquarters')){
            return response()->json([
                'msg' => 'You don\'t have permission to do this action.'
            ], 403);
        }

        $departments = $this->departmentService->getDepartments();
        
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

    /**     @OA\DELETE(
      *         path="/api/department/{uuid}",
      *         operationId="delete_department",
      *         tags={"Helper"},
      *         summary="Delete department",
      *         description="Delete department",
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
    public function destroy(Department $department, DepartmentService $departmentService)
    {
        $departmentService->deleteDepartment($department);
    }
}
