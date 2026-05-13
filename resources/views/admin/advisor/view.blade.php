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

                   


                    <!-- Screen Name -->
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="screen_name">{{ translate('Screen Name') }} :
                                {{ $get_customer->screen_name }} </label>
                        </div>
                    </div>

                    <!-- Service Name -->
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="service_name">{{ translate('Service Name') }} :
                                {{ $get_customer->service_name }} </label>
                        </div>
                    </div>
                    <!-- Paypal ID -->
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="paypal_id">{{ translate('Paypal ID') }} : </label>
                            <div>{{ $get_customer->paypal_id }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="gender">{{ translate('Gender') }} : {{ @$Customerdetail->gender }}</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="nationality">{{ translate('Nationality') }} : {{ @$Customerdetail->nationality }}</label>
                        </div>
                    </div>
                   
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="psychic_advisor">{{ translate('Psychic advisor') }} :</label>
                            <div>{{ $Customerdetail->psychic_advisor }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="psychic_line">{{ translate('Psychic line') }} : </label>
                            <div>{{ $Customerdetail->psychic_line }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="social_media_name">{{ translate('Social Media Name') }} :</label>
                            <div>{{ $Customerdetail->social_media_name }}</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12"><hr></div>
                </div>
                <div class="row">
                    <!-- About My Service -->
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="about_my_service">{{ translate('About my service') }} :
                            </label>
                            <div>{{ $get_customer->about_my_service }}</div>
                        </div>
                    </div>

                    <!-- About Me -->
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="about_me">{{ translate('About me') }} : </label>
                            <div>{{ $get_customer->about_me }}</div>
                        </div>
                    </div>

                    <!-- Ordering instructions -->
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="col-form-label"
                                for="ordering_instructions">{{ translate('Ordering instructions') }} : </label>
                            <div>{{ $get_customer->ordering_instructions }}</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12"><hr></div>
                </div>
                <div class="row">
                    <div class="col-md-6">                            
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="ordering_instructions">{{ translate('My Video') }} : </label>
                            <div>
                                <?php if( $get_customer->my_video != '' ){  $video_url = get_image_upload_s3($get_customer->my_video); ?>
                                    <video controls style="width: 100%; max-height: 400px; border: 1px solid #000;padding: 10px;">
                                        <source src="{{ $video_url }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="kyc_image">{{ translate("ID, Passport or Driver's License") }} :</label>
                            <?php if ($Customerdetail->kyc_image != '') { ?>
                                <div><img src="<?php echo url('uploads/customerdetail', $Customerdetail->kyc_image); ?>"  style="width: 100%;height: auto;max-height: 500px;object-fit: cover;object-position: center;border: 1px solid #000;padding: 10px;"></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12"><hr></div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="service_name">{{ translate('Categories') }} :  </label>
                            <div><?php if (count($customer_category_arr)>0) { echo implode(', ', $customer_category_arr); } ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="service_name">{{ translate('Availability') }} :  </label>
                            <div>
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ translate('Title') }}</th>
                                            <th>{{ translate('Charges') }}</th>
                                            <th>{{ translate('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        <?php foreach ($availability_arrs as $key => $value) { ?>
                                            <tr>
                                                <td>{{ $value['title'] }}</td>
                                                <td>{{ $value['charges'] }}</td>
                                                <td>{!! checkStatus($value['status']) !!}</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
@endsection
