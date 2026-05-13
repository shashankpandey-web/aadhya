@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.sendnotification') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.sendnotification.addEdit') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }}<span class="mandatory cls"
                                        style="color:red; font-size:15px">*</span></label>
                                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" name="title"
                                    type="text" value="{{ old('title') }}"
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
                                <label class="col-form-label">{{ translate('Coupon') }}</label>
                                <select id="coupon_id" name="coupon" class="form-select single-select" data-allow-clear="true">
                                    <option value="">Select Coupon</option>
                                    <?php foreach ($Coupon as $key => $value) { ?>
                                        <option value="{{$value->id}}">{{$value->title}}</option>
                                    <?php } ?>
                                </select>
                                @error('coupon_id')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                                        </div>
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('type') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="type">{{ translate('Customer/Advisor') }}</label>
                                <select id="type" name="type" class="form-select single-select"
                                    data-allow-clear="true">
                                    <option value="">All</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Advisor">Advisor</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="customer_advisor_box">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Description') }}</label>
                                <textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" placeholder="{{ translate('Enter Description') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                                        </div>
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <span class="tf-icons bx bx-save me-1"></span>Send
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script type="text/javascript">
    $(document).ready(function () { 
        $(document).on("change", '#type', function() {
            var val = $(this).val();
            if (val!='') {
                $.ajax({
                    url: "{{ route('admin.sendnotification.checktype') }}",
                    type: 'post',
                    data: {
                        'val': val,
                        "_token": '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#customer_advisor_box').html(response);
                        $("#customer_advisor_group").select2();
                    }
                });
            }else{
                $('#customer_advisor_box').html('');
            }
        });
    });
</script>
@endsection