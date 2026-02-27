<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApprovalRequest;

class ApprovalsController extends Controller
{
    public function pending(Request $request)
    {
        $query = ApprovalRequest::where('status', 'pending')->latest();

        // Search
        if ($request->filled('q')) {
            $q = $request->q;

            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('requester_name', 'like', "%{$q}%")
                    ->orWhere('requester_email', 'like', "%{$q}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $pending = $query->paginate(10)->withQueryString();

        $types = ApprovalRequest::select('type')
            ->distinct()
            ->pluck('type');

        return view('faculty.pendings', compact('pending', 'types'));
    }

    public function approve(ApprovalRequest $approval)
    {
        if ($approval->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }

        $approval->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Request approved successfully.');
    }

    public function reject(Request $request, ApprovalRequest $approval)
    {
        if ($approval->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        $approval->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', 'Request rejected successfully.');
    }
}