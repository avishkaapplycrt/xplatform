<?php

namespace App\Http\Controllers;

use App\Models\BookDemo;
use Illuminate\Http\Request;

class AdminDemoRequestController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('status', 'all');

        $query = BookDemo::latest();

        if ($filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($filter === 'approved') {
            $query->where('status', 'approved');
        }

        $demos   = $query->paginate(20)->withQueryString();
        $counts  = [
            'all'      => BookDemo::count(),
            'pending'  => BookDemo::where('status', 'pending')->count(),
            'approved' => BookDemo::where('status', 'approved')->count(),
        ];

        return view('admin.demo-requests.index', compact('demos', 'counts', 'filter'));
    }

    public function approve(BookDemo $demo)
    {
        $demo->update(['status' => 'approved']);

        return back()->with('success', "Demo request from {$demo->first_name} {$demo->last_name} approved.");
    }

    public function destroy(BookDemo $demo)
    {
        $demo->delete();

        return back()->with('success', 'Demo request deleted.');
    }
}
