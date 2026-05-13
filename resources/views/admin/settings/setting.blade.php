@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}

                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        @foreach ($get_settings as $key => $value)
                            @if (get_setting_data($key, 'type') == 'file')
                                <div class="col-md-4">
                                    <div class="form-group mb-3 {{ $errors->has($key) ? 'has-danger' : '' }}">
                                        <label class="col-form-label">{!! html_entity_decode(get_setting_data($key, 'title')) !!} </label>
                                        <input type="file"
                                            class="form-control {{ $errors->has($key) ? 'form-control-danger' : '' }}"
                                            onchange="loadFile(event,'{{ $key }}')" name="{{ $key }}"
                                            type="file">
                                        @error($key)
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- placeholder --}}
                                    @php
                                        $extension_arr = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'];
                                        $ext = pathinfo($get_settings[$key], PATHINFO_EXTENSION);
                                        $image_ = url('assets/uploads/file-upload.png');
                                        if (in_array($ext, $extension_arr)) {
                                            $image_ = url('assets/uploads/setting', $get_settings[$key]);
                                        }
                                    @endphp
                                    <div class="media-left">
                                        <img class="user-img  img-css" style="height:100px" id="{{ $key }}"
                                            src="{{ $get_settings[$key] != '' ? $image_ : asset('assets/uploads/placeholder/placeholder.png') }}">
                                    </div>
                                </div>
                            @elseif (get_setting_data($key, 'type') == 'text' || get_setting_data($key, 'type') == 'number')
                                <div class="col-md-4">
                                    <div class="form-group mb-3 {{ $errors->has('email') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">{!! html_entity_decode(get_setting_data($key, 'title')) !!}</label>
                                        <input type="text"
                                            class="form-control {{ $errors->has($key) ? 'form-control-danger' : '' }} {{ get_setting_data($key, 'type') == 'number' ? 'numberonly' : '' }}"
                                            name="{{ $key }}" id="name" placeholder="{!! strip_tags(get_setting_data($key, 'title')) !!}"
                                            name="{{ $key }}" value="{{ old($key, $get_settings[$key]) }}">
                                        @error($key)
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            @elseif (get_setting_data($key, 'type') == 'textarea')
                                <div class="col-md-12">
                                    <div class="form-group mb-3 {{ $errors->has('email') ? 'has-danger' : '' }}">
                                        <label class="col-form-label" for="description">{!! html_entity_decode(get_setting_data($key, 'title')) !!}</label>
                                        <textarea class="form-control input_type_text_tags {{ $errors->has($key) ? 'is-invalid' : '' }}" id="footer_text"
                                            placeholder="{!! strip_tags(get_setting_data($key, 'title')) !!}" name="{{ $key }}">{{ old($key, $get_settings[$key]) }}</textarea>
                                        @error($key)
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            @elseif (get_setting_data($key, 'type') == 'checkbox')
                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" id="{{ $key }}" type="checkbox"
                                            {{ $get_settings[$key] == 'active' ? 'checked' : '' }}>
                                        <input type="hidden" value="{{ $get_settings[$key] }}" name="{{ $key }}"
                                            id="inp_social">
                                        <label class="form-check-label" for="{{ $key }}">Social Icon
                                            Status</label>
                                    </div>
                                </div>
                            @endif
                        @endforeach



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
