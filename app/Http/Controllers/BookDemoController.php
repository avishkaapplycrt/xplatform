<?php

namespace App\Http\Controllers;

use App\Mail\DemoBookedNotification;
use App\Models\BookDemo;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookDemoController extends Controller
{
    public function show()
    {
        return view('book-demo');
    }

    public function thankYou(Request $request)
    {
        $demoDate = $request->query('dt');
        return view('book-demo-thank-you', ['demoDate' => $demoDate]);
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

        $displayDate = $data['demo_date'] ?? null;
        if (!empty($data['demo_date'])) {
            try {
                $data['demo_date'] = Carbon::parse($data['demo_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $data['demo_date'] = null;
            }
        }

        try {
            BookDemo::create($data);

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

            Auth::guard('client')->login($client);
            $request->session()->regenerate();

            // Send admin email notification
            try {
                $emailData = array_merge($data, ['demo_date' => $displayDate]);
                $adminEmail = config('mail.admin_email', 'contact@mockmaster.ai');

                Mail::to($adminEmail)->send(new DemoBookedNotification($emailData));

                Log::info('Demo booking admin email sent successfully to: ' . $adminEmail);
            } catch (\Exception $emailException) {
                Log::error('Failed to send demo booking admin email: ' . $emailException->getMessage());
            }

            $demoDate = null;
            if (!empty($data['demo_date']) && !empty($data['demo_time'])) {
                $demoDate = \Carbon\Carbon::parse($data['demo_date'])->format('d M Y') . ' at ' . $data['demo_time'];
                if (!empty($data['timezone'])) {
                    $demoDate .= ' (' . $data['timezone'] . ')';
                }
            }

            return response()->json([
                'redirect' => route('book-demo.thank-you', $demoDate ? ['dt' => $demoDate] : []),
            ]);

        } catch (\Exception $e) {
            Log::error('BookDemo store failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}