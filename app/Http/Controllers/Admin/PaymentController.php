<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Subscription;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return view('admin.payments.index', [
            // Load users with their subscription and the package inside it
            'users' => User::where('role', 'user')->with('subscription.package')->paginate(10),
            'packages' => Package::all(),
            'gatewayStatus' => 'Active', // Simulated payment gateway status 
        ]);
    }

    public function override(Request $request, User $user)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        //Subscription Overrides
        $user->subscription()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'package_id' => $request->package_id,
                'status' => 'active',
                'emails_used' => 0,
                'expires_at' => now()->addMonth(), // Standard 30-day manual grant
            ]
        );

        return back()->with('success', "Access granted to {$user->name} successfully.");
    }
}