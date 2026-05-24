<div class="container mt-4">
    <!-- Additional Subscriptions Section -->
    <h5 class="fw-bold">{{ trans('members.registration_subscriptions') }}</h5>
    <hr>

    @if($additional_subscriptions and !empty($additional_subscriptions) and count($additional_subscriptions) !=0)
        <div class="additional-subscriptions mb-4">
            <div class="row">
                @foreach($additional_subscriptions as $additionalsubscription)
                    <div class="col-md-4">
                        <div class="card draggable-zone" style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px; position: relative;">
                            <!-- Checkbox in Top-Right Corner -->
                            <div class="form-check" style="position: absolute; top: 10px; right: 10px;">
                                <input class="form-check-input" type="checkbox" name="subscriptions[]" id="subscription_{{ $additionalsubscription->id }}" value="{{ $additionalsubscription->id }}">
                            </div>
                            <!-- Card Content -->
                            <div class="card-body draggable">
                                <h6 class="card-title" style="font-weight: bold;">
                                    {{ $additionalsubscription->special_subscriptions->name }}
                                </h6>

                                <ul>
                                    <li>{{ trans('members.session_num') }}: {{ $additionalsubscription->special_subscriptions->duration ?? 0 }}</li>
                                    <li>{{ trans('members.session_attendance') }}: {{ is_countable($additionalsubscription->member_attendance) ? count($additionalsubscription->member_attendance) : 0 }}</li>
                                    <li>{{ trans('members.remain_session') }}:
                                        {{ ($additionalsubscription->special_subscriptions->duration ?? 0) - (is_countable($additionalsubscription->member_attendance) ? count($additionalsubscription->member_attendance) : 0) }}
                                    </li>
                                    <li>{{ trans('members.end_date') }}: {{ $additionalsubscription->end_date }}</li>
                                </ul>


                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="text-muted">{{ trans('members.no_additional_subscriptions') }}</p>
    @endif

<!-- Registered Subscriptions Section -->
    <h5 class="fw-bold">{{ trans('members.subscriptions_is_registered') }}</h5>
    <hr>

    @if($register_subscriptions and !empty($register_subscriptions) and count($register_subscriptions) !=0)
        <div class="registered-subscriptions mb-4">
            <div class="row">
                @php
                    $x = 1;
                @endphp
                @foreach($register_subscriptions as $registersubscription)
                    <div class="col-md-4">
                        <div class="card draggable-zone" style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px; position: relative;">
                            <!-- Card Content -->
                            <div class="card-body draggable" >
                                <h6 class="card-title" style="font-weight: bold;">
                                    {{$x++}} - {{ $registersubscription->additional_subscription->special_subscriptions->name }}
                                </h6>
                                <a href="{{route('admin.subscriptions.delete_member_attendance', $registersubscription->id)}}" class="btn btn-danger btn-sm" style="margin-top: 10px;" onclick="return confirm('{{ trans('members.are_you_sure') }}')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="text-muted">{{ trans('members.no_subscriptions_is_registered') }}</p>
    @endif
</div>

<!-- Submit Button Section -->
<div class="d-flex justify-content-end">
    <button type="submit" id="" class="btn btn-primary">
        <span class="indicator-label">{{trans('forms.save_btn')}}</span>
        <span class="indicator-progress">Please wait...
            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
        </span>
    </button>
</div>
<script>
    var containers = document.querySelectorAll(".min-h-200px draggable-zone");

    if (containers.length === 0) {
        return false;
    }

    var swappable = new Sortable.default(containers, {
        draggable: ".draggable",
        handle: ".draggable .draggable-handle",
        mirror: {
            //appendTo: selector,
            appendTo: "body",
            constrainDimensions: true
        }
    });
</script>
