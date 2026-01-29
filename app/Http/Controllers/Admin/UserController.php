<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function overrideSubscription(Request $request, User $user)
{
    $request->validate(['package_id' => 'required|exists:packages,id']);

    // Create or Update the subscription manually
    $user->subscription()->updateOrCreate(
        ['user_id' => $user->id],
        [
            'package_id' => $request->package_id,
            'status' => 'active',
            'emails_used' => 0,
            'expires_at' => now()->addMonth(), // Standard 30-day override
        ]
    );

    return back()->with('success', "Subscription for {$user->name} has been updated.");
}
}
