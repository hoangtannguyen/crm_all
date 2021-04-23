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
use App\Models\Guarantee;
use Carbon\Carbon;
class GuaranteeController extends Controller {

    public function index(Request $request){
        $keyword = isset($request->key) ? $request->key : '';
        $status = isset($request->status) ? $request->status : '';
        $departments_key = isset($request->department_key) ? $request->department_key : '';
        $cates_key = isset($request->cate_key) ? $request->cate_key : '';
        $devices_key = isset($request->device_key) ? $request->device_key : '';
        $data_link = array();
        $department_name = Department::select('id','title')->get();
        $user_name = User::select('id','name')->get();
        $cate_name = Cates::select('id','title')->get();
        $device_name = Device::select('id','title')->get();
        $equipments = Equipment::query();
        if($keyword != ''){
            $equipments = $equipments->where(function ($query) use ($keyword) {
                                                $query->where('title','like','%'.$keyword.'%')
                                                    ->orWhere('code','like','%'.$keyword.'%')
                                                    ->orWhere('model','like','%'.$keyword.'%')
                                                    ->orWhere('serial','like','%'.$keyword.'%');
                                                });
            $data_link['keyword'] = $keyword;
        }
        if($status != '') {
            $equipments = $equipments->where('status',$status);
            $data_link['status'] = $status;
        }
        if($departments_key != '') {
            $equipments = $equipments->where('department_id',$departments_key);
            $data_link['status'] = $status;
        }
        if($cates_key != '') {
            $equipments = $equipments->where('cate_id',$cates_key);
            $data_link['status'] = $status;
        }
        if($devices_key != ''){
            $equipments = $equipments->where('devices_id',$cates_key);
            $data_link['devices_key'] = $devices_key;   
        }            
        $equipments = $equipments->whereNotIn('status',['inactive','liquidated'])->orderBy('created_at', 'desc')->latest()->paginate(15);

        $data = [
            'equipments'        => $equipments,
            'keyword'           => $keyword,
            'status'            => $status,
            'departments_key'   => $departments_key,
            'cates_key'         => $cates_key,
            'devices_key'       => $devices_key,
            'data_link'         => $data_link,
            'department_name'   => $department_name,
            'cate_name'         => $cate_name,
            'device_name'       => $device_name,
        ];
        return view('backends.guarantees.list', $data);
    }

    public function store(Request $request,$id){
        $rules = [

        ];
        $messages = [
          
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->back()->withErrors($validator)->withInput();
        else:
            $equipments = Equipment::findOrFail($id);
            $atribute = $request->all();
            $atribute['equipment_id'] =  $equipments->id;
            Guarantee::create($atribute);
            return redirect()->route('guarantee.index')->with('success','Thêm thành công');
        endif;
    }

    public function edit($id){
        $equipments = Equipment::findOrFail($id);
        return view('backends.guarantees.edit',compact('equipments'));
    }

    public function update(Request $request,$id){
        $rules = [
            'time'=>'required',
            'provider'=>'required',
            'content'=>'required',
        ];
        $messages = [
            'time.required'=>'Vui lòng chọn thời gian bảo hành',
            'provider.required'=>'Vui lòng nhập đơn vị thực hiện',
            'content.required'=>'Vui lòng nhập nội dung bảo hành',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->back()->withErrors($validator)->withInput();
        else:
            $guarantee = Guarantee::findOrFail($id);
            $atribute = $request->all();
            $guarantee->update($atribute);
            if($guarantee){
                if($guarantee->wasChanged()){
                    return redirect()->back()->with('success','Cập nhật thành công');
                }else{
                    return redirect()->back();
                }
            }else{
                return redirect()->back()->with('error','Cập nhật không thành công');
            }
        endif;
    }

    
    public function destroy($id){
        $equipments = Guarantee::findOrFail($id);
        $equipments->delete();
        return redirect()->back()->with('success','Xóa thành công');
    }

}