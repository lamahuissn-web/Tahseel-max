@extends('dashbord.layouts.master')

@section('title', 'إدارة دفعات واتساب')

@section('content')
@include('dashbord.whatsapp._partials.tab-nav')
<div id="kt_app_content_container" class="app-container container-xxxl" dir="rtl">
    <style>
        .queue-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem}.queue-card{border:1px solid #e8e8e8;border-radius:12px;background:#fff}.queue-summary{padding:1rem}.queue-sections{display:flex;gap:.5rem;overflow-x:auto;padding-bottom:.25rem}.queue-sections .btn{white-space:nowrap}.queue-batch-row{min-height:88px;padding:.8rem 1rem;border:0;border-bottom:1px solid #edf0f4}.queue-batch-row:last-child{border-bottom:0}.queue-batch-main{min-width:0}.queue-batch-title{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.queue-progress{height:5px}.queue-actions{min-width:150px}.queue-range{font-size:.85rem;color:#7e8299}.queue-message-card{border:1px solid #e8edf3;border-radius:12px;padding:1rem;background:#fff;box-shadow:0 2px 8px rgba(31,45,61,.04)}.queue-message-card details{border-top:1px dashed #dfe3e8;margin-top:.7rem;padding-top:.55rem}.queue-message-card summary{cursor:pointer;color:#7e8299}.queue-messages-mobile{display:none}@media(max-width:767px){.queue-grid{grid-template-columns:repeat(2,1fr)}.queue-batch-row{align-items:flex-start!important;min-height:96px}.queue-actions{min-width:auto}.queue-actions .btn{padding:.55rem}.queue-messages-desktop{display:none}.queue-messages-mobile{display:grid;gap:.75rem}.queue-summary{padding:.85rem}.filter-actions .btn{flex:1}}
    </style>

    @php
        $statusLabels=['pending'=>'قيد الانتظار','sending'=>'جارٍ الإرسال','sent'=>'مرسلة','failed'=>'فاشلة','cancelled'=>'ملغاة'];
        $batchStatusLabels=['queued'=>'في الانتظار','running'=>'قيد التنفيذ','cancelling'=>'جارٍ الإلغاء','completed_with_errors'=>'مكتملة بأخطاء','completed'=>'مكتملة','cancelled'=>'ملغاة'];
        $sourceLabels=['manual'=>'يدوي','automation'=>'آلي','autoreceipt'=>'إيصال تلقائي','calendar'=>'تقويم','cron'=>'مجدول','system'=>'نظام'];
        $sectionLabels=['attention'=>'تحتاج متابعة','completed'=>'مكتملة','cancelled'=>'ملغاة'];
        $preserved=request()->except(['section','batches_page','batch','messages_page']);
    @endphp

    <div class="queue-grid mb-6">
        <div class="queue-card queue-summary"><strong>{{ $queuePaused ? 'متوقف مؤقتاً' : 'يعمل' }}</strong><div class="text-muted">حالة التوصيل العامة</div>@can('control_whatsapp_queue')<button id="togglePause" class="btn btn-sm {{ $queuePaused ? 'btn-success' : 'btn-warning' }} mt-2">{{ $queuePaused ? 'استئناف الطابور' : 'إيقاف الطابور مؤقتاً' }}</button>@endcan</div>
        <div class="queue-card queue-summary"><strong class="fs-2">{{ $pending }}</strong><div class="text-muted">رسائل بانتظار الإرسال</div></div>
        <div class="queue-card queue-summary"><strong class="fs-2">{{ $sending }}</strong><div class="text-muted">جارٍ إرسالها</div></div>
        <div class="queue-card queue-summary"><strong class="fs-2">{{ $failedToday }}</strong><div class="text-muted">إخفاقات اليوم</div></div>
    </div>

    <div class="card mb-6"><div class="card-body"><form method="GET" class="row g-3">
        <input type="hidden" name="section" value="{{ $section }}">
        <div class="col-md-3"><label class="form-label">حالة الرسالة</label><select class="form-select" name="status"><option value="">الكل</option>@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected($statusFilter===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">المصدر</label><select class="form-select" name="source">@foreach($sourceOptions as $value=>$label)<option value="{{ $value }}" @selected($sourceFilter===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">من تاريخ</label><input class="form-control" type="date" name="date_from" value="{{ $dateFrom }}"></div>
        <div class="col-md-2"><label class="form-label">إلى تاريخ</label><input class="form-control" type="date" name="date_to" value="{{ $dateTo }}"></div>
        <div class="col-md-2 d-flex align-items-end gap-2 filter-actions"><button class="btn btn-primary">تطبيق</button><a class="btn btn-light" href="{{ route('admin.whatsapp.queue') }}">مسح</a></div>
    </form></div></div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div class="queue-sections" aria-label="أقسام الدفعات">
            @foreach($sectionLabels as $value=>$label)
                <a class="btn btn-sm {{ $section===$value ? 'btn-primary' : 'btn-light-primary' }}" href="{{ route('admin.whatsapp.queue', array_merge($preserved,['section'=>$value])) }}">{{ $label }} <span class="badge badge-light ms-1">{{ $sectionCounts[$value] }}</span></a>
            @endforeach
        </div>
        <div class="queue-range">عرض {{ $batches->total() ? $batches->firstItem() : 0 }}–{{ $batches->lastItem() ?? 0 }} من {{ $batches->total() }}</div>
    </div>

    <div class="queue-card queue-batch-list mb-4">
        @forelse($batches as $batch)
            @php
                $done=$batch->sent_count+$batch->failed_count+$batch->cancelled_count;
                $progress=$batch->total_count ? min(100,round($done*100/$batch->total_count)) : 0;
                $detailsQuery=array_merge(request()->except(['batch','messages_page']),['batch'=>$batch->id]);
            @endphp
            <div class="queue-batch-row d-flex justify-content-between align-items-center gap-3">
                <div class="queue-batch-main flex-grow-1">
                    <div class="d-flex align-items-center gap-2"><strong class="queue-batch-title">{{ $batch->title }}</strong><span class="badge badge-light-primary">{{ $batchStatusLabels[$batch->status] ?? $batch->status }}</span></div>
                    <div class="text-muted fs-7 mt-1">{{ $sourceLabels[$batch->source] ?? $batch->source }} · {{ $batch->created_at->format('Y-m-d H:i') }} · أُرسل {{ $batch->sent_count }} من {{ $batch->total_count }}@if($batch->pending_count || $batch->sending_count || $batch->failed_count) · متبقي {{ $batch->pending_count+$batch->sending_count }} · فشل {{ $batch->failed_count }}@endif</div>
                    <div class="progress queue-progress mt-2"><div class="progress-bar {{ $batch->failed_count ? 'bg-warning' : 'bg-success' }}" style="width:{{ $progress }}%"></div></div>
                </div>
                <div class="queue-actions d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.whatsapp.queue',$detailsQuery) }}#messages" class="btn btn-sm btn-light-primary">التفاصيل</a>
                    @can('control_whatsapp_queue')
                    <div class="dropdown"><button class="btn btn-sm btn-light" data-bs-toggle="dropdown" aria-expanded="false">الإجراءات ⋮</button><div class="dropdown-menu dropdown-menu-end">
                        @if($batch->pending_count>0 && !in_array($batch->status,['cancelling','cancelled']))<button class="dropdown-item text-danger batch-cancel" data-preview="{{ route('admin.whatsapp.queue.batches.cancel_preview',$batch) }}" data-url="{{ route('admin.whatsapp.queue.batches.cancel',$batch) }}">إلغاء المتبقي</button>@endif
                        @if($batch->failed_count>0 && $batch->status!=='cancelled' && !$batch->archived_at)<button class="dropdown-item batch-retry" data-url="{{ route('admin.whatsapp.queue.batches.retry',$batch) }}">إعادة الفاشلة</button>@endif
                        @if(in_array($batch->status,['completed','completed_with_errors','cancelled']))<button class="dropdown-item batch-archive" data-url="{{ route('admin.whatsapp.queue.batches.archive',$batch) }}">أرشفة</button>@endif
                    </div></div>
                    @endcan
                </div>
            </div>
        @empty
            <div class="p-5 text-center text-muted">لا توجد دفعات في هذا القسم.</div>
        @endforelse
    </div>
    {{ $batches->onEachSide(1)->links() }}

    @if($legacyBatches->isNotEmpty())
        <details class="queue-card p-4 mt-6"><summary class="fw-bold">دفعات قديمة متوافقة ({{ $legacyBatches->count() }} كحد أقصى)</summary><div class="mt-3">
            @foreach($legacyBatches as $legacy)<div class="d-flex justify-content-between border-bottom py-2 gap-3"><span class="text-truncate">دفعة قديمة</span><span class="text-muted">{{ $legacy->sent_count }}/{{ $legacy->total_count }} مرسلة · {{ $legacy->failed_count }} فاشلة</span></div>@endforeach
        </div></details>
    @endif

    @if($selectedBatch && $messages)
        @php
            $backQuery=request()->except(['batch','messages_page']);
            $maskPhone=function($phone){$phone=(string)$phone;return str_repeat('*',max(0,strlen($phone)-3)).substr($phone,-3);};
        @endphp
        <section class="card mt-6" id="messages" data-page-name="messages_page">
            <div class="card-header align-items-center"><div><h3 class="card-title mb-0">رسائل: {{ $selectedBatch->title }}</h3><div class="queue-range mt-1">عرض {{ $messages->total() ? $messages->firstItem() : 0 }}–{{ $messages->lastItem() ?? 0 }} من {{ $messages->total() }}</div></div><a class="btn btn-sm btn-light" href="{{ route('admin.whatsapp.queue',$backQuery) }}">العودة إلى الدفعات</a></div>
            <div class="card-body">
                <div class="table-responsive queue-messages-desktop"><table class="table table-row-bordered align-middle mb-0"><thead><tr><th>العميل</th><th>الهاتف</th><th>الحالة</th><th>المصدر</th><th>التاريخ</th></tr></thead><tbody>@forelse($messages as $log)<tr><td>{{ $log->client_name ?? '-' }}</td><td dir="ltr">{{ $maskPhone($log->phone) }}</td><td><span class="badge badge-light">{{ $statusLabels[$log->status] ?? $log->status }}</span></td><td>{{ $sourceLabels[$selectedBatch->source] ?? $selectedBatch->source }}</td><td>{{ $log->created_at->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="5" class="text-center">لا توجد رسائل مطابقة.</td></tr>@endforelse</tbody></table></div>
                <div class="queue-messages-mobile">@forelse($messages as $log)<article class="queue-message-card"><div class="d-flex justify-content-between gap-2"><strong>{{ $log->client_name ?? 'عميل غير معروف' }}</strong><span class="badge badge-light">{{ $statusLabels[$log->status] ?? $log->status }}</span></div><div class="d-flex justify-content-between text-muted mt-2"><span dir="ltr">{{ $maskPhone($log->phone) }}</span><span>{{ $sourceLabels[$selectedBatch->source] ?? $selectedBatch->source }}</span></div><div class="text-muted fs-7 mt-2">{{ $log->created_at->format('Y-m-d H:i') }}</div><details><summary>بيانات تقنية</summary><div class="small text-muted text-break mt-2">{{ $log->message }}</div><div class="small text-muted text-break">{{ $log->sent_by }}</div></details></article>@empty<div class="text-center text-muted">لا توجد رسائل مطابقة.</div>@endforelse</div>
                <div class="mt-4">{{ $messages->onEachSide(1)->links() }}</div>
            </div>
        </section>
    @endif
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
