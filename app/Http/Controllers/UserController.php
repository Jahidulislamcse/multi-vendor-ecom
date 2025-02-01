<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function userList()
    {
        // $users = User::where('role', '!=', 'admin')->get();
        $users = User::all();
        return view('admin.user.index', compact('users'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:vendor,admin',
        ]);

        // dd($request->all());

        // Create the user
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone,
            'address' => $request->address,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        // Redirect with success message
        return redirect()->route('admin.user.list')->with('success', 'User created successfully!');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id); // Fetch the user by ID
        return response()->json($user); // Return user data as JSON
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id); // Fetch the user by ID
        $user->update($request->all()); // Update user data
        return redirect()->route('admin.user.list')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id); // Find the user by ID
        $user->delete(); // Delete the user

        // Redirect back with a success message
        return redirect()->route('admin.user.list')->with('success', 'User deleted successfully');
    }
}
