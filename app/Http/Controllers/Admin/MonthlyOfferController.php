<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings;

class MonthlyOfferController extends Controller
{
    private $keys = [
        'enable_twice_monthly_sale',
        'monthly_sale_discount_percentage',
        'sale_period_1_start_day',
        'sale_period_1_end_day',
        'sale_period_2_start_day',
        'sale_period_2_end_day',
        'sale_custom_message',
    ];

    public function index(Request $request)
    {
        $common                 = [];
        $common['title']        = 'Monthly Offers';
        $common['main_menu']    = 'monthly_offers';
        $common['submain_menu'] = 'monthly_offers';

        $settings = [];
        foreach ($this->keys as $key) {
            $row = Settings::where('meta_title', $key)->first();
            $settings[$key] = $row ? $row->content : '';
        }

        if ($request->isMethod('post')) {
            $settings['enable_twice_monthly_sale']       = $request->has('enable_twice_monthly_sale') ? 'active' : 'inactive';
            $settings['monthly_sale_discount_percentage'] = $request->monthly_sale_discount_percentage ?? 0;
            $settings['sale_period_1_start_day']         = $request->sale_period_1_start_day ?? 1;
            $settings['sale_period_1_end_day']           = $request->sale_period_1_end_day ?? 7;
            $settings['sale_period_2_start_day']         = $request->sale_period_2_start_day ?? 15;
            $settings['sale_period_2_end_day']           = $request->sale_period_2_end_day ?? 21;
            $settings['sale_custom_message']             = $request->sale_custom_message ?? '';

            foreach ($settings as $key => $value) {
                Settings::where('meta_title', $key)->update(['content' => $value]);
            }

            return back()->withErrors(['success' => 'Monthly offer settings saved successfully.']);
        }

        return view('admin.monthly-offer.index', compact('common', 'settings'));
    }
}
