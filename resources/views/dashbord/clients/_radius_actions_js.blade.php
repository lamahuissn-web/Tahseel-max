<script>
/**
 * Get the current locale prefix from the URL
 */
function getLocalePrefix() {
    var path = window.location.pathname;
    var match = path.match(/^\/(en|ar)\//);
    return match ? match[1] : 'en';
}


/**
 * RADIUS Actions for Client Management
 * Disconnect, Toggle, Change Speed, Schedule Stop
 */

function radiusDisconnect(clientId) {
    Swal.fire({
        title: '{{ trans("clients.confirm_disconnect") }}',
        text: '{{ trans("clients.confirm_disconnect_text") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ trans("clients.yes_disconnect") }}',
        cancelButtonText: '{{ trans("clients.cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            var btn = $('button[onclick*="radiusDisconnect(' + clientId + ')"]');
            var original = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: '/' + getLocalePrefix() + '/admin/clients/' + clientId + '/disconnect',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    Swal.fire({
                        icon: res.success ? 'success' : 'error',
                        title: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    if (res.success) setTimeout(function() { location.reload(); }, 1500);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ trans("forms.error") }}',
                        text: '{{ trans("clients.disconnect_failed") }}',
                        timer: 3000,
                        showConfirmButton: false
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).html(original);
                }
            });
        }
    });
}

function radiusToggle(clientId) {
    var btn = $('button[onclick*="radiusToggle(' + clientId + ')"]');
    var original = btn.html();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
        url: '/' + getLocalePrefix() + '/admin/clients/' + clientId + '/toggle-radius',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            var statusIcon = res.status === 'enabled' ? 'success' : 'warning';
            Swal.fire({
                icon: res.success ? statusIcon : 'error',
                title: res.message,
                timer: 2000,
                showConfirmButton: false
            });
            if (res.success) setTimeout(function() { location.reload(); }, 1500);
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: '{{ trans("forms.error") }}',
                text: '{{ trans("clients.toggle_failed") }}',
                timer: 3000,
                showConfirmButton: false
            });
        },
        complete: function() {
            btn.prop('disabled', false).html(original);
        }
    });
}

function radiusChangeSpeed(clientId, speed) {
    if (!speed) return;
    Swal.fire({
        title: 'تأكيد تغيير السرعة',
        text: 'هل تريد تغيير السرعة إلى ' + speed + '؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'نعم، تغيير',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/' + getLocalePrefix() + '/admin/clients/' + clientId + '/change-radius-speed',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    speed: speed
                },
                success: function(res) {
                    Swal.fire({
                        icon: res.success ? 'success' : 'error',
                        title: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    if (res.success) setTimeout(function() { location.reload(); }, 1500);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ trans("forms.error") }}',
                        text: '{{ trans("clients.speed_change_failed") }}',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            });
        }
    });
}

function radiusScheduleStop(clientId) {
    var today = new Date().toISOString().split('T')[0];
    Swal.fire({
        title: '{{ trans("clients.schedule_stop") }}',
        html: '<input type="date" id="stopDateInput" class="form-control" min="' + today + '">',
        showCancelButton: true,
        confirmButtonColor: '#6c757d',
        cancelButtonColor: '#dc3545',
        confirmButtonText: '{{ trans("clients.schedule") }}',
        cancelButtonText: '{{ trans("clients.cancel") }}',
        preConfirm: function() {
            var date = document.getElementById('stopDateInput').value;
            if (!date) {
                Swal.showValidationMessage('{{ trans("clients.stop_date_required") }}');
                return false;
            }
            return date;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            var btn = $('button[onclick*="radiusScheduleStop(' + clientId + ')"]');
            var original = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: '/' + getLocalePrefix() + '/admin/clients/' + clientId + '/schedule-radius-stop',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    stop_date: result.value
                },
                success: function(res) {
                    Swal.fire({
                        icon: res.success ? 'success' : 'error',
                        title: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    if (res.success) setTimeout(function() { location.reload(); }, 1500);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ trans("forms.error") }}',
                        text: '{{ trans("clients.schedule_failed") }}',
                        timer: 3000,
                        showConfirmButton: false
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).html(original);
                }
            });
        }
    });
}

/* Quick Panel handler for data attributes */
$(document).ready(function() {
    $(document).on('click', '[data-radius-action]', function() {
        var action = $(this).data('radius-action');
        var clientId = $(this).data('client-id');
        if (action === 'disconnect') radiusDisconnect(clientId);
        else if (action === 'toggle') radiusToggle(clientId);
        else if (action === 'change-speed') radiusChangeSpeed(clientId);
        else if (action === 'schedule-stop') radiusScheduleStop(clientId);
    });
});
</script>