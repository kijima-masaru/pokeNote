<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Battle;
use App\Models\CustomPokemon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        $recentBattles = Battle::withCount('turns')
            ->where('user_id', $userId)
            ->latest('played_at')
            ->limit(5)
            ->get();
        $recentCustomPokemon = CustomPokemon::with(['pokemon.types', 'ability'])
            ->where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();

        // 対戦統計
        $battleStats = Battle::where('user_id', $userId)->selectRaw('
            COUNT(*) as total,
            SUM(result = "win") as wins,
            SUM(result = "lose") as loses,
            SUM(result = "draw") as draws,
            SUM(result IS NULL OR result NOT IN ("win","lose","draw")) as ongoing
        ')->first();

        // 直近10戦の結果（グラフ用）
        $recentResults = Battle::where('user_id', $userId)
            ->whereNotNull('result')
            ->latest('played_at')
            ->limit(10)
            ->get(['id', 'result', 'played_at'])
            ->reverse()
            ->values();

        return view('dashboard.index', compact(
            'recentBattles', 'recentCustomPokemon', 'battleStats', 'recentResults'
        ));
    }
}
