@forelse($clients as $client)
<a href="{{ route('admin.mobile_client_details', $client->id) }}" class="col-12 text-decoration-none d-block">
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-2">
        <!-- Header -->
        <div class="card-header bg-primary py-2 px-3 border-0">
            <div class="d-flex justify-content-between w-100 text-white align-items-center">
                <span class="fs-7 fw-semibold">
                    <i class="bi bi-calendar-check me-1"></i>
                    {{ $client->start_date ?? 'N/A' }}
                </span>
                <span class="fs-7 fw-bold dir-ltr">{{ number_format($client->remaining_balance ?? 0, 0) }} $</span>
            </div>
        </div>
        
        <!-- Body -->
        <div class="card-body p-3">
            <div class="d-flex flex-column gap-2">
                <!-- Name & Type -->
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2 flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-fill fs-6 text-primary"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="fw-bold fs-6 text-gray-800 d-block text-truncate">{{ $client->name }}</span>
                        <span class="text-muted fs-8">{{ $client->client_type ?? 'N/A' }}</span>
                    </div>
                    <span class="badge {{ $client->is_active ? 'bg-success' : 'bg-danger' }} fs-9 ms-auto flex-shrink-0">
                        {{ $client->is_active ? 'نشط' : 'متوقف' }}
                    </span>
                </div>

                <!-- Phone -->
                @if($client->phone)
                <div class="d-flex align-items-center text-decoration-none">
                    <i class="bi bi-telephone fs-6 text-gray-500 me-2"></i>
                    <span class="text-gray-800 fs-7 fw-medium dir-ltr">{{ $client->phone }}</span>
                </div>
                @endif

                <!-- SAS Username / Status -->
                <div class="d-flex align-items-center justify-content-between gap-2 mobile-sas-row">
                    <div class="d-flex align-items-center min-w-0">
                        <i class="bi bi-router fs-6 text-gray-500 me-2"></i>
                        <span class="text-gray-700 fs-7 me-1 flex-shrink-0">SAS:</span>
                        @if(!empty($client->sas_username))
                            <span class="fw-bold text-gray-900 fs-7 dir-ltr text-truncate">{{ $client->sas_username }}</span>
                        @else
                            <span class="text-muted fs-7">غير مربوط</span>
                        @endif
                    </div>
                    @if(!empty($client->sas_username))
                        <span class="badge badge-light-warning fs-9 flex-shrink-0 mobile-sas-indicator"
                              data-username="{{ e($client->sas_username) }}">
                            جاري الفحص
                        </span>
                    @else
                        <span class="badge badge-light-secondary fs-9 flex-shrink-0">غير مربوط</span>
                    @endif
                </div>

                <!-- Address -->
                @if($client->address1)
                <div class="d-flex align-items-start">
                    <i class="bi bi-geo-alt fs-6 text-gray-500 me-2 mt-1"></i>
                    <span class="text-gray-700 fs-7 text-truncate-2" style="line-height: 1.4;">{{ $client->address1 }}</span>
                </div>
                @endif

                <!-- Notes -->
                {{-- @if(!empty($client->notes)) --}}
                <div class="d-flex align-items-start">
                    <i class="bi bi-card-text fs-6 text-gray-500 me-2 mt-1"></i>
                    <span class="text-gray-700 fs-7 text-truncate-2" style="line-height: 1.4;">{{ @$client->notes ?? "-" }}</span>
                </div>
                {{-- @endif --}}
            </div>
        </div>
    </div>
</a>
@empty
<div class="col-12 text-center py-5">
    <div class="d-flex flex-column align-items-center justify-content-center">
        <i class="bi bi-clipboard-x fs-1 text-muted mb-2"></i>
        <div class="text-muted fs-6 fw-medium">{{ trans('general.no_data_found') ?? 'لا توجد بيانات' }}</div>
    </div>
</div>
@endforelse

<style>
/* Custom utilities for mobile optimization */
.fs-7 { font-size: 0.875rem !important; }
.fs-8 { font-size: 0.75rem !important; }
.fs-9 { font-size: 0.7rem !important; }
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.min-w-0 { min-width: 0; }
</style>
