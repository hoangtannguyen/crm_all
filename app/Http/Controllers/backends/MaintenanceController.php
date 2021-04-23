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
use Carbon\Carbon;

class MaintenanceController extends Controller {

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
        $equipments = $equipments->select('id', 'title', 'code', 'model', 'serial', 'department_id', 'status', 'cate_id', 'devices_id',  'regular_inspection');
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
            $data_link['departments_key'] = $departments_key;
        }
        if($cates_key != '') {
            $equipments = $equipments->where('cate_id',$cates_key);
            $data_link['cates_key'] = $cates_key;
        }
        if($devices_key != ''){
            $equipments = $equipments->where('devices_id',$cates_key);
            $data_link['devices_key'] = $devices_key;   
        }            
        $equipments = $equipments->with('equipment_department:id,title')->latest()->paginate(15);

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
        return view('backends.equipments.maintenances', $data);
    }

    public function create(Request $request, $equip_id){
        $user= Auth::user();
        if($user->can('create', Maintenance::class)){ 
            $equipment = Equipment::select('id', 'title', 'code', 'model', 'serial')->findOrFail($equip_id);
            $maintenances = $equipment->maintenances->sortBy('created_at')->simplePaginate(10);
            $data = [
                'equipment' => $equipment,
                'maintenances' => $maintenances,
                'frequency' => generate_frequency(),
            ];
            return view('backends.equipments.maintenance_create', $data);
        }else{
            abort(403);
        }
        
    }

    public function store(Request $request, $equip_id){
        $equipment = Equipment::findOrFail($equip_id);
        $rules = [
            'title'=>'required',
        ];
        $messages = [
            'title.required'=> __('Nhập tên hoạt động bảo dưỡng!'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->route('equip_maintenance.create',['equip_id'=>$equip_id])->withErrors($validator)->withInput();
        else:
            $array_frq = array_keys(generate_frequency());
            $request['frequency'] = in_array($request->frequency, $array_frq) ? $request->frequency : $array_frq[0];
            $request['start_date'] = $request->start_date != NULL ? $request->start_date : Carbon::now()->format('Y-m-d');
            $request['author_id'] = Auth::id();
            $result = $equipment->maintenances()->create($request->only(['title', 'frequency', 'start_date', 'note', 'author_id']));
            if($result) $request->session()->flash('success', 'Tạo thành công!');
                else $request->session()->flash('error', 'Tạo thất bại!');
            return redirect()->route('equip_maintenance.create',['equip_id'=>$equip_id]);
        endif;
    }

    public function edit(Request $request, $equip_id, $main_id){
        $user = Auth::user();
        $maintenance = Maintenance::findOrFail($main_id);
        if($user->can('update', $maintenance)){
            $equipment = Equipment::findOrFail($equip_id);
            $data = [
                'equipment'     => $equipment,
                'maintenance'   => $maintenance,
                'frequency'     => generate_frequency(),
            ];

            return view('backends.equipments.maintenance_edit', $data);
        }else{
          abort(403);
        }
        
        
    }

    public function update(Request $request, $equip_id, $main_id){
        $equipment = Equipment::findOrFail($equip_id);
        $maintenance = Maintenance::findOrFail($main_id);
        $rules = [
            'title'=>'required',
        ];
        $messages = [
            'title.required'=> __('Nhập tên hoạt động bảo dưỡng!'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->route('equip_maintenance.edit',['equip_id'=>$equip_id, 'main_id'=>$main_id])->withErrors($validator)->withInput();
        else:
            $array_frq = array_keys(generate_frequency());
            $maintenance->frequency = in_array($request->frequency, $array_frq) ? $request->frequency : $array_frq[0];
            $maintenance->start_date = $request->start_date != NULL ? $request->start_date : Carbon::now()->format('Y-m-d');
            $maintenance->title = $request->title;
            $maintenance->note = $request->note;
            if($maintenance->save()) {
                if($maintenance->wasChanged()) $request->session()->flash('success', 'Cập nhật thành công!');
            }else $request->session()->flash('error', 'Cập nhật thất bại!');
            return redirect()->route('equip_maintenance.edit',['equip_id'=>$equip_id, 'main_id'=>$main_id]);
        endif;
    }

    public function destroy(Request $request, $equip_id, $main_id){
        $user = Auth::user();
        $maintenance = Maintenance::findOrFail($main_id);
        if ($user->can('delete', $maintenance)) {
            if($maintenance->delete()) $request->session()->flash('success', 'Xoá thành công!');
                else $request->session()->flash('error', 'Xoá thất bại!');
            return redirect()->route('equip_maintenance.create',['equip_id'=>$equip_id]);
        }else{
          abort(403);
        }
        
    }
    
}