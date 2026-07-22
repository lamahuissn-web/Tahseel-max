{{-- WhatsApp Control Center Tab Navigation --}}
@php
    $tabs = [
        ['route' => 'admin.whatsapp.dashboard', 'label' => 'لوحة التحكم', 'icon' => 'bi-kanban', 'emoji' => '📊'],
        ['route' => 'admin.whatsapp.monitor', 'label' => 'المراقبة', 'icon' => 'bi-activity', 'emoji' => '🩺'],
        ['route' => 'admin.whatsapp.safety', 'label' => 'الأمان', 'icon' => 'bi-shield-check', 'emoji' => '🛡️'],
        ['route' => 'admin.whatsapp.templates', 'label' => 'القوالب', 'icon' => 'bi-pencil-square', 'emoji' => '📝'],
        ['route' => 'admin.whatsapp.send', 'label' => 'إرسال', 'icon' => 'bi-send', 'emoji' => '📨'],
        ['route' => 'admin.whatsapp.collectors', 'label' => 'المحصلين', 'icon' => 'bi-person-badge', 'emoji' => '🧾'],
        ['route' => 'admin.whatsapp.automation', 'label' => 'الأتمتة', 'icon' => 'bi-gear', 'emoji' => '🤖'],
        ['route' => 'admin.whatsapp.log', 'label' => 'السجل', 'icon' => 'bi-clock-history', 'emoji' => '📋'],
        ['route' => 'admin.whatsapp.queue', 'label' => 'القائمة', 'icon' => 'bi-list-task', 'emoji' => '⏳'],
    ];
@endphp

<div class="whatsapp-tab-nav sticky-top bg-white border-bottom px-3 px-md-4 py-2" style="z-index: 1020;">
    <div class="d-flex align-items-center gap-2 flex-nowrap overflow-auto" style="scrollbar-width:none; -ms-overflow-style:none;">
        @foreach($tabs as $tab)
            @php
                $isActive = request()->routeIs($tab['route']);
            @endphp
            <a href="{{ route($tab['route']) }}"
               class="btn btn-sm {{ $isActive ? 'btn-success text-white' : 'btn-light-secondary' }} fw-semibold text-nowrap flex-shrink-0"
               style="border-radius: 20px; font-size: 0.85rem;">
                {{ $tab['emoji'] }} {{ $tab['label'] }}
                @if($tab['route'] === 'admin.whatsapp.dashboard')
                    <span class="connection-dot ms-1 d-inline-block rounded-circle"
                          id="nav-connection-dot"
                          style="width: 8px; height: 8px; background-color: #ccc;"
                          title="جاري التحقق..."></span>
                @endif
            </a>
        @endforeach
    </div>
</div>

<script>
    // Connection status dot auto-refresh (every 30 seconds)
    (function() {
        var dotUrl = '{{ route("admin.whatsapp.check_connection") }}';
        var $dot = null;

        function refreshDot() {
            if (!$dot) $dot = $('#nav-connection-dot');
            if (!$dot.length) return;

            $.ajax({
                url: dotUrl,
                type: 'GET',
                timeout: 5000,
                success: function(res) {
                    if (res.connected) {
                        $dot.css('background-color', '#50cd89').attr('title', 'متصل ✅');
                    } else {
                        $dot.css('background-color', '#f1416c').attr('title', 'غير متصل ❌');
                    }
                },
                error: function() {
                    $dot.css('background-color', '#ccc').attr('title', 'خطأ في الاتصال');
                }
            });
        }

        $(document).ready(function() {
            $dot = $('#nav-connection-dot');
            refreshDot();
            setInterval(refreshDot, 30000);
        });
    })();
</script>

<style>
    .whatsapp-tab-nav {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .whatsapp-tab-nav::-webkit-scrollbar {
        display: none;
    }
</style>
