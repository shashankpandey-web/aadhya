<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Tarot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class TarotController extends Controller
{
    public function index(Request $request)
    {
        $common = array();
        $common['title'] = 'Tarots';
        $common['main_menu'] = 'tarot';
        $common['submain_menu'] = 'tarot';
        $common['filter_tarot_title'] = '';
        $common['filter_tarot_status'] = '';

        if ($request->isMethod('post')) {
            $filterFields = ['filter_tarot_title', 'filter_tarot_status'];
            $common = updateSessionFilters($request, $filterFields, $common);
        }

        $tarots = Tarot::whereNull('is_delete')->orderBy('id', 'desc');

        if ($common['filter_tarot_title']) {
            $tarots->where('title', 'like', '%' . $common['filter_tarot_title'] . '%');
        }

        if ($common['filter_tarot_status']) {
            $tarots->where('status', $common['filter_tarot_status']);
        }

        $tarots = $tarots->paginate(config('adminconfig.records_per_page'));

        return view('admin.tarot.index', compact('common', 'tarots'));
    }

    public function store(Request $request, $id = '')
    {
        $common = array();
        $common = [
            'title' => 'Tarot',
            'main_menu' => 'tarot',
            'submain_menu' => 'tarot',
        ];

        $get_tarot = getTableColumn('tarot');
        
        if ($id != '') {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
            }
            $id = checkDecrypt($id);
            $get_tarot = Tarot::where('id', $id)->first();
            if (!$get_tarot) {
                return back()->withErrors(['error' => translate('Something went wrong')]);
            }
        }

        if ($request->isMethod('post')) {
            $req_fields = array();
            $req_fields['title'] = 'required';
            $req_fields['message'] = 'required';

            $errormsg = [
                'title' => translate('Title'),
                'message' => translate('Message'),
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
                $Tarot = Tarot::find($request->id);
            } else {
                $message = translate('Add Successfully');
                $status = 'success';
                $Tarot = new Tarot();
            }
            $Tarot->title = $request->title;
            $Tarot->message = $request->message;
            $Tarot->status = $request->status;
            $Tarot->save();

            return redirect()->route('admin.tarots')->withErrors([$status => $message]);
        }

        
        return view('admin.tarot.store', compact('common', 'get_tarot'));
    }

    public function delete($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
        }
        $id = checkDecrypt($id);
        $status = 'error';
        $message = translate('Something went wrong!');
        $get_tarot = Tarot::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_tarot) {
            $get_tarot->is_delete = 1;
            $get_tarot->save();
        }
        $status = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }
}
