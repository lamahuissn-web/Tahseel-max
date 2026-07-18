@extends('dashbord.layouts.master')

@section('title')
{{ trans('clients.whatsapp_control_center') }}
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('clients.whatsapp_control_center');
    $breadcrumbs = [
        ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
        ['label' => trans('clients.whatsapp_control_center'), 'link' => route('admin.whatsapp.dashboard')],
        ['label' => 'Collector Reminders', 'link' => ''],
    ];
    PageTitle($title, $breadcrumbs);
    @endphp
</div>
@endsection

@section('content')
@include('dashbord.whatsapp._partials.tab-nav')
<div id="kt_app_content_container" class="app-container container-xxxl">

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-8">
            <i class="bi bi-check-circle-fill fs-2 me-4"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="alert alert-light-primary d-flex align-items-start p-5 mb-8">
        <i class="bi bi-info-circle fs-2 me-4 text-primary"></i>
        <div>
            <h4 class="mb-1">Collector Reminder MVP / تذكير المحصلين</h4>
            <div class="text-gray-700">
                Safe mode: no new database tables, no finance data changes. Collector assignment is based only on markers inside customer names.
            </div>
        </div>
    </div>

    <div class="card mb-8">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-tags me-2 text-success"></i>Detected Marker Suggestions</h3>
        </div>
        <div class="card-body">
            <div class="text-muted fs-7 mb-3">
                Click inside a rule marker field, then click a marker below. This reduces typing mistakes.
            </div>
            <div class="d-flex flex-wrap gap-2">
                @forelse(($markerSuggestions ?? []) as $suggestion)
                    <button type="button" class="btn btn-sm btn-light-success" onclick="addMarkerToFocusedRule('{{ $suggestion['marker'] }}')">
                        {{ $suggestion['marker'] }}
                        <span class="badge badge-light ms-1">{{ $suggestion['count'] }}</span>
                    </button>
                @empty
                    <span class="text-muted">No marker-like codes found in customer names.</span>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mb-8">
        <div class="col-xl-5">
            <div class="card card-xl-stretch">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-person-badge me-2 text-primary"></i>Collector Rules</h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary" onclick="addCollectorRule()">
                            <i class="bi bi-plus-circle me-1"></i> Add Rule
                        </button>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.whatsapp.collectors.rules.save') }}">
                    @csrf
                    <div class="card-body" id="collector-rules-list">
                        @php $rulesForView = count($rules ?? []) ? $rules : [['admin_id' => null, 'name' => '', 'phone' => '', 'markers' => [], 'active' => true]]; @endphp
                        @foreach($rulesForView as $index => $rule)
                            @php
                                $selectedAdminId = $rule['admin_id'] ?? null;
                                if (!$selectedAdminId && !empty($rule['name'])) {
                                    $matchedCollector = collect($collectorUsers ?? [])->firstWhere('name', $rule['name']);
                                    $selectedAdminId = $matchedCollector['id'] ?? null;
                                }
                            @endphp
                            <div class="collector-rule border rounded p-4 mb-4" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold text-gray-800">Rule #{{ $index + 1 }}</span>
                                    <button type="button" class="btn btn-sm btn-light-danger" onclick="removeCollectorRule(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Collector</label>
                                    <select name="collector_user_id[{{ $index }}]" class="form-select collector-user-select" onchange="syncCollectorPhone(this)">
                                        <option value="">Select collector from admin users...</option>
                                        @foreach(($collectorUsers ?? []) as $collectorUser)
                                            <option value="{{ $collectorUser['id'] }}" data-phone="{{ $collectorUser['phone'] }}" {{ (string) $selectedAdminId === (string) $collectorUser['id'] ? 'selected' : '' }}>
                                                #{{ $collectorUser['id'] }} — {{ $collectorUser['name'] }}{{ !empty($collectorUser['phone']) ? ' — ' . $collectorUser['phone'] : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Loaded from /admin/users active admin users.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Collector WhatsApp</label>
                                    <input type="text" name="collector_phone[{{ $index }}]" class="form-control collector-phone-input" value="{{ $rule['phone'] ?? '' }}" placeholder="+961...">
                                    <div class="form-text">Auto-filled from selected admin user; edit only if needed.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Markers</label>
                                    <input type="text" name="collector_markers[{{ $index }}]" class="form-control collector-marker-input" value="{{ implode(', ', $rule['markers'] ?? []) }}" placeholder="W.K, W.K.F" onfocus="setActiveMarkerInput(this)">
                                    <div class="form-text">Use suggestion buttons above, or type markers separated by comma, Arabic comma, semicolon, space, or new line.</div>
                                </div>
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="collector_active[{{ $index }}]" value="1" {{ ($rule['active'] ?? true) ? 'checked' : '' }}>
                                    <span class="form-check-label fw-semibold">Active</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i> Save Rules
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card card-xl-stretch mb-8">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-eye me-2 text-info"></i>Preview Today</h3>
                    <div class="card-toolbar d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-light-primary" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Preview
                        </button>
                        <button type="button" class="btn btn-sm btn-success" id="send-collector-reminders-btn" onclick="sendCollectorRemindersNow()">
                            <i class="bi bi-send me-1"></i> Send Now
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4 mb-6">
                        <div class="col-md-3"><div class="border rounded p-4"><div class="text-muted fs-7">Collectors</div><div class="fs-2 fw-bold">{{ $preview['summary']['collectors_with_customers'] ?? 0 }}</div></div></div>
                        <div class="col-md-3"><div class="border rounded p-4"><div class="text-muted fs-7">Customers</div><div class="fs-2 fw-bold">{{ $preview['summary']['customers'] ?? 0 }}</div></div></div>
                        <div class="col-md-3"><div class="border rounded p-4"><div class="text-muted fs-7">Invoices</div><div class="fs-2 fw-bold">{{ $preview['summary']['invoices'] ?? 0 }}</div></div></div>
                        <div class="col-md-3"><div class="border rounded p-4"><div class="text-muted fs-7">Total</div><div class="fs-2 fw-bold">${{ number_format($preview['summary']['total_amount'] ?? 0, 2) }}</div></div></div>
                    </div>

                    @if(($preview['summary']['conflicts'] ?? 0) > 0)
                        <div class="alert alert-warning">⚠️ {{ $preview['summary']['conflicts'] }} customers match multiple collectors. They will not be sent automatically.</div>
                    @endif
                    @if(($preview['summary']['unmatched'] ?? 0) > 0)
                        <div class="alert alert-light-warning">ℹ️ {{ $preview['summary']['unmatched'] }} due customers have no collector marker match.</div>
                    @endif

                    @forelse(($preview['groups'] ?? []) as $group)
                        <div class="border rounded p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <h4 class="mb-1">{{ $group['name'] ?: 'Unnamed Collector' }}</h4>
                                    <div class="text-muted fs-7">{{ $group['phone'] ?: 'No phone' }} · Markers: {{ implode(', ', $group['markers'] ?? []) }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-light-primary">{{ $group['customer_count'] }} customers</span>
                                    <span class="badge badge-light-success">${{ number_format($group['total_amount'], 2) }}</span>
                                </div>
                            </div>

                            @if(($group['customer_count'] ?? 0) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-row-dashed align-middle mb-0">
                                        <thead><tr><th>Customer</th><th>Phone</th><th>Invoices</th><th>Due</th><th>Amount</th></tr></thead>
                                        <tbody>
                                        @foreach(array_slice($group['customers'], 0, 20) as $customer)
                                            <tr>
                                                <td>{{ $customer['name'] }}</td>
                                                <td>{{ $customer['phone'] ?: '—' }}</td>
                                                <td>{{ $customer['invoice_count'] }}</td>
                                                <td>{{ $customer['first_due_date_formatted'] }}</td>
                                                <td>${{ number_format($customer['total_amount'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if(count($group['customers']) > 20)
                                    <div class="text-muted fs-8 mt-2">Showing first 20 customers only.</div>
                                @endif
                            @else
                                <div class="text-muted">No due customers for this collector today.</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-10">
                            <i class="bi bi-person-x fs-3x d-block mb-3"></i>
                            No collector rules yet. Add rules to preview today's collection reminders.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let collectorRuleIndex = {{ count($rulesForView ?? []) }};
const collectorUsers = @json($collectorUsers ?? []);
let activeMarkerInput = null;

function setActiveMarkerInput(input) {
    activeMarkerInput = input;
}

function splitMarkers(value) {
    return String(value || '')
        .split(/[,،;؛\n]+/)
        .map(function(marker) { return marker.trim(); })
        .filter(Boolean);
}

function addMarkerToFocusedRule(marker) {
    const $input = activeMarkerInput ? $(activeMarkerInput) : $('.collector-marker-input').first();
    if (!$input.length) return;

    const existing = splitMarkers($input.val());
    const exists = existing.some(function(item) {
        return item.toLowerCase() === String(marker).toLowerCase();
    });

    if (!exists) {
        existing.push(marker);
    }

    $input.val(existing.join(', ')).focus();
}

function collectorOptionsHtml() {
    let html = '<option value="">Select collector from admin users...</option>';
    collectorUsers.forEach(function(user) {
        const label = '#' + user.id + ' — ' + user.name + (user.phone ? ' — ' + user.phone : '');
        html += '<option value="' + user.id + '" data-phone="' + escapeHtml(user.phone || '') + '">' + escapeHtml(label) + '</option>';
    });
    return html;
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function syncCollectorPhone(selectEl) {
    const phone = $(selectEl).find('option:selected').data('phone') || '';
    const $rule = $(selectEl).closest('.collector-rule');
    const $phone = $rule.find('.collector-phone-input');
    if (!$phone.val() || $phone.data('auto-filled')) {
        $phone.val(phone).data('auto-filled', true);
    }
}

function addCollectorRule() {
    const idx = collectorRuleIndex++;
    const html = `
        <div class="collector-rule border rounded p-4 mb-4" data-index="${idx}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold text-gray-800">New Rule</span>
                <button type="button" class="btn btn-sm btn-light-danger" onclick="removeCollectorRule(this)"><i class="bi bi-trash"></i></button>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Collector</label>
                <select name="collector_user_id[${idx}]" class="form-select collector-user-select" onchange="syncCollectorPhone(this)">${collectorOptionsHtml()}</select>
                <div class="form-text">Loaded from /admin/users active admin users.</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Collector WhatsApp</label>
                <input type="text" name="collector_phone[${idx}]" class="form-control collector-phone-input" placeholder="+961...">
                <div class="form-text">Auto-filled from selected admin user; edit only if needed.</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Markers</label>
                <input type="text" name="collector_markers[${idx}]" class="form-control collector-marker-input" placeholder="W.K, W.K.F" onfocus="setActiveMarkerInput(this)">
                <div class="form-text">Use suggestion buttons above, or type markers separated by comma, Arabic comma, semicolon, space, or new line.</div>
            </div>
            <label class="form-check form-switch form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" name="collector_active[${idx}]" value="1" checked><span class="form-check-label fw-semibold">Active</span></label>
        </div>`;
    $('#collector-rules-list').append(html);
}

function removeCollectorRule(button) {
    $(button).closest('.collector-rule').remove();
}

function sendCollectorRemindersNow() {
    $('#send-collector-reminders-btn').prop('disabled', true);
    $.post('{{ route('admin.whatsapp.collectors.send_now') }}', { _token: '{{ csrf_token() }}' })
        .done(function(res) {
            if (res.queued > 0) {
                Swal.fire({ icon: 'success', text: 'Queued ' + res.queued + ' collector reminder messages.' });
                setTimeout(function() { window.location.href = res.redirect_url; }, 900);
            } else {
                Swal.fire({ icon: 'info', text: 'No collector reminders were queued. Check rules, phones, and preview.' });
            }
        })
        .fail(function(xhr) {
            Swal.fire({ icon: 'error', text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to queue collector reminders.' });
        })
        .always(function() {
            $('#send-collector-reminders-btn').prop('disabled', false);
        });
}
</script>
@endsection
