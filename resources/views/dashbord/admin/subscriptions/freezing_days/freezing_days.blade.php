@if($freezing_days->isNotEmpty())
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>{{ trans('members.process_num') }}</th>
            <th>{{ trans('members.day') }}</th>
            <th>{{ trans('members.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($freezing_days as $index => $freezing_day)
            <tr id="freezing_day_{{ $freezing_day->id }}">
                <td>{{ $index + 1 }}</td>
                <td>{{ $freezing_day->members_subscriptions->process_num }}</td>
                <td>{{ $freezing_day->freezing_day }}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="deleteFreezingDay({{ $freezing_day->id }})">X</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>No freezing days available.</p>
@endif

