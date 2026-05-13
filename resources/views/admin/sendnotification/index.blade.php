@extends('admin.layout.master')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow mb-4">
        <div class="card-header">
            <strong class="card-title f-18">{{ $common['title'] }}</strong>
            <a href="{{ route('admin.sendnotification.addEdit') }}" class="dt-button create-new btn btn-primary float-end">
                <span><i class="bx bx-plus me-sm-1"></i>
                    <span class="d-none d-sm-inline-block">{{ translate('Add New') }}</span></span>
            </a>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('Title') }}</th>
                        <th>{{ translate('Description') }}</th>
                        <th>{{ translate('Date') }}</th>
                        <th>{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @if ($get_sendnotifications->isNotEmpty())
                        @foreach ($get_sendnotifications as $key => $value)
                            <tr>
                                <td>{{ $get_sendnotifications->firstItem() + $key }}</td>
                                <td>{{ $value['title'] }}</td>
                                <td>{{ $value['description'] }}</td>
                                <td>{{date('Y-m-d',strtotime($value->created_at))}}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu"> 
                                            <a class="dropdown-item confirm-delete" href="javascript:void(0);" data-href="{{ route('admin.sendnotification.delete', encrypt($value['id'])) }}">
                                                <i class="bx bx-trash me-1"></i>
                                                {{ translate('Delete') }} 
                                            </a>
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
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="dataTables_paginate paging_simple_numbers mt-4 p-2">
                    {{ $get_sendnotifications->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection