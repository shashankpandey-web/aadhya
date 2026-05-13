@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <a href="javascript:void(0)" onclick="back()" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="full_name">{{ translate('Name') }} :-  {{ $Customers->full_name }} </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <?php 
                            $get_status = array('Open','Pending','In Progress','On Hold','Resolved','Closed','Reopened');
                        ?>
                        <form method="POST" action="{{ route('admin.customersupports.update') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ encrypt($get_customersupport['id']) }}">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                        <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                        <select id="status" name="status" class="form-select single-select"
                                            data-allow-clear="true">
                                            <?php foreach ($get_status as $key => $value) { ?>
                                                <option value="{{ $value }}" {{ getSelected($value, $get_customersupport['status']) }}>{{ $value }}</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="tf-icons bx bx-save me-1"></span>Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="full_name">{{ translate('Reason') }} :- </label><br>
                            {{ $get_customersupport->message }}
                        </div>
                    </div>
                </div>
                @if ($Customersupportimage->isNotEmpty())
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="col-form-label" for="full_name">{{ translate('Image') }} :- </label><br>
                                @foreach ($Customersupportimage as $key => $value)
                                    <?php if ($value->image != '') { ?>
                                        <img src="{{ url('uploads/customersupport', $value->image) }}" style="width: 200px;object-fit: cover;object-position: center;margin-right: 8px;" />
                                    <?php } ?>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
