<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    // Teacher dashboard: own groups (if any) with each member's points per
    // section, or an offer to become a teacher and start one.
    public function index(Request $request): Response
    {
        $user = $request->user();

        $groups = $user->isTeacher()
            ? $user->ownedGroups()
                ->with(['students' => function ($q) {
                    $q->withSum('attempts as points', 'points')
                        ->withMax('attempts as last_practiced', 'created_at');
                }])
                ->get()
                ->map(fn (Group $group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'join_code' => $group->join_code,
                    'students' => $group->students->map(fn (User $s) => [
                        'id' => $s->id,
                        'nick' => $s->nick,
                        'points' => $s->points ?? 0,
                        'last_practiced' => $s->last_practiced,
                    ]),
                ])
            : [];

        return Inertia::render('Teacher/Dashboard', [
            'isTeacher' => $user->isTeacher(),
            'groups' => $groups,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50'],
        ]);

        $user = $request->user();
        $user->forceFill(['role' => 'teacher'])->save();

        $group = Group::create([
            'name' => $validated['name'],
            'join_code' => Group::generateJoinCode(),
            'owner_id' => $user->id,
        ]);

        return redirect()->route('teacher.dashboard')->with('status', "Klasa \"{$group->name}\" utworzona. Kod dołączenia: {$group->join_code}");
    }

    // Teacher can't see a child's PIN (it's hashed) - only reset it and show
    // the new one once, to relay to the student directly.
    public function resetStudentPin(Request $request, Group $group, User $student)
    {
        abort_unless($group->owner_id === $request->user()->id, 403);
        abort_unless($group->students()->where('users.id', $student->id)->exists(), 404);

        $newPin = (string) random_int(1000, 9999);
        $student->forceFill(['pin_hash' => $newPin])->save();

        return back()->with('status', "Nowy PIN dla {$student->nick}: {$newPin}");
    }
}
