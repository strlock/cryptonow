<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Resources\UserSettingsResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Throwable;

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
            'user' => new UserResource(auth('api')->user()),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        auth('api')->logout();
        return response()->json([
            'success' => true,
        ]);
    }

    /**
     *
     */
    public function getSettings()
    {
        try {
            $user = Auth::user();
            return new UserSettingsResource($user);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true]);
        }
    }

    /**
     *
     */
    public function getUser()
    {
        try {
            $user = Auth::user();
            return new UserResource($user);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveUser(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            $data = $request->post();
            $user->fill($data);
            $user->save();
            return response()->json(['message' => 'Settings saved']);
        } catch (Throwable $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }
}
