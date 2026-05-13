<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Page;

class PagesController extends Controller
{
    public function index()
    {
        $common = array();
        $common['title'] = 'Pages';
        $common['main_menu']    = 'pages';
        $common['submain_menu'] = 'pages';
        $pages = Page::orderBy('id', 'asc')->paginate(config('adminconfig.records_per_page'));
        return view('admin.pages.index', compact('common', 'pages'));
    }

    
    public function store(Request $request, $id = ""){

        $common = array();
        $common['title']        = 'Pages';
        $common['main_menu']    = 'pages';
        $common['submain_menu'] = 'pages';
        if ($id != "") {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $Pages = Page::where('id', $id)->first();
            $message    = translate("Update Successfully");
            $status     = "success";
            if (!$Pages) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }else{
            return back()->withErrors(["error" => translate("Something went wrong")]);
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['title']         = "required";
            $req_fields['description']   = "required";
            $errormsg = [
                "title" => translate("Title"),
                "description" => translate("Description"),
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
            $Pages->title         = $request->title;
            $Pages->description   = $request->description;
            $Pages->save();
            return redirect()->route('admin.pages')->withErrors([$status => $message]);
        }
        return view('admin.pages.store', compact('common', 'Pages','id'));
    }

    
}