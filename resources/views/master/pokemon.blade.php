@extends('layouts.app')
@section('title', 'ポケモン登録')
@section('content')
<div x-data="masterPokemon()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-database-add"></i> ポケモン登録</h4>
        <button class="btn btn-success btn-sm" @click="openCreate()">
            <i class="bi bi-plus-circle"></i> 新規登録
        </button>
    </div>

    <div x-show="showForm" class="card border-success shadow-sm mb-4" x-cloak>
        <div class="card-header bg-success text-white d-flex justify-content-between">
            <strong x-text="editId ? 'ポケモンを編集' : 'ポケモンを登録'"></strong>
            <button class="btn-close btn-close-white btn-sm" @click="showForm=false"></button>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-1">
                    <label class="form-label">図鑑No. <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm" x-model.number="form.pokedex_number" min="1"
                           :class="errors.pokedex_number ? 'is-invalid' : ''">
                    <div class="invalid-feedback" x-text="errors.pokedex_number"></div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">日本語名 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" x-model="form.name_ja"
                           :class="errors.name_ja ? 'is-invalid' : ''">
                    <div class="invalid-feedback" x-text="errors.name_ja"></div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">英語名 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" x-model="form.name_en"
                           :class="errors.name_en ? 'is-invalid' : ''">
                    <div class="invalid-feedback" x-text="errors.name_en"></div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">フォルム名</label>
                    <input type="text" class="form-control form-control-sm" x-model="form.form_name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">スプライトURL</label>
                    <input type="text" class="form-control form-control-sm" x-model="form.sprite_url"
                           placeholder="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/{no}.png"
                           @input.debounce="updateSpritePreview()">
                </div>
                <!-- 種族値 -->
                <div class="col-md-12">
                    <label class="form-label">種族値 <span class="text-danger">*</span></label>
                    <div class="row g-1">
                        @foreach(['base_hp'=>'HP','base_attack'=>'攻撃','base_defense'=>'防御','base_sp_attack'=>'特攻','base_sp_defense'=>'特防','base_speed'=>'素早さ'] as $key => $label)
                        <div class="col-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" style="font-size:.75rem">{{ $label }}</span>
                                <input type="number" class="form-control" x-model.number="form.{{ $key }}" min="1" max="255">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <!-- タイプ -->
                <div class="col-md-4">
                    <label class="form-label">タイプ1 <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" x-model="form.type1"
                            :class="errors.types ? 'is-invalid' : ''">
                        <option value="">-- 選択 --</option>
                        @foreach($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" x-text="errors.types"></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">タイプ2</label>
                    <select class="form-select form-select-sm" x-model="form.type2">
                        <option value="">なし</option>
                        @foreach($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <template x-if="spritePreview">
                        <img :src="spritePreview" style="width:64px;height:64px;object-fit:contain" class="me-2">
                    </template>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-success btn-sm" @click="save()" :disabled="saving">
                        <span x-show="saving" class="spinner-border spinner-border-sm me-1"></span>
                        保存
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" @click="showForm=false">キャンセル</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 検索 -->
    <div class="mb-2 d-flex gap-2">
        <input type="text" class="form-control form-control-sm" x-model="filterName" placeholder="名前で絞り込み" style="max-width:200px">
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr><th>No.</th><th></th><th>名前</th><th>タイプ</th><th>HP</th><th>攻撃</th><th>防御</th><th>特攻</th><th>特防</th><th>素早さ</th><th>合計</th><th></th></tr>
                </thead>
                <tbody>
                    <template x-for="p in filteredPokemon" :key="p.id">
                        <tr>
                            <td class="text-muted" x-text="p.pokedex_number"></td>
                            <td>
                                <img x-show="p.sprite_url" :src="p.sprite_url" style="width:32px;height:32px;object-fit:contain">
                            </td>
                            <td>
                                <div class="fw-semibold" x-text="p.name_ja"></div>
                                <small class="text-muted" x-text="p.name_en"></small>
                            </td>
                            <td>
                                <template x-for="t in (p.types||[])" :key="t.slot">
                                    <span class="type-badge me-1" :class="'type-'+t.type" x-text="typeLabel(t.type)" style="font-size:.65rem"></span>
                                </template>
                            </td>
                            <td x-text="p.base_hp"></td>
                            <td x-text="p.base_attack"></td>
                            <td x-text="p.base_defense"></td>
                            <td x-text="p.base_sp_attack"></td>
                            <td x-text="p.base_sp_defense"></td>
                            <td x-text="p.base_speed"></td>
                            <td class="fw-bold" x-text="p.base_hp+p.base_attack+p.base_defense+p.base_sp_attack+p.base_sp_defense+p.base_speed"></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:1px 6px"
                                            @click="openEdit(p)">編集</button>
                                    <label class="btn btn-xs btn-outline-primary" style="font-size:.75rem;padding:1px 6px;cursor:pointer"
                                           :title="'画像をアップロード: ' + p.name_ja">
                                        <i class="bi bi-image"></i>
                                        <input type="file" accept="image/*" class="d-none"
                                               @change="uploadImage(p, $event)">
                                    </label>
                                    <button class="btn btn-xs btn-outline-danger" style="font-size:.75rem;padding:1px 6px"
                                            @click="deleteItem(p.id)">削除</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-2 text-muted small" x-text="filteredPokemon.length + '件'"></div>
</div>
@endsection
@push('scripts')
<script>
const POKEMON_TYPE_LABELS = {
    normal:'ノーマル',fire:'ほのお',water:'みず',electric:'でんき',grass:'くさ',ice:'こおり',
    fighting:'かくとう',poison:'どく',ground:'じめん',flying:'ひこう',psychic:'エスパー',
    bug:'むし',rock:'いわ',ghost:'ゴースト',dragon:'ドラゴン',dark:'あく',steel:'はがね',fairy:'フェアリー'
};
function masterPokemon() {
    return {
        pokemon: @json($pokemon->items()),
        showForm: false, editId: null, saving: false, filterName: '',
        spritePreview: '',
        form: {pokedex_number:'',name_ja:'',name_en:'',form_name:'',
               base_hp:50,base_attack:50,base_defense:50,base_sp_attack:50,base_sp_defense:50,base_speed:50,
               sprite_url:'',type1:'',type2:''},
        errors: {},

        async init() {},

        typeLabel(t) { return POKEMON_TYPE_LABELS[t] || t; },

        get filteredPokemon() {
            if (!this.filterName) return this.pokemon;
            return this.pokemon.filter(p => p.name_ja.includes(this.filterName) || p.name_en.includes(this.filterName));
        },

        updateSpritePreview() {
            this.spritePreview = this.form.sprite_url || '';
        },

        openCreate() {
            this.editId = null;
            this.form = {pokedex_number:'',name_ja:'',name_en:'',form_name:'',
                         base_hp:50,base_attack:50,base_defense:50,base_sp_attack:50,base_sp_defense:50,base_speed:50,
                         sprite_url:'',type1:'',type2:''};
            this.spritePreview = '';
            this.errors = {};
            this.showForm = true;
        },

        openEdit(p) {
            this.editId = p.id;
            const types = (p.types||[]).sort((a,b) => a.slot-b.slot);
            this.form = {
                pokedex_number: p.pokedex_number, name_ja: p.name_ja, name_en: p.name_en,
                form_name: p.form_name||'',
                base_hp: p.base_hp, base_attack: p.base_attack, base_defense: p.base_defense,
                base_sp_attack: p.base_sp_attack, base_sp_defense: p.base_sp_defense, base_speed: p.base_speed,
                sprite_url: p.sprite_url||'',
                type1: types[0]?.type||'', type2: types[1]?.type||'',
            };
            this.spritePreview = p.sprite_url||'';
            this.errors = {};
            this.showForm = true;
        },

        async save() {
            this.errors = {};
            this.saving = true;
            const types = [this.form.type1];
            if (this.form.type2) types.push(this.form.type2);
            const payload = {...this.form, types};
            delete payload.type1; delete payload.type2;

            const url = this.editId ? `/api/v1/pokemon/${this.editId}` : '/api/v1/pokemon';
            const method = this.editId ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            this.saving = false;
            if (!res.ok) {
                this.errors = Object.fromEntries(
                    Object.entries(data.errors || {}).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                );
                return;
            }
            if (this.editId) {
                const idx = this.pokemon.findIndex(p => p.id === this.editId);
                if (idx >= 0) this.pokemon[idx] = data;
            } else {
                this.pokemon.push(data);
            }
            this.showForm = false;
        },

        async uploadImage(pokemon, event) {
            const file = event.target.files[0];
            if (!file) return;
            const fd = new FormData();
            fd.append('image', file);
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            try {
                const res = await fetch(`/api/v1/pokemon/${pokemon.id}/image`, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: fd,
                });
                const data = await res.json();
                if (res.ok) {
                    const idx = this.pokemon.findIndex(p => p.id === pokemon.id);
                    if (idx >= 0) this.pokemon[idx] = {...this.pokemon[idx], sprite_url: data.sprite_url};
                    // リアクティブ更新のため配列を再代入
                    this.pokemon = [...this.pokemon];
                } else {
                    alert('画像のアップロードに失敗しました');
                }
            } catch (e) {
                alert('画像のアップロードに失敗しました: ' + e.message);
            }
            event.target.value = '';
        },

        async deleteItem(id) {
            if (!confirm('削除しますか？ 関連するカスタムポケモンも影響を受ける可能性があります。')) return;
            await fetch(`/api/v1/pokemon/${id}`, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            });
            this.pokemon = this.pokemon.filter(p => p.id !== id);
        },
    };
}
</script>
@endpush
