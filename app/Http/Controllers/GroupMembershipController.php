<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GroupMembershipController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Groups/Join', [
            'currentGroup' => optional($request->user()?->currentGroup())->only(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'join_code' => ['required', 'string'],
        ]);

        $group = Group::where('join_code', mb_strtoupper(trim($validated['join_code'])))->first();

        if (! $group) {
            throw ValidationException::withMessages(['join_code' => 'Nie znaleziono klasy o tym kodzie.']);
        }

        $user = $request->user();
        $user->groups()->sync([$group->id]); // one active group at a time

        return redirect()->route('join.show')->with('status', "Dołączono do klasy \"{$group->name}\".");
    }
}
