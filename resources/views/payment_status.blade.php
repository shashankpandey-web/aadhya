<!DOCTYPE html>

<html lang="en" class="light-style layout-wide  customizer-hide" dir="ltr" data-theme="theme-default"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Login | AADYA APP</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}">

    <!-- Helers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>


<style>
    .back-home {
        display: flex;
        gap: 20px;
    }

    .authentication-wrapper.authentication-basic .authentication-inner {
        max-width: 500px;
        position: relative;
    }

    .payemnt_page {
        text-align: center;
    }

    .authentication-wrapper.authentication-basic .authentication-inner:after {
        left: -250px;
    }
</style>

<body style="background: #fff;">


    <!-- Content -->
    <div class="container">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">

                <!-- Register Card -->

                <div class="container">
                    <div class="payemnt_page">
                        @if (isset($payment_data['payment_status']))
                            {{-- {{dd($payment_data['payment_status'])}} --}}
                            @switch($payment_data['payment_status'])
                                @case('FAILED')
                                    <img alt="" src="{{ asset('/assets/images/Failed.gif') }}" loading="lazy" />
                                @break

                                @case('PENDING')
                                    <img alt="" src="{{ asset('/assets/images/Pending.gif') }}" loading="lazy" />
                                @break

                                @case('NOT_ATTEMPTED')
                                @case('CANCELLED')

                                @case('USER_DROPPED')
                                @case('VOID')
                                    <img alt="" src="{{ asset('/assets/images/Cancel.gif') }}" loading="lazy" />
                                @break

                                @case('SUCCESS')
                                    <img alt="" src="{{ asset('/assets/images/successful_purchase.gif') }}"
                                        loading="lazy" />
                                @break

                                @default
                                    <p>Unknown payment status. Please contact support.</p>
                            @endswitch

                            <h3 class="payemnt_page_h3">
                                {{ $payment_data['message'] }}
                            </h3>
                        @endif



                        <div class="back-home">
                            <button name="back_to_home" id="back_to_home"
                                class="back_to_home btn btn-primary d-grid w-50" onclick="handleRedirect('back_home')">
                                Back To home
                            </button>
                            <button name="back_to_home" id="back_to_home"
                                class="back_to_home btn btn-primary d-grid w-50"
                                onclick="handleRedirect('booking_history')">
                                Booking History
                            </button>
                        </div>
                    </div>


                </div>

                <!-- Register Card -->

            </div>

        </div>

    </div>

    <script>
        function handleRedirect(btn) {

            window.FlutterChannel.postMessage(btn);
            // console.log("btn",btn)

        }
    </script>


















</body>



</html>
