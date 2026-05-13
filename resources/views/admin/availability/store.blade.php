@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>
                <a href="{{ route('admin.availabilities') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.availabilities.add', $common['id']) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $availability->id ?? '' }}">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="col-form-label">{{ translate('Title') }} <span
                                        class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" name="title"
                                    type="text" value="{{ old('title', $availability->title ?? '') }}"
                                    placeholder="{{ translate('Enter Title') }}">
                                @error('title')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}</div>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="col-form-label">{{ translate('Sub Title') }}</label>
                                <input class="form-control {{ $errors->has('sub_title') ? 'is-invalid' : '' }}"
                                    name="sub_title" type="text"
                                    value="{{ old('sub_title', $availability->sub_title ?? '') }}"
                                    placeholder="{{ translate('Enter Sub Title') }}">
                                @error('sub_title')
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
                                <input type="file" name="image"
                                    class="form-control {{ $errors->has('image') ? 'form-control-danger' : '' }}"
                                    id="bs-validation-upload-file" onchange="loadFile(event,'avail_image')">
                                <div class="show-image">
                                    <img class="user-img img-circle img-css" id="avail_image"
                                        src="{{ isset($availability) && $availability->image ? asset('/uploads/availability/' . $availability->image) : asset('/uploads/placeholder/dummy_image.png') }}"
                                        width="80px;">
                                </div>


                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                <select id="status" name="status" class="form-select single-select"
                                    data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $availability->status ?? '') }}>
                                        {{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $availability->status ?? '') }}>
                                        {{ translate('Deactive') }}</option>
                                </select>
                                @error('status')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationStatus" data-validator="notEmpty">{{ $message }}
                                        </div>
                                    </div>
                                @enderror
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
@endsection
