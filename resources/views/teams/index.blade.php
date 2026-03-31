@extends('layouts.app')
@section('title', 'チームビルダー')
@section('content')
<div x-data="teamBuilder()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-people"></i> チームビルダー</h4>
        <button class="btn btn-success btn-sm" @click="openNewTeam()">
            <i class="bi bi-plus-circle"></i> 新規チーム
        </button>
    </div>

    <!-- チーム一覧 -->
    <div class="row g-3" id="teamList">
        <template x-if="teams.length === 0">
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-people" style="font-size:3rem"></i>
                <div class="mt-2">チームがありません</div>
                <button class="btn btn-success mt-3" @click="openNewTeam()">最初のチームを作成</button>
            </div>
        </template>
        <template x-for="team in teams" :key="team.id">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <strong x-text="team.name"></strong>
                            <small class="text-muted ms-2" x-text="team.memo || ''"></small>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" @click="editTeamInfo(team)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" @click="deleteTeam(team.id)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- 6スロット -->
                        <div class="row g-2 mb-3">
                            <template x-for="slot in [1,2,3,4,5,6]" :key="slot">
                                <div class="col-4 col-md-2">
                                    <div class="border rounded p-2 text-center"
                                         style="min-height:110px;cursor:pointer;position:relative"
                                         :class="getMember(team,slot) ? 'border-primary bg-light' : 'border-dashed border-secondary'"
                                         @click="openSlotPicker(team,slot)">
                                        <template x-if="getMember(team,slot)">
                                            <div>
                                                <img :src="getMember(team,slot).custom_pokemon?.pokemon?.sprite_url || ''"
                                                     x-show="getMember(team,slot).custom_pokemon?.pokemon?.sprite_url"
                                                     style="width:48px;height:48px;object-fit:contain">
                                                <i class="bi bi-question-circle text-muted"
                                                   x-show="!getMember(team,slot).custom_pokemon?.pokemon?.sprite_url"
                                                   style="font-size:1.8rem"></i>
                                                <div style="font-size:.72rem;font-weight:600;line-height:1.2" x-text="getMember(team,slot).custom_pokemon?.nickname || getMember(team,slot).custom_pokemon?.pokemon?.name_ja || '?'"></div>
                                                <div class="mt-1">
                                                    <template x-for="t in (getMember(team,slot).custom_pokemon?.pokemon?.types||[])" :key="t.type">
                                                        <span class="type-badge" :class="'type-'+t.type" style="font-size:.6rem" x-text="typeLabel(t.type)"></span>
                                                    </template>
                                                </div>
                                                <button class="btn btn-xs p-0 text-danger" style="font-size:.65rem;position:absolute;top:2px;right:4px"
                                                        @click.stop="clearSlot(team,slot)">✕</button>
                                            </div>
                                        </template>
                                        <template x-if="!getMember(team,slot)">
                                            <div class="d-flex flex-column justify-content-center align-items-center h-100" style="min-height:80px">
                                                <i class="bi bi-plus-circle text-muted" style="font-size:1.5rem"></i>
                                                <div style="font-size:.7rem;color:#adb5bd">スロット <span x-text="slot"></span></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- タイプ相性分析 -->
                        <div x-show="teamHasMembers(team)">
                            <div class="border-top pt-2">
                                <small class="text-muted fw-bold">タイプ相性（被弾）— 赤=弱点 / 青=耐性 / グレー=等倍 / 黒=無効</small>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    <template x-for="[type,eff] in typeEffectivenessEntries(team)" :key="type">
                                        <span class="badge"
                                              :class="eff > 1 ? 'bg-danger' : (eff < 1 && eff > 0 ? 'bg-primary' : (eff === 0 ? 'bg-dark' : 'bg-secondary'))"
                                              style="font-size:.7rem">
                                            <span x-text="typeLabel(type)"></span>
                                            <span x-text="effLabel(eff)"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- 新規/編集チームモーダル -->
    <div class="modal fade" id="teamModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="editingTeam ? 'チーム編集' : '新規チーム'"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">チーム名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" x-model="teamForm.name" placeholder="例: ランクマッチ用パーティ">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">メモ</label>
                        <input type="text" class="form-control" x-model="teamForm.memo" placeholder="コンセプトなど">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="saveTeam()">保存</button>
                </div>
            </div>
        </div>
    </div>

    <!-- スロット選択モーダル -->
    <div class="modal fade" id="slotModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ポケモンを選択（スロット <span x-text="pickingSlot"></span>）</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control form-control-sm mb-3"
                           x-model="slotSearch" placeholder="名前で絞り込み...">
                    <div class="row g-2" style="max-height:400px;overflow-y:auto">
                        <template x-for="cp in filteredMyPokemon" :key="cp.id">
                            <div class="col-4 col-md-3">
                                <div class="card border-0 shadow-sm pokemon-card p-2 text-center h-100"
                                     @click="selectSlotPokemon(cp)">
                                    <img :src="cp.pokemon?.sprite_url || ''"
                                         x-show="cp.pokemon?.sprite_url"
                                         style="height:48px;object-fit:contain;margin:0 auto">
                                    <i class="bi bi-question-circle text-muted"
                                       x-show="!cp.pokemon?.sprite_url" style="font-size:1.5rem"></i>
                                    <div class="fw-semibold" style="font-size:.8rem" x-text="cp.nickname || cp.pokemon?.name_ja || '?'"></div>
                                    <div>
                                        <template x-for="t in (cp.pokemon?.types||[])" :key="t.type">
                                            <span class="type-badge" :class="'type-'+t.type" style="font-size:.6rem" x-text="typeLabel(t.type)"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="filteredMyPokemon.length === 0">
                            <div class="col-12 text-center text-muted py-3">マイポケモンがありません</div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function teamBuilder() {
    return {
        teams: [],
        myPokemon: @json($myPokemonList->map(fn($cp) => [
            'id'      => $cp->id,
            'nickname'=> $cp->nickname,
            'pokemon' => $cp->pokemon ? [
                'id'         => $cp->pokemon->id,
                'name_ja'    => $cp->pokemon->name_ja,
                'sprite_url' => $cp->pokemon->sprite_url,
                'types'      => $cp->pokemon->types->map(fn($t)=>['type'=>$t->type])->values(),
            ] : null,
        ])->values()),
        teamForm: {name:'', memo:''},
        editingTeam: null,
        pickingTeam: null,
        pickingSlot: null,
        slotSearch: '',
        teamModal: null,
        slotModal: null,

        get filteredMyPokemon() {
            const q = this.slotSearch.toLowerCase();
            if (!q) return this.myPokemon;
            return this.myPokemon.filter(cp =>
                (cp.nickname||'').toLowerCase().includes(q) ||
                (cp.pokemon?.name_ja||'').toLowerCase().includes(q)
            );
        },

        typeLabels: {
            normal:'ノーマル',fire:'ほのお',water:'みず',electric:'でんき',grass:'くさ',
            ice:'こおり',fighting:'かくとう',poison:'どく',ground:'じめん',flying:'ひこう',
            psychic:'エスパー',bug:'むし',rock:'いわ',ghost:'ゴースト',dragon:'ドラゴン',
            dark:'あく',steel:'はがね',fairy:'フェアリー'
        },
        typeLabel(t) { return this.typeLabels[t] || t; },
        effLabel(e) {
            if (e === 0)   return ' ×0';
            if (e < 1)     return ' ×½';
            if (e > 2)     return ' ×4';
            if (e > 1)     return ' ×2';
            return '';
        },

        // タイプ相性表（攻撃側タイプ → 防御側タイプへの倍率）
        typeChart: {
            normal:    {rock:.5, steel:.5, ghost:0},
            fire:      {fire:.5, water:.5, rock:.5, dragon:.5, grass:2, ice:2, bug:2, steel:2},
            water:     {water:.5, grass:.5, dragon:.5, fire:2, ground:2, rock:2},
            electric:  {electric:.5, grass:.5, dragon:.5, ground:0, flying:2, water:2},
            grass:     {fire:.5, grass:.5, poison:.5, flying:.5, bug:.5, dragon:.5, steel:.5, water:2, ground:2, rock:2},
            ice:       {water:.5, ice:.5, fire:2, fighting:2, rock:2, steel:2, grass:2, dragon:2, ground:2, flying:2},
            fighting:  {poison:.5, flying:.5, psychic:.5, bug:.5, fairy:.5, ghost:0, normal:2, rock:2, steel:2, ice:2, dark:2},
            poison:    {poison:.5, ground:.5, rock:.5, ghost:.5, steel:0, grass:2, fairy:2},
            ground:    {grass:.5, bug:.5, flying:0, fire:2, electric:2, poison:2, rock:2, steel:2},
            flying:    {electric:.5, rock:.5, steel:.5, fighting:2, bug:2, grass:2},
            psychic:   {psychic:.5, steel:.5, dark:0, fighting:2, poison:2},
            bug:       {fire:.5, fighting:.5, flying:.5, ghost:.5, steel:.5, fairy:.5, grass:2, psychic:2, dark:2},
            rock:      {fighting:.5, ground:.5, steel:.5, fire:2, ice:2, flying:2, bug:2},
            ghost:     {normal:0, dark:.5, psychic:2, ghost:2},
            dragon:    {steel:.5, fairy:0, dragon:2},
            dark:      {fighting:.5, dark:.5, fairy:.5, psychic:2, ghost:2},
            steel:     {fire:.5, water:.5, electric:.5, steel:.5, rock:2, ice:2, fairy:2},
            fairy:     {fire:.5, poison:.5, steel:.5, dragon:0, fighting:2, dark:2},
        },

        // チームの全ポケモンに対する被弾倍率を集計（最大×4まで）
        typeEffectivenessEntries(team) {
            const allTypes = Object.keys(this.typeLabels);
            const totals = {};
            allTypes.forEach(atk => { totals[atk] = 1; });

            const members = (team.members || []).filter(m => m.custom_pokemon?.pokemon?.types);
            if (members.length === 0) return [];

            // チーム全員の平均的な弱点を示すため、1体ずつ倍率を累積
            // ここでは「何体が弱点か」を示す
            const weakCount  = {};
            const resistCount= {};
            const immuneCount= {};
            allTypes.forEach(t => { weakCount[t]=0; resistCount[t]=0; immuneCount[t]=0; });

            members.forEach(m => {
                const defTypes = (m.custom_pokemon.pokemon.types || []).map(t => t.type);
                allTypes.forEach(atk => {
                    let mult = 1;
                    defTypes.forEach(def => {
                        mult *= (this.typeChart[atk]?.[def] ?? 1);
                    });
                    if (mult > 1)      weakCount[atk]++;
                    else if (mult === 0) immuneCount[atk]++;
                    else if (mult < 1)  resistCount[atk]++;
                });
            });

            // 弱点が多いタイプを優先表示
            return allTypes
                .map(t => {
                    let eff = 1;
                    if (weakCount[t] > resistCount[t]) eff = 2;
                    else if (resistCount[t] > weakCount[t]) eff = 0.5;
                    else if (immuneCount[t] > 0 && weakCount[t] === 0) eff = 0;
                    return [t, eff, weakCount[t]];
                })
                .filter(([,e,w]) => e !== 1 || w > 0)
                .sort((a,b) => b[2]-a[2] || b[1]-a[1])
                .map(([t,e]) => [t,e]);
        },

        teamHasMembers(team) {
            return (team.members||[]).some(m => m.custom_pokemon_id);
        },

        getMember(team, slot) {
            return (team.members||[]).find(m => m.slot === slot) || null;
        },

        async init() {
            this.teamModal = new bootstrap.Modal(document.getElementById('teamModal'));
            this.slotModal = new bootstrap.Modal(document.getElementById('slotModal'));
            await this.loadTeams();
        },

        async loadTeams() {
            const res = await fetch('/api/v1/teams', {
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            });
            this.teams = await res.json();
        },

        openNewTeam() {
            this.editingTeam = null;
            this.teamForm = {name:'', memo:''};
            this.teamModal.show();
        },

        editTeamInfo(team) {
            this.editingTeam = team;
            this.teamForm = {name: team.name, memo: team.memo || ''};
            this.teamModal.show();
        },

        async saveTeam() {
            if (!this.teamForm.name.trim()) { alert('チーム名を入力してください'); return; }
            const url    = this.editingTeam ? `/api/v1/teams/${this.editingTeam.id}` : '/api/v1/teams';
            const method = this.editingTeam ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify(this.teamForm),
            });
            if (res.ok) {
                this.teamModal.hide();
                await this.loadTeams();
                if (window.showToast) showToast(this.editingTeam ? 'チームを更新しました' : 'チームを作成しました');
            }
        },

        async deleteTeam(id) {
            if (!confirm('このチームを削除しますか？')) return;
            await fetch(`/api/v1/teams/${id}`, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            });
            await this.loadTeams();
            if (window.showToast) showToast('チームを削除しました', 'info');
        },

        openSlotPicker(team, slot) {
            this.pickingTeam = team;
            this.pickingSlot = slot;
            this.slotSearch = '';
            this.slotModal.show();
        },

        async selectSlotPokemon(cp) {
            const res = await fetch(`/api/v1/teams/${this.pickingTeam.id}/members/${this.pickingSlot}`, {
                method: 'PUT',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify({custom_pokemon_id: cp.id}),
            });
            if (res.ok) {
                const updated = await res.json();
                const idx = this.teams.findIndex(t => t.id === this.pickingTeam.id);
                if (idx !== -1) this.teams[idx] = updated;
                this.slotModal.hide();
            }
        },

        async clearSlot(team, slot) {
            const res = await fetch(`/api/v1/teams/${team.id}/members/${slot}`, {
                method: 'PUT',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify({custom_pokemon_id: null}),
            });
            if (res.ok) {
                const updated = await res.json();
                const idx = this.teams.findIndex(t => t.id === team.id);
                if (idx !== -1) this.teams[idx] = updated;
            }
        },
    };
}
</script>
@endpush
