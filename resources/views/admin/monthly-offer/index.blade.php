@extends('admin.layout.master')
@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Monthly Offers</h4>
            <p class="text-muted mb-0">Configure twice-monthly discount sale periods shown in the app.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <span class="tf-icons bx bx-chevrons-left me-1"></span>Back
        </a>
    </div>

    @if ($errors->has('success'))
        <div class="alert alert-success alert-dismissible mb-4" role="alert">
            {{ $errors->first('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->has('error'))
        <div class="alert alert-danger alert-dismissible mb-4" role="alert">
            {{ $errors->first('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.monthly_offers') }}">
        @csrf

        {{-- ── Enable toggle ───────────────────────────────────── --}}
        <div class="card shadow mb-4">
            <div class="card-body d-flex align-items-center justify-content-between py-3">
                <div>
                    <h6 class="mb-1">Enable Twice Monthly Sale</h6>
                    <small class="text-muted">When enabled, the sale banner and discount are shown in the app during the configured periods.</small>
                </div>
                <div class="form-check form-switch ms-4">
                    <input class="form-check-input" type="checkbox" id="enable_twice_monthly_sale"
                        name="enable_twice_monthly_sale"
                        {{ ($settings['enable_twice_monthly_sale'] ?? '') === 'active' ? 'checked' : '' }}
                        style="width:3rem; height:1.5rem; cursor:pointer;">
                </div>
            </div>
        </div>

        {{-- ── Discount & Message ───────────────────────────────── --}}
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="f-18">Offer Details</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Discount Percentage <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="monthly_sale_discount_percentage" class="form-control numberonly"
                                min="1" max="100" value="{{ old('monthly_sale_discount_percentage', $settings['monthly_sale_discount_percentage'] ?? 10) }}"
                                placeholder="e.g. 20">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Discount applied to all service prices during active sale periods.</div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Custom Sale Message</label>
                        <textarea name="sale_custom_message" class="form-control" rows="3"
                            placeholder="e.g. 🎉 Flash Sale! Get 20% off all readings this week.">{{ old('sale_custom_message', $settings['sale_custom_message'] ?? '') }}</textarea>
                        <div class="form-text">This message is displayed in the app during an active sale period.</div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Sale Periods ─────────────────────────────────────── --}}
        <div class="row g-4 mb-4">

            {{-- Period 1 --}}
            <div class="col-md-6">
                <div class="card shadow h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <span class="badge bg-primary">Period 1</span>
                        <strong>First Sale Window</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Runs from the <strong>start day</strong> to the <strong>end day</strong> of every month.<br>
                            <em>Example: 1st – 7th means the sale is active on days 1, 2, 3, 4, 5, 6, and 7.</em>
                        </p>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Start Day</label>
                                <input type="number" name="sale_period_1_start_day" class="form-control numberonly"
                                    min="1" max="31" value="{{ old('sale_period_1_start_day', $settings['sale_period_1_start_day'] ?? 1) }}"
                                    placeholder="1">
                            </div>
                            <div class="col-6">
                                <label class="form-label">End Day</label>
                                <input type="number" name="sale_period_1_end_day" class="form-control numberonly"
                                    min="1" max="31" value="{{ old('sale_period_1_end_day', $settings['sale_period_1_end_day'] ?? 7) }}"
                                    placeholder="7">
                            </div>
                        </div>

                        @php
                            $p1s = (int)($settings['sale_period_1_start_day'] ?? 1);
                            $p1e = (int)($settings['sale_period_1_end_day'] ?? 7);
                            $today = (int) date('j');
                            $p1active = $today >= $p1s && $today <= $p1e;
                        @endphp
                        <div class="mt-3">
                            @if ($p1active)
                                <span class="badge bg-success"><i class="bx bx-check-circle me-1"></i>Active today (day {{ $today }})</span>
                            @else
                                <span class="badge bg-label-secondary">Inactive today (day {{ $today }})</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Period 2 --}}
            <div class="col-md-6">
                <div class="card shadow h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <span class="badge bg-info">Period 2</span>
                        <strong>Second Sale Window</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Runs from the <strong>start day</strong> to the <strong>end day</strong> of every month.<br>
                            <em>Example: 15th – 21st means the sale is active on days 15 through 21.</em>
                        </p>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Start Day</label>
                                <input type="number" name="sale_period_2_start_day" class="form-control numberonly"
                                    min="1" max="31" value="{{ old('sale_period_2_start_day', $settings['sale_period_2_start_day'] ?? 15) }}"
                                    placeholder="15">
                            </div>
                            <div class="col-6">
                                <label class="form-label">End Day</label>
                                <input type="number" name="sale_period_2_end_day" class="form-control numberonly"
                                    min="1" max="31" value="{{ old('sale_period_2_end_day', $settings['sale_period_2_end_day'] ?? 21) }}"
                                    placeholder="21">
                            </div>
                        </div>

                        @php
                            $p2s = (int)($settings['sale_period_2_start_day'] ?? 15);
                            $p2e = (int)($settings['sale_period_2_end_day'] ?? 21);
                            $p2active = $today >= $p2s && $today <= $p2e;
                        @endphp
                        <div class="mt-3">
                            @if ($p2active)
                                <span class="badge bg-success"><i class="bx bx-check-circle me-1"></i>Active today (day {{ $today }})</span>
                            @else
                                <span class="badge bg-label-secondary">Inactive today (day {{ $today }})</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Live Preview ─────────────────────────────────────── --}}
        @php
            $isEnabled   = ($settings['enable_twice_monthly_sale'] ?? '') === 'active';
            $isSaleNow   = $isEnabled && ($p1active || $p2active);
            $discountPct = $settings['monthly_sale_discount_percentage'] ?? 0;
            $saleMsg     = $settings['sale_custom_message'] ?? '';
        @endphp
        <div class="card shadow mb-4 border-{{ $isSaleNow ? 'success' : 'secondary' }}">
            <div class="card-header d-flex align-items-center gap-2">
                <strong>Current Status</strong>
                @if ($isSaleNow)
                    <span class="badge bg-success ms-auto">SALE IS LIVE</span>
                @elseif ($isEnabled)
                    <span class="badge bg-warning ms-auto">Enabled — Not in sale window today</span>
                @else
                    <span class="badge bg-label-secondary ms-auto">Disabled</span>
                @endif
            </div>
            <div class="card-body">
                @if ($isSaleNow)
                    <p class="mb-1"><strong>Discount:</strong> {{ $discountPct }}% off all readings</p>
                    @if ($saleMsg)
                        <p class="mb-0"><strong>Message shown in app:</strong> {{ $saleMsg }}</p>
                    @endif
                @else
                    <p class="text-muted mb-0">No sale is currently active. The app will show normal pricing.</p>
                @endif
            </div>
        </div>

        {{-- Save button --}}
        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <span class="tf-icons bx bx-save me-1"></span>Save Settings
            </button>
        </div>

    </form>
</div>

@endsection
