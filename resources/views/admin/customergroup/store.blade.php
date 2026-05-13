@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">

            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.customergroups') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}

                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.customergroup.add') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $get_customergroup['id'] }}">

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }} <span
                                        class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" name="title"
                                    type="text" value="{{ old('title', $get_customergroup['title']) }}"
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
                            <?php 
                                /*if( $get_customergroup['customer_id'] != ''){
                                    $get_customer_ids = json_decode($get_customergroup['customer_id'], true);
                                    echo "<pre>";
                                    print_r($get_customer_ids);
                                    echo "</pre>";
                                }*/
                            ?>
                            <div class="form-group mb-3 {{ $errors->has('customer_id') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="customer_id">{{ translate('Customers') }} <span
                                        class="mandatory cls">*</span></label>
                                <select id="customer_id" name="customer_id[]" class="form-select single-select" multiple data-allow-clear="true">
                                    @foreach( $get_customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                            <?php 
                                                if( $get_customergroup['customer_id'] != ''){
                                                    $get_customer_ids = json_decode($get_customergroup['customer_id'], true);
                                                    if( in_array($customer->id, $get_customer_ids)){
                                                        echo "selected";
                                                    } 
                                                }else{
                                                    //echo getSelected($customer->id, $get_customergroup['customer_id']);
                                                }
                                            ?> 
                                            > 
                                            {{ $customer->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
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
                                    <option value="Active" {{ getSelected('Active', $get_customergroup['status']) }}>
                                        {{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $get_customergroup['status']) }}>
                                        {{ translate('Deactive') }}</option>

                                </select>
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
