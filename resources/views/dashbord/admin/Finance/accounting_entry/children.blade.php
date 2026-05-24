<ul>
    @foreach($children as $child)
        <li>
            {{ $child->name }}
            {{--<a href="{{ route('admin.store.categories.show', $child) }}">View</a>
            <a href="{{ route('admin.store.categories.edit', $child) }}">Edit</a>
            <a href="{{ route('admin.store.categories.destroy', $child) }}">Edit</a>
--}}
            @if($child->children->isNotEmpty())
                @include('dashbord.admin.Finance.accounts.children', ['children' => $child->children])
            @endif
        </li>
    @endforeach
</ul>
