@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                    <form action="" method="post" id="product_filter" class="">
                        @csrf
                        <div class="row gy-4 gy-sm-1">
                            <div class="col-md-3">
                                <div class="form-group mb-3 ">
                                    <label class="col-form-label">{{ translate('Title') }}</label>
                                    <input class="form-control" name="filter_title" type="text"
                                        value="{{ $common['filter_title'] }}" placeholder="{{ translate('Enter title') }}">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex justify-content-left mt-4">
                                <div class="filter_button my-auto">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light m-r-10 fs-4"
                                        name="is_filter" value="1">
                                        <i class="fa fa-filter" aria-hidden="true"></i>
                                    </button>
                                    <button type="submit" class="btn btn-primary waves-effect waves-light m-r-10 fs-4"
                                        name="reset" value="1">
                                        <i class="fa fa-undo" aria-hidden="true"></i>
                                    </button>
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
                            <th>{{ translate('Transaction Title') }}</th>
                            <th>{{ translate('Type') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($getAdvisorTransactionList->isNotEmpty())
                            @foreach ($getAdvisorTransactionList as $key => $value)
                                <tr>
                                    <td>{{ $getAdvisorTransactionList->firstItem() + $key }}</td>
                                    <td>{{ $value['transaction_title'] }}</td>
                                    <td><span
                                            class="badge bg-label-{{ $value['type'] == 'Credit' ? 'success' : 'danger' }} ">
                                            {{ $value['type'] }}
                                        </span>
                                    </td>
                                    <td>{{ $value['amount'] }}</td>
                                    <td>{{ $value['transaction_status'] }}</td>
                                    <td>{{ date('Y-m-d h:i', strtotime($value['created_at'])) }}</td>
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
                        {{ $getAdvisorTransactionList->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection
