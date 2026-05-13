<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $common = array();
        $common['title'] = 'Category';
        $common['main_menu'] = 'category';
        $common['submain_menu'] = 'category';
        $common['filter_category_title'] = '';
        $common['filter_category_status'] = '';

        if ($request->isMethod('post')) {
            $filterFields = ['filter_category_title', 'filter_category_status'];
            $common = updateSessionFilters($request, $filterFields, $common);
        }

        $categories = Categories::whereNull('is_delete')->orderBy('id', 'desc');

        if ($common['filter_category_title']) {
            $categories->where('title', 'like', '%' . $common['filter_category_title'] . '%');
        }

        if ($common['filter_category_status']) {
            $categories->where('status', $common['filter_category_status']);
        }

        $categories = $categories->paginate(config('adminconfig.records_per_page'));

        return view('admin.category.index', compact('common', 'categories'));
    }

    public function store(Request $request, $id = '')
    {
        $common = array();
        $common = [
            'title' => 'Category',
            'main_menu' => 'category',
            'submain_menu' => 'category',
        ];

        $get_category = getTableColumn('categories');
        
        if ($id != '') {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
            }
            $id = checkDecrypt($id);
            $get_category = Categories::where('id', $id)->first();
            if (!$get_category) {
                return back()->withErrors(['error' => translate('Something went wrong')]);
            }
        }

        if ($request->isMethod('post')) {
            $req_fields = array();
            $req_fields['title'] = 'required';
            // $req_fields['parent']   = "required";

            $errormsg = [
                'title' => translate('Title'),
                'parent' => translate('Parent'),
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
                $Category = Categories::find($request->id);
            } else {
                $message = translate('Add Successfully');
                $status = 'success';
                $Category = new Categories();
            }

            if ($request->hasFile('image')) {
                $path = 'uploads/category';
                if ($id && $Category && $Category->image) {
                    $imagePath = image_upload($request->file('image'), $path, $Category->image);
                } else {
                    $imagePath = image_upload($request->file('image'), $path);
                }
                $Category->image = $imagePath;
            }
            $Category->title = $request->title;
            $Category->status = $request->status;
            $Category->save();

            return redirect()->route('admin.categories')->withErrors([$status => $message]);
        }

        
        return view('admin.category.store', compact('common', 'get_category'));
    }

    public function delete($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
        }
        $id = checkDecrypt($id);
        $status = 'error';
        $message = translate('Something went wrong!');
        $get_category = Categories::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_category) {
            $get_category->is_delete = 1;
            $get_category->save();
        }
        $status = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }
}
