<?php

use App\Http\Controllers\Api\AbilityController;
use App\Http\Controllers\Api\CustomPokemonExportImportController;
use App\Http\Controllers\Api\ScreenshotRecognitionController;
use App\Http\Controllers\Api\BattleController;
use App\Http\Controllers\Api\BattleOpponentPokemonController;
use App\Http\Controllers\Api\CustomPokemonController;
use App\Http\Controllers\Api\DamageCalcAdhocController;
use App\Http\Controllers\Api\DamageCalcController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\MoveController;
use App\Http\Controllers\Api\PokeApiImportController;
use App\Http\Controllers\Api\PokemonController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TurnController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['web', 'auth'])->group(function () {
    // マスターデータ CRUD
    Route::apiResource('pokemon', PokemonController::class);
    Route::post('/pokemon/{id}/image', [PokemonController::class, 'uploadImage']);
    Route::apiResource('moves', MoveController::class);
    Route::apiResource('abilities', AbilityController::class);
    Route::apiResource('items', ItemController::class);
    Route::post('/items/{id}/image', [ItemController::class, 'uploadImage']);

    // カスタムポケモン CRUD
    Route::apiResource('custom-pokemon', CustomPokemonController::class);
    Route::post('/custom-pokemon/{id}/duplicate', [CustomPokemonController::class, 'duplicate']);
    // カスタムポケモン エクスポート/インポート
    Route::get('/custom-pokemon/export-all',    [CustomPokemonExportImportController::class, 'exportAll']);
    Route::post('/custom-pokemon/import-csv',   [CustomPokemonExportImportController::class, 'importCsv']);
    Route::get('/custom-pokemon/{id}/export',   [CustomPokemonExportImportController::class, 'export']);
    Route::post('/custom-pokemon/import',        [CustomPokemonExportImportController::class, 'import']);

    // ダメージ計算
    Route::post('/damage-calc', [DamageCalcController::class, 'calculate']);
    Route::post('/damage-calc/adhoc', [DamageCalcAdhocController::class, 'calculate']);

    // 対戦セッション CRUD
    Route::apiResource('battles', BattleController::class);

    // チームビルダー
    Route::apiResource('teams', TeamController::class);
    Route::put('/teams/{id}/members/{slot}', [TeamController::class, 'setMember']);

    // PokeAPI インポート
    Route::post('/import/pokemon',            [PokeApiImportController::class, 'importPokemon']);
    Route::post('/import/pokemon/bulk',       [PokeApiImportController::class, 'importPokemonBulk']);
    Route::post('/import/move',               [PokeApiImportController::class, 'importMove']);
    Route::post('/import/evolutions',         [PokeApiImportController::class, 'importEvolutions']);

    // 画面認識
    Route::post('/recognize-pokemon', [ScreenshotRecognitionController::class, 'recognize']);

    // 対戦相手のポケモン
    Route::get('/battles/{battleId}/opponent-pokemon', [BattleOpponentPokemonController::class, 'index']);
    Route::post('/battles/{battleId}/opponent-pokemon', [BattleOpponentPokemonController::class, 'store']);
    Route::delete('/battles/{battleId}/opponent-pokemon/{slot}', [BattleOpponentPokemonController::class, 'destroy']);

    // ターン履歴
    Route::get('/battles/{battleId}/turns', [TurnController::class, 'index']);
    Route::post('/battles/{battleId}/turns', [TurnController::class, 'store']);
    Route::put('/battles/{battleId}/turns/{turnNumber}', [TurnController::class, 'update']);
    Route::delete('/battles/{battleId}/turns/{turnNumber}', [TurnController::class, 'destroy']);
});
