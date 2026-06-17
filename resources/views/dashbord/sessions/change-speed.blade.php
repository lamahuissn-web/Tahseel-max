@extends("dashbord.layouts.master")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-speedometer2 me-2"></i>
                        تغيير سرعة <code>{{ $username }}</code>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        المستخدم متصل منذ {{ \Carbon\Carbon::parse($session->acctstarttime)->diffForHumans(null, true) }}
                        @if($session->framedipaddress)
                            <br>IP: <code>{{ $session->framedipaddress }}</code>
                        @endif
                    </div>

                    @if($currentProfile)
                    <div class="alert alert-secondary mb-3">
                        <i class="bi bi-lightning me-1"></i>
                        الباقة الحالية: <strong>{{ $currentProfile }}</strong>
                    </div>
                    @endif

                    <form action="{{ route('admin.sessions.change-speed.post', $username) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">اختر باقة السرعة</label>
                            <div class="d-flex flex-column gap-2">
                                @forelse($profiles as $profile)
                                <div class="form-check p-3 border rounded-3 {{ $currentProfile == $profile->name ? 'border-primary bg-primary bg-opacity-10' : '' }}"
                                     onclick="this.querySelector('input[type=radio]').click()"
                                     style="cursor:pointer; transition: all 0.15s ease;"
                                     onmouseover="this.style.borderColor='#3b82f6'"
                                     onmouseout="this.style.borderColor=''">
                                    <input class="form-check-input" type="radio" name="profile"
                                           value="{{ $profile->name }}"
                                           id="profile_{{ $profile->id }}"
                                           {{ $currentProfile == $profile->name ? 'checked' : '' }}
                                           required>
                                    <label class="form-check-label w-100" for="profile_{{ $profile->id }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="fw-bold">{{ $profile->name }}</span>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-arrow-down-up"></i>
                                                    {{ $profile->speed ?? $profile->name }}
                                                </small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>
                                    <p>لا توجد باقات سرعة متاحة</p>
                                    <a href="{{ route('admin.profiles.create') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> إضافة باقة
                                    </a>
                                </div>
                                @endforelse
                            </div>
                            @error('profile')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.sessions.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-right me-1"></i> رجوع
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-lightning me-1"></i> تطبيق الباقة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
