@extends("dashbord.layouts.master")

@section("content")
<style>
    .nas-form-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .nas-form-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        padding: 18px 24px;
        border: none;
    }
    .nas-form-header h5 {
        color: #f1f5f9;
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }
    .nas-form-header h5 i {
        color: #3b82f6;
        font-size: 1.3rem;
    }
    .nas-form-body {
        padding: 28px 24px;
    }
    .nas-form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #374151;
        margin-bottom: 6px;
    }
    .nas-form-label .required {
        color: #ef4444;
    }
    .nas-form-control, .nas-form-select {
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    .nas-form-control:focus, .nas-form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
    }
    .nas-form-hint {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 4px;
    }
    .nas-form-footer {
        padding: 16px 24px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
    }
    .btn-nas-back {
        background: #f3f4f6;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 9px 18px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #374151;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-nas-back:hover {
        background: #e5e7eb;
        color: #1f2937;
    }
    .btn-nas-submit {
        background: #3b82f6;
        border: none;
        border-radius: 10px;
        padding: 9px 24px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #fff;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-nas-submit:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59,130,246,0.35);
    }
    .required-star {
        color: #ef4444;
    }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-8">
            <div class="card nas-form-card">
                <div class="card-header nas-form-header">
                    <h5>
                        <i class="fas fa-{{ isset($nas) ? "edit" : "plus-circle" }}"></i>
                        {{ isset($nas) ? "تعديل جهاز NAS" : "إضافة جهاز NAS جديد" }}
                    </h5>
                </div>

                <form action="{{ isset($nas) ? route("admin.nas.update", $nas->id) : route("admin.nas.store") }}"
                      method="POST">
                    @csrf
                    @if(isset($nas)) @method("PUT") @endif

                    <div class="card-body nas-form-body">
                        <div class="row g-4">
                            {{-- IP Address --}}
                            <div class="col-md-6">
                                <label class="nas-form-label">
                                    <i class="fas fa-globe text-muted me-1" style="font-size:0.75rem;"></i>
                                    IP Address <span class="required-star">*</span>
                                </label>
                                <input type="text" name="nasname"
                                       class="form-control nas-form-control @error("nasname") is-invalid @enderror"
                                       dir="ltr" placeholder="192.168.0.1"
                                       value="{{ old("nasname", $nas->nasname ?? "") }}">
                                <div class="nas-form-hint">عنوان IP الخاص بالراوتر</div>
                                @error("nasname") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            {{-- Short Name --}}
                            <div class="col-md-6">
                                <label class="nas-form-label">
                                    <i class="fas fa-tag text-muted me-1" style="font-size:0.75rem;"></i>
                                    الاسم المختصر
                                </label>
                                <input type="text" name="shortname"
                                       class="form-control nas-form-control"
                                       placeholder="Core-Router-01"
                                       value="{{ old("shortname", $nas->shortname ?? "") }}">
                                <div class="nas-form-hint">اسم سهل التعرف عليه (اختياري)</div>
                            </div>

                            {{-- Shared Secret --}}
                            <div class="col-md-6">
                                <label class="nas-form-label">
                                    <i class="fas fa-key text-muted me-1" style="font-size:0.75rem;"></i>
                                    Shared Secret <span class="required-star">*</span>
                                </label>
                                <input type="text" name="secret"
                                       class="form-control nas-form-control @error("secret") is-invalid @enderror"
                                       dir="ltr" placeholder="كلمة سر RADIUS"
                                       value="{{ old("secret", $nas->secret ?? "") }}">
                                <div class="nas-form-hint">يجب أن تطابق secret المحدد في جهاز MikroTik</div>
                                @error("secret") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            {{-- Type --}}
                            <div class="col-md-6">
                                <label class="nas-form-label">
                                    <i class="fas fa-microchip text-muted me-1" style="font-size:0.75rem;"></i>
                                    النوع
                                </label>
                                <select name="type" class="form-select nas-form-select">
                                    <option value="mikrotik" {{ (old("type", $nas->type ?? "") == "mikrotik") ? "selected" : "" }}>🔷 MikroTik</option>
                                    <option value="cisco" {{ (old("type", $nas->type ?? "") == "cisco") ? "selected" : "" }}>🔶 Cisco</option>
                                    <option value="linux" {{ (old("type", $nas->type ?? "") == "linux") ? "selected" : "" }}>🐧 Linux</option>
                                    <option value="other" {{ (old("type", $nas->type ?? "") == "other") ? "selected" : "" }}>📡 Other</option>
                                </select>
                            </div>

                            {{-- Ports --}}
                            <div class="col-md-6">
                                <label class="nas-form-label">
                                    <i class="fas fa-plug text-muted me-1" style="font-size:0.75rem;"></i>
                                    Ports
                                </label>
                                <input type="number" name="ports"
                                       class="form-control nas-form-control"
                                       placeholder="0" min="0"
                                       value="{{ old("ports", $nas->ports ?? "0") }}">
                                <div class="nas-form-hint">منفذ RADIUS (الافتراضي 1812)</div>
                            </div>

                            {{-- Description --}}
                            <div class="col-md-12">
                                <label class="nas-form-label">
                                    <i class="fas fa-align-left text-muted me-1" style="font-size:0.75rem;"></i>
                                    الوصف
                                </label>
                                <input type="text" name="description"
                                       class="form-control nas-form-control"
                                       placeholder="وصف الجهاز (اختياري)"
                                       value="{{ old("description", $nas->description ?? "") }}">
                                <div class="nas-form-hint">معلومات إضافية عن الجهاز وموقعه</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer nas-form-footer">
                        <a href="{{ route("admin.nas.index") }}" class="btn-nas-back">
                            <i class="fas fa-arrow-right"></i> رجوع
                        </a>
                        <button type="submit" class="btn-nas-submit">
                            <i class="fas fa-save"></i>
                            {{ isset($nas) ? "تحديث البيانات" : "إضافة الجهاز" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
