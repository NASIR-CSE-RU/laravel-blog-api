<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $user = User::create($request->validated());
            DB::commit();

            return $this->successResponse(
                data: new UserResource($user),
                message: 'User registered successfully',
                statusCode: 201
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to register user', 400);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $tokenResult = $user->createToken('auth-token');

        return $this->successResponse(
            data: new AuthResource([
                'user' => $user,
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
            ]),
            message: 'User logged in successfully'
        );
    }
}
