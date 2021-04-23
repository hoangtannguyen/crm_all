<?php
namespace App\Http\Controllers\backends;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\Provider;
use App\Models\User;
use App\Models\Unit;
use App\Models\Department;
use App\Models\Device;
use App\Models\Supplie;
use App\Models\ScheduleRepair;
use App\Models\Transfer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use App\Notifications\RepairNotifications;
use App\Notifications\PublicRepairNotifications;
use Mail;
class EqRepairController extends Controller {
    public function index(Request $request) {
        $user = Auth::user();
        if($user->can('eqrepair.read')){
            $eqrepairs = Equipment::query();
            $keyword = isset($request->key) ? $request->key : '';
            $department_id = isset($request->department_id) ? $request->department_id : '';
            $device_id = isset($request->device_id) ? $request->device_id : '';
            $status_id = isset($request->status_id) ? $request->status_id : '';
            $departments = Department::select('id','title')->get();
            $devices = Device::select('id','title')->get();
            if($keyword != ''){
                $eqrepairs = $eqrepairs->where(function ($query) use ($keyword) {
                $query->where('title','like','%'.$keyword.'%')
                    ->orWhere('code','like','%'.$keyword.'%')
                    ->orWhere('model','like','%'.$keyword.'%')
                    ->orWhere('serial','like','%'.$keyword.'%')
                    ->orWhere('manufacturer','like','%'.$keyword.'%');
                });
            }
            if($department_id != ''){
                $eqrepairs = $eqrepairs->where('department_id',$department_id);
            }
            if($device_id != ''){
                $eqrepairs = $eqrepairs->whereHas('equipment_device', function($query) use ($device_id) {
                    $query->where('device_id',$device_id);
                });    
            }
            if($status_id != ''){
                $eqrepairs = $eqrepairs->where('status',$status_id);
            }
            $eqrepairs = $eqrepairs->whereIn('status',['corrected','was_broken'])->orderBy('status', 'asc')->paginate(10);
            $data=[
                'eqrepairs'=>$eqrepairs,
                'keyword'=>$keyword,
                'department_id'=>$department_id,
                'device_id'=>$device_id,
                'status_id'=>$status_id,
                'departments'=>$departments,
                'devices'=>$devices
            ];
            return view('backends.eqrepairs.list',$data);
        }else{
          abort(403);
        }
    }
    public function create($equip_id) {
        $user = Auth::user();
        if($user->can('create', ScheduleRepair::class)) {
            $equipments = Equipment::findOrFail($equip_id);
            $repairs = Provider::select('id','title','type')->repair()->get();
            $data=[
                'equipments'=>$equipments,
                'repairs'=>$repairs,
            ];
            return view('backends.eqrepairs.create',$data);
        }else{
          abort(403);
        }
    }
    public function store(Request $request, $equip_id){
        $rules = [
            'provider_id'=>'required',
        ];
        $messages = [
            'provider_id.required'=>'Vui lòng chọn đơn vị sửa chữa !', 
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->route('eqrepair.create',['equip_id'=>$equip_id])->withErrors($validator)->withInput();
        else:
            $equipment = Equipment::findOrFail($equip_id);
            $request['user_id'] = Auth::id();
            $request['planning_date'] = Carbon::now()->toDateString();
            $schedule_repairs = $equipment->schedule_repairs()->create($request->all());
            $padded_repair_id = Str::padLeft($schedule_repairs->id, 6, 'X');
            $newYear = Carbon::now()->format('dmY'); 
            $schedule_repairs['code'] = $newYear.'-'.$padded_repair_id;
            $schedule_repairs->save();
            $equipment['status']='corrected';
            $equipment->update($request->only('reason','status'));
            // Attachment
            if($request->attachment && $request->attachment != '' && is_array(explode(',', $request->attachment)))
                $schedule_repairs->attachments()->attach(array_filter(explode(',', $request->attachment)));
            //notify
            $roles = ['admin','nvkp','Nvpvt','Ddt','TK','TPVT'];
            $array_user = User::role($roles)->pluck('id')->toArray();
                if($array_user != null){
                    foreach ($array_user as $key => $value) {
                        $users = User::findOrFail($value);
                        $users->notify(new RepairNotifications($schedule_repairs));
                    }
            }
            //email
            $user = Auth::user();
            $content = '';
            $content .='<div class="content">
                            <h4>'.__('Thông tin lịch sửa chữa thiết bị').'</h4>           
                            <table class="table table-bordered">
                                <tbody>
                                    <tr><td>'.__('Tên thiết bị: ').'</td><td>'.$equipment->title.'</td></tr>
                                    <tr><td>'.__('Mã thiết bị: ').'</td><td>'.$equipment->code.'</td></tr>
                                    <tr><td>'.__('Model: ').'</td><td>'.$equipment->model.'</td></tr>
                                    <tr><td>'.__('Serial: ').'</td><td>'.$equipment->serial.'</td></tr>
                                    <tr><td>'.__('Thời gian bắt đầu sửa chữa: ').'</td><td>'.$schedule_repairs->repair_date.'</td></tr>
                                    <tr><td>'.__('Đơn vị sửa chữa: ').'</td><td>'.$schedule_repairs->provider->title.'</td></tr>
                                    <tr><td>'.__('Thông tin liên hệ: ').'</td><td>'.$schedule_repairs->user->name.'</td></tr>
                                </tbody>
                            </table>
                        </div>';
            $data = array( 'email' =>'phongvtyt2020@gmail.com', 'from' => $user->email, 'content' => $content, 'title'=>$equipment->title );
            Mail::send( 'mails.repair', compact('data'), function( $message ) use ($data){
                    $message->to($data['email'])
                    ->from( $data['from'], '[CRM]')
                    ->subject('Lịch sửa chữa thiết bị '.$data['title'] );
            });
            
            if($schedule_repairs){
                activity()->causedBy(Auth::user())->performedOn($equipment)->withProperties(['attributes'=>$equipment])->log($equipment->status);
                $request->session()->flash('success', 'Thêm thành công!');
            }else { 
                $request->session()->flash('error', 'Thêm không thành công!');
            }
                return redirect()->route('eqrepair.index');
        endif;
    }
    public function listRepair(Request $request, $equip_id) {
        $equipment = Equipment::findOrFail($equip_id);
        $data = [
            'equipment'         => $equipment,
            'repairs'           => $equipment->schedule_repairs->sortByDesc('planning_date')->simplePaginate(10),
        ];
        return view('backends.eqrepairs.list-histories',$data);
    }
    public function edit($equip_id, $repair_id) {
        $user = Auth::user();
        $repair = ScheduleRepair::findOrFail($repair_id);
        if ($user->can('update', $repair)) {
            $equipment = Equipment::findOrFail($equip_id);
            $repairs = Provider::select('id','title','type')->repair()->get();
            $users = User::select('id','name')->get();
            $approved = User::whereHas("roles", function($q){ $q->where("name", "TPVT"); })->first();
            $data = [
                'equipment'     => $equipment,
                'repair'   => $repair,
                'repairs'   => $repairs,
                'users'   => $users,
                'approved'   => $approved,
            ];
            return view('backends.eqrepairs.edit', $data);
        }else{
          abort(403);
        }
    }
    public function update(Request $request, $equip_id, $repair_id){
        $equipment = Equipment::findOrFail($equip_id);
        $repair = ScheduleRepair::findOrFail($repair_id);
        $approved = User::whereHas("roles", function($q){ $q->where("name", "TPVT"); })->first();
        $request['person_up'] = Auth::id();
        $request['update_date'] = Carbon::now()->toDateString();
        $request['approved'] = $approved->id;
        $request['expected_cost'] = $request->expected_cost;
        $request['actual_costs'] =$request->actual_costs;
        $repair->update($request->all());
        $equipment->update($request->only('reason'));
        if($request->acceptance == 'accepted'){
            //notify
            $user= User::where('id',$repair->user_id)->first();
            $roles = [$user->roles->first()->name];
            $array_user = User::role($roles)->pluck('id')->toArray();
                if($array_user != null){
                    foreach ($array_user as $key => $value) {
                        $user = User::findOrFail($value);
                        $user->notify(new PublicRepairNotifications($repair));
                    }
            }
        }
        if($request->attachment && $request->attachment != '' && is_array(explode(',', $request->attachment)))
            $repair->attachments()->sync(array_filter(explode(',', $request->attachment)));
        else $repair->attachments()->sync(array());
        if($repair){
           $request->session()->flash('success', 'Cập nhật thành công!');
        }else $request->session()->flash('error', 'Cập nhật thất bại!');
        return redirect()->route('eqrepair.edit',['equip_id'=>$equip_id, 'repair_id'=>$repair_id]);
    }
    public function destroy($equip_id, $repair_id){
        $user = Auth::user();
        $repair = ScheduleRepair::findOrFail($repair_id);
        if ($user->can('delete', $repair)) {
            $repair->delete();
            \DB::table('notifications')
            ->where('type','App\Notifications\RepairNotifications')
            ->orWhere('type','App\Notifications\PublicRepairNotifications')
            ->where('data->id',intval($repair_id))
            ->delete();
            return redirect()->route('eqrepair.history',['equip_id'=>$equip_id])->with('success','Xóa thành công');
        }else{
          abort(403);
        }
    }
    public function stateTransition(Request $request, $equip_id){
        $equipments = Equipment::findOrFail($equip_id);
        $equipments['status']  = $request->status;
        $equipments->save();
        if($equipments){
            if($equipments->wasChanged()){
                activity()->causedBy(Auth::user())->performedOn($equipments)->withProperties(['attributes'=>$equipments])->log($equipments->status);
                return redirect()->back()->with('success','Đã chuyển trạng thái thiết bị '.$equipments->title.' ');
            }
            else{
                return redirect()->back();
            }
        }else{
            return redirect()->back()->with('error','Cập nhật không thành công');
        }
    }
}
