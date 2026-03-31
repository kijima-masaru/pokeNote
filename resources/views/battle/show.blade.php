@extends('layouts.app')
@section('title', '対戦詳細')
@section('content')
<div x-data="battleShow({{ $battle->id }})" x-init="init()">
    <!-- ヘッダー -->
    <div class="d-flex align-items-start justify-content-between mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('battles.index') }}">対戦履歴</a></li>
                    <li class="breadcrumb-item active">対戦詳細</li>
                </ol>
            </nav>
            <h4 class="mb-0">{{ $battle->title ?? 'vs '.($battle->opponent_name ?? '名無し') }}</h4>
            <small class="text-muted">
                {{ $battle->format ?? '' }}
                @if($battle->played_at) / {{ $battle->played_at->format('Y/m/d H:i') }} @endif
            </small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="btn-group btn-group-sm">
                <button class="btn" :class="battleResult==='win'?'btn-success':'btn-outline-success'" @click="updateResult('win')">勝</button>
                <button class="btn" :class="battleResult==='lose'?'btn-danger':'btn-outline-danger'" @click="updateResult('lose')">負</button>
                <button class="btn" :class="battleResult==='draw'?'btn-secondary':'btn-outline-secondary'" @click="updateResult('draw')">分</button>
            </div>
            <a href="{{ route('damage-calc.index') }}" class="btn btn-sm btn-outline-warning" title="ダメージ計算">
                <i class="bi bi-calculator"></i>
            </a>
            <button class="btn btn-sm btn-outline-danger" @click="deleteBattle()" title="対戦を削除">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>

    <!-- メモ -->
    <div class="mb-3">
        <template x-if="!editingMemo">
            <div class="d-flex align-items-start gap-2">
                <div class="text-muted flex-grow-1" style="font-size:.9rem;line-height:1.6"
                     x-show="memo"
                     x-html="renderMarkdown(memo)"></div>
                <span x-show="!memo" class="text-muted" style="font-size:.9rem">メモなし</span>
                <button class="btn btn-sm btn-link p-0 text-secondary flex-shrink-0" @click="editingMemo=true" title="メモを編集（Markdown対応）">
                    <i class="bi bi-pencil-square"></i>
                </button>
            </div>
        </template>
        <template x-if="editingMemo">
            <div class="d-flex gap-2 align-items-start">
                <div class="flex-grow-1">
                    <textarea class="form-control form-control-sm" x-model="memo" rows="4"
                              style="font-size:.9rem;font-family:monospace"
                              placeholder="Markdown記法が使えます（# 見出し, **太字**, - リスト など）"></textarea>
                    <div x-show="memo" class="border rounded p-2 mt-1 bg-light" style="font-size:.85rem">
                        <small class="text-muted d-block mb-1">プレビュー</small>
                        <div x-html="renderMarkdown(memo)" style="line-height:1.6"></div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-1">
                    <button class="btn btn-sm btn-success" @click="saveMemo()">保存</button>
                    <button class="btn btn-sm btn-outline-secondary" @click="editingMemo=false">取消</button>
                </div>
            </div>
        </template>
    </div>

    <!-- タグ -->
    <div class="mb-3">
        <template x-if="!editingTags">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <template x-if="tags.length === 0">
                    <span class="text-muted" style="font-size:.85rem">タグなし</span>
                </template>
                <template x-for="tag in tags" :key="tag">
                    <span class="badge rounded-pill bg-secondary" x-text="tag"></span>
                </template>
                <button class="btn btn-sm btn-link p-0 text-secondary ms-1" @click="editingTags=true;tagsInput=tags.join(', ')" title="タグを編集">
                    <i class="bi bi-tag"></i>
                </button>
            </div>
        </template>
        <template x-if="editingTags">
            <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control form-control-sm" x-model="tagsInput"
                       placeholder="タグをカンマ区切りで入力（例: ランクマッチ,受け崩し）"
                       @keydown.enter.prevent="saveTags()">
                <button class="btn btn-sm btn-success" @click="saveTags()">保存</button>
                <button class="btn btn-sm btn-outline-secondary" @click="editingTags=false">取消</button>
            </div>
        </template>
    </div>

    <!-- 対戦相手のポケモン -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-person-fill text-danger"></i> 相手のポケモン</strong>
            <small class="text-muted">最大6体まで設定可</small>
        </div>
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2">
                <template x-for="slot in [1,2,3,4,5,6]" :key="slot">
                    <div class="border rounded p-2 text-center" style="width:90px;min-height:80px;cursor:pointer;position:relative"
                         :class="getOpponentSlot(slot) ? 'border-danger' : 'border-dashed border-secondary'"
                         @click="openOpponentSlot(slot)">
                        <template x-if="getOpponentSlot(slot)">
                            <div>
                                <img :src="getOpponentSlot(slot).pokemon?.sprite_url || ''"
                                     x-show="getOpponentSlot(slot).pokemon?.sprite_url"
                                     style="width:40px;height:40px;object-fit:contain">
                                <i class="bi bi-question-circle text-muted d-block"
                                   x-show="!getOpponentSlot(slot).pokemon?.sprite_url"
                                   style="font-size:1.5rem"></i>
                                <div style="font-size:.72rem;font-weight:600" x-text="getOpponentSlot(slot).nickname || getOpponentSlot(slot).pokemon?.name_ja || '?'"></div>
                                <button class="btn btn-xs p-0 text-danger" style="font-size:.65rem;position:absolute;top:2px;right:4px"
                                        @click.stop="clearOpponentSlot(slot)">✕</button>
                            </div>
                        </template>
                        <template x-if="!getOpponentSlot(slot)">
                            <div class="text-muted" style="font-size:.75rem;padding-top:12px">
                                <i class="bi bi-plus-circle" style="font-size:1.2rem"></i><br>
                                <span x-text="'スロット'+slot"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- スロット編集フォーム -->
            <div x-show="editingOpponentSlot !== null" class="mt-3 p-2 border rounded bg-light" x-cloak>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4" style="position:relative">
                        <label style="font-size:.8rem">ポケモン</label>
                        <input type="text" class="form-control form-control-sm" x-model="opponentPokemonSearch"
                               placeholder="名前で検索..."
                               @input.debounce.300ms="searchOpponentPokemon()">
                        <div x-show="opponentPokemonResults.length > 0" class="border rounded bg-white mt-1"
                             style="max-height:150px;overflow-y:auto;position:absolute;z-index:100;width:200px">
                            <template x-for="p in opponentPokemonResults" :key="p.id">
                                <div class="px-2 py-1 d-flex align-items-center gap-1"
                                     style="cursor:pointer;font-size:.8rem"
                                     @click="selectOpponentPokemon(p)"
                                     @mouseenter="$el.style.background='#f0f0f0'"
                                     @mouseleave="$el.style.background=''">
                                    <img :src="p.sprite_url" x-show="p.sprite_url" style="width:24px;height:24px;object-fit:contain">
                                    <span x-text="p.name_ja"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label style="font-size:.8rem">ニックネーム（任意）</label>
                        <input type="text" class="form-control form-control-sm" x-model="opponentSlotForm.nickname" placeholder="任意">
                    </div>
                    <div class="col-md-5 d-flex gap-1 align-items-end flex-wrap">
                        <button class="btn btn-sm btn-danger" @click="saveOpponentSlot()">設定</button>
                        <label class="btn btn-sm btn-outline-primary mb-0" title="スクリーンショットから認識" :class="recognizing?'disabled':''">
                            <i class="bi bi-camera"></i>
                            <span x-show="!recognizing"> 画像認識</span>
                            <span x-show="recognizing"><span class="spinner-border spinner-border-sm"></span> 認識中...</span>
                            <input type="file" accept="image/*" class="d-none" @change="recognizeFromScreenshot($event)" :disabled="recognizing">
                        </label>
                        <button class="btn btn-sm btn-outline-secondary" @click="editingOpponentSlot=null;opponentPokemonResults=[];recognitionResults=[]">取消</button>
                    </div>
                </div>

                <!-- 画像認識結果 -->
                <div x-show="recognitionResults.length > 0" class="mt-2">
                    <div style="font-size:.78rem;color:#6c757d" class="mb-1">
                        <i class="bi bi-stars text-warning"></i> 認識結果（クリックで選択）
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <template x-for="r in recognitionResults" :key="r.pokemon.id">
                            <div class="border rounded p-1 text-center bg-white"
                                 style="cursor:pointer;min-width:72px;max-width:80px"
                                 @click="selectOpponentPokemon(r.pokemon);recognitionResults=[]"
                                 @mouseenter="$el.style.borderColor='#0d6efd'"
                                 @mouseleave="$el.style.borderColor=''">
                                <img :src="r.pokemon.sprite_url" style="width:40px;height:40px;object-fit:contain">
                                <div style="font-size:.65rem;font-weight:600;line-height:1.2" x-text="r.pokemon.name_ja"></div>
                                <div style="font-size:.6rem;color:#6c757d" x-text="r.similarity+'%'"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div x-show="recognitionMessage" class="mt-2 text-muted" style="font-size:.78rem" x-text="recognitionMessage"></div>
            </div>
        </div>
    </div>

    <!-- タイプ相性サマリー -->
    <div class="card border-0 shadow-sm mb-3" x-show="opponentTypeSummary.length > 0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center"
             style="cursor:pointer" @click="typeSummaryOpen=!typeSummaryOpen">
            <strong><i class="bi bi-grid-3x3 text-danger"></i> 相手タイプ相性サマリー</strong>
            <i class="bi" :class="typeSummaryOpen?'bi-chevron-up':'bi-chevron-down'"></i>
        </div>
        <div x-show="typeSummaryOpen" x-collapse>
            <div class="card-body">
                <p class="text-muted mb-2" style="font-size:.8rem">相手ポケモンへの攻撃で効果が高いタイプ（×2以上）と弱いタイプ（×0.5以下）を集計します。</p>
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="fw-semibold mb-1" style="font-size:.85rem"><span class="text-success">◎ 有利タイプ（×2以上のポケモン数）</span></div>
                        <div class="d-flex flex-wrap gap-1">
                            <template x-for="entry in opponentTypeSummary.filter(e=>e.superEffective>0).sort((a,b)=>b.superEffective-a.superEffective)" :key="entry.type">
                                <div class="d-flex align-items-center gap-1 border rounded px-2 py-1" style="font-size:.8rem">
                                    <span :class="'type-badge type-'+entry.type" x-text="typeLabel(entry.type)"></span>
                                    <span class="fw-bold text-success" x-text="'×'+entry.superEffective"></span>
                                </div>
                            </template>
                            <span x-show="opponentTypeSummary.filter(e=>e.superEffective>0).length===0" class="text-muted" style="font-size:.8rem">なし</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fw-semibold mb-1" style="font-size:.85rem"><span class="text-danger">△ 不利タイプ（×0.5以下のポケモン数）</span></div>
                        <div class="d-flex flex-wrap gap-1">
                            <template x-for="entry in opponentTypeSummary.filter(e=>e.notVeryEffective>0).sort((a,b)=>b.notVeryEffective-a.notVeryEffective)" :key="entry.type">
                                <div class="d-flex align-items-center gap-1 border rounded px-2 py-1" style="font-size:.8rem">
                                    <span :class="'type-badge type-'+entry.type" x-text="typeLabel(entry.type)"></span>
                                    <span class="fw-bold text-danger" x-text="'×'+entry.notVeryEffective"></span>
                                </div>
                            </template>
                            <span x-show="opponentTypeSummary.filter(e=>e.notVeryEffective>0).length===0" class="text-muted" style="font-size:.8rem">なし</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 対戦レポート（折りたたみ） -->
    <div class="card border-0 shadow-sm mb-3" x-show="turns.length > 0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center"
             style="cursor:pointer" @click="reportOpen = !reportOpen">
            <strong><i class="bi bi-bar-chart-line"></i> 対戦レポート</strong>
            <i class="bi" :class="reportOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
        </div>
        <div x-show="reportOpen" x-collapse>
            <div class="card-body">
                <div class="row g-3">
                    <!-- HP推移グラフ -->
                    <div class="col-12">
                        <canvas id="hpChart" height="120"></canvas>
                    </div>
                    <!-- 使用技頻度 -->
                    <div class="col-md-6">
                        <small class="fw-bold text-success d-block mb-2"><i class="bi bi-person"></i> 自分の使用技</small>
                        <template x-for="[move, cnt] in myMoveUsage" :key="move">
                            <div class="d-flex align-items-center mb-1 gap-2">
                                <span style="font-size:.8rem;min-width:100px" x-text="move"></span>
                                <div class="flex-grow-1" style="height:8px;background:#e9ecef;border-radius:4px;overflow:hidden">
                                    <div style="height:100%;border-radius:4px;background:#198754"
                                         :style="'width:'+Math.round(cnt/turns.length*100)+'%'"></div>
                                </div>
                                <span style="font-size:.75rem;color:#6c757d;min-width:30px" x-text="cnt+'回'"></span>
                            </div>
                        </template>
                        <div x-show="myMoveUsage.length === 0" class="text-muted" style="font-size:.85rem">技記録なし</div>
                    </div>
                    <div class="col-md-6">
                        <small class="fw-bold text-danger d-block mb-2"><i class="bi bi-person-fill"></i> 相手の使用技</small>
                        <template x-for="[move, cnt] in oppMoveUsage" :key="move">
                            <div class="d-flex align-items-center mb-1 gap-2">
                                <span style="font-size:.8rem;min-width:100px" x-text="move"></span>
                                <div class="flex-grow-1" style="height:8px;background:#e9ecef;border-radius:4px;overflow:hidden">
                                    <div style="height:100%;border-radius:4px;background:#dc3545"
                                         :style="'width:'+Math.round(cnt/turns.length*100)+'%'"></div>
                                </div>
                                <span style="font-size:.75rem;color:#6c757d;min-width:30px" x-text="cnt+'回'"></span>
                            </div>
                        </template>
                        <div x-show="oppMoveUsage.length === 0" class="text-muted" style="font-size:.85rem">技記録なし</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ターンリスト -->
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>ターン履歴</strong>
            <span class="badge bg-secondary" x-text="turns.length+'ターン'"></span>
        </div>

        <template x-if="turns.length === 0">
            <div class="text-center text-muted py-3 border rounded">まだターンが記録されていません</div>
        </template>

        <template x-for="turn in turns" :key="turn.id">
            <div class="card border-0 shadow-sm mb-2">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-start gap-2">
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:28px;height:28px;font-size:.75rem;font-weight:bold"
                             x-text="turn.turn_number"></div>
                        <div class="flex-grow-1">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <small class="text-success fw-semibold">自分</small>
                                    <div style="font-size:.85rem">
                                        <span x-text="turn.my_pokemon?.display_name||'-'" class="fw-semibold"></span>
                                        <span x-show="turn.my_move" class="text-muted">
                                            → <span x-text="turn.my_move?.name_ja"></span>
                                        </span>
                                    </div>
                                    <template x-if="turn.my_hp_remaining !== null && turn.my_hp_remaining !== undefined">
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="stat-bar flex-grow-1">
                                                <div class="stat-bar-fill"
                                                     :style="'width:'+turn.my_hp_remaining+'%;background:'+hpColor(turn.my_hp_remaining)"></div>
                                            </div>
                                            <small x-text="turn.my_hp_remaining+'%'" class="text-muted" style="white-space:nowrap"></small>
                                        </div>
                                    </template>
                                </div>
                                <div class="col-md-5">
                                    <small class="text-danger fw-semibold">相手</small>
                                    <div style="font-size:.85rem">
                                        <span x-text="turn.opponent_pokemon_name||'-'" class="fw-semibold"></span>
                                        <span x-show="turn.opponent_move" class="text-muted">
                                            → <span x-text="turn.opponent_move?.name_ja"></span>
                                        </span>
                                    </div>
                                    <template x-if="turn.opponent_hp_remaining !== null && turn.opponent_hp_remaining !== undefined">
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="stat-bar flex-grow-1">
                                                <div class="stat-bar-fill"
                                                     :style="'width:'+turn.opponent_hp_remaining+'%;background:'+hpColor(turn.opponent_hp_remaining)"></div>
                                            </div>
                                            <small x-text="turn.opponent_hp_remaining+'%'" class="text-muted" style="white-space:nowrap"></small>
                                        </div>
                                    </template>
                                </div>
                                <div class="col-md-2 d-flex align-items-center justify-content-end gap-1">
                                    <button class="btn btn-sm btn-outline-secondary"
                                            @click="startEditTurn(turn)"
                                            x-show="!turn.editing"
                                            title="編集">
                                        <i class="bi bi-pencil" style="font-size:.75rem"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            @click="deleteTurn(turn.battle_id, turn.turn_number)"
                                            x-show="!turn.editing"
                                            title="削除">
                                        <i class="bi bi-trash" style="font-size:.75rem"></i>
                                    </button>
                                </div>
                            </div>
                            <div x-show="turn.description && !turn.editing" class="mt-1 text-muted" style="font-size:.8rem" x-text="turn.description"></div>

                            <!-- インライン編集フォーム -->
                            <template x-if="turn.editing">
                                <div class="mt-2 border-top pt-2">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <small class="text-success fw-semibold">自分HP%</small>
                                            <div class="d-flex align-items-center gap-1">
                                                <input type="range" class="form-range" min="0" max="100"
                                                       x-model.number="turn.editData.my_hp_remaining">
                                                <span style="width:38px;font-size:.8rem" x-text="turn.editData.my_hp_remaining+'%'"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <small class="text-danger fw-semibold">相手HP%</small>
                                            <div class="d-flex align-items-center gap-1">
                                                <input type="range" class="form-range" min="0" max="100"
                                                       x-model.number="turn.editData.opponent_hp_remaining">
                                                <span style="width:38px;font-size:.8rem" x-text="turn.editData.opponent_hp_remaining+'%'"></span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <input type="text" class="form-control form-control-sm"
                                                   x-model="turn.editData.description"
                                                   placeholder="メモ（任意）">
                                        </div>
                                        <div class="col-12 d-flex gap-2">
                                            <button class="btn btn-sm btn-success" @click="saveEditTurn(turn)">保存</button>
                                            <button class="btn btn-sm btn-outline-secondary" @click="turn.editing=false">取消</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- ターン追加フォーム -->
    <div class="card border-2 border-success shadow-sm">
        <div class="card-header bg-success text-white">
            <strong><i class="bi bi-plus-circle"></i> ターンを追加</strong>
            <span class="ms-2 badge bg-light text-dark" x-text="'ターン '+nextTurnNumber"></span>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <!-- 自分側 -->
                <div class="col-md-5">
                    <div class="fw-semibold text-success mb-1" style="font-size:.85rem"><i class="bi bi-person"></i> 自分</div>
                    <div class="mb-1">
                        <select id="turn-my-pokemon" class="form-select form-select-sm" x-model="newTurn.my_pokemon_id"
                                @change="loadMyMoves()"
                                @keydown.tab.prevent="focusNext('turn-my-move')">
                            <option value="">ポケモン選択</option>
                            @foreach($myPokemonList as $cp)
                                <option value="{{ $cp->id }}">{{ $cp->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <select id="turn-my-move" class="form-select form-select-sm" x-model="newTurn.my_move_id"
                                :disabled="myMoves.length===0"
                                @keydown.tab.prevent="focusNext('turn-my-hp')">
                            <option value="">技を選択</option>
                            <template x-for="m in myMoves" :key="m.id">
                                <option :value="m.id" x-text="m.name_ja"></option>
                            </template>
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label style="font-size:.75rem;white-space:nowrap">自HP%</label>
                        <input id="turn-my-hp" type="range" class="form-range" min="0" max="100" step="5"
                               x-model.number="newTurn.my_hp_remaining"
                               @keydown.left.prevent="newTurn.my_hp_remaining = Math.max(0, newTurn.my_hp_remaining-5)"
                               @keydown.right.prevent="newTurn.my_hp_remaining = Math.min(100, newTurn.my_hp_remaining+5)"
                               @keydown.tab.prevent="focusNext('turn-opp-pokemon')">
                        <span style="width:38px;font-size:.8rem" x-text="newTurn.my_hp_remaining+'%'"></span>
                    </div>
                </div>

                <!-- 相手側 -->
                <div class="col-md-5">
                    <div class="fw-semibold text-danger mb-1" style="font-size:.85rem"><i class="bi bi-person-fill"></i> 相手</div>
                    <div class="mb-1">
                        <input id="turn-opp-pokemon" type="text" class="form-control form-control-sm"
                               x-model="newTurn.opponent_pokemon_name"
                               placeholder="相手のポケモン名"
                               list="pokemon-datalist"
                               @keydown.tab.prevent="focusNext('turn-opp-move-search')">
                        <datalist id="pokemon-datalist">
                            @foreach($myPokemonList as $cp)
                                <option value="{{ $cp->pokemon->name_ja }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="mb-1">
                        <div class="input-group input-group-sm">
                            <input id="turn-opp-move-search" type="text" class="form-control" placeholder="技名で検索..."
                                   x-model="opponentMoveSearch"
                                   @input.debounce.400ms="searchOpponentMoves()"
                                   @keydown.tab.prevent="focusNext('turn-opp-move-select')">
                        </div>
                        <select id="turn-opp-move-select" class="form-select form-select-sm mt-1" x-model="newTurn.opponent_move_id"
                                @keydown.tab.prevent="focusNext('turn-opp-hp')">
                            <option value="">技を選択</option>
                            <template x-for="m in opponentMoveResults" :key="m.id">
                                <option :value="m.id" x-text="m.name_ja"></option>
                            </template>
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label style="font-size:.75rem;white-space:nowrap">相手HP%</label>
                        <input id="turn-opp-hp" type="range" class="form-range" min="0" max="100" step="5"
                               x-model.number="newTurn.opponent_hp_remaining"
                               @keydown.left.prevent="newTurn.opponent_hp_remaining = Math.max(0, newTurn.opponent_hp_remaining-5)"
                               @keydown.right.prevent="newTurn.opponent_hp_remaining = Math.min(100, newTurn.opponent_hp_remaining+5)"
                               @keydown.tab.prevent="focusNext('turn-description')">
                        <span style="width:38px;font-size:.8rem" x-text="newTurn.opponent_hp_remaining+'%'"></span>
                    </div>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-success w-100" @click="addTurn()">
                        <i class="bi bi-plus"></i> 追加
                    </button>
                </div>

                <div class="col-12">
                    <textarea id="turn-description" class="form-control form-control-sm" x-model="newTurn.description"
                              rows="1" placeholder="メモ（任意） - Ctrl+Enter で追加"
                              @keydown.ctrl.enter.prevent="addTurn()"
                              @keydown.shift.tab.prevent="focusNext('turn-opp-hp')"></textarea>
                </div>
                <div class="col-12">
                    <small class="text-muted">
                        <kbd>Tab</kbd> 次フィールド &nbsp;
                        <kbd>Shift+Tab</kbd> 前フィールド &nbsp;
                        <kbd>←</kbd><kbd>→</kbd> HPスライダー±5% &nbsp;
                        <kbd>Ctrl+Enter</kbd> ターン追加
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked@9.1.6/marked.min.js"></script>
<script>
function battleShow(battleId) {
    return {
        battleId,
        turns: @json($battle->turns->map(fn($t) => [
            'id' => $t->id,
            'battle_id' => $t->battle_id,
            'turn_number' => $t->turn_number,
            'my_pokemon' => $t->myPokemon ? ['display_name' => $t->myPokemon->display_name] : null,
            'opponent_pokemon_name' => $t->opponent_pokemon_name,
            'my_move' => $t->myMove ? ['name_ja' => $t->myMove->name_ja] : null,
            'opponent_move' => $t->opponentMove ? ['name_ja' => $t->opponentMove->name_ja] : null,
            'my_hp_remaining' => $t->my_hp_remaining,
            'opponent_hp_remaining' => $t->opponent_hp_remaining,
            'description' => $t->description,
            'editing' => false,
            'editData' => null,
        ])->values()),
        battleResult: '{{ $battle->result ?? '' }}',
        memo: @json($battle->memo ?? ''),
        editingMemo: false,
        tags: @json(array_filter(array_map('trim', explode(',', $battle->tags ?? '')))),
        editingTags: false,
        tagsInput: '{{ $battle->tags ?? '' }}',
        newTurn: {my_pokemon_id:'',opponent_pokemon_name:'',my_move_id:'',opponent_move_id:'',
                  my_hp_remaining:100,opponent_hp_remaining:100,description:''},
        myMoves: [],
        opponentMoveSearch: '',
        opponentMoveResults: [],
        // 相手ポケモン
        opponentPokemon: @json($battle->opponentPokemon->map(fn($op) => [
            'id' => $op->id,
            'slot' => $op->slot,
            'pokemon_id' => $op->pokemon_id,
            'nickname' => $op->nickname,
            'pokemon' => $op->pokemon ? [
                'id' => $op->pokemon->id,
                'name_ja' => $op->pokemon->name_ja,
                'sprite_url' => $op->pokemon->sprite_url,
                'types' => $op->pokemon->types->map(fn($t) => ['type' => $t->type])->values(),
            ] : null,
        ])->values()),
        editingOpponentSlot: null,
        opponentSlotForm: {pokemon_id: null, nickname: ''},
        opponentPokemonSearch: '',
        opponentPokemonResults: [],
        recognitionResults: [],
        recognitionMessage: '',
        recognizing: false,
        reportOpen: false,
        hpChartInstance: null,
        typeSummaryOpen: true,

        typeLabel(t) {
            const m = {normal:'ノーマル',fire:'ほのお',water:'みず',electric:'でんき',grass:'くさ',
                ice:'こおり',fighting:'かくとう',poison:'どく',ground:'じめん',flying:'ひこう',
                psychic:'エスパー',bug:'むし',rock:'いわ',ghost:'ゴースト',dragon:'ドラゴン',
                dark:'あく',steel:'はがね',fairy:'フェアリー'};
            return m[t] || t;
        },

        // タイプ相性表 (攻撃タイプ → 防御タイプ → 倍率)
        typeChart: {
            normal:   {rock:.5,ghost:0,steel:.5},
            fire:     {fire:.5,water:.5,rock:.5,grass:2,ice:2,bug:2,dragon:.5,steel:2},
            water:    {fire:2,water:.5,grass:.5,ground:2,rock:2,dragon:.5},
            electric: {water:2,electric:.5,grass:.5,ground:0,flying:2,dragon:.5},
            grass:    {fire:.5,water:2,grass:.5,poison:.5,ground:2,flying:.5,bug:.5,rock:2,dragon:.5,steel:.5},
            ice:      {fire:.5,water:.5,grass:2,ice:.5,ground:2,flying:2,dragon:2,steel:.5},
            fighting: {normal:2,ice:2,poison:.5,flying:.5,psychic:.5,bug:.5,rock:2,ghost:0,dark:2,steel:2,fairy:.5},
            poison:   {grass:2,poison:.5,ground:.5,rock:.5,ghost:.5,steel:0,fairy:2},
            ground:   {fire:2,electric:2,grass:.5,poison:2,flying:0,bug:.5,rock:2,steel:2},
            flying:   {electric:.5,grass:2,fighting:2,bug:2,rock:.5,steel:.5},
            psychic:  {fighting:2,poison:2,psychic:.5,dark:0,steel:.5},
            bug:      {fire:.5,grass:2,fighting:.5,flying:.5,psychic:2,ghost:.5,dark:2,steel:.5,fairy:.5},
            rock:     {fire:2,ice:2,fighting:.5,ground:.5,flying:2,bug:2,steel:.5},
            ghost:    {normal:0,psychic:2,ghost:2,dark:.5},
            dragon:   {dragon:2,steel:.5,fairy:0},
            dark:     {fighting:.5,psychic:2,ghost:2,dark:.5,fairy:.5},
            steel:    {fire:.5,water:.5,electric:.5,ice:2,rock:2,steel:.5,fairy:2},
            fairy:    {fire:.5,fighting:2,poison:.5,dragon:2,dark:2,steel:.5},
        },

        calcTypeEffectiveness(atkType, defTypes) {
            let mult = 1;
            for (const dt of defTypes) {
                const row = this.typeChart[atkType] || {};
                mult *= (row[dt] !== undefined ? row[dt] : 1);
            }
            return mult;
        },

        get opponentTypeSummary() {
            const allTypes = ['normal','fire','water','electric','grass','ice','fighting','poison',
                              'ground','flying','psychic','bug','rock','ghost','dragon','dark','steel','fairy'];
            const result = allTypes.map(t => ({type: t, superEffective: 0, notVeryEffective: 0}));
            const mons = this.opponentPokemon.filter(op => op.pokemon && op.pokemon.types && op.pokemon.types.length > 0);
            if (mons.length === 0) return [];
            for (const op of mons) {
                const defTypes = op.pokemon.types.map(t => t.type);
                for (const entry of result) {
                    const mult = this.calcTypeEffectiveness(entry.type, defTypes);
                    if (mult >= 2) entry.superEffective++;
                    else if (mult <= 0.5) entry.notVeryEffective++;
                }
            }
            return result.filter(e => e.superEffective > 0 || e.notVeryEffective > 0);
        },

        get nextTurnNumber() {
            return this.turns.length > 0 ? Math.max(...this.turns.map(t=>t.turn_number))+1 : 1;
        },

        renderMarkdown(text) {
            if (!text) return '';
            if (window.marked) return marked.parse(text, { breaks: true, gfm: true });
            // marked未読込の場合は改行のみ変換
            return text.replace(/\n/g, '<br>');
        },

        focusNext(id) {
            const el = document.getElementById(id);
            if (el) { el.focus(); if (el.select) el.select(); }
        },

        // レポート: 使用技集計
        get myMoveUsage() {
            const cnt = {};
            this.turns.forEach(t => {
                if (t.my_move?.name_ja) cnt[t.my_move.name_ja] = (cnt[t.my_move.name_ja]||0)+1;
            });
            return Object.entries(cnt).sort((a,b)=>b[1]-a[1]).slice(0,6);
        },
        get oppMoveUsage() {
            const cnt = {};
            this.turns.forEach(t => {
                if (t.opponent_move?.name_ja) cnt[t.opponent_move.name_ja] = (cnt[t.opponent_move.name_ja]||0)+1;
            });
            return Object.entries(cnt).sort((a,b)=>b[1]-a[1]).slice(0,6);
        },

        initHpChart() {
            if (!window.Chart) return;
            const ctx = document.getElementById('hpChart');
            if (!ctx) return;
            if (this.hpChartInstance) this.hpChartInstance.destroy();
            const labels = this.turns.map(t => 'T'+t.turn_number);
            this.hpChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: '自分HP%',
                            data: this.turns.map(t => t.my_hp_remaining ?? null),
                            borderColor: '#198754', backgroundColor: 'rgba(25,135,84,.1)',
                            tension: 0.3, fill: true, spanGaps: true, pointRadius: 3,
                        },
                        {
                            label: '相手HP%',
                            data: this.turns.map(t => t.opponent_hp_remaining ?? null),
                            borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,.1)',
                            tension: 0.3, fill: true, spanGaps: true, pointRadius: 3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    scales: { y: { min: 0, max: 100, ticks: { callback: v => v+'%' } } },
                    plugins: { legend: { position: 'bottom' } },
                },
            });
        },

        init() {
            this.$watch('reportOpen', val => {
                if (val) this.$nextTick(() => this.initHpChart());
            });
        },

        // 相手ポケモン
        getOpponentSlot(slot) {
            return this.opponentPokemon.find(p => p.slot === slot) || null;
        },
        openOpponentSlot(slot) {
            this.editingOpponentSlot = slot;
            const existing = this.getOpponentSlot(slot);
            this.opponentSlotForm = {
                pokemon_id: existing?.pokemon_id || null,
                nickname: existing?.nickname || '',
            };
            this.opponentPokemonSearch = existing?.pokemon?.name_ja || '';
            this.opponentPokemonResults = [];
        },
        async clearOpponentSlot(slot) {
            await fetch(`/api/v1/battles/${this.battleId}/opponent-pokemon/${slot}`, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            });
            this.opponentPokemon = this.opponentPokemon.filter(p => p.slot !== slot);
        },
        async searchOpponentPokemon() {
            if (!this.opponentPokemonSearch.trim()) { this.opponentPokemonResults = []; return; }
            const res = await fetch(`/api/v1/pokemon?name=${encodeURIComponent(this.opponentPokemonSearch)}&per_page=10`);
            const data = await res.json();
            this.opponentPokemonResults = data.data || [];
        },
        selectOpponentPokemon(p) {
            this.opponentSlotForm.pokemon_id = p.id;
            this.opponentPokemonSearch = p.name_ja;
            this.opponentPokemonResults = [];
        },
        async recognizeFromScreenshot(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.recognizing = true;
            this.recognitionResults = [];
            this.recognitionMessage = '';
            try {
                const fd = new FormData();
                fd.append('image', file);
                const res = await fetch('/api/v1/recognize-pokemon', {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                    body: fd,
                });
                const data = await res.json();
                if (!res.ok) {
                    this.recognitionMessage = data.error || '認識に失敗しました';
                } else if (data.message) {
                    this.recognitionMessage = data.message;
                } else {
                    this.recognitionResults = data.results || [];
                    if (this.recognitionResults.length === 0) {
                        this.recognitionMessage = '一致するポケモンが見つかりませんでした';
                    }
                }
            } catch(e) {
                this.recognitionMessage = '通信エラーが発生しました';
            } finally {
                this.recognizing = false;
                event.target.value = '';
            }
        },

        async saveOpponentSlot() {
            if (!this.editingOpponentSlot) return;
            const res = await fetch(`/api/v1/battles/${this.battleId}/opponent-pokemon`, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify({
                    slot: this.editingOpponentSlot,
                    pokemon_id: this.opponentSlotForm.pokemon_id,
                    nickname: this.opponentSlotForm.nickname || null,
                }),
            });
            if (res.ok) {
                const data = await res.json();
                const idx = this.opponentPokemon.findIndex(p => p.slot === this.editingOpponentSlot);
                const record = {
                    id: data.id, slot: data.slot, pokemon_id: data.pokemon_id,
                    nickname: data.nickname, pokemon: data.pokemon || null,
                };
                if (idx >= 0) this.opponentPokemon[idx] = record;
                else this.opponentPokemon.push(record);
                this.opponentPokemon = [...this.opponentPokemon];
                this.editingOpponentSlot = null;
                this.opponentPokemonResults = [];
            }
        },

        hpColor(pct) {
            if (pct > 50) return '#28a745';
            if (pct > 25) return '#ffc107';
            return '#dc3545';
        },

        async updateResult(result) {
            this.battleResult = result;
            await fetch(`/api/v1/battles/${this.battleId}`, {
                method: 'PUT',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify({result}),
            });
        },

        async saveMemo() {
            await fetch(`/api/v1/battles/${this.battleId}`, {
                method: 'PUT',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify({memo: this.memo}),
            });
            this.editingMemo = false;
        },

        async saveTags() {
            const res = await fetch(`/api/v1/battles/${this.battleId}`, {
                method: 'PUT',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify({tags: this.tagsInput}),
            });
            if (res.ok) {
                this.tags = this.tagsInput.split(',').map(t=>t.trim()).filter(t=>t);
                this.editingTags = false;
                if (window.showToast) showToast('タグを保存しました');
            }
        },

        async deleteBattle() {
            if (!confirm('この対戦記録を削除しますか？')) return;
            const res = await fetch(`/api/v1/battles/${this.battleId}`, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
            });
            if (res.ok) location.href = '/battles';
        },

        startEditTurn(turn) {
            turn.editData = {
                my_hp_remaining: turn.my_hp_remaining ?? 100,
                opponent_hp_remaining: turn.opponent_hp_remaining ?? 100,
                description: turn.description ?? '',
            };
            turn.editing = true;
        },

        async saveEditTurn(turn) {
            const payload = {
                turn_number: turn.turn_number,
                my_pokemon_id: null,
                opponent_pokemon_name: turn.opponent_pokemon_name ?? null,
                my_move_id: null,
                opponent_move_id: null,
                my_hp_remaining: turn.editData.my_hp_remaining,
                opponent_hp_remaining: turn.editData.opponent_hp_remaining,
                description: turn.editData.description || null,
            };
            const res = await fetch(`/api/v1/battles/${turn.battle_id}/turns/${turn.turn_number}`, {
                method: 'PUT',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify(payload),
            });
            if (res.ok) {
                turn.my_hp_remaining = turn.editData.my_hp_remaining;
                turn.opponent_hp_remaining = turn.editData.opponent_hp_remaining;
                turn.description = turn.editData.description || null;
                turn.editing = false;
            }
        },

        async loadMyMoves() {
            this.myMoves = [];
            this.newTurn.my_move_id = '';
            if (!this.newTurn.my_pokemon_id) return;
            const res = await fetch(`/api/v1/custom-pokemon/${this.newTurn.my_pokemon_id}`);
            const data = await res.json();
            this.myMoves = data.moves || [];
        },

        async searchOpponentMoves() {
            if (!this.opponentMoveSearch.trim()) return;
            const res = await fetch(`/api/v1/moves?name=${encodeURIComponent(this.opponentMoveSearch)}&per_page=20`);
            const data = await res.json();
            this.opponentMoveResults = data.data || [];
        },

        async addTurn() {
            const payload = {
                turn_number: this.nextTurnNumber,
                my_pokemon_id: this.newTurn.my_pokemon_id || null,
                opponent_pokemon_name: this.newTurn.opponent_pokemon_name || null,
                my_move_id: this.newTurn.my_move_id || null,
                opponent_move_id: this.newTurn.opponent_move_id || null,
                my_hp_remaining: this.newTurn.my_hp_remaining,
                opponent_hp_remaining: this.newTurn.opponent_hp_remaining,
                description: this.newTurn.description || null,
            };
            const res = await fetch(`/api/v1/battles/${this.battleId}/turns`, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify(payload),
            });
            if (res.ok) {
                const turn = await res.json();
                // ターン表示用にリレーション名を変換
                const displayed = {
                    id: turn.id, battle_id: turn.battle_id, turn_number: turn.turn_number,
                    my_pokemon: turn.my_pokemon || null,
                    opponent_pokemon_name: turn.opponent_pokemon_name,
                    my_move: turn.my_move || null,
                    opponent_move: turn.opponent_move || null,
                    my_hp_remaining: turn.my_hp_remaining,
                    opponent_hp_remaining: turn.opponent_hp_remaining,
                    description: turn.description,
                };
                this.turns.push(displayed);
                this.newTurn = {my_pokemon_id:'',opponent_pokemon_name:'',my_move_id:'',opponent_move_id:'',
                                my_hp_remaining:100,opponent_hp_remaining:100,description:''};
                this.opponentMoveSearch = '';
                this.opponentMoveResults = [];
            }
        },

        async deleteTurn(bId, turnNumber) {
            if (!confirm(`ターン ${turnNumber} を削除しますか？`)) return;
            await fetch(`/api/v1/battles/${bId}/turns/${turnNumber}`, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
            });
            this.turns = this.turns.filter(t => t.turn_number !== turnNumber);
        },
    };
}
</script>
@endpush
