<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DataValidator;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Dotenv\Exception\ValidationException;

class UserController extends Controller
{
     public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:8|confirmed',
        ];

        $validator = DataValidator::make($request->all(), $rules);

        // If validation fails, return a validation error response.
        if ($validator->fails()) {
            return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return ResponseService::response('SUCCESS', $user, 'User registered successfully.');
    }

    public function login(Request $request)
    {
        // Validate request data
        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];

        $validator = DataValidator::make($request->all(), $rules);

        // If validation fails, return a validation error response.
        if ($validator->fails()) {
            return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
        }
    
        // Attempt to retrieve the user
        $user = User::where('email', $request->email)->first();
    
        // Check if user exists and verify password
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Incorrect Credantials");
        }
    
        // Generate token and return response
        $token = $user->createToken('user-token')->plainTextToken;
    
        return response()->json(['user' => $user, 'token' => $token], 200);
    }

    public function getAll(Request $request)
    {
        $users = User::all();
        return ResponseService::response('SUCCESS', $users);
    }

    public function getOne($id)
    {
        $user = User::find($id);

        if (!$user) {
            return ResponseService::response('NOT_FOUND', null, "User not found.");
        }

        return ResponseService::response('SUCCESS', $user);
    }

    public function storeOrUpdate(Request $request, $id = null)
    {
        $isCreating = !isset($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
        }

        if ($isCreating) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $message = "User created successfully.";
        } else {
            $user = User::find($id);
            if (!$user) {
                return ResponseService::response('NOT_FOUND', null, "User not found.");
            }

            $user->update($request->only('name', 'email', 'password'));
            $message = "User updated successfully.";
        }

        return ResponseService::response('SUCCESS', $user, $message);
    }

    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return ResponseService::response('NOT_FOUND', null, "User not found.");
        }

        $user->delete();

        return ResponseService::response('SUCCESS', "User deleted successfully.");
    }

    public function getAuthenticatedUser()
    {
        try {
            $user = Auth::user();
            return ResponseService::response('SUCCESS', $user, 'Authenticated user retrieved successfully.');
        } catch (\Throwable $exception) {
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

}
