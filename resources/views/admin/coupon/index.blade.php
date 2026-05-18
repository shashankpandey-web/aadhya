@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
             <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.coupon.add_edit') }}" class="dt-button create-new btn btn-primary float-end">
                    <span><i class="bx bx-plus me-sm-1"></i>
                        <span class="d-none d-sm-inline-block">{{ translate('Add New') }}</span></span>
                </a>

            </div>

            {{-- table-responsive --}}
            <div class=" text-nowrap dataTables_wrapper ">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Title') }}</th>
                            <th>{{ translate('Code') }}</th>
                            <th>{{ translate('Value') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($getCoupon->isNotEmpty())
                            @foreach ($getCoupon as $key => $value)
                                <tr>
                                    <td>{{ $getCoupon->firstItem() + $key }}</td>
                                    <td>
                                        {{ $value['title'] }}
                                        @if ($value['is_comeback_offer'])
                                            <span class="badge bg-label-warning ms-1">Comeback Offer</span>
                                        @endif
                                        @if ($value['is_birthday_offer'])
                                            <span class="badge bg-label-info ms-1">Birthday Offer</span>
                                        @endif
                                        @if ($value['is_first_order_offer'])
                                            <span class="badge bg-label-success ms-1">First Time User</span>
                                        @endif
                                    </td>
                                    <td>{{ $value['code'] }}</td>
                                    <td>{{ $value['value'] }}%</td>
                                    <td>{!! checkStatus($value['status']) !!}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-vertical-rounded"></i></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.coupon.add_edit', encrypt($value['id'])) }}"><i
                                                        class="bx bx-edit-alt me-1"></i> {{ translate('Edit') }}</a>
                                                <a class="dropdown-item confirm-delete" href="javascript:void(0);"
                                                    data-href="{{ route('admin.coupon.delete', encrypt($value['id'])) }}"><i
                                                        class="bx bx-trash me-1"></i>{{ translate('Delete') }} </a>
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
                        {{ $getCoupon->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection
