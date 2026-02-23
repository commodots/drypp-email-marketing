<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::all();
        return view('admin.packages.index', compact('packages'));
    }
    public function create()
    {
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required|in:cold_email,email_marketing',
            'email_limit' => 'required|integer',
            'price_ngn' => 'required|integer'
        ]);

        Package::create($request->all());
        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }
    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Package removed.');
    }
    public function edit(Package $package)
    {
        return view('admin.packages.edit', compact('package'));
    }
    public function update(Request $request, Package $package)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required|in:cold_email,email_marketing',
            'email_limit' => 'required|integer',
            'price_ngn' => 'required|integer'
        ]);

        $package->update($request->all());

        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        //  Grab the first row as Headers
        $headers = fgetcsv($handle);
        $headers = array_map(fn($h) => Str::slug($h, '_'), $headers);

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            // Combine headers with row values
            $data = array_combine($headers, $row);

            // Separate Standard from Meta
            $standardFields = ['email', 'name', 'country'];
            $meta = array_diff_key($data, array_flip($standardFields));

            //  Create or Update Contact
            \App\Models\Contact::updateOrCreate(
                ['email' => $data['email'], 'user_id' => auth()->id()],
                [
                    'name'    => $data['name'] ?? null,
                    'country' => $data['country'] ?? null,
                    'meta'    => $meta
                ]
            );
            $count++;
        }

        fclose($handle);

        return back()->with('success', "Successfully imported $count contacts.");
    }
}
