<div class="col-md-12">
    <div class="card" style="margin-right: 30px;margin-left: 30px; margin-top:-60px" >
        <div class="card-body" style="padding: 10px">
            <ul class="nav nav-pills nav-pills-custom mb-3">

                <li class="nav-item mb-3 me-3 me-lg-6">

                    <a href="{{route('admin.company_projects',$all_data->id)}}" style="background-color: powderblue;"
                       class="nav-link btn btn-outline btn-flex btn-color-muted
                     btn-active-color-danger flex-column overflow-hidden w-80px h-85px
                     pt-5 pb-2 {{ request()->routeIs('admin.company_projects') ? 'active' : '' }}" >
                        <div class="nav-icon mb-3">
                            <i class="fonticon-like-1 fs-1 p-0"></i>
                        </div>
                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">{{trans('company.projects')}}</span>
                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>

                    </a>

                </li>

            </ul>
        </div>
    </div>
</div>
