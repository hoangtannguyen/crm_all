<?php
namespace App\Http\Controllers\backends;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\Eqsupplie;
use App\Models\Provider;
use App\Models\User;
use App\Models\Cates;
use App\Models\Unit;
use App\Models\Department;
use App\Models\Device;
use App\Models\Supplie;
use App\Models\Action;
use App\Models\Project;
use Illuminate\Support\Facades\Validator;
use App\Exports\EquipmentsExport;
use App\Exports\EqsuppliesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EquipmentsImport;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Validation\Rule;
use App\Notifications\HanOverNotifications;
use App\Notifications\ReportFailureNotifications;
use Mail;
use PDF;
use Spatie\Activitylog\Traits\LogsActivity;
class EquipmentController extends Controller {
    public function index(Request  $request) {
        $user = Auth::user();
        $keyword = isset($request->key) ? $request->key : '';
        $status = isset($request->status) ? $request->status : '';
        $departments_key = isset($request->department_key) ? $request->department_key : '';
        $cates_key = isset($request->cate_key) ? $request->cate_key : '';
        $devices_key = isset($request->device_key) ? $request->device_key : '';
        $department_name = Department::select('id','title')->get();
        $user_name = User::select('id','name')->get();
        $cate_name = Cates::select('id','title')->get();
        $device_name = Device::select('id','title')->get();
        $equipments = Equipment::query();
        $cur_time = Carbon::now()->format('Y-m-d');
        $order = '';
        $sort = '';
        if($keyword != ''){
            $equipments = $equipments->where(function ($query) use ($keyword) {
            $query->where('title','like','%'.$keyword.'%')
                ->orWhere('code','like','%'.$keyword.'%')
                ->orWhere('model','like','%'.$keyword.'%')
                ->orWhere('serial','like','%'.$keyword.'%');
            });
        }
        if($status != ''){
            $equipments = $equipments->where('status',$status);
        }
        if($cates_key != ''){
            $equipments = $equipments->where('cate_id',$cates_key);
        }
        if($devices_key != ''){
            $equipments = $equipments->where('devices_id',$devices_key);
        }
        if($request->sortByTitle && in_array($request->sortByTitle, ['asc','a'])){
            $equipments = $equipments->orderBy('title',$request->sortByTitle);
            $sort = 'sortByTitle';
            $order = $request->sortByTitle;
        }
        if($request->sortByModel && in_array($request->sortByModel, ['asc','desc'])){
            $equipments = $equipments->orderBy('model',$request->sortByModel);
            $sort = 'sortByModel';
            $order = $request->sortByModel;
        }
        if($request->sortBySeria && in_array($request->sortBySeria, ['asc','desc'])){
            $equipments = $equipments->orderBy('serial',$request->sortBySeria);
            $sort = 'sortBySeria';
            $order = $request->sortBySeria;
        }
        if($request->sortByStatus && in_array($request->sortByStatus, ['asc','desc'])){
            $equipments = $equipments->orderBy('status',$request->sortByStatus);
            $sort = 'sortByStatus';
            $order = $request->sortByStatus;
        }
        if($request->sortByCode && in_array($request->sortByCode, ['asc','desc'])){
            $equipments = $equipments->orderBy('code',$request->sortByCode);
            $sort = 'sortByCode';
            $order = $request->sortByCode;
        }
        if($request->sortByDepartment && in_array($request->sortByDepartment, ['asc','desc'])){
            $equipments = $equipments->orderBy('code',$request->sortByDepartment);
            $sort = 'sortByDepartment';
            $order = $request->sortByDepartment;
        }
        if($user->can('equipment.show_all')){
            if($departments_key != ''){
                $equipments = $equipments->where('department_id',$departments_key);
            }
            
        }else{
            $equipments = $equipments->where('department_id',$user->department_id);
            if($departments_key != ''){
                $equipments = $equipments->where('department_id',$departments_key);
            }
        }
        $equipments = $equipments->orderBy('created_at', 'desc')->paginate(15);
            return view('backends.equipments.list',
            compact('equipments',
            'keyword',
            'sort','order',
            'status',
            'department_name','departments_key',
            'cate_name','cates_key',
            'device_name','devices_key',
            'user_name','cur_time',
            'user',
        ));
    }
    public function indexMedical(Request  $request) {
        $user = Auth::user();
            $keyword = isset($request->key) ? $request->key : '';
            $status = isset($request->status) ? $request->status : '';
            $departments_key = isset($request->department_key) ? $request->department_key : '';
            $cates_key = isset($request->cate_key) ? $request->cate_key : '';
            $devices_key = isset($request->device_key) ? $request->device_key : '';
            $department_name = Department::select('id','title')->get();
            $user_name = User::select('id','name')->get();
            $cate_name = Cates::select('id','title')->get();
            $device_name = Device::select('id','title')->get();
            $equipments = Equipment::query();
            $order = '';
            $sort = '';
            if($keyword != ''){
                $equipments = $equipments->where(function ($query) use ($keyword) {
                $query->where('title','like','%'.$keyword.'%')
                    ->orWhere('code','like','%'.$keyword.'%')
                    ->orWhere('model','like','%'.$keyword.'%')
                    ->orWhere('serial','like','%'.$keyword.'%');
                });
            }
            if($status != ''){
                $equipments = $equipments->where('status',$status);
            }
            if($departments_key != ''){
                $equipments = $equipments->where('department_id',$departments_key);
            }
            if($cates_key != ''){
                $equipments = $equipments->where('cate_id',$cates_key);
            }
            if($devices_key != ''){
                $equipments = $equipments->whereHas('equipment_device', function($query) use ($devices_key) {
                    $query->where('device_id',$devices_key);
                });    
            }
            $equipments = $equipments->where('department_id',$user->department_id)->paginate(15);
            //dd($equipments);
                return view('backends.equipments.mediacal',
                compact('equipments',
                'keyword',
                'sort','order',
                'status',
                'department_name','departments_key',
                'cate_name','cates_key',
                'device_name','devices_key',
                'user_name',
                'user',
            ));
    }
    public function indexGuarantee(Request  $request) {
            $keyword = isset($request->key) ? $request->key : '';
            $equipments = Equipment::query();
            if($keyword != ''){
                $equipments = $equipments->where(function ($query) use ($keyword) {
                $query->where('title','like','%'.$keyword.'%')
                    ->orWhere('code','like','%'.$keyword.'%')
                    ->orWhere('model','like','%'.$keyword.'%')
                    ->orWhere('serial','like','%'.$keyword.'%');
                });
            }
            $equipments = $equipments->orderBy('created_at', 'desc')->paginate(15);
            return view('backends.guarantees.list',compact('equipments','keyword'));
    }
    public function showHistory() {
        $user = Auth::user();
        if($user->can('history_status.read')){
            $activities = Activity::where("description","updated")
            ->where("subject_type","App\Models\Equipment")
            ->whereJsonContains('properties->attributes->type','devices')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            return view('backends.equipments.history',compact('activities'));  
        }else{
            abort(403);
        }  
    }
    public function destroyHistory($id){
        $user = Auth::user();
        if($user->can('history_status.delete')){
            $activities = Activity::findOrFail($id);
            $activities->delete();
            return redirect()->route('equipment.history')->with('success','X??a th??nh c??ng');
        }else{
            abort(403);
        }  
    }
    public function deleteChooseHistory(Request $request){
        $items = explode(",",$request->items);
        if(count($items)>0){
            $request->session()->flash('success', 'X??a th??nh c??ng!');
            Activity::destroy($items);
        }else{
            $request->session()->flash('error', 'C?? l???i!');
        }
        return redirect()->route('equipment.history');
    }
    public function show(Request $request ,$id) {
        $user = Auth::user();
        if($user->can('equipment.show')){
            $equipments = Equipment::findOrFail($id);
            $activities = Activity::where("subject_type","App\Models\Equipment")->where("subject_id",$equipments->id)->orderBy('created_at', 'desc')->paginate(15);
            return view('backends.equipments.show', compact('equipments','activities'));
        }else{
            abort(403);
        } 
    }
    public function showPdf($id){
        $equipments = Equipment::findOrFail($id);
        $activities = Activity::where("subject_type","App\Models\Equipment")->where("subject_id",$equipments->id)->orderBy('created_at', 'desc')->get();
        $pdf = PDF::loadView('backends.equipments.pdf', compact('equipments','activities'));
        return $pdf->download(''.$equipments->title. '.pdf');   
    }
    public function create(){
        $user = Auth::user();
        if($user->can('create', Equipment::class)) {
            $maintenances = Provider::select('id','title','type')->maintenance()->get();
            $providers = Provider::select('id','title','type')->provider()->get();
            $repairs = Provider::select('id','title','type')->repair()->get();
            $users = User::select('id','name')->get();
            $users_vt = User::select('id','name')->where('department_id',$user->department_id)->get();
            $cates = Cates::select('id','title')->get();
            $units = Unit::select('id','title')->get();
            $departments = Department::select('id','title')->get();
            $devices = Device::select('id','title')->get();
            $projects = Project::select('id','title')->get();
            $cur_day = Carbon::now()->format('Y-m-d'); 
            return view('backends.equipments.create',compact('maintenances',
                'providers',
                'repairs','users',
                'cates','units',
                'departments','devices',
                'projects',
                'cur_day','users_vt'
            ));
        }else{
          abort(403);
        }
    }
    public function createSupplie($id){
        $user = Auth::user();
        if($user->can('equipment.create_supplie')) {
            $equipments = Equipment::findOrFail($id);
            $maintenances = Provider::select('id','title','type')->maintenance()->get();
            $providers = Provider::select('id','title','type')->provider()->get();
            $repairs = Provider::select('id','title','type')->repair()->get();
            $users = User::select('id','name')->get();
            $units = Unit::select('id','title')->get();
            $departments = Department::select('id','title')->get();
            $supplies = Supplie::select('id','title')->get();
            $cur_day = Carbon::now()->format('Y-m-d'); 
            return view('backends.equipments.createsupplie',compact('maintenances',
            'providers','repairs',
            'users','units',
            'departments','supplies',
            'equipments','cur_day'
        ));
        }else{
          abort(403);
        }
    }
    public function storeSupplie(Request  $request)
    {
        $rules = [
            'title'=>'required',
            'supplie_id'=>'required',
            'amount'=>'required|min:0',
            'unit_id'=>'required',
            'import_price'=>'required',
            'used_amount'=>'numeric|max:'.intval($request->amount).'| min:0',
        ];
        $messages = [
            'title.required'=>'Vui l??ng nh???p t??n thi???t b???!',
            'supplie_id.required'=>'Vui l??ng nh???p lo???i v???t t??!',
            'amount.required'=>'Vui l??ng nh???p s??? l?????ng!',
            'amount.min'=>'Vui l??ng nh???p s??? l?????ng kh??ng ???????c nh??? h??n 0 !',
            'unit_id.required'=>'Vui l??ng nh???p ????n v??? t??nh!',
            'import_price.required'=>'Vui l??ng nh???p gi?? nh???p!',
            'used_amount.max'=>'S??? l?????ng d??ng kh??ng ???????c nh???p v?????t qu?? s??? l?????ng !',
            'used_amount.min'=>'S??? l?????ng d??ng kh??ng ???????c nh???p nh??? h??n 0 !',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->back()->withErrors($validator)->withInput();
        else:           
        $atribute = $request->all();
        $eqsupplies = Eqsupplie::create($atribute);
        $eqsupplies->save();
        $eqsupplies->supplie_devices()->attach($request->supplie_devices,['note' => 'spelled_by_device','amount' => $request->used_amount,'user_id' => Auth::user()->id ]);
        if($eqsupplies){
            return redirect()->back()->with('success','Th??m th??nh c??ng');
        }else{
            return redirect()->back()->with('success','Th??m kh??ng th??nh c??ng');
        }
        endif;
    }
    public function store(Request  $request)
    {
        $rules = [
            'title'=>'required',
            'unit_id'=>'required',
            'amount'=>'required|numeric|min:0',
            'serial'=>'required|unique:equipments,serial',
            'code' =>'unique:equipments,code',
            'model'=>'required',
            'manufacturer' => 'required',
            'origin' => 'required',
            'year_manufacture' => 'required',
            'regular_inspection' => 'required',
        ];
        $messages = [
            'title.required'=>'Vui l??ng nh???p t??n thi???t b???!',
            'unit_id.required'=>'Vui l??ng nh???p ????n v??? t??nh!',
            'amount.unique'=>'Vui l??ng nh???p s??? l?????ng!',
            'code.unique'=>'M?? thi???t b??? ???? t???n t???i!',
            'amount.min'=>'S??? l?????ng kh??ng ???????c nh??? h??n 0!',
            'serial.required'=>'Vui l??ng nh???p s??? serial !',
            'serial.unique'=>'S??? serial ???? t???n t???i !',
            'model.required'=>'Vui l??ng nh???p model!',
            'manufacturer.required'=>'Vui l??ng nh???p h??ng s???n xu???t!',
            'origin.required'=>'Vui l??ng nh???p xu???t x???!',
            'year_manufacture.required'=>'Vui l??ng nh???p n??m s???n xu???t!',
            'regular_inspection.required'=>'Vui l??ng nh???p ki???m ?????nh ?????nh k???!',
            'first_value.min'=>'Gi?? tr??? ban ?????u kh??ng ???????c nh??? h??n 0!',
            'first_value.max'=>'Gi?? tr??? ban ?????u kh??ng ???????c l???n h??n 100!',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->route('equipment.create')->withErrors($validator)->withInput();
        else:
        $atribute = $request->all();
        $atribute['status'] = 'not_handed';
        $atribute['department_id'] = Auth::user()->department_id;
        $equipments = Equipment::create($atribute);
        $cates = Cates::where('id',$request->cate_id)->select("id", "code")->first();
        $devices = Device::where('id',$request->devices_id)->select("id", "code")->first();
        $padded_cates = Str::padLeft(isset($cates->code) ? $cates->code :'', 1, 'X');
        $padded_devices = Str::padLeft(isset($devices->code) ? $devices->code :'', 6, 'X');
        $padded_equipments_id = Str::padLeft($equipments->id, 6, 'X');
        $newYear = Carbon::now()->format('dmY'); 
        $equipments['code'] = $padded_cates.'-'.$padded_devices.'-'.$newYear.'-'.$padded_equipments_id;
        $equipments->save();
        // Attachment
        $user = Auth::user();
        if($request->attachment && $request->attachment != '' && is_array(explode(',', $request->attachment))) {
            $attachments = array_filter(explode(',', $request->attachment));
            if(!$user->can('media.read')) {
                foreach ($attachments as $item) {
                    if(!$user->medias->contains($item)) $attachments = array_diff($attachments,[$item]);
                }                
            }
            $equipments->attachments()->attach($attachments);
        }
        $equipments->equipment_user_use()->attach($request->equipment_user_use);
        $equipments->equipment_user_training()->attach($request->equipment_user_training);
        if($equipments){
            return redirect()->route('equipment.index')->with('success','Th??m th??nh c??ng');
        }else{
            return redirect()->route('equipment.index')->with('error','Th??m kh??ng th??nh c??ng');
        }
        endif;
    }
    public function edit($id){
        $user = Auth::user();
        $equipments = Equipment::with('attachments:id,title,path,type')->findOrFail($id);
        if($user->can('update', $equipments)) {
            $maintenances = Provider::select('id','title','type')->maintenance()->get();
            $providers = Provider::select('id','title','type')->provider()->get();
            $repairs = Provider::select('id','title','type')->repair()->get();
            $users = User::select('id','name')->get();
            $cates = Cates::select('id','title')->get();
            $units = Unit::select('id','title')->get();
            $departments = Department::select('id','title')->get();
            $devices = Device::select('id','title')->get();
            $projects = Project::select('id','title')->get();
            $cur_day = Carbon::now()->format('Y-m-d'); 
            $users_vt = User::select('id','name')->where('department_id',$user->department_id)->get();
            $array_user_use = $equipments->equipment_user_use->pluck('id')->toArray();
            $array_user_training = $equipments->equipment_user_training->pluck('id')->toArray();
            return view('backends.equipments.edit',compact('equipments',
            'maintenances','providers',
            'repairs','users',
            'cates','units',
            'departments','devices',
            'array_user_use','array_user_training',
            'projects',
            'cur_day',
            'users_vt'
        ));
        }else{
            abort(403);
        }
    }
    public function update(Request  $request , $id)
    {
        $equipments = Equipment::findOrFail($id);
        $rules = [
            'title'=>'required',
            'unit_id'=>'required',
            'amount'=>'required|numeric|min:0',
            'serial'=>['required',Rule::unique('equipments')->ignore($equipments->id)],
            'code'=>[Rule::unique('equipments')->ignore($equipments->id)],
            'model'=>'required',
            'manufacturer' => 'required',
            'origin' => 'required',
            'year_manufacture' => 'required',
            'regular_inspection' => 'required',
        ];
        $messages = [
            'title.required'=>'Vui l??ng nh???p t??n thi???t b???!',
            'unit_id.required'=>'Vui l??ng nh???p ????n v??? t??nh!',
            'amount.min'=>'S??? l?????ng kh??ng ???????c nh??? h??n 0!',
            'serial.required'=>'Vui l??ng nh???p s??? serial !',
            'serial.unique'=>'S??? serial ???? t???n t???i !',
            'code.unique'=>'M?? thi???t b??? ???? t???n t???i !',
            'model.required'=>'Vui l??ng nh???p model!',
            'manufacturer.required'=>'Vui l??ng nh???p h??ng s???n xu???t!',
            'origin.required'=>'Vui l??ng nh???p xu???t x???!',
            'year_manufacture.required'=>'Vui l??ng nh???p n??m s???n xu???t!',
            'regular_inspection.required'=>'Vui l??ng nh???p ki???m ?????nh ?????nh k???!',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->route('equipment.edit',$id)->withErrors($validator)->withInput();
        else:
        $atribute = $request->all();
        $equipments->update($atribute);
        $cates = Cates::where('id',$request->cate_id)->select("id", "code")->first();
        $devices = Device::where('id',$request->devices_id)->select("id", "code")->first();
        $padded_cates = Str::padLeft(isset($cates->code) ? $cates->code :'', 1, 'X');
        $padded_devices = Str::padLeft(isset($devices->code) ? $devices->code :'', 6, 'X');
        $padded_equipments_id = Str::padLeft($equipments->id, 6, 'X');
        $newYear = Carbon::now()->format('dmY'); 
        $equipments['code'] = $padded_cates.'-'.$padded_devices.'-'.$newYear.'-'.$padded_equipments_id;
        $equipments->save();
        $user = Auth::user();
        /*if($request->attachment && $request->attachment != '' && is_array(explode(',', $request->attachment))) {
            $attachments = array_filter(explode(',', $request->attachment));
            if(!$user->can('media.read')) {
                foreach ($attachments as $item) {
                    if(!$user->medias->contains($item)) $attachments = array_diff($attachments,[$item]);
                }                
            }     
            $equipments->attachments()->sync($attachments);
        }else $equipments->attachments()->sync(array());*/
        $attachment = array();
        foreach (explode(',', $request->attachment) as $attach){
            $attachment[$attach] = ['type' => 'attach'] ;
        }
        if($request->attachment && $request->attachment != '' && is_array(explode(',', $request->attachment)))
            $equipments->attachments()->sync($attachment);
        else $equipments->attachments()->sync(array());
        
        $equipments->equipment_user_use()->sync($request->equipment_user_use);
        $equipments->equipment_user_training()->sync($request->equipment_user_training);
        if($equipments){
            if($equipments->wasChanged())
                return redirect()->route('equipment.edit',$id)->with('success','C???p nh???t th??nh c??ng');
            else 
                return redirect()->route('equipment.edit',$id);        
        }else{
            return redirect()->route('equipment.edit',$id)->with('error','C???p nh???t kh??ng th??nh c??ng');
        }
    endif;
    }
    public function updateHandOver(Request  $request , $id)
    {
        $rules = [
            'department_id'=>'required',
            'officer_department_charge_id'=>'required',
        ];
        $messages = [
            'department_id.required'=>'Vui l??ng nh???p khoa ph??ng ban !',
            'officer_department_charge_id.required'=>'Vui l??ng ch???n ng?????i ph??? tr??ch khoa !',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->back()->withErrors($validator)->withInput();
        else:
        $equipments = Equipment::findOrFail($id);
        $equipments->department_id = $request->department_id;
        $equipments->officer_department_charge_id = $request->officer_department_charge_id;
        $equipments->date_delivery = $request->date_delivery;
        $equipments['status']  = "active";
        $equipments->save();
        $equipments->equipment_user_use()->attach($request->equipment_user_use);
        if($request->attachment && $request->attachment != '' && is_array(explode(',', $request->attachment)))
            $equipments->hand_over()->attach(array_filter(explode(',', $request->attachment)),['type' => 'hand_over']);

        $roles = ['admin','nvkp','Nvpvt','Ddt','TK','TPVT'];
        $array_user = User::role($roles)->pluck('id')->toArray();
            if($array_user != null){
                foreach ($array_user as $key => $value) {
                    $user = User::findOrFail($value);
                    $user->notify(new HanOverNotifications($equipments));
                }
        }
        if($equipments){
            if($equipments->wasChanged()){
                return redirect()->back()->with('success','???? b??n giao thi???t b??? '.$equipments->title.' ');
            }else{
                return redirect()->back();
            }
        }else{
            return redirect()->back()->with('error','C???p nh???t kh??ng th??nh c??ng');
        }
    endif;
    }
    public function selectHandOver(Request $request ){
        $users = User::select('id','name','department_id')->where('department_id', $request->id)->get();
        $html = '<select  class="select2 form-control" name="officer_department_charge_id">';
        if($users) {
            foreach($users as $item) {
        $html .= '<option value="'.$item->id.'">'.$item->name.'</option>';
            }
        }
        $user_use = User::select('id','name','department_id')->get();
        $html_user_use = '<label class="control-label">'.__('CB s??? d???ng').'</label>';
        $html_user_use .= '<select  class="select2 form-control" name="equipment_user_use[]"  multiple="multiple">';
        if($user_use) {
            foreach($user_use as $item) {
            $html_user_use .= '<option value="'.$item->id.'"'.(($item->department_id == $request->id ? ' selected' : '')).'>'.$item->name.'</option>';
            }
        }
        return response()->json([
            'check' => 'true',
            'html' => $html,
            'html_user_use' => $html_user_use,
        ]);
    }
    public function updateCorrected(Request  $request , $id)
    {
        $equipments = Equipment::findOrFail($id);
        $equipments->status = $request->status;
        $equipments->save();
        if($equipments){
            if($equipments->wasChanged('status')){
                activity()->causedBy(Auth::user())->performedOn($equipments)->withProperties(['attributes'=>$equipments])->log($equipments->status);
                return redirect()->back()->with('success','C???p nh???t th??nh c??ng');
            }
            else {
                return redirect()->back();
            }
        }else{
            return redirect()->back()->with('error','C???p nh???t kh??ng th??nh c??ng');
        }
    }
    public function updateInactive(Request  $request , $id)
    {
        $equipments = Equipment::findOrFail($id);
        $equipments['liquidation_date'] = $request->liquidation_date;
        $equipments['status']  = "liquidated";
        $equipments->save();
        if($equipments){
            if($equipments->wasChanged('status')){
                activity()->causedBy(Auth::user())->performedOn($equipments)->withProperties(['attributes'=>$equipments])->log($equipments->status);
                return redirect()->back()->with('success','C???p nh???t th??nh c??ng');
            }
            else{
                return redirect()->back();
            }
        }else{
            return redirect()->back()->with('error','C???p nh???t kh??ng th??nh c??ng');
        }
    }
    public function updateWasBroken(Request  $request , $id){
        $equipments = Equipment::findOrFail($id);
        $equipments['status'] = "was_broken";
        $equipments->save();
        if($equipments){
            if($equipments->wasChanged('status')){
                $equipments['date_failure'] = Carbon::now()->toDateTimeString();
                $equipments->update($request->only('date_failure','reason'));
                $equipments->equipment_user_use()->attach($request->equipment_user_use);
                if($request->file && $request->file != '' && is_array(explode(',', $request->file)))
                    $equipments->was_broken()->attach(array_filter(explode(',', $request->file)),['type' => 'was_broken']);
                $user = Auth::user();
                $content = '';
                $content .='<div class="content">
                                <h4>'.__('Th??ng tin thi???t b??? b??o h???ng').'</h4>           
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr><td>'.__('T??n thi???t b???: ').'</td><td>'.$equipments->title.'</td></tr>
                                        <tr><td>'.__('M?? thi???t b???: ').'</td><td>'.$equipments->code.'</td></tr>
                                        <tr><td>'.__('Model: ').'</td><td>'.$equipments->model.'</td></tr>
                                        <tr><td>'.__('Serial: ').'</td><td>'.$equipments->serial.'</td></tr>
                                        <tr><td>'.__('L?? do h???ng: ').'</td><td>'.$equipments->reason.'</td></tr>
                                        <tr><td>'.__('Ng?????i b??o h???ng: ').'</td><td>'.$user->roles->first()->name.'</td></tr>
                                        <tr><td>'.__('M?? ng?????i b??o h???ng: ').'</td><td>'.$user->email.'</td></tr>
                                        <tr><td>'.__('Th???i gian b??o h???ng: ').'</td><td>'.$equipments->date_failure.'</td></tr>
                                    </tbody>
                                </table>
                            </div>';
                $data = array( 'email' =>'phongvtyt2020@gmail.com', 'from' => $user->email, 'content' => $content, 'title'=>$equipments->title );
                Mail::send( 'mails.fail', compact('data'), function( $message ) use ($data){
                        $message->to($data['email'])
                        ->from( $data['from'], '[CRM]')
                        ->subject('Thi???t b??? b??o h???ng '.$data['title']);
                });
                $roles = ['admin','nvkp','Nvpvt','Ddt','TK','TPVT'];
                $array_user = User::role($roles)->pluck('id')->toArray();
                    if($array_user != null){
                        foreach ($array_user as $key => $value) {
                            $user = User::findOrFail($value);
                            $user->notify(new ReportFailureNotifications($equipments));
                        }
                }
                activity()->causedBy(Auth::user())->performedOn($equipments)->withProperties(['attributes'=>$equipments])->log($equipments->status);
                return redirect()->back()->with('success','???? b??o h???ng thi???t b??? '. $equipments->title );
            }else{
                return redirect()->back()->with('error','Thi???t b??? '. $equipments->title .' ???? ???????c b??o h???ng tr?????c ????. Vui l??ng kh??ng b??o h???ng l???i' );
            }
        }else{
            return redirect()->back()->with('error','C???p nh???t kh??ng th??nh c??ng');
        }
    }
    public function updateWasBrokenDevice(Request  $request , $id){
        $equipments = Equipment::findOrFail($id);
        $equipments['status']  = "corrected";
        $equipments->save();
        if($equipments){
            if($equipments->wasChanged('status')){
                return redirect()->back()->with('success','C???p nh???t th??nh c??ng');
            }
            else{
                return redirect()->back()->with('error','C???p nh???t kh??ng th??nh c??ng');
            }
        }else{
            return redirect()->back()->with('error','C???p nh???t kh??ng th??nh c??ng');
        }
    }
    public function destroy($id){
        $user = Auth::user();
        $equipments = Equipment::findOrFail($id);
        if ($user->can('delete', $equipments)) {
            $equipments->attachments()->detach();
            $equipments->delete();
            return redirect()->route('equipment.index')->with('success','X??a th??nh c??ng');
        }else{
            abort(403);
        }
    }
    public function select(Request $request ){
        $devices = Device::select('id','title','cat_id')->where('cat_id', $request->id)->get();
        $html_devices = '<label class="control-label">'.__('Lo???i thi???t b???').' <small></small></label>';
        $html_devices .= '<select  class="select2 form-control" name="devices_id">';
        if($devices) {
            foreach($devices as $item) {
        $html_devices .= '<option value="'.$item->id.'">'.$item->title.'</option>';
            }
        }
        $users = User::select('id','name','department_id')->where('department_id', $request->id)->get();
        $html_officer_department_charge_device = '<label class="control-label">'.__('CB khoa ph??ng ph??? tr??ch').' <small></small></label>';
        $html_officer_department_charge_device .= '<select  class="select2 form-control" name="officer_department_charge_id">';
        if($users) {
            foreach($users as $item) {
        $html_officer_department_charge_device .= '<option value="'.$item->id.'">'.$item->name.'</option>';
            }
        }
        $user_use = User::select('id','name','department_id')->get();
        $html_user_use_device = '<label class="control-label">'.__('CB s??? d???ng').'</label>';
        $html_user_use_device .= '<select  class="select2 form-control" name="equipment_user_use[]"  multiple="multiple">';
        if($user_use) {
            foreach($user_use as $item) {
            $html_user_use_device .= '<option value="'.$item->id.'"'.(($item->department_id == $request->id ? ' selected' : '')).'>'.$item->name.'</option>';
            }
        }
        $html_user_training_device = '<label class="control-label">'.__('CB ???????c ????o t???o').'</label>';
        $html_user_training_device .= '<select  class="select2 form-control" name="equipment_user_training[]"  multiple="multiple">';
        if($user_use) {
            foreach($user_use as $item) {
            $html_user_training_device .= '<option value="'.$item->id.'"'.(($item->department_id == $request->id ? ' selected' : '')).'>'.$item->name.'</option>';
            }
        }
        return response()->json([
            'check' => 'true',
            'html_devices' => $html_devices,
            'html_officer_department_charge_device' => $html_officer_department_charge_device,
            'html_user_use_device' => $html_user_use_device,
            'html_user_training_device' => $html_user_training_device
        ]);
    }
    public function export(Request $request) {
        $departments_id = isset($request->departments_id) ? $request->departments_id : '';
        $cate_id = isset($request->cate_id) ? $request->cate_id : '';
        $device_id = isset($request->device_id) ? $request->device_id : '';
        $status_id = isset($request->status_id) ? $request->status_id : '';
        $key = isset($request->key) ? $request->key : '';
        $user = Auth::user();
        if($user->can('equipment.export')) {
            return Excel::download(new EquipmentsExport($departments_id,$key,$cate_id,$device_id,$status_id), 'Danh s??ch thi???t b??? ' . Carbon::now()->format('d-m-Y') . '.xlsx');
        }else{
            abort(403);
        }
    }
    public function import(Request $request) 
    {   
        $rules = [
            'department_id'=>'required',
        ];
        $messages = [
            'department_id.required'=>'Vui l??ng ch???n khoa - ph??ng ban!',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()){ 
            return redirect()->route('equipment.listimport')->withErrors($validator)->withInput();
        }
        else{
            if($request->hasFile('equipment_file')){
                $department_id = $request->department_id;
                $status = $request->status;
                $import = new EquipmentsImport;
                $import = Excel::import($import, request()->file('equipment_file'));
                if($import){
                    return redirect()->route('equipment.index')->with('success','Import thi???t b??? th??nh c??ng');
                }else{
                    return redirect()->route('equipment.index')->with('success','Import thi???t b??? th??nh c??ng');
                }
            }
        }
    }
    public function listImport(){
        $user = Auth::user();
        if($user->can('imports.equipment')){
            $departments = Department::select('id','title')->get();
            return view('backends.equipments.listimport',compact('departments'));
        }else{
            abort(403);
        }
    }

}