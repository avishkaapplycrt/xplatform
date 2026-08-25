<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class SegmentationController extends Controller
{
    public function index(Request $request) {
        return view('segmentation.index');
    }
    public function createSegment(Request $request) {
        return redirect()->back();
    }
    public function segmentCustomers($segment) {
        return view('segmentation.index');
    }
    public function recalculateSegment($segment) {
        return response()->json(['success' => true]);
    }
}