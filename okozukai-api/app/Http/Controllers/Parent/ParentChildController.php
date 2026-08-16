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
        $children = User::query()
            ->where('family_id', $request->user()->family_id)
            ->where('role', 'child')
            ->orderBy('name')
            ->get();

        return view('parent.children.index', [
            'children' => $children,
        ]);
    }

    // お子様管理
    public function show(User $child): View
    {
        Gate::authorize('viewFamilyChild', $child);

        return view('pages.placeholder', [
            'title' => 'お子様管理',
            'description' => "{$child->name}さんの管理画面をここに実装します。",
        ]);
    }
}
