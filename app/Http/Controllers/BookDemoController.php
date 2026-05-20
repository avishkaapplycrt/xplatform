<?php

namespace App\Http\Controllers;

use App\Models\BookDemo;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookDemoController extends Controller
{
    public function show()
    {
        return view('book-demo');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'               => 'required|string|max:100',
            'last_name'                => 'required|string|max:100',
            'email'                    => 'required|email|max:255',
            'company_name'             => 'required|string|max:255',
            'job_title'                => 'required|string|max:150',
            'company_size'             => 'nullable|string|max:100',
            'industry'                 => 'nullable|string|max:100',
            'monthly_active_customers' => 'nullable|string|max:100',
            'monthly_revenue'          => 'nullable|string|max:100',
            'primary_challenge'        => 'nullable|string|max:200',
            'data_sources'             => 'nullable|string|max:500',
            'demo_notes'               => 'nullable|string|max:2000',
            'demo_date'                => 'nullable|string|max:50',
            'demo_time'                => 'nullable|string|max:20',
            'timezone'                 => 'nullable|string|max:100',
        ]);

        // Parse the human-readable date string ("15 May 2026") into Y-m-d for DB
        if (!empty($data['demo_date'])) {
            try {
                $data['demo_date'] = Carbon::parse($data['demo_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $data['demo_date'] = null;
            }
        }

        try {
            // Save the demo booking
            BookDemo::create($data);

            // Create or retrieve a client account from the form details
            $client = Client::firstOrCreate(
                ['email' => $data['email']],
                [
                    'company_name' => $data['company_name'],
                    'size'         => $data['company_size'] ?? null,
                    'password'     => Hash::make(Str::random(16)),
                    'status'       => 'active',
                    'timezone'     => $data['timezone'] ?? null,
                ]
            );

            // Log the client in
            Auth::guard('client')->login($client);
            $request->session()->regenerate();

            return response()->json([
                'redirect' => route('client.dashboard'),
            ]);

        } catch (\Exception $e) {
            Log::error('BookDemo store failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}
