@extends("dashbord.layouts.master")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>
                        تغيير سرعة {{ $username }}
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

                    <form action="{{ route('admin.sessions.change-speed.post', $username) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">السرعة الجديدة</label>
                            <div class="input-group">
                                <input type="text" name="speed" class="form-control @error('speed') is-invalid @enderror"
                                    value="{{ old('speed', '10M/10M') }}" placeholder="مثال: 20M/20M" required>
                                <span class="input-group-text">Mbps</span>
                                @error('speed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">الصيغة: <code>تحميل/رفع</code> مثل <code>10M/10M</code> أو <code>4M/2M</code></div>
                        </div>

                        {{-- Quick speed buttons --}}
                        <div class="mb-3">
                            <label class="form-label">سرعات سريعة</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(['2M/2M', '4M/2M', '10M/10M', '20M/20M', '50M/50M', '100M/100M'] as $s)
                                    <button type="button" class="btn btn-outline-secondary btn-sm speed-preset"
                                        data-speed="{{ $s }}">{{ $s }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.sessions.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-right me-1"></i> رجوع
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-lightning me-1"></i> تغيير السرعة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
document.querySelectorAll('.speed-preset').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelector('input[name="speed"]').value = this.dataset.speed;
        document.querySelectorAll('.speed-preset').forEach(b => b.classList.remove('btn-primary'));
        this.classList.add('btn-primary');
        this.classList.remove('btn-outline-secondary');
    });
});
</script>
@endsection
