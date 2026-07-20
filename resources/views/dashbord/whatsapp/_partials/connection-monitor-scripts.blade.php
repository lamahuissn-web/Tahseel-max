<script>
    var qrPollInterval = null;
    var connectionPollInterval = null;
    var monitorAutoRefreshInterval = null;
    var qrBaseUrl = '{{ route("admin.whatsapp.qr_code") }}';
    var checkConnectionUrl = '{{ route("admin.whatsapp.check_connection") }}';
    var settingsStatusUrl = '{{ route("admin.settings.whatsapp.api_status") }}';
    var settingsQrUrl = '{{ route("admin.settings.whatsapp.api_qr") }}';
    var restartSessionUrl = '{{ route("admin.settings.whatsapp.restart") }}';
    var revokeSessionUrl = '{{ route("admin.whatsapp.monitor.revoke_session") }}';

    function refreshConnectionMonitor(silent) {
        if (typeof silent === 'undefined') silent = false;
        $('#monitor-refresh-btn').prop('disabled', true);
        $.get(settingsStatusUrl)
            .done(function(res) {
                if (!silent) {
                    var lines = [];
                    lines.push('API reachable: ' + ((res.reachable ?? false) ? 'yes' : 'no'));
                    lines.push('Session status: ' + (res.status || 'unknown'));
                    lines.push('Connected: ' + ((res.connected ?? false) ? 'yes' : 'no'));
                    if (res.phone) {
                        lines.push('Phone: ' + res.phone);
                    }
                    Swal.fire({ icon: (res.connected ? 'success' : 'info'), title: 'Connection status', html: lines.join('<br>') });
                }

                $('#monitor-last-checked').text('Last checked: just now');
                setTimeout(function() {
                    if (!Swal.isVisible()) {
                        location.reload();
                    }
                }, silent ? 300 : 700);
            })
            .fail(function() {
                if (!silent) {
                    Swal.fire({ icon: 'error', text: 'Failed to refresh OpenWA status.' });
                }
            })
            .always(function() {
                $('#monitor-refresh-btn').prop('disabled', false);
            });
    }

    function startMonitorAutoRefresh() {
        if (monitorAutoRefreshInterval) {
            clearInterval(monitorAutoRefreshInterval);
        }

        monitorAutoRefreshInterval = setInterval(function() {
            if (document.visibilityState === 'visible' && !Swal.isVisible()) {
                refreshConnectionMonitor(true);
            }
        }, 30000);
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Connected',
                        html: '<div class="py-2"><div class="fw-bold text-success mb-2">Session already connected — no QR needed.</div><div class="text-muted fs-7">' + (res.message || '') + '</div></div>',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        width: 520
                    });
                    return;
                }

                if (res.qr) {
                    showQRSwal(res.qr);
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'QR unavailable',
                    html: '<div class="py-2 text-muted">' + (res.message || 'QR not available right now.') + '</div>',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    width: 520
                });
            })
            .fail(function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to load QR state.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: '<div class="py-2 text-danger">' + message + '</div>',
                    confirmButtonText: 'OK',
                    width: 520
                });
            });
    }

    function showQRSwal(qrData) {
        startMonitorAutoRefresh();

        var qrSrc = '';
        if (String(qrData).startsWith('data:')) {
            qrSrc = qrData;
        } else if (String(qrData).startsWith('http')) {
            qrSrc = qrData;
        } else if (String(qrData).length > 100) {
            qrSrc = 'data:image/png;base64,' + qrData;
        }

        if (qrSrc) {
            Swal.fire({
                title: 'Scan QR Code',
                html: '<div class="text-center">' +
                      '<div style="display:inline-block; background:#fff; padding:12px; border-radius:8px; line-height:0;">' +
                      '<img src="' + qrSrc + '" alt="WhatsApp QR" ' +
                      'style="width:320px; max-width:100%; height:auto; aspect-ratio:1; display:block; ' +
                      'image-rendering:-webkit-optimize-contrast; image-rendering:crisp-edges; image-rendering:pixelated;">' +
                      '</div>' +
                      '<p class="text-muted fs-7 mt-3 mb-1">Scan this QR with the WhatsApp phone.</p>' +
                      '<p class="mt-0 mb-0"><a href="#" onclick="refreshQRCode(event)" class="text-primary fw-semibold fs-6">↻ Refresh QR (if expired)</a></p>' +
                      '</div>',
                confirmButtonText: 'Close',
                showCloseButton: true,
                didOpen: function() {
                    var c = Swal.getHtmlContainer();
                    if (c) {
                        c.style.maxHeight = 'none';
                        c.style.overflow = 'hidden';
                        c.style.margin = '0.5em 1em 0';
                        c.style.padding = '0';
                    }
                    var p = Swal.getPopup();
                    if (p) {
                        p.style.maxHeight = 'none';
                        p.style.overflowY = 'hidden';
                    }
                }
            });
        } else {
            Swal.fire({
                title: 'QR Code',
                html: '<pre class="border p-3 bg-white rounded d-inline-block text-start" style="font-size:12px; line-height:1.2; direction:ltr;">' + qrData + '</pre>',
                confirmButtonText: 'Close',
                showCloseButton: true
            });
        }
    }

    function refreshQRCode(event) {
        if (event) {
            event.preventDefault();
        }

        if (monitorAutoRefreshInterval) {
            clearInterval(monitorAutoRefreshInterval);
            monitorAutoRefreshInterval = null;
        }

        Swal.fire({
            title: 'Refreshing QR...',
            html: '<div class="py-4"><div class="spinner-border text-primary mb-3" role="status"></div><div>Checking for a new QR code...</div></div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        });

        pollQRCode(0, false);
    }

    function pollQRCode(attempt, restarted) {
        $.get(settingsQrUrl)
            .done(function(res) {
                if (res.connected) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Connected',
                        html: '<div class="py-2"><div class="fw-bold text-success mb-2">Session already connected — no QR needed.</div></div>',
                        confirmButtonText: 'OK',
                        width: 520
                    }).then(function() { startMonitorAutoRefresh(); });
                    return;
                }

                if (res.qr) {
                    showQRSwal(res.qr);
                    return;
                }

                var qrMessage = String(res.message || 'OpenWA did not return a QR yet.');
                var sessionNeedsStart = qrMessage.toLowerCase().indexOf('not started') !== -1;

                if (!restarted && sessionNeedsStart) {
                    askRestartForQRCode(qrMessage);
                    return;
                }

                if (attempt < 8) {
                    Swal.update({
                        title: 'QR not ready yet',
                        html: '<div class="py-4"><div class="spinner-border text-primary mb-3" role="status"></div><div>' + qrMessage + '</div><div class="text-muted fs-8 mt-2">Attempt ' + (attempt + 1) + ' of 9</div></div>'
                    });
                    setTimeout(function() { pollQRCode(attempt + 1, restarted); }, 2000);
                    return;
                }

                if (!restarted) {
                    askRestartForQRCode(qrMessage);
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'QR unavailable',
                    html: '<div class="py-2 text-muted">' + (res.message || 'QR not available yet. Try again in a moment.') + '</div>',
                    confirmButtonText: 'OK',
                    width: 520
                }).then(function() { startMonitorAutoRefresh(); });
            })
            .fail(function(xhr) {
                if (attempt < 8) {
                    Swal.update({
                        title: 'Waiting for QR...',
                        html: '<div class="py-4"><div class="spinner-border text-primary mb-3" role="status"></div><div>OpenWA QR endpoint is not ready yet.</div><div class="text-muted fs-8 mt-2">Attempt ' + (attempt + 1) + ' of 9</div></div>'
                    });
                    setTimeout(function() { pollQRCode(attempt + 1, restarted); }, 2000);
                    return;
                }

                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to retrieve QR code.';
                Swal.fire({
                    icon: 'error',
                    title: 'QR fetch failed',
                    html: '<div class="py-2 text-danger">' + message + '</div>',
                    confirmButtonText: 'OK',
                    width: 520
                }).then(function() { startMonitorAutoRefresh(); });
            });
    }

    function askRestartForQRCode(message) {
        Swal.fire({
            icon: 'warning',
            title: 'QR still not ready',
            html: '<div class="py-2 text-muted">' + message + '</div><div class="mt-3">Restart the session and try generating a new QR?</div>',
            showCancelButton: true,
            confirmButtonText: 'Restart and fetch QR',
            cancelButtonText: 'Cancel',
            width: 560
        }).then(function(result) {
            if (result.isConfirmed) {
                restartForNewQRCode();
            } else {
                startMonitorAutoRefresh();
            }
        });
    }

    function restartForNewQRCode() {
        Swal.fire({
            title: 'Restarting session...',
            html: '<div class="py-4"><div class="spinner-border text-primary mb-3" role="status"></div><div>Generating a fresh QR code. Please wait...</div></div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        });

        $.post(restartSessionUrl, { _token: '{{ csrf_token() }}' })
            .done(function() {
                setTimeout(function() { pollQRCode(0, true); }, 2000);
            })
            .fail(function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to restart session.';
                Swal.fire({
                    icon: 'error',
                    title: 'Restart failed',
                    html: '<div class="py-2 text-danger">' + message + '</div>',
                    confirmButtonText: 'OK',
                    width: 520
                }).then(function() { startMonitorAutoRefresh(); });
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

    function revokeWhatsAppSession() {
        Swal.fire({
            icon: 'warning',
            title: 'Revoke WhatsApp session?',
            html: '<div class="text-start">' +
                '<p class="mb-2">This will logout the current WhatsApp phone immediately.</p>' +
                '<p class="mb-2 text-danger fw-bold">Sending will stop until a new QR code is scanned.</p>' +
                '<p class="mb-0">Type <code>REVOKE</code> to continue.</p>' +
                '</div>',
            input: 'text',
            inputPlaceholder: 'REVOKE',
            showCancelButton: true,
            confirmButtonText: 'Revoke Session',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33',
            preConfirm: function(value) {
                if (String(value || '').trim() !== 'REVOKE') {
                    Swal.showValidationMessage('Type REVOKE exactly to continue.');
                    return false;
                }
                return value;
            }
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $('#monitor-revoke-btn').prop('disabled', true);
            Swal.fire({
                title: 'Revoking session...',
                html: '<div class="py-4"><div class="spinner-border text-danger mb-3" role="status"></div><div>Please wait. Do not close this page.</div></div>',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
            });

            $.post(revokeSessionUrl, { _token: '{{ csrf_token() }}', confirmation: 'REVOKE' })
                .done(function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Session revoked',
                            text: res.message || 'Scan the new QR code to connect another phone.',
                            confirmButtonText: 'Show QR'
                        }).then(function() {
                            fetchMonitorQR();
                            setTimeout(function() { location.reload(); }, 3000);
                        });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Revoke not completed', text: res.message || 'OpenWA did not revoke the session.' });
                    }
                })
                .fail(function(xhr) {
                    var res = xhr.responseJSON || {};
                    Swal.fire({
                        icon: res.blocked ? 'warning' : 'error',
                        title: res.blocked ? 'Queue active' : 'Revoke failed',
                        text: res.message || 'Failed to revoke WhatsApp session.'
                    });
                })
                .always(function() {
                    $('#monitor-revoke-btn').prop('disabled', false);
                });
        });
    }

    $(document).ready(function() {
        startMonitorAutoRefresh();
    });
</script>