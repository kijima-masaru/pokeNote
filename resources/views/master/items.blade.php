@extends('layouts.app')
@section('title', '持ち物管理')
@section('content')
<div x-data="masterItems()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-bag"></i> 持ち物管理</h4>
        <button class="btn btn-success btn-sm" @click="openCreate()">
            <i class="bi bi-plus-circle"></i> 新規登録
        </button>
    </div>

    <div x-show="showForm" class="card border-success shadow-sm mb-4" x-cloak>
        <div class="card-header bg-success text-white d-flex justify-content-between">
            <strong x-text="editId ? '持ち物を編集' : '持ち物を登録'"></strong>
            <button class="btn-close btn-close-white btn-sm" @click="showForm=false"></button>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">日本語名 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" x-model="form.name_ja">
                </div>
                <div class="col-md-3">
                    <label class="form-label">英語名 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" x-model="form.name_en">
                </div>
                <div class="col-md-2">
                    <label class="form-label">カテゴリ</label>
                    <select class="form-select form-select-sm" x-model="form.category">
                        <option value="">-- 選択 --</option>
                        <option value="boost">boost（強化）</option>
                        <option value="berry">berry（きのみ）</option>
                        <option value="recovery">recovery（回復）</option>
                        <option value="other">other（その他）</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">説明</label>
                    <input type="text" class="form-control form-control-sm" x-model="form.description">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-success btn-sm" @click="save()">保存</button>
                    <button class="btn btn-outline-secondary btn-sm" @click="showForm=false">キャンセル</button>
                    <span x-show="errorMsg" class="text-danger small" x-text="errorMsg"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr><th style="width:50px">ID</th><th style="width:36px"></th><th>日本語名</th><th>英語名</th><th>カテゴリ</th><th>説明</th><th style="width:120px"></th></tr>
                </thead>
                <tbody>
                    <template x-for="item in items" :key="item.id">
                        <tr>
                            <td class="text-muted" x-text="item.id"></td>
                            <td>
                                <img x-show="item.image_url" :src="item.image_url" style="width:28px;height:28px;object-fit:contain">
                                <i x-show="!item.image_url" class="bi bi-bag text-muted" style="font-size:1.1rem"></i>
                            </td>
                            <td class="fw-semibold" x-text="item.name_ja"></td>
                            <td class="text-muted" x-text="item.name_en"></td>
                            <td><span class="badge bg-light text-dark border" x-text="item.category||'-'"></span></td>
                            <td style="font-size:.8rem" x-text="item.description||'-'"></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:1px 6px"
                                            @click="openEdit(item)">編集</button>
                                    <label class="btn btn-xs btn-outline-primary" style="font-size:.75rem;padding:1px 6px;cursor:pointer" title="画像アップロード">
                                        <i class="bi bi-image"></i>
                                        <input type="file" accept="image/*" class="d-none"
                                               @change="uploadImage(item, $event)">
                                    </label>
                                    <button class="btn btn-xs btn-outline-danger" style="font-size:.75rem;padding:1px 6px"
                                            @click="deleteItem(item.id)">削除</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-2 text-muted small" x-text="items.length + '件'"></div>
</div>
@endsection
@push('scripts')
<script>
function masterItems() {
    return {
        items: @json($items->items()),
        showForm: false, editId: null, saving: false,
        form: {name_ja:'', name_en:'', category:'', description:''},
        errors: {},

        async init() {},

        openCreate() {
            this.editId = null;
            this.form = {name_ja:'', name_en:'', category:'', description:''};
            this.errors = {};
            this.showForm = true;
        },

        openEdit(item) {
            this.editId = item.id;
            this.form = {name_ja: item.name_ja, name_en: item.name_en, category: item.category||'', description: item.description||''};
            this.errors = {};
            this.showForm = true;
        },

        async save() {
            this.errors = {};
            this.saving = true;
            const url = this.editId ? `/api/v1/items/${this.editId}` : '/api/v1/items';
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
                const idx = this.items.findIndex(i => i.id === this.editId);
                if (idx >= 0) this.items[idx] = data;
            } else {
                this.items.push(data);
            }
            this.showForm = false;
        },

        async uploadImage(item, event) {
            const file = event.target.files[0];
            if (!file) return;
            const fd = new FormData();
            fd.append('image', file);
            try {
                const res = await fetch(`/api/v1/items/${item.id}/image`, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: fd,
                });
                const data = await res.json();
                if (res.ok) {
                    const idx = this.items.findIndex(i => i.id === item.id);
                    if (idx >= 0) this.items[idx] = {...this.items[idx], image_url: data.image_url};
                    this.items = [...this.items];
                } else {
                    alert('画像のアップロードに失敗しました');
                }
            } catch (e) {
                alert('画像のアップロードに失敗しました: ' + e.message);
            }
            event.target.value = '';
        },

        async deleteItem(id) {
            if (!confirm('削除しますか？')) return;
            await fetch(`/api/v1/items/${id}`, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            });
            this.items = this.items.filter(i => i.id !== id);
        },
    };
}
</script>
@endpush
