@extends("dashbord.layouts.master")
@section("content")
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-{{ isset($nas) ? "edit" : "plus" }} me-2"></i>
                        {{ isset($nas) ? "تعديل جهاز NAS" : "إضافة جهاز NAS جديد" }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($nas) ? route("admin.nas.update", $nas->id) : route("admin.nas.store") }}"
                          method="POST">
                        @csrf
                        @if(isset($nas)) @method("PUT") @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">IP Address <span class="text-danger">*</span></label>
                                <input type="text" name="nasname" class="form-control" required
                                       placeholder="192.168.0.1"
                                       value="{{ old("nasname", $nas->nasname ?? "") }}">
                                @error("nasname") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">الاسم المختصر</label>
                                <input type="text" name="shortname" class="form-control"
                                       placeholder="Core-Router-01"
                                       value="{{ old("shortname", $nas->shortname ?? "") }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shared Secret <span class="text-danger">*</span></label>
                                <input type="text" name="secret" class="form-control" required
                                       placeholder="كلمة سر RADIUS"
                                       value="{{ old("secret", $nas->secret ?? "") }}">
                                <small class="text-muted">يجب أن تطابق secret المحدد في جهاز MikroTik</small>
                                @error("secret") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">النوع</label>
                                <select name="type" class="form-select">
                                    <option value="mikrotik" {{ (old("type", $nas->type ?? "") == "mikrotik") ? "selected" : "" }}>MikroTik</option>
                                    <option value="other" {{ (old("type", $nas->type ?? "") == "other") ? "selected" : "" }}>Other</option>
                                    <option value="cisco" {{ (old("type", $nas->type ?? "") == "cisco") ? "selected" : "" }}>Cisco</option>
                                    <option value="linux" {{ (old("type", $nas->type ?? "") == "linux") ? "selected" : "" }}>Linux</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ports</label>
                                <input type="number" name="ports" class="form-control"
                                       placeholder="0"
                                       value="{{ old("ports", $nas->ports ?? "0") }}">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">الوصف</label>
                                <input type="text" name="description" class="form-control"
                                       placeholder="وصف الجهاز (اختياري)"
                                       value="{{ old("description", $nas->description ?? "") }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route("admin.nas.index") }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> رجوع
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                {{ isset($nas) ? "تحديث" : "إضافة" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
