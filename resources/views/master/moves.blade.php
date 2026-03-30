@extends('layouts.app')
@section('title', 'わざ管理')
@section('content')
<div x-data="masterMoves()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-lightning-charge"></i> わざ管理</h4>
        <button class="btn btn-success btn-sm" @click="openCreate()">
            <i class="bi bi-plus-circle"></i> 新規登録
        </button>
    </div>

    <div x-show="showForm" class="card border-success shadow-sm mb-4" x-cloak>
        <div class="card-header bg-success text-white d-flex justify-content-between">
            <strong x-text="editId ? 'わざを編集' : 'わざを登録'"></strong>
            <button class="btn-close btn-close-white btn-sm" @click="showForm=false"></button>
        </div>
        <div class="card-body">
            <div class="row g-2">
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
                <div class="col-md-2">
                    <label class="form-label">タイプ <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" x-model="form.type"
                            :class="errors.type ? 'is-invalid' : ''">
                        <option value="">-- 選択 --</option>
                        @foreach($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" x-text="errors.type"></div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">分類 <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" x-model="form.category"
                            :class="errors.category ? 'is-invalid' : ''">
                        <option value="">-- 選択 --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" x-text="errors.category"></div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">威力</label>
                    <input type="number" class="form-control form-control-sm" x-model.number="form.power" min="1" max="999">
                </div>
                <div class="col-md-1">
                    <label class="form-label">命中</label>
                    <input type="number" class="form-control form-control-sm" x-model.number="form.accuracy" min="1" max="100">
                </div>
                <div class="col-md-1">
                    <label class="form-label">PP <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm" x-model.number="form.pp" min="1" max="64"
                           :class="errors.pp ? 'is-invalid' : ''">
                    <div class="invalid-feedback" x-text="errors.pp"></div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">優先度</label>
                    <input type="number" class="form-control form-control-sm" x-model.number="form.priority" min="-7" max="5">
                </div>
                <div class="col-12 d-flex gap-3 align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" x-model="form.makes_contact" id="contactCheck">
                        <label class="form-check-label" for="contactCheck" style="font-size:.85rem">直接攻撃</label>
                    </div>
                    <button class="btn btn-success btn-sm" @click="save()" :disabled="saving">
                        <span x-show="saving" class="spinner-border spinner-border-sm me-1"></span>
                        保存
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" @click="showForm=false">キャンセル</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 絞り込み -->
    <div class="mb-2 d-flex gap-2">
        <input type="text" class="form-control form-control-sm" x-model="filterName" placeholder="技名で絞り込み" style="max-width:200px">
        <select class="form-select form-select-sm" x-model="filterType" style="max-width:120px">
            <option value="">全タイプ</option>
            @foreach($types as $type)
                <option value="{{ $type->value }}">{{ $type->label() }}</option>
            @endforeach
        </select>
        <select class="form-select form-select-sm" x-model="filterCategory" style="max-width:100px">
            <option value="">全分類</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr><th>ID</th><th>技名</th><th>タイプ</th><th>分類</th><th>威力</th><th>命中</th><th>PP</th><th>優先</th><th></th></tr>
                </thead>
                <tbody>
                    <template x-for="move in filteredMoves" :key="move.id">
                        <tr>
                            <td class="text-muted" x-text="move.id"></td>
                            <td class="fw-semibold" x-text="move.name_ja"></td>
                            <td><span class="type-badge" :class="'type-'+move.type" x-text="typeLabel(move.type)" style="font-size:.7rem"></span></td>
                            <td>
                                <span class="badge"
                                      :class="move.category==='physical'?'bg-danger':move.category==='special'?'bg-primary':'bg-secondary'"
                                      x-text="move.category==='physical'?'物理':move.category==='special'?'特殊':'変化'"
                                      style="font-size:.7rem"></span>
                            </td>
                            <td x-text="move.power||'-'"></td>
                            <td x-text="move.accuracy||'-'"></td>
                            <td x-text="move.pp"></td>
                            <td x-text="move.priority||0"></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:1px 6px"
                                            @click="openEdit(move)">編集</button>
                                    <button class="btn btn-xs btn-outline-danger" style="font-size:.75rem;padding:1px 6px"
                                            @click="deleteItem(move.id)">削除</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-2 text-muted small" x-text="filteredMoves.length + '件'"></div>
</div>
@endsection
@push('scripts')
<script>
const TYPE_LABELS = {
    normal:'ノーマル',fire:'ほのお',water:'みず',electric:'でんき',grass:'くさ',ice:'こおり',
    fighting:'かくとう',poison:'どく',ground:'じめん',flying:'ひこう',psychic:'エスパー',
    bug:'むし',rock:'いわ',ghost:'ゴースト',dragon:'ドラゴン',dark:'あく',steel:'はがね',fairy:'フェアリー'
};
function masterMoves() {
    return {
        moves: @json($moves->items()),
        showForm: false, editId: null, saving: false,
        filterName: '', filterType: '', filterCategory: '',
        form: {name_ja:'',name_en:'',type:'',category:'',power:null,accuracy:null,pp:10,priority:0,makes_contact:false},
        errors: {},

        async init() {},

        typeLabel(t) { return TYPE_LABELS[t] || t; },

        get filteredMoves() {
            return this.moves.filter(m => {
                if (this.filterName && !m.name_ja.includes(this.filterName)) return false;
                if (this.filterType && m.type !== this.filterType) return false;
                if (this.filterCategory && m.category !== this.filterCategory) return false;
                return true;
            });
        },

        openCreate() {
            this.editId = null;
            this.form = {name_ja:'',name_en:'',type:'',category:'',power:null,accuracy:null,pp:10,priority:0,makes_contact:false};
            this.errors = {};
            this.showForm = true;
        },

        openEdit(move) {
            this.editId = move.id;
            this.form = {
                name_ja: move.name_ja, name_en: move.name_en, type: move.type,
                category: move.category, power: move.power, accuracy: move.accuracy,
                pp: move.pp, priority: move.priority||0, makes_contact: move.makes_contact||false
            };
            this.errors = {};
            this.showForm = true;
        },

        async save() {
            this.errors = {};
            this.saving = true;
            const url = this.editId ? `/api/v1/moves/${this.editId}` : '/api/v1/moves';
            const method = this.editId ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify(this.form),
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
                const idx = this.moves.findIndex(m => m.id === this.editId);
                if (idx >= 0) this.moves[idx] = data;
            } else {
                this.moves.push(data);
            }
            this.showForm = false;
        },

        async deleteItem(id) {
            if (!confirm('削除しますか？')) return;
            await fetch(`/api/v1/moves/${id}`, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            });
            this.moves = this.moves.filter(m => m.id !== id);
        },
    };
}
</script>
@endpush
