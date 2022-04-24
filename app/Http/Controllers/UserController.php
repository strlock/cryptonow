<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (!$token = auth('api')->attempt($validator->validated())) {
            return response()->json(['error' => 'Invalid login or password'], 401);
        }
        return response()->json([
            'access_token' => $token,
            'user_name' => auth('api')->user()->name,
        ]);
    }
}
