<tr class="account-row level-{{ $account->level }}">
    <td>{{ $loop->iteration }}</td>
    <td>{{ $account->name }}</td>
    <td>{{ $account->parent ? $account->parent->name : 'N/A' }}</td>
    <td>{{ $account->level }}</td>
    <td>
        <a href="javascript:void(0);" onclick="viewTransactions({{ $account->id }})" class="text-primary"
            style="text-decoration: underline;">
            @if ($account->children->count() > 0)
                {{ $account->totalAmount() }}
            @else
                {{ $account->financial_transactions_sum_amount ?? 0 }}
            @endif
        </a>
    </td>
    {{-- <td>{{ $account->user ? $account->user->name : 'N/A' }}</td> --}}
    <td>{{ $account->admin ? $account->admin->name : 'N/A' }}</td>
    <td>
        @can('edit_account')
            <a data-bs-toggle="modal" data-bs-target="#modalAccounts" onclick="edit_account({{ $account->id }})"
                class="btn btn-sm btn-warning" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
        @endcan
        @can('delete_account')
            <a onclick="return confirm('Are You Sure To Delete?')" href="{{ route('admin.delete_account', $account->id) }}"
                class="btn btn-sm btn-danger">
                <i class="bi bi-trash"></i>
            </a>
        @endcan
    </td>
</tr>

@if ($account->children && $account->children->count() > 0)
    @foreach ($account->children as $child)
        @include('dashbord.accounts.partials.account_row', ['account' => $child])
    @endforeach
@endif
