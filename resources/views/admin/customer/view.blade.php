@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <div>
                    @php
                        $imag_url = url('uploads/placeholder/dummy_image.png');
                        if ($get_customer->image != '') {
                            $imag_url = url('uploads/image', $get_customer->image);
                        }
                    @endphp
                    <img src="{{ $imag_url }}"
                        style="width: 70px;border-radius: 50%;height: 70px;object-fit: cover;object-position: center;margin-right: 8px;" />
                    <strong class="card-title f-18">
                        {{ $get_customer['full_name'] }}
                    </strong>
                </div>

                <a href="javascript:void(0)" onclick="back()" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}

                </a>
            </div>
            <div class="card-body">

                <div class="row">

                    <!-- Email -->
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="email">{{ translate('Email') }} :
                                {{ $get_customer->email }} </label>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="phone_number">{{ translate('Phone Number') }} :
                                {{ $get_customer['phone_number'] }} </label>
                        </div>
                    </div>


                    <!-- Date of birth -->
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="date_of_birth">{{ translate('Date of birth') }} :
                                {{ $get_customer['date_of_birth'] }} </label>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="status">{{ translate('Status') }} :
                                {{ $get_customer['status'] }} </label>
                        </div>
                    </div>

                    <!-- Country -->
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="country">{{ translate('Country') }} :
                                {{ $get_customer->country }} </label>
                        </div>
                    </div>

                    <!-- State -->
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="state">{{ translate('State') }} :
                                {{ $get_customer->state }} </label>
                        </div>
                    </div>

                    <!-- City -->
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="city">{{ translate('City') }} :
                                {{ $get_customer->city }} </label>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="location">{{ translate('Address') }} :
                                {{ $get_customer->location }} </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
