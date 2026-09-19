<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class RankingController extends Controller
{
    private const LIMIT = 50;

    // Only nicks and points ever leave this controller - no other user data.
    public function index(Request $request): Response
    {
        $period = $request->query('period') === 'week' ? 'week' : 'all';
        $since = $period === 'week' ? Carbon::now()->startOfWeek() : null;

        $global = $this->topByPoints(User::query(), $since);

        $group = null;
        $user = $request->user();
        if ($user && $currentGroup = $user->currentGroup()) {
            $group = [
                'name' => $currentGroup->name,
                'entries' => $this->topByPoints($currentGroup->students(), $since),
            ];
        }

        return Inertia::render('Ranking', [
            'period' => $period,
            'global' => $global,
            'group' => $group,
        ]);
    }

    /**
     * @param  Builder<User>|BelongsToMany<User, User>  $query
     */
    private function topByPoints(Builder|BelongsToMany $query, ?Carbon $since): array
    {
        return $query
            ->withSum(['attempts as points' => function ($q) use ($since) {
                if ($since) {
                    $q->where('created_at', '>=', $since);
                }
            }], 'points')
            ->orderByDesc('points')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (User $u) => ['nick' => $u->nick, 'points' => (int) ($u->points ?? 0)])
            ->values()
            ->all();
    }
}
