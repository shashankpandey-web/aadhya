@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.banners') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}

                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.banners.add') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $get_banner['id'] }}">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }} <span
                                        class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" name="title"
                                    type="text" value="{{ old('title', $get_banner['title']) }}"
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
                            <div class="form-group mb-3 {{ $errors->has('image') ? 'is-invalid' : '' }}">
                                <label class="col-form-label">{{ translate('Image') }}</label>
                                <input 
                                    type="file" 
                                    name="image"
                                    class="form-control { $errors->has('image') ? 'form-control-danger' : '' }}"
                                    id="bs-validation-upload-file" onchange="loadFile(event,'cat_image')">
                                @error('image')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                                        </div>
                                    </div>
                                @enderror
                                <div class="show-image">
                                    <img class="user-img img-circle img-css"
                                        id="cat_image"
                                            src="{{ $get_banner['image'] != '' ? asset('/uploads/banner/'.$get_banner['image']) : asset('/uploads/placeholder/dummy_image.png') }}" width="80px;">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                <select id="status" name="status" class="form-select single-select"
                                    data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $get_banner['status']) }}>
                                        {{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $get_banner['status']) }}>
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
