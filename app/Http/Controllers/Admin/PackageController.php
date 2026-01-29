<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index() {
        $packages = Package::all();
        return view('admin.packages.index', compact('packages'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required',
            'type' => 'required|in:cold_email,email_marketing',
            'email_limit' => 'required|integer',
            'price_ngn' => 'required|integer'
        ]);

        Package::create($request->all());
        return back()->with('success', 'Package created.');
    }
    public function destroy(Package $package) {
        $package->delete();
        return back()->with('success', 'Package removed.');
    }
}