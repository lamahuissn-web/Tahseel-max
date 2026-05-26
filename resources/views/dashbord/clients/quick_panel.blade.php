<div id="clientQuickPanelContent" data-client-id="{{ $client->id }}">

    {{-- Header --}}
    <div class="text-center text-white p-4" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
        <div class="mb-2" style="opacity: 0.6; font-size: 11px; letter-spacing: 1.2px; text-transform: uppercase;">{{ trans('clients.ID') }} {{ $client->id }}</div>
        <div class="fw-bold mb-3" style="font-size: 20px; letter-spacing: -0.3px;">{{ $client->name }}</div>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            @if($client->is_active == '1')
                <span class="badge rounded-pill" style="background: rgba(40,167,69,0.25); color: #2ecc71; border: 1px solid rgba(46,204,113,0.4); font-size: 12px; padding: 6px 14px;">
                    {{ trans('clients.active') }}
                </span>
            @else
                <span class="badge rounded-pill" style="background: rgba(220,53,69,0.25); color: #ff6b6b; border: 1px solid rgba(255,107,107,0.4); font-size: 12px; padding: 6px 14px;">
                    {{ trans('clients.inactive') }}
                </span>
            @endif
            @if($client->client_code)
                <span class="badge rounded-pill" style="background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.85); border: 1px solid rgba(255,255,255,0.25); font-size: 12px; padding: 6px 14px;">
                    {{ $client->client_code }}
                </span>
            @endif
        </div>
    </div>

    {{-- Info List --}}
    <div class="bg-white">

        <div class="d-flex justify-content-between align-items-center px-4" style="padding-top: 18px; padding-bottom: 14px; border-bottom: 1px solid #f0f0f0;">
            <span class="text-muted" style="font-size: 14px;">{{ trans('clients.phone') }}</span>
            <span class="fw-medium" style="font-size: 14px; color: #222; direction: ltr;">{{ $client->phone ?? '—' }}</span>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4" style="padding-top: 14px; padding-bottom: 14px; border-bottom: 1px solid #f0f0f0;">
            <span class="text-muted" style="font-size: 14px;">{{ trans('clients.user') }}</span>
            <span class="fw-medium" style="font-size: 14px; color: #222;">{{ $client->user ?? '—' }}</span>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4" style="padding-top: 14px; padding-bottom: 14px; border-bottom: 1px solid #f0f0f0;">
            <span class="text-muted" style="font-size: 14px;">{{ trans('clients.box_switch') }}</span>
            <span class="fw-medium" style="font-size: 14px; color: #222;">{{ $client->box_switch ?? '—' }}</span>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4" style="padding-top: 14px; padding-bottom: 14px; border-bottom: 1px solid #f0f0f0;">
            <span class="text-muted" style="font-size: 14px;">{{ trans('clients.client_type') }}</span>
            <span class="fw-medium" style="font-size: 14px; color: #222;">{{ $client->client_type ?? '—' }}</span>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4" style="padding-top: 14px; padding-bottom: 14px; border-bottom: 1px solid #f0f0f0;">
            <span class="text-muted" style="font-size: 14px;">{{ trans('clients.subscription') }}</span>
            <span class="fw-medium" style="font-size: 14px; color: #222;">{{ $client->subscription->name ?? '—' }}</span>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4" style="padding-top: 14px; padding-bottom: 14px; border-bottom: 1px solid #f0f0f0;">
            <span class="text-muted" style="font-size: 14px;">{{ trans('clients.price') }}</span>
            <span class="fw-medium" style="font-size: 14px; color: #222;">{{ $client->price ?? '—' }}</span>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4" style="padding-top: 14px; padding-bottom: 14px; border-bottom: 1px solid #f0f0f0;">
            <span class="text-muted" style="font-size: 14px;">{{ trans('clients.remaining_amount') }}</span>
            <span class="fw-bold" style="font-size: 14px; color: #dc3545; cursor: pointer;" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">
                {{ number_format($client->remaining_amount_total ?? 0, 2) }}
                <i class="bi bi-chevron-left" style="font-size: 10px; opacity: 0.5;"></i>
            </span>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4" style="padding-top: 14px; padding-bottom: 18px;">
            <span class="text-muted" style="font-size: 14px;">{{ trans('clients.status') }}</span>
            <span class="fw-medium" style="font-size: 14px; color: #222;">
                @if($client->is_active == '1')
                    {{ trans('clients.active') }}
                @else
                    {{ trans('clients.inactive') }}
                @endif
            </span>
        </div>

        @if($client->address1)
        <div class="px-4" style="padding-top: 14px; padding-bottom: 18px; border-top: 1px solid #f0f0f0;">
            <span class="text-muted d-block mb-1" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">{{ trans('clients.address1') }}</span>
            <span class="fw-medium d-block" style="font-size: 14px; color: #222;">{{ $client->address1 }}</span>
        </div>
        @endif

    </div>

    {{-- Actions --}}
    <div class="p-4" style="background: #f8f9fa; border-top: 1px solid #e9ecef;">
        <button class="btn w-100 mb-2 fw-semibold" style="background: #0d6efd; color: #fff; border-radius: 10px; padding: 12px; font-size: 15px; border: none; letter-spacing: 0.2px;" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">
            {{ trans('clients.client_unpaid_invoices') }}
        </button>

        @can('add_client_invoice')
        <a href="{{ route('admin.client_invoices', $client->id) }}" class="btn w-100 mb-2 fw-semibold" style="background: #fff; color: #198754; border-radius: 10px; padding: 12px; font-size: 15px; border: 1px solid #198754; letter-spacing: 0.2px;">
            {{ trans('clients.client_add_invoice') }}
        </a>
        @endcan

        @can('update_client')
        <a href="{{ route('admin.clients.change_status', [$client->id, $client->is_active]) }}"
           class="btn w-100 mb-2 fw-semibold"
           style="background: #fff; color: #dc3545; border-radius: 10px; padding: 12px; font-size: 15px; border: 1px solid #dc3545; letter-spacing: 0.2px;"
           onclick="return confirm('{{ trans('clients.change_status_msg') }}');">
            {{ trans('clients.change_status') }}
        </a>
        @endcan

        <button class="btn w-100 fw-semibold" style="background: #fff; color: #0d6efd; border-radius: 10px; padding: 12px; font-size: 15px; border: 1px solid #0d6efd; letter-spacing: 0.2px;" onclick="showClientDetails({{ $client->id }}); $('#clientQuickPanelModal').modal('hide');">
            {{ trans('clients.view_full_details') }}
        </button>
    </div>

</div>
