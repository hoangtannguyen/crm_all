<?php

namespace App\Http\Controllers\backends;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Requests;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class RequestController extends Controller {

    public function index(Request $request){
        $user = Auth::user();
        $status = isset($request->status) ? $request->status : '';
        if($user->can('requests.read')){
            if($status != '') $list_request = Requests::where('status',$status)->latest()->get();
            else $list_request = Requests::latest()->get();
        }else{
            if($status != '') $list_request = Requests::where('user_id',$user->id)->where('status',$status)->latest()->get();
            else $list_request = Requests::latest()->get();
        }
        return view('backends.requests.list', compact('list_request','status'));
    }

    public function create(){
        $user= Auth::user();
        if($user->can('create', Requests::class)){ 
            $departments = Department::select('id','title')->get();
            return view('backends.requests.create', compact('departments','user'));  
        }else{abort(403);}
    }
    public function store(Request $request){
        $rules = [
            'note'=>'required',
        ];
        $messages = [
            'note.required'=>'Vui lòng nhập nội dung yêu cầu',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->route('request.create')->withErrors($validator)->withInput();
		else:
        $request['user_id'] = Auth::id();
        $request['time'] = Carbon::now()->toDateString();
        $request['department_id'] = Auth::user()->user_department->id;
        $atribute = $request->all();
        $requests = Requests::create($atribute);
        // Attachment
        if($request->attachment && $request->attachment != '' && is_array(explode(',', $request->attachment)))
            $requests->attachments()->attach(array_filter(explode(',', $request->attachment)),['type' => 'image']);
        if($request->file && $request->file != '' && is_array(explode(',', $request->file)))
            $requests->files()->attach(array_filter(explode(',', $request->file)),['type' => 'file']);
        return redirect()->route('request.index')->with('success','Thêm thành công');
        endif;
    }

    public function edit($id){
        $user = Auth::user();
        $request = Requests::find($id);
        if($user->can('update', $request)) {
            $departments = Department::select('id','title')->get();
            return view('backends.requests.edit',compact('request','departments','user'));
        }else{abort(403);}    
    }

    public function update(Request $request, $id){
        $rules = [
            'reply'=>'required',
        ];
        $messages = [
            'reply.required'=>'Vui lòng nhập câu trả lời',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->route('request.edit',$id)->withErrors($validator)->withInput();
		else:
            $requests = Requests::findOrFail($id);
            $request['status'] = 'confirmed';
            $atribute = $request->all();
            $requests->update($atribute);
            if($request->attachment && $request->attachment != '' && is_array(explode(',', $request->attachment))){
                $attachment = array();
                foreach (explode(',', $request->attachment) as $attach){
                    $attachment[$attach] = ['type' => 'image'] ;
                }
                $requests->attachments()->sync($attachment);
            }else{$requests->attachments()->sync(array());}

            if($request->file && $request->file != '' && is_array(explode(',', $request->file))){
                $file = array();
                foreach (explode(',', $request->file) as $value){
                    $file[$value] = ['type' => 'file'] ;
                }
                $requests->files()->sync($file);
            }else{$requests->files()->sync(array());}

        return redirect()->route('request.edit',$id)->with('success','Cập nhật thành công');
            
        endif;
    }
     public function destroy($id){
        $user = Auth::user();
        $request = Requests::findOrFail($id);
        if ($user->can('delete', $request)) {
            $request->delete();
            return redirect()->route('request.index')->with('success','Xóa thành công');
        }else{abort(403);}
    }
}