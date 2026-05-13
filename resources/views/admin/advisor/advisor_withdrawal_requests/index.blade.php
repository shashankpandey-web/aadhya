@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-4">
            {{-- Uncomment and update the form as needed for filtering --}}
            {{-- <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">

                <form action="{{ route('admin.availabilities') }}" method="post" id="availability_filter" class="">
                    @csrf
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="col-form-label">{{ translate('Name') }}</label>
                                <input class="form-control" name="filter_availability_name" type="text" value="{{ $common['filter_availability_name'] }}" placeholder="{{ translate('Enter name') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                <select id="status" name="filter_availability_status" class="form-select single-select" data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $common['filter_availability_status']) }}>{{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $common['filter_availability_status']) }}>{{ translate('Deactive') }}</option>
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
            </div> --}}
        </div>

        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>
            </div>

            {{-- table-responsive --}}
            <div class="text-nowrap dataTables_wrapper">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Request Number') }}</th>
                            <th>{{ translate('Advisor Name') }}</th>
                            <th>{{ translate('Requested Amount') }}</th>
                            <th>{{ translate('Remaining Amount') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($getCustomerWallet->isNotEmpty())
                            @foreach ($getCustomerWallet as $key => $value)
                                <tr>
                                    <td>{{ $getCustomerWallet->firstItem() + $key }}</td>
                                    <td class="withdrawl-req" json="{{ json_encode($value) }}">{{ $value->request_number }}
                                    </td>
                                    <td class="recipient">{{ $value->full_name }}</td>
                                    <td class="request-amount">
                                        {{ $value->amount }}
                                    </td>
                                    <td>
                                        {{ $value->remaining_wallet_amount }}
                                    </td>
                                    <td>{!! checkStatus($value->withdrawal_status) !!}</td>
                                    <td>
                                        {{ date('Y-m-d H:i', strtotime($value->created_at)) }}
                                    </td>
                                    <td>
                                        @if ($value->withdrawal_status == 'SUCCESS')
                                            {!! checkStatus($value->withdrawal_status) !!}
                                        @else
                                            <!-- <button class="btn btn-outline-warning me-1 mb-1 payout-btn"
                                                type="button">Payout</button> -->
                                            <a class="btn btn-success dropdown-item confirm-Payout" href="javascript:void(0);"
                                                    data-href="{{ route('admin.withdrawal_request_payout_new', encrypt($value['id'])) }}">{{ translate('Payout') }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center">{{ translate('No record Found') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div class="row">
                    <div class="dataTables_paginate paging_simple_numbers mt-4">
                        {{ $getCustomerWallet->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
