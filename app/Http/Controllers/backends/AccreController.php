<?php
namespace App\Http\Controllers\backends;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\Cates;
use App\Models\Device;
use App\Models\Maintenance;
use App\Models\User;
use App\Models\Accre;
use Carbon\Carbon;
class AccreController extends Controller {

    public function index(Request  $request) {
        $user = Auth::user();
        $equipments = Equipment::query();
        $keyword = isset($request->key) ? $request->key : '';
        $inspections_key = isset($request->inspection) ? $request->inspection : '';
        if($user->can('eqaccre.read')){
            if($inspections_key != ''){
                $equipments = $equipments->where('regular_inspection',$inspections_key);
            }
        }else{
            $equipments = $equipments->where('user_id',$user->id);
            if($inspections_key != ''){
                $equipments = $equipments->where('regular_inspection',$inspections_key);
            }
        }
        if($keyword != ''){
            $equipments = $equipments->where(function ($query) use ($keyword) {
            $query->where('title','like','%'.$keyword.'%')
                ->orWhere('code','like','%'.$keyword.'%')
                ->orWhere('model','like','%'.$keyword.'%')
                ->orWhere('serial','like','%'.$keyword.'%');
            });
        }
        $equipments = $equipments->whereNotIn('status',['inactive','liquidated'])->orderBy('created_at', 'desc')->paginate(15);
            return view('backends.accres.list',compact('equipments','keyword','inspections_key'));
    }

    public function store(Request $request,$id){
        $rules = [
            'time'=>'required',
            'provider'=>'required',
            'content'=>'required',
        ];
        $messages = [
            'time.required'=>'Vui lòng chọn thời gian kiểm định',
            'provider.required'=>'Vui lòng nhập đơn vị thực hiện',
            'content.required'=>'Vui lòng nhập nội dung kiểm định',
          
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->back()->withErrors($validator)->withInput();
        else:
            $equipments = Equipment::findOrFail($id);
            $atribute = $request->all();
            $atribute['equipment_id'] =  $equipments->id;
            Accre::create($atribute);
            return redirect()->route('accre.index')->with('success','Thêm thành công');
        endif;
    }


    public function update(Request $request,$id){
        $rules = [
            'time'=>'required',
            'provider'=>'required',
            'content'=>'required',
        ];
        $messages = [
            'time.required'=>'Vui lòng chọn thời gian kiểm định',
            'provider.required'=>'Vui lòng nhập đơn vị thực hiện',
            'content.required'=>'Vui lòng nhập nội dung kiểm định',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->back()->withErrors($validator)->withInput();
        else:
            $accre = Accre::findOrFail($id);
            $atribute = $request->all();
            $accre->update($atribute);
            if($accre){
                if($accre->wasChanged()){
                    return redirect()->back()->with('success','Cập nhật thành công');
                }else{
                    return redirect()->back();
                }
            }else{
                return redirect()->back()->with('error','Cập nhật không thành công');
            }
        endif;
    }


    public function edit($id){
        $equipments = Equipment::findOrFail($id);
        return view('backends.accres.edit',compact('equipments'));
    }

    public function destroy($id){
        $equipments = Accre::findOrFail($id);
        $equipments->delete();
        return redirect()->back()->with('success','Xóa thành công');
    }





}