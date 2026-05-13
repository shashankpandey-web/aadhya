<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $common = array();
        $common['title'] = 'Banners';
        $common['main_menu'] = 'banner';
        $common['submain_menu'] = 'banner';
        $banners = Banner::whereNull('is_delete')->orderBy('id', 'desc');
        $banners = $banners->paginate(config('adminconfig.records_per_page'));

        return view('admin.banner.index', compact('common', 'banners'));
    }

    public function store(Request $request, $id = '')
    {
        $common = array();
        $common = [
            'title' => 'Banner',
            'main_menu' => 'banner',
            'submain_menu' => 'banner',
        ];

        $get_banner = getTableColumn('banner');
        
        if ($id != '') {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
            }
            $id = checkDecrypt($id);
            $get_banner = Banner::where('id', $id)->first();
            if (!$get_banner) {
                return back()->withErrors(['error' => translate('Something went wrong')]);
            }
        }

        if ($request->isMethod('post')) {
            $req_fields = array();
            $req_fields['title'] = 'required';

            if ($request->id == '') {
                $req_fields['image'] = 'required';
            }
            
            $errormsg = [
                'title' => translate('Title'),
                'image' => translate('Image'),
            ];

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                $validation->errors()->add('error', 'Something is wrong with required field!');
                return back()->withErrors($validation)->withInput();
            }
            if ($request->id != '') {
                $message = translate('Update Successfully');
                $status = 'success';
                $Banner = Banner::find($request->id);
            } else {
                $message = translate('Add Successfully');
                $status = 'success';
                $Banner = new Banner();
            }

            if ($request->hasFile('image')) {
                $path = 'uploads/banner';
                if ($id && $Banner && $Banner->image) {
                    $imagePath = image_upload($request->file('image'), $path, $Banner->image);
                } else {
                    $imagePath = image_upload($request->file('image'), $path);
                }
                $Banner->image = $imagePath;
            }
            $Banner->title = $request->title;
            $Banner->status = $request->status;
            $Banner->save();

            return redirect()->route('admin.banners')->withErrors([$status => $message]);
        }

        
        return view('admin.banner.store', compact('common', 'get_banner'));
    }

    public function delete($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
        }
        $id = checkDecrypt($id);
        $status = 'error';
        $message = translate('Something went wrong!');
        $get_banner = Banner::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_banner) {
            $get_banner->is_delete = 1;
            $get_banner->save();
        }
        $status = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }
}
