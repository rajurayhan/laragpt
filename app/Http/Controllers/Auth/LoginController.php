<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Authentication
 *
 * APIs for user login
 */
class LoginController extends Controller
{
    /**
     * Log in a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @bodyParam email string required The email of the user. Example: john.doe@example.com
     * @bodyParam password string required The password of the user. Example: secret
     *
     * @response 200 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john.doe@example.com",
     *     "created_at": "2023-07-28 12:00:00",
     *     "updated_at": "2023-07-28 12:00:00"
     *   },
     *   "token": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
     * }
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            if ($token = Auth::attempt($credentials)) {
                $user = Auth::user();
                return response(['user' => $user, 'token' => $token], 200);
            } else {
                return response(['message' => 'Invalid credentials'], 401);
            }
        } catch (\Exception $e) {
            return response(['message' => 'Error occurred during login'], 500);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }
    public function guard()
    {
        return Auth::guard();
    }
}
