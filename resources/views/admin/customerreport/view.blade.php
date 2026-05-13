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
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="full_name">{{ translate('Reporter Name') }} :-  {{ $Customers->full_name }} </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="full_name">{{ translate('Reported Name') }} :-  {{ $To_Customers->full_name }} </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="col-form-label" for="full_name">{{ translate('Reason') }} :- </label><br>
                            {{ $get_customerreport->reason }}
                        </div>
                    </div>
                </div>
                @if ($Customerreportimage->isNotEmpty())
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="col-form-label" for="full_name">{{ translate('Image') }} :- </label><br>
                                @foreach ($Customerreportimage as $key => $value)
                                    <?php if ($value->image != '') { ?>
                                        <img src="{{ url('uploads/customerreport', $value->image) }}" style="width: 200px;object-fit: cover;object-position: center;margin-right: 8px;" />
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
