<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'user' => $request->user()->only([
                'id',
                'name',
                'first_name',
                'last_name',
                'email',
                'role',
                'created_at',
                'updated_at',
            ]),
        ]);
    }
}
