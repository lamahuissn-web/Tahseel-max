<form method="post" action="{{ route('admin.client_add_invoice', $all_data->id) }}" enctype="multipart/form-data">
    @csrf
    <div class="row col-md-12 ">
        <div class="col-md-4">
            <label for="invoice_number" class="form-label">{{ trans('clients.invoice_number') }}</label>
            <div class="input-group flex-nowrap">
                <span class="input-group-text">{!! form_icon('number') !!}</span>
                <input type="text" class="form-control" name="invoice_number" id="invoice_number"
                    value="{{ old('invoice_number', $invoiceNumber) }}" readonly>
            </div>
        </div>


        <div class="col-md-4">
            <label for="invoice_type" class="form-label">{{ trans('clients.invoice_type') }}</label>
            <select class="form-control" name="invoice_type" id="invoice_type" required onchange="toggleSubscription()">
                <option value="service">{{ trans('clients.service') }}</option>
                <option value="subscription">{{ trans('clients.subscription_t') }}</option>
            </select>
        </div>

        <div class="col-md-4" style="display: none;" id="subscription_section">
            <label for="basic-url"class="form-label">{{ trans('clients.subscription') }}</label>
            <div class="input-group flex-nowrap ">
                <span class="input-group-text" id="basic-addon3">{!! form_icon('select1') !!}</i></span>
                <div class="overflow-hidden flex-grow-1">
                    <select class="form-select rounded-start-0" name="subscription_id" id="subscription_id"
                        onchange="get_price(this.value)" data-placeholder="{{ trans('clients.select') }}">
                        <option value="">{{ trans('clients.select') }}</option>
                        @foreach ($subscriptions as $item)
                            <option value="{{ $item->id }}"
                                {{ old('subscription_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @error('subscription_id')
                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div>


        <div class="col-md-4">
            <label for="amount" class="form-label">{{ trans('clients.amount') }}</label>
            <div class="input-group flex-nowrap">
                <span class="input-group-text">{!! form_icon('price') !!}</span>
                <input type="number" step="0.01" class="form-control" name="amount" id="amount"
                    value="{{ old('amount') }}" required min="1">
            </div>
            @error('amount')
                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div>

        {{-- <div class="col-md-4">
            <label for="remaining_amount" class="form-label">{{ trans('clients.remaining_amount') }}</label>
            <div class="input-group flex-nowrap">
                <span class="input-group-text">{!! form_icon('price') !!}</span>
                <input type="number" step="0.01" class="form-control" name="remaining_amount" id="remaining_amount"
                        value="{{ old('remaining_amount') }}">
            </div>
            @error('remaining_amount')
            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div> --}}

        <div class="col-md-10" style="margin-top: 10px;">
            <label for="notes" class="form-label fw-bold">{{ trans('clients.notes') }}</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"
                placeholder="{{ trans('clients.enter_notes') }}">{{ old('notes') }}</textarea>
            @error('notes')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>


        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" name="add" value="add" id="add_ezn" class="btn btn-success w-100">
                <i class="bi bi-save"></i> {{ trans('employees.SaveButton') }}
            </button>
        </div>
    </div>

</form>

@section('js')
    <script>
        $(document).ready(function() {
            toggleSubscription();
            setTimeout(function() {
                $("#subscription_id").trigger("change");
            }, 300);
        });

        function toggleSubscription() {
            let invoiceType = document.getElementById("invoice_type").value;
            let subscriptionSection = document.getElementById("subscription_section");
            let amountField = document.getElementById("amount");

            if (invoiceType === "subscription") {
                subscriptionSection.style.display = "block";
                amountField.readOnly = true;
                amountField.value = '';
            } else {
                subscriptionSection.style.display = "none";
                amountField.readOnly = false;
                amountField.value = '';
            }
        }
    </script>
    <script>
        function get_price(id) {
            if (!id) {
                document.getElementById("amount").value = "";
                return;
            }

            $.ajax({
                url: "{{ route('admin.get_price', ['id' => '__id__']) }}".replace('__id__', id),
                type: "get",
                dataType: "json",
                success: function(data) {
                    $('#amount').val(data.price || '<?= old('amount') ?>');
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching price:", status, error);
                    $('#amount').val('0');
                }
            });
        }
    </script>
@endsection
