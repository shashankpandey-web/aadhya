@extends('admin.layout.master')
@section('content')
        
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.coupon') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}

                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.coupon.add_edit',$CouponID) }}" enctype="multipart/form-data" id="addFrom">
                    @csrf
                   
                    <div class="row">

                        
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" name="title"
                                    type="text" value="{{ old('title', $Coupon['title']) }}"
                                    placeholder="{{ translate('Enter Title') }}">
                                @error('title')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                                        </div>
                                    </div>
                                @enderror

                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Code') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}" name="code"
                                    type="text" value="{{ old('code', $Coupon['code']) }}"
                                    placeholder="{{ translate('Enter Code') }}">
                                @error('code')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                                        </div>
                                    </div>
                                @enderror

                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Value') }} (%) </label>
                                <input class="form-control {{ $errors->has('value') ? 'is-invalid' : '' }}" name="value"
                                    type="text" value="{{ old('value', $Coupon['value']) }}"
                                    placeholder="{{ translate('Enter Value') }}">
                                @error('value')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                                        </div>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('type') ? 'is-invalid' : '' }}">
                                <label class="col-form-label">{{ translate('Type') }} <span class="mandatory cls">*</span></label>
                                <select id="type" name="type" class="form-select single-select">
                                    <option value="flat" {{ getSelected('flat', $Coupon['type'] ?? 'flat') }}>{{ translate('Flat') }}</option>
                                    <option value="percentage" {{ getSelected('percentage', $Coupon['type'] ?? 'flat') }}>{{ translate('Percentage') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Maximum Discount') }}</label>
                                <input class="form-control {{ $errors->has('maximum_amount') ? 'is-invalid' : '' }}" name="maximum_amount"
                                    type="text" value="{{ old('maximum_amount', $Coupon['maximum_amount']) }}"
                                    placeholder="{{ translate('Enter Maximum Discount') }}">
                                @error('maximum_amount')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                                        </div>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Usage limit per coupon') }}</label>
                                <input class="form-control {{ $errors->has('usage_limit_per_coupon') ? 'is-invalid' : '' }}" name="usage_limit_per_coupon"
                                    type="number" value="{{ old('usage_limit_per_coupon', $Coupon['usage_limit_per_coupon']) }}"
                                    placeholder="{{ translate('Enter usage limit per coupon') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Usage limit per user') }}</label>
                                <input class="form-control {{ $errors->has('usage_limit_per_user') ? 'is-invalid' : '' }}" name="usage_limit_per_user"
                                    type="number" value="{{ old('usage_limit_per_user', $Coupon['usage_limit_per_user']) }}"
                                    placeholder="{{ translate('Enter usage limit per user') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Start Date') }}</label>
                                <input class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}" name="start_date"
                                    type="text" value="{{ old('start_date', $Coupon['start_date']) }}"
                                    placeholder="{{ translate('Enter Start Date') }}" id="start_date">
                                @error('start_date')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                                        </div>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Expiry Date') }}</label>
                                <input class="form-control {{ $errors->has('expiry_date') ? 'is-invalid' : '' }}" name="expiry_date"
                                    type="text" value="{{ old('expiry_date', $Coupon['expiry_date']) }}"
                                    placeholder="{{ translate('Enter Expiry Date') }}" id="expiry_date">
                                @error('expiry_date')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                                        </div>
                                    </div>
                                @enderror
                            </div>
                        </div>
                       
                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                <select id="status" name="status" class="form-select single-select"
                                    data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $Coupon['status']) }}>
                                        {{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $Coupon['status']) }}>
                                        {{ translate('Deactive') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="col-form-label d-block">{{ translate('Offer Flags') }}</label>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="checkbox" id="is_comeback_offer" name="is_comeback_offer" value="1"
                                        {{ old('is_comeback_offer', $Coupon['is_comeback_offer'] ?? 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_comeback_offer">{{ translate('Comeback Offer') }}</label>
                                </div>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="checkbox" id="is_birthday_offer" name="is_birthday_offer" value="1"
                                        {{ old('is_birthday_offer', $Coupon['is_birthday_offer'] ?? 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_birthday_offer">{{ translate('Birthday Offer') }}</label>
                                </div>
                                <div class="form-text text-muted">Enable to use this coupon automatically in scheduled jobs.</div>
                            </div>
                        </div>

                        <div class="col-md-10"></div>
                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-primary">
                                <span class="tf-icons bx bx-save me-1"></span>Save
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
    </div>
@endsection
@section('script')
<script type="text/javascript">
    $(document).ready(function () { 
        $(document).on("change", '#type', function() {
            var val = $(this).val();
            if (val=='Fixed') {
                $('.currency_symbol').show();
            }else{
                $('.currency_symbol').hide();
            }
        });
    });
</script>
<script type="text/javascript">
const start_date = flatpickr("#start_date", {
  dateFormat: 'Y-m-d',
  onChange: function(sel_date, date_str) {
    expiry_date.set("minDate", date_str);
  }
});

const expiry_date = flatpickr("#expiry_date", {
  dateFormat: 'Y-m-d'
});
</script>

<script type="text/javascript">
    $(document).on("submit", "#addFrom", function(e) {
        jQuery('#common_loader').show();
    });
</script>

@endsection