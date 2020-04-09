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
}
