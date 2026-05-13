@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.advisor.review_list') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}

                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.advisor.review_edit') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $Advisorreviews['id'] }}">
                    <div class="row">                       
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="col-form-label" for="status">{{ translate('Advisor Name') }}</label>
                                {{$advisor_name}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="col-form-label" for="status">{{ translate('Customer Name') }}</label>
                                {{$customer_name}}
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('rating') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="rating">{{ translate('Rating') }}</label>
                                <select id="rating" name="rating" class="form-select" data-allow-clear="true">
                                    <?php for ($i = 5; $i >= 1; $i--) {  ?>
                                        <option value="{{$i}}" {{ getSelected($i, $Advisorreviews['rating']) }}>{{ $i }}</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                <select id="status" name="status" class="form-select single-select"
                                    data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $Advisorreviews['status']) }}>
                                        {{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $Advisorreviews['status']) }}>
                                        {{ translate('Deactive') }}</option>

                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">                        
                        <div class="col-md-12">
                            <label class="col-form-label" for="rating_text">{{ translate('Rating text') }} <span class="mandatory cls">*</span></label>
                            <textarea name="rating_text"  id="" cols="" rows="4" class="form-control {{ $errors->has('Rating text') ? 'is-invalid' : '' }}">{{ old('rating_text', $Advisorreviews['rating_text']) }}</textarea>
                            @error('rating_text')
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                                    </div>
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="row" style="margin-top: 50px;">                      
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
