@extends('layouts.app')
@section('title', '特性管理')
@section('content')
<div x-data="masterAbilities()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-stars"></i> 特性管理</h4>
        <button class="btn btn-success btn-sm" @click="openCreate()">
            <i class="bi bi-plus-circle"></i> 新規登録
        </button>
    </div>

    <!-- 新規登録・編集モーダル -->
    <div x-show="showForm" class="card border-success shadow-sm mb-4" x-cloak>
        <div class="card-header bg-success text-white d-flex justify-content-between">
            <strong x-text="editId ? '特性を編集' : '特性を登録'"></strong>
            <button class="btn-close btn-close-white btn-sm" @click="showForm=false"></button>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">日本語名 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" x-model="form.name_ja" placeholder="もうか"
                           :class="errors.name_ja ? 'is-invalid' : ''">
                    <div class="invalid-feedback" x-text="errors.name_ja"></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">英語名 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" x-model="form.name_en" placeholder="blaze"
                           :class="errors.name_en ? 'is-invalid' : ''">
                    <div class="invalid-feedback" x-text="errors.name_en"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">説明</label>
                    <input type="text" class="form-control form-control-sm" x-model="form.description" placeholder="特性の説明">
                </div>
                <div class="col-12 d-flex gap-2 align-items-center">
                    <button class="btn btn-success btn-sm" @click="save()" :disabled="saving">
                        <span x-show="saving" class="spinner-border spinner-border-sm me-1"></span>
                        保存
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" @click="showForm=false">キャンセル</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 一覧テーブル -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr><th style="width:50px">ID</th><th>日本語名</th><th>英語名</th><th>説明</th><th style="width:100px"></th></tr>
                </thead>
                <tbody>
                    <template x-for="ab in abilities" :key="ab.id">
                        <tr>
                            <td class="text-muted" x-text="ab.id"></td>
                            <td class="fw-semibold" x-text="ab.name_ja"></td>
                            <td class="text-muted" x-text="ab.name_en"></td>
                            <td style="font-size:.8rem" x-text="ab.description||'-'"></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:1px 6px"
                                            @click="openEdit(ab)">編集</button>
                                    <button class="btn btn-xs btn-outline-danger" style="font-size:.75rem;padding:1px 6px"
                                            @click="deleteItem(ab.id)">削除</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-2 text-muted small" x-text="abilities.length + '件'"></div>
</div>
@endsection
@push('scripts')
<script>
function masterAbilities() {
    return {
        abilities: @json($abilities->items()),
        showForm: false, editId: null, saving: false,
        form: {name_ja:'', name_en:'', description:''},
        errors: {},

        async init() {},

        openCreate() {
            this.editId = null;
            this.form = {name_ja:'', name_en:'', description:''};
            this.errors = {};
            this.showForm = true;
        },

        openEdit(ab) {
            this.editId = ab.id;
            this.form = {name_ja: ab.name_ja, name_en: ab.name_en, description: ab.description || ''};
            this.errors = {};
            this.showForm = true;
        },

        async save() {
            this.errors = {};
            this.saving = true;
            const url = this.editId ? `/api/v1/abilities/${this.editId}` : '/api/v1/abilities';
            const method = this.editId ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify(this.form),
            });
            const data = await res.json();
            this.saving = false;
            if (!res.ok) {
                // フィールドごとのエラーに変換
                this.errors = Object.fromEntries(
                    Object.entries(data.errors || {}).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                );
                return;
            }
            if (this.editId) {
                const idx = this.abilities.findIndex(a => a.id === this.editId);
                if (idx >= 0) this.abilities[idx] = data;
            } else {
                this.abilities.push(data);
            }
            this.showForm = false;
        },

        async deleteItem(id) {
            if (!confirm('削除しますか？')) return;
            await fetch(`/api/v1/abilities/${id}`, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            });
            this.abilities = this.abilities.filter(a => a.id !== id);
        },
    };
}
</script>
@endpush
