@extends('dashbord.layouts.master')
@section('css')
    <link href="{{asset('assets/plugins/custom/jstree/jstree.bundle.css')}}" rel="stylesheet" type="text/css"/>

@endsection

@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('account.create')}}</h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">

                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">
                        {{trans('Toolbar.home')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.site')}}
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.account')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.finance.accounts.create')}}"
                   class="btn btn-icon btn-sm btn-success flex-shrink-0 ms-4">
                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                    <span class="svg-icon svg-icon-2">
													<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
														<rect opacity="0.5" x="11.364" y="20.364" width="16" height="2"
                                                              rx="1" transform="rotate(-90 11.364 20.364)"
                                                              fill="currentColor"/>
														<rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                                              fill="currentColor"/>
													</svg>
												</span>
                    <!--end::Svg Icon-->
                </a>
                <a href="{{route('admin.finance.accounts.index')}}"
                   class="btn btn-icon btn-sm btn-info flex-shrink-0 ms-4">
                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                    <span class="svg-icon svg-icon-2">
													<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
<path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z" fill="currentColor"/>
<path opacity="0.3"
      d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z"
      fill="currentColor"/>
</svg>
												</span>
                    <!--end::Svg Icon-->
                </a>
            </div>
            <!--end::Filter menu-->
            <!--begin::Secondary button-->
            <!--end::Secondary button-->
            <!--begin::Primary button-->
            <!--end::Primary button-->
        </div>
        <!--end::Actions-->
    </div>
    <!--end::Toolbar container-->
@endsection
@section('content')
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxxl">

        <div class="card card-flush">

            <div class="card-body pt-0">
                <div id="kt_docs_jstree_ajax"></div>

                {{-- <div id="kt_docs_jstree_basic">

                 <ul>
                     @foreach($categories as $category)
                         <li>
                             {{ $category->name }}
  <a href="{{ route('admin.finance.accounts.show', $category) }}">View</a>
                             <a href="{{ route('admin.finance.accounts.edit', $category) }}">Edit</a>
                             <a href="{{ route('admin.finance.accounts.destroy', $category) }}">Edit</a>


                             @if($category->children->isNotEmpty())
                                 @include('dashbord.admin.Finance.accounts.children', ['children' => $category->children])
                             @endif
                         </li>
                     @endforeach
                 </ul>
                 </div>--}}
            </div>
        </div>
    </div>
    <!-- Modal for Editing -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm">
                        <input type="hidden" id="editCategoryId">
                        <div class="form-group">
                            <label for="editCategoryNameEn">Name (English)</label>
                            <input type="text" class="form-control" id="editCategoryNameEn" required>
                        </div>
                        <div class="form-group">
                            <label for="editCategoryNameAr">Name (Arabic)</label>
                            <input type="text" class="form-control" id="editCategoryNameAr" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal --->
    <div class="modal fade" tabindex="-1" id="kt_modal_1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">{{trans('contactus.details')}}</h3>

                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                         aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body" id="load_div">


                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('js')
    <script src="{{asset("assets/plugins/custom/jstree/jstree.bundle.js")}}"></script>
    <script>
        /*
            $('#kt_docs_jstree_basic').jstree({
                "core" : {
                    "themes" : {
                        "responsive": false
                    }
                },

                "plugins": ["contextmenu"]

            });
        */


    </script>

    <script>
        $(function () {
            $('#kt_docs_jstree_ajax').jstree({
                'core': {
                    "themes": {
                        "responsive": false
                    },
                    "check_callback": true,
                    'data': {
                        'url': function (node) {
                            return node.id === '#' ? '{{ route("admin.finance.accounts.load_roots") }}' : '{{ route("admin.finance.accounts.load_child") }}';

                        },
                        'data': function (node) {
                            return {'id': node.id};
                        }
                    }
                },
                /* "state" : { "key" : "demo2" },
                 "plugins" : [ "contextmenu", "state", "types",'dnd' ],
 */
                "types": {
                    "default": {
                        "icon": "ki-solid ki-folder text-primary"
                    },
                    "file": {
                        "icon": "ki-solid ki-file  text-primary"
                    }
                },
                "state": {"key": "demo2"},
                "plugins": ["contextmenu", "state", "types"],
                'contextmenu': {
                    'items': function ($node) {
                        return {
                            'Edit': {
                                'label': 'Edit',
                                'action': function () {
                                    editCategory($node.id);
                                },
                            },
                            'Delete': {
                                'label': 'Delete',
                                'action': function () {
                                    if (confirm('Are you sure you want to delete this category?')) {
                                        deleteCategory($node.id);
                                    }
                                }
                            }
                        };
                    }
                }
            });

            function editCategory(id) {
                $.ajax({
                    url: '{{route('admin.finance.accounts.load_edit')}}',
                    type: 'GET',
                    data: {id: id},
                    success: function (data) {
                        $('#editCategoryId').val(data.id);
                        $('#editCategoryNameEn').val(data.name.en);
                        $('#editCategoryNameAr').val(data.name.ar);
                        $('#editCategoryModal').modal('show');
                    },
                    error: function (xhr) {
                        alert('Error loading category data.');
                    }
                });
            }

            function deleteCategory(id) {
                $.ajax({
                    url: '/categories/' + id,
                    type: 'DELETE',
                    data: {
                        '_token': '{{ csrf_token() }}'
                    },
                    success: function () {
                        $('#category-tree').jstree(true).refresh();
                    },
                    error: function (xhr) {
                        alert('Error deleting category.');
                    }
                });
            }

            $('#editCategoryForm').submit(function (event) {
                event.preventDefault();
                var id = $('#editCategoryId').val();
                var nameEn = $('#editCategoryNameEn').val();
                var nameAr = $('#editCategoryNameAr').val();
                $.ajax({
                    url: '/categories/' + id,
                    type: 'PUT',
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'name': {
                            'en': nameEn,
                            'ar': nameAr
                        }
                    },
                    success: function () {
                        $('#editCategoryModal').modal('hide');
                        $('#category-tree').jstree(true).refresh();
                    },
                    error: function (xhr) {
                        alert('Error updating category.');
                    }
                });
            });
        });
    </script>
@endsection
