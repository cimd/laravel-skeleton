<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Auth\Models\User;
use Modules\Auth\Notifications\ResetPasswordNotification;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['login', 'forgotPassword', 'resetPassword', 'store', 'index']);
    }

    public function index(Request $request): JsonResponse
    {
        $result = User::apiQuery($request);

        return response()->index($result);
    }

    public function login(Request $request): array|JsonResponse
    {
        if (Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
            $user = Auth::user();
            $token = $user->createToken('app-token')->plainTextToken;

            return [
                'data' => $user,
                'token' => $token,
            ];
        }

        return response()->json('Error logging in', 400);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json('logout', 201);
    }

    public function store(Request $request): array|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:65'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('app-token')->plainTextToken;

        return [
            'data' => $user,
            'token' => $token,
        ];
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $user->fill($request->all());
        $user->password = bcrypt($request->input('password'));
        $user->save();

        return response()->update($user);
    }

    public function show(User $user): JsonResponse
    {
        return response()->show($user);
    }

    public function forgotPassword(Request $request): array
    {
        $user = User::where('email', $request->email)->firstOrFail();
        $token = Str::random(60);
        DB::table('password_resets')
            ->insert([
                'email' => $user->email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);
        $user->notify(new ResetPasswordNotification($token));

        return [
            'message' => 'We have e-mailed your password reset link!',
        ];
    }

    public function resetPassword(Request $request): array|JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();
        $user->password = bcrypt($request->password);
        $user->save();

        $user->tokens()->delete();

        $req = [];
        $req['email'] = $user->email;
        $req['password'] = $request->password;

        return $this->login(new Request($req));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->show(
            auth()->user()
        );
    }
}
