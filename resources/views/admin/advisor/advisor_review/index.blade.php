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
                                <div class="form-group mb-3">
                                    <label class="col-form-label" for="filter_advisor_review_advisor">{{ translate('Advisor') }}</label>
                                    <select id="filter_advisor_review_advisor" name="filter_advisor_review_advisor" class="form-select single-select" data-allow-clear="true">
                                        <option value=""  > {{ translate('All') }}</option>
                                        <?php foreach ($Advisor as $key => $value) { ?>
                                            <option value="{{$value['id']}}" {{ getSelected($value['id'], $common['filter_advisor_review_advisor']) }}>{{$value['full_name']}}</option>
                                        <?php } ?>                                        
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="col-form-label" for="filter_advisor_review_customer">{{ translate('Customer') }}</label>
                                    <select id="filter_advisor_review_customer" name="filter_advisor_review_customer" class="form-select single-select" data-allow-clear="true">
                                        <option value=""  > {{ translate('All') }}</option>
                                        <?php foreach ($Customer as $key => $value) { ?>
                                            <option value="{{$value['id']}}" {{ getSelected($value['id'], $common['filter_advisor_review_customer']) }}>{{$value['full_name']}}</option>
                                        <?php } ?>                                        
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                    <select id="status" name="filter_advisor_review_status" class="form-select single-select" data-allow-clear="true">
                                        <option value="" {{ getSelected('Active', $common['filter_advisor_review_status']) }}> {{ translate('All') }}</option>
                                        <option value="Active" {{ getSelected('Active', $common['filter_advisor_review_status']) }}> {{ translate('Active') }}</option>
                                        <option value="Deactive" {{ getSelected('Deactive', $common['filter_advisor_review_status']) }}>{{ translate('Deactive') }}</option>
                                    </select>
                                </div>
                            </div>
                       
                            <div class="col-md-3 d-flex justify-content-left mt-4">
                                <div class="filter_button my-auto">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light m-r-10 fs-4" name="is_filter" value="1">
                                        <i class="fa fa-filter" aria-hidden="true"></i>
                                    </button>
                                    <button type="submit" class="btn btn-primary waves-effect waves-light m-r-10 fs-4" name="reset" value="1">
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
            <div class="">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Advisor Name') }}</th>
                            <th>{{ translate('Customer Name') }}</th>
                            <th>{{ translate('Rating') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Change Status') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($get_all_Advisorreviews->isNotEmpty())
                            @foreach ($Advisorreviews as $key => $value)
                                <tr>
                                    <td>{{ $get_all_Advisorreviews->firstItem() + $key }}</td>
                                    <td>{{ $value['advisor_name'] }}</td>
                                    <td>{{ $value['customer_name'] }}</td>
                                    <td>{{ $value['rating'] }}</td>
                                    <td>{!! checkStatus($value['status']) !!}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input advisor_review_change_status" type="checkbox"
                                                id="{{ $value['id'] }}"
                                                {{ $value['status'] == 'Active' ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.advisor.review_edit',encrypt($value['id']))}}"><i
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

                <div class="">
                    <div class="dataTables_paginate paging_simple_numbers mt-4">
                        {{ $get_all_Advisorreviews->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection
@section('script')
<script>
$(document).on('change', '.advisor_review_change_status', function() {
    showLoader()
    let _this = $(this);
    $.ajax({
        "type": "POST",
        "data": {
            id: _this.attr('id'),
            _token: "{{ csrf_token() }}",
        },
        url: "{{ route('admin.advisor.review_change_status') }}",
        success: function(response) {
            if (response?.status == 'Active') {
                _this.parent().parent().closest('tr').find('.badge').attr('class',
                    'badge bg-label-success')
                _this.parent().parent().closest('tr').find('.badge').text(response?.status)
            }
            if (response?.status == 'Deactive') {
                _this.parent().parent().closest('tr').find('.badge').attr('class',
                    'badge bg-label-danger')
                _this.parent().parent().closest('tr').find('.badge').text(response?.status)
            }
            setTimeout(() => {
                success_msg(response.message);
                Swal.fire({
                    title: 'Success!',
                    text: `Advisor Review is ${response?.status}`,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }, 2000);
        },
        error: function() {
            // Hide SweetAlert loader and show error message
            Swal.fire({
                title: 'Error!',
                text: 'There was an error fetching the data.',
                icon: 'error',
                confirmButtonText: 'Try Again'
            });
        }
    })
})
</script>
@endsection