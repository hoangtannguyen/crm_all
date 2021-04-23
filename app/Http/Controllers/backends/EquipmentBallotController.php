<?php
namespace App\Http\Controllers\backends;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\EquipmentBallot;
use App\Models\Provider;
use App\Models\Equipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon; 
use Illuminate\Support\Str;
class EquipmentBallotController extends Controller {
    public function index(Request  $request){
        $user = Auth::user();
        if($user->can('eqballot.read')){
            $keyword = isset($request->key) ? $request->key : '';
            $departments_key = isset($request->department_key) ? $request->department_key : '';
            $department_name = Department::select('id','title')->get();
            $ballots = EquipmentBallot::query();
            if($keyword != ''){
                $ballots = $ballots->whereHas('departments', function ($query) use ($keyword){
                $query->select('id','title')->where('departments.title','like','%'.$keyword.'%');
                    })->orWhereHas('providers', function ($q) use ($keyword){
                $q->select('id','title')->where('providers.title','like','%'.$keyword.'%');
                    })->orWhereHas('users', function ($q) use ($keyword){
                        $q->select('id','name')->where('users.name','like','%'.$keyword.'%');
                    });
                }
            if($departments_key != ''){
                $ballots = $ballots->where('department_id',$departments_key);
            }
            $ballots = $ballots->orderBy('created_at', 'desc')->paginate(15);
            return view('backends.ballot.list',compact('ballots','keyword','department_name','departments_key'));
        }else{abort(403);}
    }
    public function create(){
        $user= Auth::user();
        if($user->can('create', EquipmentBallot::class)){ 
            $departments = Department::select('id','title')->get();
            $equipments = Equipment::select('id','title','code','serial','model')->where('status','not_handed')->get();
            $providers = Provider::select('id','title','type')->provider()->get();
            $cur_time = Carbon::now()->format('Y-m-d');
            return view('backends.ballot.create',compact('departments','providers','cur_time','equipments'));
        }else{abort(403);}
    }
    public function store(Request $request){
        $rules = [
            'department_id'=>'required',
            'provider_id'=>'required',
        ];
        $messages = [
            'department_id.required'=>'Vui lòng chọn khoa phòng',
            'provider_id.required'=>'Vui lòng chọn nhà cung cấp',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->back()->withErrors($validator)->withInput();
        else:
        $atribute = $request->all();
        // $array = array();
        // foreach($request->data_id as $key => $value){
        //     $array[] = array('id' => $value,'amount' => $request->amount[$key]);
        // }
        // $atribute['equi_array'] = json_encode($array);
        $atribute['ballot'] = Carbon::now()->format('YmdHis');
        $atribute['status'] = "pendding";
        $ballots = EquipmentBallot::create($atribute);
        foreach($request->data_id as $key => $items){
            $ballots->equipments()->attach($items,['amount' => $request->amount[$key] ,'unit_price' =>  $request->unit_price[$key] ]);
        }
        return redirect()->route('ballot.index')->with('success','Thêm thành công');
        endif;
    }
    public function edit($id){
        $user = Auth::user();
        $ballots = EquipmentBallot::findOrFail($id);
        if($user->can('update', $ballots)) {
            $departments = Department::select('id','title')->get();
            $equipments = Equipment::select('id','title','code')->get();
            $providers = Provider::select('id','title','type')->provider()->get();
            $cur_time = Carbon::now()->format('Y-m-d');
            return view('backends.ballot.edit',compact('departments','providers','cur_time','equipments','ballots'));
        }else{abort(403);}
    }
    public function update(Request  $request , $id){
        $ballots = EquipmentBallot::findOrFail($id);
        $atribute = $request->all();
        $ballots->update($atribute);
        $array = array();
        foreach($request->data_id as $key => $value){
            $array[] = array('equipment_id' => $value,'amount' => $request->amount[$key],'unit_price' => $request->unit_price[$key] );
        }
        $ballots->equipments()->sync($array);
        if($ballots){
            return redirect()->route('ballot.edit',$id)->with('success','Cập nhật thành công');
        }else{
            return redirect()->route('ballot.edit',$id)->with('error','Cập nhật không thành công');
        }
    }
    public function updateSuccess(Request  $request , $id){
        $ballots = EquipmentBallot::findOrFail($id);
        $ballots->status = $request->status;
        $ballots->save();
        if($ballots){
            return redirect()->back()->with('success','Cập nhật thành công');
        }else{
            return redirect()->back()->with('error','Cập nhật không thành công');
        }
    }
    public function destroy($id){
        $user = Auth::user();
        $ballots = EquipmentBallot::findOrFail($id);
        if ($user->can('delete', $ballots)) {
            $ballots->delete();
            $ballots->equipments()->detach();
            return redirect()->route('ballot.index')->with('success','Xóa thành công');
        }else{abort(403);}
    }
    public function showEqui(Request $request ){
        $ballots = EquipmentBallot::where('id', $request->id)->first();
        $html_ballot = '';
        $html_ballot = $ballots->ballot;
        if($ballots){
            $html = '';
            foreach($ballots->equipments as $key => $items){
                $html .=  '<tr>';
                        $html .= '<td>'. ++$key .'</td>';
                        $html .= '<td>'. $items->code.'</td>';
                        $html .= '<td>'. $items->title.'</td>';
                        $html .= '<td>'. $items->model.'</td>';
                        $html .= '<td>'. $items->serial.'</td>';
                        $html .= '<td>'. $items->pivot->amount.'</td>';
                $html .=  '</tr>';
            }
        }
        return response()->json([
            'check' => 'true',
            'html' => $html,
            'html_ballot' => $html_ballot,
        ]);
    }
    public function table(Request $request ){
        $check = false;
        $equipments = Equipment::where('id',$request->id)->first();
        if($equipments) {
            $html = '';
                $html .=  '<tr data-id="'.$equipments->id.'">';
                        $html .= 
                        '<td>
                            <input name="data_id[]" type="hidden" class="hidden" value="'.$equipments->id.'">
                            <a class="remove-ballot text-danger"><i class="fas fa-times"></i></a>
                        </td>';
                        $html .= '<td>'. $equipments->code.'</td>';
                        $html .= '<td>'. $equipments->title.'</td>';
                        $html .= '<td>'. $equipments->model.'</td>';
                        $html .= '<td>'. $equipments->serial.'</td>';
                        $html .= 
                        '<td><div class="quanlity">
                                <input class="quanlity-z" name="amount[]" type="number" min="0" class="form-control" value="1">
                            </div>
                        </td>';
                        $html .= 
                        '<td><div class="currency">
                                <input class="currency-z" name="unit_price[]" type="number" min="0" class="form-control" value="'.$equipments->import_price.'">
                            </div>
                        </td>';
                        $html .= '<td class="total" name="price[]">'.$equipments->import_price.'</td>';
                $html .=  '</tr>';
            $check = true;
        };
        return response()->json([
            'check' => 'true',
            'html' => $html,
        ]);
    }
}