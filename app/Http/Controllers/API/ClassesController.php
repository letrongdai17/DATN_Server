<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ClassesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClassesController extends Controller
{
    protected $classService;

    public function __construct(ClassesService $classService)
    {
        $this->classService = $classService;
    }

    public function index()
    {
        $userId = Auth::id();
        $classes = $this->classService->getClassByUserId($userId);

        return response()->json(['data'=>$classes], 200);
    }

    public function getClassById(Request $request)
    {
        $classId = $request->id;
        Log::info('Class id: '.$classId);

        if (!$classId) {
            return response()->json(['error'=>'Bad request'], 401);
        }

        $classDetails = $this->classService->getClassDetailByClassId($classId);
        return response()->json(['data'=>$classDetails], 200);
    }

    public function createClassDetail(Request $request)
    {
        
    }
}
