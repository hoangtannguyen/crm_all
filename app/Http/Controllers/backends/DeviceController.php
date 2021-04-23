<?php

namespace App\Http\Controllers\backends;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Cates;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class DeviceController extends Controller {

    public function index(Request  $request){
        $user = Auth::user();
        $type_devices = Device::query();
        $keyword = isset($request->key) ? $request->key : '';
        if($user->can('device.read')){
            if($keyword != ''){
                $type_devices = $type_devices->where('title','like','%'.$keyword.'%'); 
            }
        }else{
            $type_devices = $type_devices->where('author_id',$user->id);
            if($keyword != ''){
                $type_devices = $type_devices->where('title','like','%'.$keyword.'%');
            }
        }
        $type_devices = $type_devices->latest()->paginate(10);
        return view('backends.devices.list', compact('type_devices','keyword'));
    }

    public function create(){
        $user = Auth::user();
        if($user->can('create', Device::class)) {
            $equipment_cates = Cates::all();
            return view('backends.devices.create',compact('equipment_cates'));
        }else{
          abort(403);
        }
    }

    public function store(Request  $request)
    {
        $rules = [
            'title'=>'required',
            'code'=>'required',
        ];
        $messages = [
            'title.required'=>'Vui lòng nhập tên loại thiết bị',
            'code.required'=>'Vui lòng nhập mã loại thiết bị',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->route('device.create')->withErrors($validator)->withInput();
		else:
        $request['author_id'] = Auth::id();
        $atribute = $request->all();
        Device::create($atribute);
        return redirect()->route('type_device.index')->with('success','Thêm thành công');
        endif;
    }

    public function edit($id){
        $user = Auth::user();
        $type_devices = Device::findOrFail($id);
        if($user->can('update', $type_devices)) {
            $equipment_cates = Cates::all();
            return view('backends.devices.edit',compact('type_devices','equipment_cates'));
        }else{
          abort(403);
        }

    }

    public function update(Request  $request , $id){
        $rules = [
            'title'=>'required',
            'code'=>'required',
        ];
        $messages = [
            'title.required'=>'Vui lòng nhập tên loại thiết bị',
            'code.required'=>'Vui lòng nhập mã loại thiết bị',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->route('type_device.edit',$id)->withErrors($validator)->withInput();
		else:
        $type_devices = Device::findOrFail($id);
        $atribute = $request->all();
        $type_devices->update($atribute);
        if($type_devices){
            if($type_devices->wasChanged())
                return redirect()->route('type_device.edit',$id)->with('success','Cập nhật thành công');
            else 
                return redirect()->route('type_device.edit',$id);
        }else{
            return redirect()->route('type_device.edit',$id)->with('error','Cập nhật không thành công');
        }
    endif;
    }

    public function destroy($id){
        $user = Auth::user();
        $type_devices = Device::findOrFail($id);
        if ($user->can('delete', $type_devices)) {
            $type_devices->delete();
            return redirect()->route('type_device.index')->with('success','Xóa thành công');
        }else{
          abort(403);
        }
        
    }


}