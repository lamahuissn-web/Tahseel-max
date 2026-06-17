@extends('dashbord.layouts.master')
@section('content')
<form action="{{ isset($profile) ? route('admin.profiles.update', $profile) : route('admin.profiles.store') }}" method="POST">
    @csrf
    @if(isset($profile)) @method('PUT') @endif

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="card-title mb-0 fw-bold">{{ isset($profile) ? 'تعديل باقة: ' . $profile->name : 'إضافة باقة جديدة' }}</h6>
        </div>
        <div class="card-body">
            @if(!isset($profile))
            <div class="mb-3">
                <label class="form-label">اسم الباقة</label>
                <input type="text" name="name" class="form-control" required placeholder="مثال: 10M, 20M, 50M">
                <small class="text-muted">هذا الاسم سيكون Pool Name في الميكروتك</small>
            </div>
            @endif
            <div class="mb-3">
                <label class="form-label">السرعة</label>
                <input type="text" name="speed" class="form-control" required placeholder="10M/10M" value="{{ $profile->speed ?? '' }}">
                <small class="text-muted">مثال: 10M/10M, 20M/20M, 50M/50M — تغيير السرعة يطبق على كل زبائن الباقة فوراً</small>
            </div>
            <div class="mb-3">
                <label class="form-label">التزامن (Simultaneous-Use)</label>
                <input type="number" name="simultaneous_use" class="form-control" min="1" max="10" value="{{ $profile->simultaneous_use ?? 1 }}">
                <small class="text-muted">عدد الأجهزة المسموح لها بالتزامن</small>
            </div>
            <div class="mb-3">
                <label class="form-label">الخطة المرتبطة (اختياري)</label>
                <select name="subscription_id" class="form-select">
                    <option value="">— بدون خطة —</option>
                    @foreach($subscriptions as $sub)
                    <option value="{{ $sub->id }}" {{ (isset($subscription) && $subscription->id == $sub->id) ? 'selected' : '' }}>
                        {{ $sub->name }} (${{ $sub->price }})
                    </option>
                    @endforeach
                </select>
                <small class="text-muted">ربط الباقة مع خطة اشتراك — السرعة مستقلة عن السعر</small>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('admin.profiles.index') }}" class="btn btn-light">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ</button>
        </div>
    </div>
</form>
@endsection
