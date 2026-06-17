<style>
.sas4-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 16px;
    margin-top: 16px;
}
.sas4-card .sas4-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid #0d6efd;
}
.sas4-card .sas4-header h6 {
    margin: 0;
    font-weight: 700;
    color: #0d6efd;
}
.sas4-card .sas4-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 8px;
}
.sas4-card .sas4-info-item {
    display: flex;
    flex-direction: column;
}
.sas4-card .sas4-info-item .label {
    font-size: 11px;
    color: #6c757d;
    font-weight: 600;
}
.sas4-card .sas4-info-item .value {
    font-size: 13px;
    font-weight: 600;
    color: #212529;
}
.sas4-card .sas4-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}
.sas4-card .sas4-badge.online { background: #d1e7dd; color: #0f5132; }
.sas4-card .sas4-badge.offline { background: #f8d7da; color: #842029; }
.sas4-card .sas4-badge.enabled { background: #d1e7dd; color: #0f5132; }
.sas4-card .sas4-badge.disabled { background: #f8d7da; color: #842029; }
.sas4-card .sas4-badge.expired { background: #fff3cd; color: #664d03; }
.sas4-card .traffic-table {
    width: 100%;
    margin-top: 12px;
    font-size: 12px;
    border-collapse: collapse;
}
.sas4-card .traffic-table th {
    background: #e9ecef;
    padding: 6px 8px;
    text-align: center;
    font-weight: 600;
}
.sas4-card .traffic-table td {
    padding: 4px 8px;
    text-align: center;
    border-bottom: 1px solid #eee;
}
.sas4-card .sas4-loader {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}
</style>

<div class="sas4-card" id="sas4InfoCard">
    <div class="sas4-header">
        <i class="bi bi-wifi"></i>
        <h6>{{ trans('clients.sas4_internet_info') }}</h6>
    </div>
    <div class="sas4-loader" id="sas4Loader">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <span class="ms-2">{{ trans('clients.loading_details') }}</span>
    </div>
    <div id="sas4Content" style="display:none;"></div>
</div>

<script>
$(document).ready(function() {
    var clientId = {{ $client->id }};
    $.ajax({
        url: '{{ route('admin.clients.sas4_info', ['id' => '__ID__']) }}'.replace('__ID__', clientId),
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            $('#sas4Loader').hide();
            $('#sas4Content').show();

            var user = res.user || {};
            var overview = res.overview || {};
            var traffic = res.traffic || {};
            var trafficData = traffic.data || traffic.daily || [];

            var statusBadge = '';
            if (user.enabled == 1 && user.online == 1) {
                statusBadge = '<span class="sas4-badge online">{{ trans('clients.sas4_online') }}</span>';
            } else if (user.enabled == 0) {
                statusBadge = '<span class="sas4-badge disabled">{{ trans('clients.sas4_disabled') }}</span>';
            } else if (user.expired == 1) {
                statusBadge = '<span class="sas4-badge expired">{{ trans('clients.sas4_expired') }}</span>';
            } else {
                statusBadge = '<span class="sas4-badge offline">{{ trans('clients.sas4_offline') }}</span>';
            }

            var profileName = user.profile_name || user.profile || 'N/A';
            var speedDown = user.speed_down || overview.speed_down || 'N/A';
            var speedUp = user.speed_up || overview.speed_up || 'N/A';
            var speed = speedDown + ' / ' + speedUp + ' Mbps';

            var html = '<div class="sas4-info-grid">' +
                '<div class="sas4-info-item"><span class="label">{{ trans('clients.sas4_username') }}</span><span class="value">' + (user.username || 'N/A') + '</span></div>' +
                '<div class="sas4-info-item"><span class="label">{{ trans('clients.sas4_status') }}</span><span class="value">' + statusBadge + '</span></div>' +
                '<div class="sas4-info-item"><span class="label">{{ trans('clients.sas4_plan') }}</span><span class="value">' + profileName + '</span></div>' +
                '<div class="sas4-info-item"><span class="label">{{ trans('clients.sas4_speed') }}</span><span class="value">' + speed + '</span></div>' +
                '<div class="sas4-info-item"><span class="label">{{ trans('clients.sas4_balance') }}</span><span class="value">' + (user.balance || '0.00') + '</span></div>' +
                '<div class="sas4-info-item"><span class="label">{{ trans('clients.sas4_expiration') }}</span><span class="value">' + (user.expiration || 'N/A') + '</span></div>' +
                '<div class="sas4-info-item"><span class="label">{{ trans('clients.sas4_last_online') }}</span><span class="value">' + (user.last_login || 'N/A') + '</span></div>' +
                '<div class="sas4-info-item"><span class="label">{{ trans('clients.sas4_last_ip') }}</span><span class="value">' + (user.last_ip || 'N/A') + '</span></div>' +
                '<div class="sas4-info-item"><span class="label">{{ trans('clients.sas4_created') }}</span><span class="value">' + (user.created_at || 'N/A') + '</span></div>' +
                '</div>';

            if (trafficData && trafficData.length > 0) {
                html += '<table class="traffic-table"><thead><tr>' +
                    '<th>{{ trans('clients.sas4_download') }}</th>' +
                    '<th>{{ trans('clients.sas4_upload') }}</th>' +
                    '<th>{{ trans('clients.sas4_total') }}</th>' +
                    '<th>{{ trans('clients.sas4_uptime') }}</th>' +
                    '</tr></thead><tbody>';

                var last7 = trafficData.slice(-7);
                last7.forEach(function(day) {
                    html += '<tr>' +
                        '<td>' + (day.download || day.bytes_in || '0 B') + '</td>' +
                        '<td>' + (day.upload || day.bytes_out || '0 B') + '</td>' +
                        '<td>' + (day.total || day.bytes_total || '0 B') + '</td>' +
                        '<td>' + (day.uptime || day.session_time || 'N/A') + '</td>' +
                        '</tr>';
                });

                html += '</tbody></table>';
            }

            $('#sas4Content').html(html);
        },
        error: function(xhr) {
            $('#sas4Loader').hide();
            $('#sas4Content').show();
            var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : '{{ trans('clients.error_loading_details') }}';
            $('#sas4Content').html('<div class="text-center text-muted py-3"><i class="bi bi-exclamation-triangle"></i> ' + msg + '</div>');
        }
    });
});
</script>
