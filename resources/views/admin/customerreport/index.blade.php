@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                    <form action="{{ route('admin.customerreports') }}" method="post" id="product_filter" class="">
                        @csrf
                        <div class="row gy-4 gy-sm-1">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="col-form-label" for="status">{{ translate('Reporter User') }}</label>
                                    <select id="status" name="filter_customerreport_customer_id" class="form-select single-select" data-allow-clear="true">
                                        <option value="">Select Reporter User</option>
                                        <?php foreach ($customers as $key => $value) { ?>
                                                <option value="{{ $value->id }}" {{ getSelected($value->id, $common['filter_customerreport_customer_id']) }}>{{ $value->full_name }}</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="col-form-label" for="status">{{ translate('Reported User') }}</label>
                                    <select id="status" name="filter_customerreport_to_customer_id" class="form-select single-select" data-allow-clear="true">
                                        <option value="">Select Reported User</option>
                                        <?php foreach ($customers as $key => $value) { ?>
                                                <option value="{{ $value->id }}" {{ getSelected($value->id, $common['filter_customerreport_to_customer_id']) }}>{{ $value->full_name }}</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                       
                            <div class="col-md-3 d-flex justify-content-left mt-4">
                                <div class="filter_button my-auto">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light m-r-10 fs-4" name="is_filter" value="1"><i class="fa fa-filter" aria-hidden="true"></i></button>
                                    <button type="submit" class="btn btn-primary waves-effect waves-light m-r-10 fs-4" name="reset" value="1"><i class="fa fa-undo" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
             <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>
            </div>

            {{-- table-responsive --}}
            <div class=" text-nowrap dataTables_wrapper ">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Reporter Name') }}</th>
                            <th>{{ translate('Reported Name') }}</th>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($customerreports->isNotEmpty())
                            @foreach ($customerreports as $key => $value)
                                <tr>
                                    <td>{{ $customerreports->firstItem() + $key }}</td>
                                    <td>{{ $value->customer_name }}</td>
                                    <td>{{ $value->to_customer_name }}</td>
                                    <td>{{ date('M d, Y',strtotime($value->created_at)) }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-vertical-rounded"></i></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.customerreports.view', encrypt($value['id'])) }}"><i
                                                        class="bx bx-eye me-1"></i> {{ translate('View') }}</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center">{{ translate('No record Found') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div class="row">
                    <div class="dataTables_paginate paging_simple_numbers mt-4">
                        {{ $customerreports->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection
