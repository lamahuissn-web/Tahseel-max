<option value="">{{trans('select')}}</option>
@foreach($companies as $item)
    <option value="{{$item->id}}">{{$item->name}}</option>
@endforeach
