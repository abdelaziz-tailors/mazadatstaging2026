<?php

namespace App\Http\Controllers\api\User\Auth\Social;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\LoginRequest;
use App\Http\Requests\api\User\Auth\SocailLoginRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Helpers\TranslationHelper;
use App\Models\User\User;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;

class LoginController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(SocailLoginRequest $request) {


        $fullName = $request->input('name');
        $username = $this->createUniqueUsername($fullName);
        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->email)
                ->orWhere('google_id',$request->google_id);
        })->first();
        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'user_name' => $username,
                'google_id' => $request->google_id,
            ]);
        }
        $token = $user->createToken('MyApp')->accessToken;
        $user['token'] = $token;
        return $this->success_response(TranslationHelper::translate(' Account Registered Successfully '), new UserResource($user));
    }
    private function createUniqueUsername($fullName)
    {
        $nameParts = explode(' ', $fullName);
        $baseUsername = '';

        foreach ($nameParts as $index => $part) {
            if ($index < count($nameParts) - 1) {
                $baseUsername .= substr($part, 0, 1); // Initials
            } else {
                $baseUsername .= $part; // Last part of the name
            }
        }

        $baseUsername = strtolower($baseUsername);

        // Check for uniqueness
        $username = $baseUsername;
        $counter = 1;

        while (User::where('user_name', $username)->exists()) {
            // Append a number to make it unique
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }


}
