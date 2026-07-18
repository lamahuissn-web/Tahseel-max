<script>
    var qrPollInterval = null;
    var connectionPollInterval = null;
    var qrBaseUrl = '{{ route("admin.whatsapp.qr_code") }}';
    var checkConnectionUrl = '{{ route("admin.whatsapp.check_connection") }}';
    var settingsStatusUrl = '{{ route("admin.settings.whatsapp.api_status") }}';
    var settingsQrUrl = '{{ route("admin.settings.whatsapp.api_qr") }}';
    var restartSessionUrl = '{{ route("admin.settings.whatsapp.restart") }}';

    function refreshConnectionMonitor() {
        $('#monitor-refresh-btn').prop('disabled', true);
        $.get(settingsStatusUrl)
            .done(function(res) {
                var lines = [];
                lines.push('API reachable: ' + ((res.reachable ?? false) ? 'yes' : 'no'));
                lines.push('Session status: ' + (res.status || 'unknown'));
                lines.push('Connected: ' + ((res.connected ?? false) ? 'yes' : 'no'));
                if (res.phone) {
                    lines.push('Phone: ' + res.phone);
                }
                Swal.fire({ icon: (res.connected ? 'success' : 'info'), title: 'Connection status', html: lines.join('<br>') });
                setTimeout(function() { location.reload(); }, 700);
            })
            .fail(function() {
                Swal.fire({ icon: 'error', text: 'Failed to refresh OpenWA status.' });
            })
            .always(function() {
                $('#monitor-refresh-btn').prop('disabled', false);
            });
    }

    function fetchMonitorQR() {
        Swal.fire({
            title: 'QR status',
            html: '<div class="py-4"><div class="spinner-border text-primary mb-3" role="status"></div><div>Loading QR state...</div></div>',
            showConfirmButton: false,
            showCloseButton: true,
            width: 520
        });

        $.get(settingsQrUrl)
            .done(function(res) {
                if (res.connected) {
                    Swal.update({
                        icon: 'success',
                        html: '<div class="py-2"><div class="fw-bold text-success mb-2">Session already connected — no QR needed.</div><div class="text-muted fs-7">' + (res.message || '') + '</div></div>',
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (res.qr) {
                    var qrHtml = '';
                    if (String(res.qr).startsWith('data:')) {
                        qrHtml = '<img src="' + res.qr + '" class="img-fluid rounded border p-2" style="max-width:280px; width:100%;">';
                    } else if (String(res.qr).startsWith('http')) {
                        qrHtml = '<img src="' + res.qr + '" class="img-fluid rounded border p-2" style="max-width:280px; width:100%;">';
                    } else if (String(res.qr).length > 100) {
                        qrHtml = '<img src="data:image/png;base64,' + res.qr + '" class="img-fluid rounded border p-2" style="max-width:280px; width:100%;">';
                    } else {
                        qrHtml = '<pre class="border p-3 bg-white rounded d-inline-block text-start" style="font-size:12px; line-height:1.2; direction:ltr;">' + res.qr + '</pre>';
                    }

                    Swal.update({
                        icon: 'info',
                        html: '<div class="text-center">' + qrHtml + '<div class="text-muted fs-7 mt-3">Scan this QR with the WhatsApp phone.</div></div>',
                        showConfirmButton: true,
                        confirmButtonText: 'Close'
                    });
                    return;
                }

                Swal.update({
                    icon: 'warning',
                    html: '<div class="py-2 text-muted">' + (res.message || 'QR not available right now.') + '</div>',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
            })
            .fail(function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to load QR state.';
                Swal.update({
                    icon: 'error',
                    html: '<div class="py-2 text-danger">' + message + '</div>',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
            });
    }

    function restartWhatsAppSession() {
        $('#monitor-restart-btn').prop('disabled', true);
        $.post(restartSessionUrl, { _token: '{{ csrf_token() }}' })
            .done(function(res) {
                Swal.fire({ icon: res.success ? 'success' : 'warning', text: res.message || 'Restart request finished.' });
                setTimeout(function() { location.reload(); }, 1500);
            })
            .fail(function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Restart request failed.';
                Swal.fire({ icon: 'error', text: message });
            })
            .always(function() {
                $('#monitor-restart-btn').prop('disabled', false);
            });
    }
</script>