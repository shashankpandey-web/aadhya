<?php

$leftMenu = [];

/*======= Dashboard ==========*/

$leftMenu['dashboard']['title'] = translate('Dashboard');
$leftMenu['dashboard']['url'] = route('admin.dashboard');
$leftMenu['dashboard']['icon'] = 'tf-icons bx bx-home-circle';
$leftMenu['dashboard']['name'] = 'Dashboard';

$leftMenu['category']['title'] = translate('Category');
$leftMenu['category']['url'] = route('admin.categories');
$leftMenu['category']['icon'] = 'tf-icons bx bx-category';
$leftMenu['category']['name'] = 'Category';

$leftMenu['availabilities']['title'] = translate('Availabilities');
$leftMenu['availabilities']['url'] = route('admin.availabilities');
$leftMenu['availabilities']['icon'] = 'tf-icons bx bx-calendar';
$leftMenu['availabilities']['name'] = 'Availabilities';

$leftMenu['pages']['title'] = translate('Pages');
$leftMenu['pages']['url'] = route('admin.pages');
$leftMenu['pages']['icon'] = 'tf-icons bx bx-calendar';
$leftMenu['pages']['name'] = 'Pages';


$leftMenu['customers']['title'] = translate('Customers');
$leftMenu['customers']['url'] = 'javascript:void(0);';
$leftMenu['customers']['icon'] = 'menu-icon tf-icons bx bx-collection';

$leftSubMenu = [];
$leftSubMenu['customers']['title'] = translate('Customers');
$leftSubMenu['customers']['url'] = route('admin.customers');
$leftSubMenu['customers']['icon'] = 'tf-icons bx bx-user';
$leftSubMenu['customers']['name'] = 'Customers';

$leftSubMenu['customergroup']['title'] = translate('Customer Groups');
$leftSubMenu['customergroup']['url'] = route('admin.customergroups');
$leftSubMenu['customergroup']['icon'] = 'tf-icons bx bx-category';
$leftSubMenu['customergroup']['name'] = 'Customer Groups';

$leftMenu['customers']['submenu'] = $leftSubMenu;

/*======= Setting ==========*/

$leftMenu['advisors']['title'] = translate('Advisors');
$leftMenu['advisors']['url'] = 'javascript:void(0);';
$leftMenu['advisors']['icon'] = 'menu-icon tf-icons bx bx-collection';

$leftSubMenu = [];

$leftSubMenu['advisor_withdraw_request']['title'] = translate('Advisor Request');
$leftSubMenu['advisor_withdraw_request']['url'] = route('admin.advisor_withdraw_request');
$leftSubMenu['advisor_withdraw_request']['icon'] = 'tf-icons bx bx-user';
$leftSubMenu['advisor_withdraw_request']['name'] = translate('Advisor Request');

$leftSubMenu['advisor_list']['title'] = translate('Advisor List');
$leftSubMenu['advisor_list']['url'] = route('admin.advisor_list');
$leftSubMenu['advisor_list']['icon'] = 'tf-icons bx bx-user';
$leftSubMenu['advisor_list']['name'] = translate('Advisor List');

$leftSubMenu['advisor_review_list']['title'] = translate('Advisor Review');
$leftSubMenu['advisor_review_list']['url'] = route('admin.advisor.review_list');
$leftSubMenu['advisor_review_list']['icon'] = 'tf-icons bx bx-user';
$leftSubMenu['advisor_review_list']['name'] = translate('Advisor Review');

$leftSubMenu['advisorgroup']['title'] = translate('Advisor Groups');
$leftSubMenu['advisorgroup']['url'] = route('admin.advisorgroups');
$leftSubMenu['advisorgroup']['icon'] = 'tf-icons bx bx-category';
$leftSubMenu['advisorgroup']['name'] = 'Advisor Groups';

$leftMenu['advisors']['submenu'] = $leftSubMenu;

$leftMenu['settings']['title'] = translate('Settings');
$leftMenu['settings']['url'] = route('admin.settings');
$leftMenu['settings']['icon'] = 'tf-icons bx bx-user';
$leftMenu['settings']['name'] = translate('Settings');

$leftMenu['coupon']['title'] = translate('Coupons');
$leftMenu['coupon']['url'] = route('admin.coupon');
$leftMenu['coupon']['icon'] = 'tf-icons bx bx-category';
$leftMenu['coupon']['name'] = 'Coupons';


$leftMenu['sendnotification']['title'] = translate('Send Notifications');
$leftMenu['sendnotification']['url'] = route('admin.sendnotification');
$leftMenu['sendnotification']['icon'] = 'tf-icons bx bx-category';
$leftMenu['sendnotification']['name'] = 'Send Notifications';

$leftMenu['banner']['title'] = translate('Banner');
$leftMenu['banner']['url'] = route('admin.banners');
$leftMenu['banner']['icon'] = 'tf-icons bx bx-category';
$leftMenu['banner']['name'] = 'Banner';


$leftMenu['tarot']['title'] = translate('Tarot');
$leftMenu['tarot']['url'] = route('admin.tarots');
$leftMenu['tarot']['icon'] = 'tf-icons bx bx-category';
$leftMenu['tarot']['name'] = 'Tarot';


$leftMenu['customerreport']['title'] = translate('Customer Report');
$leftMenu['customerreport']['url'] = route('admin.customerreports');
$leftMenu['customerreport']['icon'] = 'tf-icons bx bx-category';
$leftMenu['customerreport']['name'] = 'Customer Report'; 

$leftMenu['customersupport']['title'] = translate('Customer Support');
$leftMenu['customersupport']['url'] = route('admin.customersupports');
$leftMenu['customersupport']['icon'] = 'tf-icons bx bx-category';
$leftMenu['customersupport']['name'] = 'Customer Support';

?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <div class="app-brand demo ">

        <a href="#" class="app-brand-link">

            <span class="app-brand-logo demo">
                <img style="height: 65px;" src="{{ asset('assets/images/logo.png') }}" />

            </span>



        </a>



        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">

            <i class="bx bx-chevron-left bx-sm align-middle"></i>

        </a>

    </div>



    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        <!-- Dashboards -->

        @foreach ($leftMenu as $key => $value)
            @php

                $hasSubMenu = 0;

            @endphp

            @if (isset($value['submenu']))
                @php

                    $hasSubMenu = 1;

                @endphp
            @endif



            {{-- menu-item active open --}}

            <li
                class="menu-item {{ $key == $common['main_menu'] ? 'active open' : '' }} @if ($hasSubMenu) pcoded-hasmenu @endif ">

                <a href="{{ $value['url'] }}" class="menu-link {{ $hasSubMenu ? ' menu-toggle' : '' }}">

                    <i class="menu-icon {{ $value['icon'] }}"></i>

                    <div data-i18n="Dashboards">{{ $value['title'] }}</div>

                </a>

                @isset($value['submenu'])
                    <ul class="menu-sub">

                        @php

                            $count = 1;

                        @endphp

                        @foreach ($value['submenu'] as $subKey => $subValue)
                            <li class="menu-item {{ $subKey == $common['submain_menu'] ? 'active' : '' }}">

                                <a href="{{ $subValue['url'] }}" class="menu-link">

                                    <div data-i18n="CRM">{{ $subValue['title'] }}</div>

                                </a>

                            </li>

                            @php

                                $count++;

                            @endphp
                        @endforeach

                    </ul>
                @endisset

            </li>
        @endforeach

    </ul>



</aside>
