@extends("dashbord.layouts.master")
@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-network-wired me-2"></i>
                        أجهزة NAS (الرواتر)
                    </h5>
                    <a href="{{ route("admin.nas.create") }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> إضافة جهاز
                    </a>
                </div>
                <div class="card-body">
                    @if(session("success"))
                        <div class="alert alert-success">{{ session("success") }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>IP Address</th>
                                    <th>الاسم المختصر</th>
                                    <th>النوع</th>
                                    <th>Secret</th>
                                    <th>الوصف</th>
                                    <th>تاريخ الإضافة</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nasDevices as $nas)
                                    <tr>
                                        <td>{{ $nas->id }}</td>
                                        <td><code>{{ $nas->nasname }}</code></td>
                                        <td>{{ $nas->shortname }}</td>
                                        <td>{{ $nas->type }}</td>
                                        <td><code>{{ substr($nas->secret, 0, 6) }}***</code></td>
                                        <td>{{ $nas->description }}</td>
                                        <td>{{ $nas->created_at ?? "—" }}</td>
                                        <td class="d-flex gap-1">
                                            <a href="{{ route("admin.nas.edit", $nas->id) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route("admin.nas.destroy", $nas->id) }}" method="POST"
                                                  onsubmit="return confirm("هل أنت متأكد من حذف جهاز NAS؟")">
                                                @csrf
                                                @method("DELETE")
                                                <button class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-router fa-2x mb-2"></i><br>
                                            لا توجد أجهزة NAS مضافة بعد.
                                            <br>
                                            <a href="{{ route("admin.nas.create") }}" class="btn btn-primary btn-sm mt-2">
                                                أضف أول جهاز
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
