<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function me()
    {
        return response()->json([
            'data' => auth()->user(),
        ]);
    }
}
