@extends("dashbord.layouts.master")

@section("content")
<style>
    .nas-form-card { border: none; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden; }
    .nas-form-header { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 18px 24px; border: none; }
    .nas-form-header h5 { color: #f1f5f9; font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; margin: 0; }
    .nas-form-header h5 i { color: #3b82f6; font-size: 1.3rem; }
    .nas-form-body { padding: 28px 24px; }
    .nas-form-label { font-weight: 600; font-size: 0.85rem; color: #374151; margin-bottom: 6px; }
    .nas-form-label .required { color: #ef4444; }
    .nas-form-control, .nas-form-select { border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 10px 14px; font-size: 0.9rem; transition: all 0.2s ease; }
    .nas-form-control:focus, .nas-form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    .nas-form-hint { font-size: 0.75rem; color: #9ca3af; margin-top: 4px; }
    .nas-form-footer { padding: 16px 24px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; }
    .btn-nas-back { background: #f3f4f6; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 9px 18px; font-weight: 600; font-size: 0.85rem; color: #374151; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-nas-back:hover { background: #e5e7eb; color: #1f2937; }
    .btn-nas-submit { background: #3b82f6; border: none; border-radius: 10px; padding: 9px 24px; font-weight: 600; font-size: 0.85rem; color: #fff; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-nas-submit:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(59,130,246,0.35); }
    .section-title { font-size: 1rem; font-weight: 700; color: #1e293b; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 20px; margin-top: 10px; }
    .toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #d1d5db; transition: 0.3s; border-radius: 24px; }
    .toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: 0.3s; border-radius: 50%; }
    .toggle-switch input:checked + .toggle-slider { background-color: #3b82f6; }
    .toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-11 col-lg-10">
            <div class="card nas-form-card">
                <div class="card-header nas-form-header">
                    <h5><i class="fas fa-{{ isset($nas) ? "edit" : "plus-circle" }}"></i> {{ isset($nas) ? "تعديل جهاز NAS" : "إضافة جهاز NAS جديد" }}</h5>
                </div>

                <form action="{{ isset($nas) ? route("admin.nas.update", $nas->id) : route("admin.nas.store") }}" method="POST">
                    @csrf
                    @if(isset($nas)) @method("PUT") @endif

                    <div class="card-body nas-form-body">
                        {{-- Section 1: Basic Information --}}
                        <h6 class="section-title"><i class="bi bi-info-circle text-primary me-1"></i> Basic Information</h6>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="nas-form-label">Name <span class="required">*</span></label>
                                <input type="text" name="shortname" class="form-control nas-form-control @error("shortname") is-invalid @enderror" placeholder="اسم الجهاز" value="{{ old("shortname", $nas->shortname ?? "") }}">
                                @error("shortname") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">IP Address <span class="required">*</span></label>
                                <input type="text" name="nasname" class="form-control nas-form-control @error("nasname") is-invalid @enderror" dir="ltr" placeholder="192.168.0.1" value="{{ old("nasname", $nas->nasname ?? "") }}">
                                @error("nasname") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">Type <span class="required">*</span></label>
                                <select name="type" class="form-select nas-form-select">
                                    <option value="mikrotik" {{ (old("type", $nas->type ?? "") == "mikrotik") ? "selected" : "" }}>Mikrotik</option>
                                    <option value="cisco" {{ (old("type", $nas->type ?? "") == "cisco") ? "selected" : "" }}>Cisco</option>
                                    <option value="linux" {{ (old("type", $nas->type ?? "") == "linux") ? "selected" : "" }}>Linux</option>
                                    <option value="other" {{ (old("type", $nas->type ?? "") == "other") ? "selected" : "" }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">Incoming (COA) Port</label>
                                <input type="number" name="coa_port" class="form-control nas-form-control" dir="ltr" value="{{ old("coa_port", $nas->coa_port ?? "3799") }}">
                                <div class="nas-form-hint">منفذ CoA (UDP 3799 لميكروتك)</div>
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">Shared Secret <span class="required">*</span></label>
                                <input type="text" name="secret" class="form-control nas-form-control @error("secret") is-invalid @enderror" dir="ltr" value="{{ old("secret", $nas->secret ?? "") }}">
                                @error("secret") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">Version</label>
                                <select name="mikrotik_version" class="form-select nas-form-select">
                                    <option value="6.36+" {{ (old("mikrotik_version", $nas->mikrotik_version ?? "") == "6.36+") ? "selected" : "" }}>Mikrotik 6.36 or higher</option>
                                    <option value="7.x" {{ (old("mikrotik_version", $nas->mikrotik_version ?? "") == "7.x") ? "selected" : "" }}>Mikrotik 7.x</option>
                                    <option value="6.x" {{ (old("mikrotik_version", $nas->mikrotik_version ?? "") == "6.x") ? "selected" : "" }}>Mikrotik 6.x</option>
                                    <option value="other" {{ (old("mikrotik_version", $nas->mikrotik_version ?? "") == "other") ? "selected" : "" }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">Site</label>
                                <select name="site" class="form-select nas-form-select">
                                    <option value="">Any</option>
                                    <option value="main" {{ (old("site", $nas->site ?? "") == "main") ? "selected" : "" }}>Main</option>
                                    <option value="branch" {{ (old("site", $nas->site ?? "") == "branch") ? "selected" : "" }}>Branch</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">HTTP Port</label>
                                <input type="number" name="http_port" class="form-control nas-form-control" dir="ltr" value="{{ old("http_port", $nas->http_port ?? "80") }}">
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">IP Accounting</label>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="ip_accounting" value="1" {{ old("ip_accounting", $nas->ip_accounting ?? false) ? "checked" : "" }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">Enabled</label>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="enabled" value="1" {{ old("enabled", $nas->enabled ?? true) ? "checked" : "" }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">Monitor (ping)</label>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="ping_monitor" value="1" {{ old("ping_monitor", $nas->ping_monitor ?? true) ? "checked" : "" }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">Pool Name (optional)</label>
                                <input type="text" name="pool_name" class="form-control nas-form-control" value="{{ old("pool_name", $nas->pool_name ?? "") }}">
                            </div>
                            <div class="col-md-6">
                                <label class="nas-form-label">SNMP Community</label>
                                <input type="text" name="community" class="form-control nas-form-control" dir="ltr" value="{{ old("community", $nas->community ?? "") }}">
                            </div>
                            <div class="col-12">
                                <label class="nas-form-label">Description</label>
                                <input type="text" name="description" class="form-control nas-form-control" placeholder="وصف الجهاز (اختياري)" value="{{ old("description", $nas->description ?? "") }}">
                            </div>
                        </div>

                        {{-- Section 2: Mikrotik Credentials --}}
                        <h6 class="section-title mt-4"><i class="bi bi-shield-lock text-warning me-1"></i> Mikrotik credentials (required for pining users)</h6>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="nas-form-label">SSH Username</label>
                                <input type="text" name="ssh_username" class="form-control nas-form-control" dir="ltr" value="{{ old("ssh_username", $nas->ssh_username ?? "") }}">
                            </div>
                            <div class="col-md-4">
                                <label class="nas-form-label">SSH Password</label>
                                <input type="password" name="ssh_password" class="form-control nas-form-control" dir="ltr" value="{{ old("ssh_password", $nas->ssh_password ?? "") }}">
                            </div>
                            <div class="col-md-4">
                                <label class="nas-form-label">SSH Port</label>
                                <input type="number" name="ssh_port" class="form-control nas-form-control" value="{{ old("ssh_port", $nas->ssh_port ?? "22") }}">
                            </div>
                        </div>
                    </div>

                    <div class="card-footer nas-form-footer">
                        <a href="{{ route("admin.nas.index") }}" class="btn-nas-back"><i class="fas fa-arrow-right"></i> Dismiss</a>
                        <button type="submit" class="btn-nas-submit"><i class="fas fa-save"></i> Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
