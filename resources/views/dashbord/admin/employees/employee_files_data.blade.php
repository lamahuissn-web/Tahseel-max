<div class="" style="margin-top: 30px">
    @if(isset($files_data) && !empty($files_data))
        <table id="table" class="example table table-bordered responsive nowrap text-center" cellspacing="0"
               width="70%">
            <thead>
            <tr class="greentd" style="background-color: lightgrey" >
                <th>{{trans('employees.hash') }}</th>
                <th>{{ trans('employees.file_name') }}</th>
                <th>{{ trans('employees.file_type') }}</th>
                <th>{{ trans('employees.attachment') }}</th>
                <th>{{ trans('employees.file_size') }}</th>
                <th>{{ trans('employees.publisher') }}</th>
                <th>{{ trans('employees.added_date') }}</th>
                <th>{{ trans('employees.added_time') }}</th>
                <th>{{ trans('employees.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @php
                $x = 1;
                $image = ['gif', 'Gif', 'ico', 'ICO', 'jpg', 'JPG', 'jpeg', 'JPEG', 'BNG', 'png', 'PNG', 'bmp', 'BMP'];
                $file = ['pdf', 'PDF', 'xls', 'xlsx', ',doc', 'docx', 'txt'];
            @endphp
            @foreach ($files_data as $morfaq)
                @php
                    $ext = pathinfo($morfaq->file, PATHINFO_EXTENSION);
                    $folder = Storage::disk('files');
                    $Destination = $folder->path($morfaq->file);
                    if(file_exists($Destination)) {
                        $size= formatFileSize($Destination);
                    }else{
                        $size =0;
                    }
                    ?>
                @endphp
                <tr>
                    <td>{{ $x++ }}</td>
                    <td>{{ $morfaq->file_name }}</td>
                    <td>
                        @php
                            $f_title = $morfaq->file_name ?? 'غير محدد';
                        @endphp
                        @if (in_array($ext, $image))
                            <i class="fas fa-image fa-1x" aria-hidden="true" title="{{ $f_title }}"></i>
                        @elseif (in_array($ext, $file))
                            <i class="fas fa-file-pdf fa-1x" aria-hidden="true" title="{{ $f_title }}"></i>
                        @endif
                    </td>
                    <td>
                        @if (in_array($ext, $image))
                            <a data-bs-toggle="modal" data-bs-target="#myModal-view-{{ $morfaq->id }}">
                                <i class="fa fa-eye fa-1x" title="{{ __('view_file') }}"></i>
                            </a>

                            <div class="modal fade" tabindex="-1" id="myModal-view-{{ $morfaq->id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3 class="modal-title">Modal title</h3>

                                            <!--begin::Close-->
                                            <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                                                <i class="ki-duotone ki-cross fs-1">&times;</i>
                                            </div>

                                        </div>

                                        <div class="modal-body">
                                            <img src="{{ asset('files/'.$morfaq->file) }}" width="100%" alt="">
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        @elseif (in_array($ext, $file))
                            <a data-bs-toggle="modal" data-bs-target="#myModal-pdf-{{ $morfaq->id }}">
                                <i class="fa fa-eye fa-1x" title="{{ __('view_file') }}"></i>
                            </a>

                            <div class="modal fade" tabindex="-1" id="myModal-pdf-{{ $morfaq->id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3 class="modal-title">Modal title</h3>

                                            <!--begin::Close-->
                                            <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                                                <i class="ki-duotone ki-cross fs-1">&times;</i>
                                            </div>

                                        </div>

                                        <div class="modal-body">
                                            <iframe src="{{ route('admin.employee_read_file',$morfaq->id) }}" style="width: 100%; height: 640px;" frameborder="0"></iframe>

                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @endif

                    </td>
                    <td class="fnt_center_blue">
                        {{ $size }}
                    </td>
                    <td class="fnt_center_blue">{{ $morfaq->publisher_n }}</td>
                    <td class="fnt_center_black">{{ \Illuminate\Support\Carbon::parse($morfaq->created_at)->format('Y-m-d') }}</td>
                    <td class="fnt_center_red">{{ \Illuminate\Support\Carbon::parse($morfaq->created_at)->format('H:i:s') }}</td>

                    @if(auth()->user()->can('download_employee_file') || auth()->user()->can('delete_employee_file'))
                        <td>
                            <div class="btn-group">
                                @can('download_employee_file')
                                    <a href="{{ route('admin.employee_download_file', $morfaq->id) }}" class="btn btn-sm btn-primary" title="{{ trans('employees.download') }}">
                                        <i class="bi bi-download"></i>
                                    </a>
                                @endcan
                                @can('delete_employee_file')
                                    <a href="{{ route('admin.employee_delete_file', $morfaq->id) }}" onclick="return confirm('Are You Sure To Delete?')" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                @endcan
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>





