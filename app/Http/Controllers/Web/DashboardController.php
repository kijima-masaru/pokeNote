<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Battle;
use App\Models\CustomPokemon;
use App\Models\Turn;
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

        // 月別勝率推移（過去6ヶ月）
        $monthlyStats = Battle::where('user_id', $userId)
            ->whereNotNull('played_at')
            ->where('played_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw('
                DATE_FORMAT(played_at, "%Y-%m") as month,
                COUNT(*) as total,
                SUM(result = "win") as wins
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month'    => $r->month,
                'win_rate' => $r->total > 0 ? round($r->wins / $r->total * 100, 1) : 0,
                'total'    => $r->total,
            ]);

        // 使用ポケモン上位5体（マイポケモン → ターン記録から）
        $topPokemon = Turn::join('battles', 'turns.battle_id', '=', 'battles.id')
            ->join('custom_pokemon', 'turns.my_pokemon_id', '=', 'custom_pokemon.id')
            ->join('pokemon', 'custom_pokemon.pokemon_id', '=', 'pokemon.id')
            ->where('battles.user_id', $userId)
            ->whereNotNull('turns.my_pokemon_id')
            ->selectRaw('pokemon.name_ja, pokemon.sprite_url, COUNT(*) as use_count')
            ->groupBy('pokemon.id', 'pokemon.name_ja', 'pokemon.sprite_url')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'recentBattles', 'recentCustomPokemon', 'battleStats', 'recentResults',
            'monthlyStats', 'topPokemon'
        ));
    }
}
