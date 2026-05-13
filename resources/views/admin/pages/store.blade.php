@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.pages') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}

                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.pages.store',encrypt($id)) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" name="title"
                                    type="text" value="{{ old('title', $Pages['title']) }}"
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

                        
                        <div class="col-12">
                            <label class="col-form-label" for="Description">{{ translate('Description') }}</label>
                            <textarea name="description"  id="" cols="" rows="4" class="form-control ckeditor">{{ old('description', $Pages['description']) }}</textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
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


