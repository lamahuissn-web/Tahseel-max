@extends('dashbord.layouts.master')

@section('title', 'إدارة دفعات واتساب')

@section('content')
@include('dashbord.whatsapp._partials.tab-nav')
<div id="kt_app_content_container" class="app-container container-xxxl" dir="rtl">
    <style>
        .queue-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem}.queue-card{border:1px solid #e8e8e8;border-radius:12px;padding:1rem;background:#fff}.batch-card{border-inline-start:4px solid #3699ff}.counts{display:flex;flex-wrap:wrap;gap:.45rem}.batch-actions{display:flex;flex-wrap:wrap;gap:.5rem}@media(max-width:767px){.queue-grid{grid-template-columns:repeat(2,1fr)}.batch-head{align-items:flex-start!important;flex-direction:column}.batch-actions .btn{flex:1}.table-responsive{font-size:.8rem}}
    </style>

    <div class="queue-grid mb-6">
        <div class="queue-card"><strong>{{ $queuePaused ? 'متوقف مؤقتاً' : 'يعمل' }}</strong><div class="text-muted">حالة التوصيل العامة</div>@can('control_whatsapp_queue')<button id="togglePause" class="btn btn-sm {{ $queuePaused ? 'btn-success' : 'btn-warning' }} mt-2">{{ $queuePaused ? 'استئناف الطابور' : 'إيقاف الطابور مؤقتاً' }}</button>@endcan</div>
        <div class="queue-card"><strong class="fs-2">{{ $pending }}</strong><div class="text-muted">رسائل بانتظار الإرسال</div></div>
        <div class="queue-card"><strong class="fs-2">{{ $sending }}</strong><div class="text-muted">جارٍ إرسالها</div></div>
        <div class="queue-card"><strong class="fs-2">{{ $failedToday }}</strong><div class="text-muted">إخفاقات اليوم</div></div>
    </div>

    <div class="card mb-6"><div class="card-body"><form method="GET" class="row g-3">
        <div class="col-md-3"><label class="form-label">حالة الرسالة</label><select class="form-select" name="status"><option value="">الكل</option>@foreach(['pending'=>'قيد الانتظار','sending'=>'جارٍ الإرسال','sent'=>'مرسلة','failed'=>'فاشلة','cancelled'=>'ملغاة'] as $value=>$label)<option value="{{ $value }}" @selected($statusFilter===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">المصدر</label><select class="form-select" name="source">@foreach($sourceOptions as $value=>$label)<option value="{{ $value }}" @selected($sourceFilter===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">من تاريخ</label><input class="form-control" type="date" name="date_from" value="{{ $dateFrom }}"></div>
        <div class="col-md-2"><label class="form-label">إلى تاريخ</label><input class="form-control" type="date" name="date_to" value="{{ $dateTo }}"></div>
        <div class="col-md-2 d-flex align-items-end gap-2"><button class="btn btn-primary">تطبيق</button><a class="btn btn-light" href="{{ route('admin.whatsapp.queue') }}">مسح</a></div>
    </form></div></div>

    <h3 class="mb-4">الدفعات النشطة والحديثة</h3>
    @forelse($batches as $batch)
        @php $done=$batch->sent_count+$batch->failed_count+$batch->cancelled_count; $progress=$batch->total_count ? round($done*100/$batch->total_count) : 0; @endphp
        <div class="queue-card batch-card mb-4">
            <div class="d-flex justify-content-between align-items-center batch-head gap-3"><div><h4>{{ $batch->title }}</h4><span class="badge badge-light-primary">{{ $batch->source }}</span> <span class="badge badge-light">{{ $batch->status }}</span><div class="text-muted fs-7 mt-2">أنشأها: {{ $batch->creator?->name ?? 'النظام' }} · {{ $batch->created_at->format('Y-m-d H:i') }}</div></div><div class="batch-actions">
                <a href="{{ route('admin.whatsapp.queue', array_merge(request()->except(['page','batch']), ['batch'=>$batch->id])) }}#messages" class="btn btn-sm btn-light-primary">التفاصيل</a>
                @can('control_whatsapp_queue')
                @if($batch->pending_count>0 && !in_array($batch->status,['cancelling','cancelled']))<button class="btn btn-sm btn-danger batch-cancel" data-preview="{{ route('admin.whatsapp.queue.batches.cancel_preview',$batch) }}" data-url="{{ route('admin.whatsapp.queue.batches.cancel',$batch) }}">إلغاء المتبقي</button>@endif
                @if($batch->failed_count>0 && $batch->status!=='cancelled' && !$batch->archived_at)<button class="btn btn-sm btn-warning batch-retry" data-url="{{ route('admin.whatsapp.queue.batches.retry',$batch) }}">إعادة الفاشلة</button>@endif
                @if(in_array($batch->status,['completed','completed_with_errors','cancelled']))<button class="btn btn-sm btn-secondary batch-archive" data-url="{{ route('admin.whatsapp.queue.batches.archive',$batch) }}">أرشفة</button>@endif
                @endcan
            </div></div>
            <div class="counts my-3"><span class="badge badge-light">الكل {{ $batch->total_count }}</span><span class="badge badge-light-warning">منتظرة {{ $batch->pending_count }}</span><span class="badge badge-light-primary">تُرسل {{ $batch->sending_count }}</span><span class="badge badge-light-success">مرسلة {{ $batch->sent_count }}</span><span class="badge badge-light-danger">فاشلة {{ $batch->failed_count }}</span><span class="badge badge-light-dark">ملغاة {{ $batch->cancelled_count }}</span></div>
            <div class="progress h-8px"><div class="progress-bar" style="width:{{ $progress }}%"></div></div>
        </div>
    @empty <div class="alert alert-info">لا توجد دفعات حديثة.</div> @endforelse
    {{ $batches->links() }}

    @if($legacyBatches->isNotEmpty())<div class="alert alert-secondary mt-6"><strong>دفعات قديمة متوافقة</strong><div class="table-responsive"><table class="table"><thead><tr><th>المرجع</th><th>الكل</th><th>منتظرة</th><th>مرسلة</th><th>فاشلة</th></tr></thead><tbody>@foreach($legacyBatches as $batch)<tr><td>{{ $batch->sent_by }}</td><td>{{ $batch->total_count }}</td><td>{{ $batch->pending_count }}</td><td>{{ $batch->sent_count }}</td><td>{{ $batch->failed_count }}</td></tr>@endforeach</tbody></table></div></div>@endif

    <div class="card mt-6" id="messages"><div class="card-header"><h3 class="card-title">تفاصيل الرسائل</h3></div><div class="card-body table-responsive"><table class="table table-row-bordered"><thead><tr><th>العميل</th><th>الهاتف</th><th>الحالة</th><th>المصدر</th><th>التاريخ</th></tr></thead><tbody>@forelse($recent as $log)<tr><td>{{ $log->client_name ?? '-' }}</td><td>{{ $log->phone }}</td><td><span class="badge badge-light">{{ $log->status }}</span></td><td>{{ $log->batch?->source ?? $log->sent_by ?? 'قديم' }}</td><td>{{ $log->created_at->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="5" class="text-center">لا توجد رسائل.</td></tr>@endforelse</tbody></table>{{ $recent->links() }}</div></div>
</div>
@endsection

@section('js')
<script>
$(function(){const token='{{ csrf_token() }}';const errorText=x=>x.responseJSON?.message||x.responseJSON?.error||'تعذر تنفيذ العملية. حاول مرة أخرى.';function failed(x){Swal.fire({icon:'error',title:'فشلت العملية',text:errorText(x)});}function post(url,data={}){return $.post(url,{_token:token,...data});}
$('#togglePause').on('click',()=>post('{{ route('admin.whatsapp.queue.pause') }}').done(()=>location.reload()).fail(failed));
$('.batch-cancel').on('click',function(){const b=$(this);$.get(b.data('preview')).done(r=>Swal.fire({title:'إلغاء الرسائل المنتظرة؟',text:r.warning+' المنتظرة: '+r.counts.pending+'، الجاري إرسالها: '+r.counts.sending,input:'text',inputPlaceholder:'سبب اختياري',showCancelButton:true,confirmButtonText:'تأكيد إلغاء المنتظرة',cancelButtonText:'رجوع'}).then(x=>{if(x.isConfirmed)post(b.data('url'),{reason:x.value}).done(()=>location.reload()).fail(failed);})).fail(failed);});
$('.batch-retry').on('click',function(){const url=$(this).data('url');Swal.fire({title:'إعادة جدولة الرسائل الفاشلة؟',text:'لن تُعاد النتائج الملتبسة دون إقرار صريح.',icon:'warning',showCancelButton:true,confirmButtonText:'إعادة الجدولة',cancelButtonText:'رجوع'}).then(x=>{if(x.isConfirmed)post(url).done(()=>location.reload()).fail(failed);});});
$('.batch-archive').on('click',function(){const url=$(this).data('url');Swal.fire({title:'أرشفة هذه الدفعة؟',text:'ستختفي من قائمة الدفعات النشطة.',icon:'warning',showCancelButton:true,confirmButtonText:'أرشفة',cancelButtonText:'رجوع'}).then(x=>{if(x.isConfirmed)post(url).done(()=>location.reload()).fail(failed);});});
});
</script>
@endsection
