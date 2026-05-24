<div class="row">
    <div class="flex-lg-row-fluid ms-lg-15">
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
            role="tablist">
            <!--begin:::Tab item-->
            <li class="nav-item" role="presentation">

                <a href="{{route('admin.member_subscriptions',$one_data->memberId)}}" class="nav-link text-active-primary pb-4 ">{{trans('members.member_subscriptions')}}</a>

            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item" role="presentation">
                <a href="{{route('admin.inbody',$one_data->memberId)}}" class="nav-link text-active-primary pb-4 ">{{trans('members.inbody')}}</a>

            </li>

        </ul>
    </div>
</div>

