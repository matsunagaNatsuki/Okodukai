<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildChoreHistoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $records = $request->user()
            ->choreRecords()
            ->with([
                'chore' => fn ($query) => $query->withTrashed(),
            ])
            ->orderByDesc('performed_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('child.chores-history.index', [
            'records' => $records,
        ]);
    }
}
