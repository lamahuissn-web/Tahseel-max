

<table class="table table-striped custom-table">
    <thead>
    <tr style="background-color: #A879C6">
        <th style="text-align-last:left">{{ trans('members.current_subscriptions') }}:</th>
        @if (!empty($current_subscriptions))
            <td style="color: white">{{ $current_subscriptions->main_subscriptions->name }}</td>
            <th style="text-align-last:left">{{ trans('members.end_date') }}:</th>
            <td style="color: white;text-align-last:right">{{ $current_subscriptions->end_date }}</td>
            <th style="text-align-last:left">{{ trans('members.freezing_days') }}:</th>
            <td style="color: white">{{ $current_subscriptions->main_subscriptions->max_freezing_days }}</td>
        @else
            <td colspan="5" style="color: white; text-align:center">{{ trans('members.no_subscription_exists') }}</td>
        @endif
    </tr>
    </thead>
</table>

