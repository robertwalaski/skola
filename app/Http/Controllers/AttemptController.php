<?php

namespace App\Http\Controllers;

use App\Support\Scoring;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AttemptController extends Controller
{
    // The client only ever reports whether an answer was correct - points
    // are always computed here from Scoring, never trusted from the request.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course' => ['required', 'string', Rule::in(Scoring::courses())],
            'section' => ['required', 'string', 'max:50'],
            'exercise_key' => ['required', 'string', 'max:100'],
            'correct' => ['required', 'boolean'],
        ]);

        if (! in_array($validated['section'], Scoring::sections($validated['course']), true)) {
            throw ValidationException::withMessages([
                'section' => 'Nieznana sekcja zadania.',
            ]);
        }

        $points = Scoring::pointsFor($validated['course'], $validated['section'], $validated['correct']);

        $request->user()->attempts()->create([
            'course' => $validated['course'],
            'section' => $validated['section'],
            'exercise_key' => $validated['exercise_key'],
            'correct' => $validated['correct'],
            'points' => $points,
        ]);

        return response()->json([
            'points' => $points,
            'total' => $request->user()->attempts()->sum('points'),
        ]);
    }
}
