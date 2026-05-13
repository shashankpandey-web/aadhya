@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
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
                            <th>{{ translate('Title') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($pages->isNotEmpty())
                            @foreach ($pages as $key => $value)
                                <tr>
                                    <td>{{ $pages->firstItem() + $key }}</td>
                                    <td>{{ $value['title'] }}</td>
                                    
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-vertical-rounded"></i></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.pages.store', encrypt($value['id'])) }}"><i
                                                        class="bx bx-edit-alt me-1"></i> {{ translate('Edit') }}</a>
                                                
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
                        {{ $pages->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection
