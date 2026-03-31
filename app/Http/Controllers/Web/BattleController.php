<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Battle;
use App\Models\CustomPokemon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BattleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Battle::withCount('turns')
            ->where('user_id', Auth::id());

        if ($request->filled('opponent')) {
            $query->where('opponent_name', 'like', '%' . $request->opponent . '%');
        }
        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }
        if ($request->filled('format')) {
            $query->where('format', 'like', '%' . $request->format . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('played_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('played_at', '<=', $request->date_to);
        }
        if ($request->filled('tag')) {
            $query->where('tags', 'like', '%' . $request->tag . '%');
        }

        $battles = $query->latest('played_at')->paginate(15)->withQueryString();

        // 対戦相手別勝率統計（相手名がある対戦のみ、上位10件）
        $opponentStats = Battle::where('user_id', Auth::id())
            ->whereNotNull('opponent_name')
            ->where('opponent_name', '!=', '')
            ->selectRaw('
                opponent_name,
                COUNT(*) as total,
                SUM(result = "win")  as wins,
                SUM(result = "lose") as loses,
                SUM(result = "draw") as draws
            ')
            ->groupBy('opponent_name')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $row->win_rate = $row->total > 0
                    ? round($row->wins / $row->total * 100, 1)
                    : 0;
                return $row;
            });

        // カレンダービュー用: 今月の対戦データ（日別集計）
        $calYear  = (int) $request->get('cal_year',  now()->year);
        $calMonth = (int) $request->get('cal_month', now()->month);
        $calData  = Battle::where('user_id', Auth::id())
            ->whereYear('played_at', $calYear)
            ->whereMonth('played_at', $calMonth)
            ->selectRaw('DATE(played_at) as date, COUNT(*) as total,
                SUM(result="win") as wins, SUM(result="lose") as loses, SUM(result="draw") as draws')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        return view('battle.index', compact('battles', 'opponentStats', 'calData', 'calYear', 'calMonth'));
    }

    public function create(): View
    {
        return view('battle.create');
    }

    public function show(int $id): View
    {
        $battle = Battle::with([
            'turns.myPokemon.pokemon', 'turns.myMove', 'turns.opponentMove',
            'opponentPokemon.pokemon.types',
        ])->where('user_id', Auth::id())->findOrFail($id);
        $myPokemonList = CustomPokemon::with(['pokemon', 'moves'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
        return view('battle.show', compact('battle', 'myPokemonList'));
    }
}
