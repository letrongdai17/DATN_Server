<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ClassesService;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

        if (!$userId) {
            return response()->json(['error'=>'Permission denied'], 403);
        }

        $classes = $this->classService->getClassByUserId($userId);

        return response()->json(['data'=>$classes], 200);
    }

    public function createClass(Request $request)
    {
        $userId = Auth::id();
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->first()], 422);
        }

        if (!$userId) {
            return response()->json(['error'=>'Permission denied'], 403);
        }

        $this->classService->createClass($request->all(), $userId);

        return response()->json(['message'=>'Create class successfully'], 201);
    }

    protected function rules()
    {
        return [
            'subject_name'  =>      'required|max:255',
            'subject_code'  =>      'required|max:255',
            'class_code'    =>      'required|max:255|unique:classes',
        ];
    }
}
