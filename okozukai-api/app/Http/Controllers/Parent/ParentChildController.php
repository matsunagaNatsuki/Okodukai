<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParentChildController extends Controller
{
    // お子様一覧
    public function index(Request $request): View
    {
        // ログイン中の保護者ユーザを取得
        $parent = $request->user();

        $children = User::query()
            ->where('family_id', $request->user()->family_id)
            ->where('role', 'child')
            ->orderBy('name')
            ->get();

        return view('parent.children.index', [
            'children' => $children,
            'parent' => $parent,
        ]);
    }

    // お子様管理
    public function show(User $child): View
    {
        Gate::authorize('viewFamilyChild', $child);

        return view('pages.placeholder', [
            'description' => "{$child->name}さんのお子様管理データです",
            'child' => $child
        ]);
    }
}
