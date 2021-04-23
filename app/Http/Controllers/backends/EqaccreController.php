<?php
namespace App\Http\Controllers\backends;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Action;
use App\Models\Equipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class ActionController extends Controller {
    public function index(Request  $request){
        $user = Auth::user();
        $keyword = isset($request->key) ? $request->key : '';
        $eqaccres = Action::query();
        if($user->can('eqaccre.read')){
            if($keyword != ''){
                $eqaccres = $eqaccres->where(function ($query) use ($keyword) {
                    $query->where('reason','like','%'.$keyword.'%')
                        ->orWhere('content','like','%'.$keyword.'%');
                    });
            }
            $eqaccres = $eqaccres->accre()->paginate(10);
            return view('backends.eqaccres.list', compact('eqaccres','keyword',));
        }else{
            if($keyword != ''){
                $eqaccres = $eqaccres->where(function ($query) use ($keyword) {
                    $query->where('reason','like','%'.$keyword.'%')
                        ->orWhere('content','like','%'.$keyword.'%');
                    });
            }
            $eqaccres = $eqaccres->where('user_id',$user->id)->accre()->paginate(10);
            return view('backends.eqaccres.list', compact('eqaccres','keyword',));
        }
    }
    public function create(){
        $user = Auth::user();
        if($user->can('eqaccre.create')){ 
            $equipments = Equipment::select('id','title')->device()->get();
            return view('backends.eqaccres.create',compact('equipments'));
        }else{
          abort(403);
        }
    }
    public function store(Request  $request){
        $rules = [
            'user_id'=>'required',
            'reason'=>'required',
            'content'=>'required',
            'equi_id'=>'required',
        ];
        $messages = [
            'user_id.required'=>'Please enter user',
            'reason.required'=>'Please enter reason',
            'content.required'=>'Please choose content',
            'equi_id.required'=>'Please enter equipment',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->route('eqaccre.create')->withErrors($validator)->withInput();
        else:
        $atribute = $request->all();
        $atribute['type'] = 'accreditation';
        Action::create($atribute);
        return redirect()->route('eqaccre.index')->with('success','Thêm thành công');
        endif;
    }
    public function edit($id){
        $user = Auth::user();
        $eqaccres = Action::findOrFail($id);
        if($user->can('update', $eqaccres)){
            $equipments = Equipment::select('id','title')->device()->get();
            return view('backends.eqaccres.edit',compact('eqaccres','equipments'));
        }else{
          abort(403);
        }
    }
    public function update(Request  $request , $id)
    {
        $rules = [
            'user_id'=>'required',
            'reason'=>'required',
            'content'=>'required',
            'equi_id'=>'required',
        ];
        $messages = [
            'user_id.required'=>'Please enter user',
            'reason.required'=>'Please enter reason',
            'content.required'=>'Please choose content',
            'equi_id.required'=>'Please enter equipment',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()):
            return redirect()->route('eqaccre.edit',$id)->withErrors($validator)->withInput();
        else:
        $eqaccres = Action::findOrFail($id);
        $atribute = $request->all();
        $eqaccres->update($atribute);
        if($eqaccres){
            if($eqaccres->wasChanged())
            return redirect()->route('eqaccre.edit',$id)->with('success','Cập nhật thành công');
        else 
            return redirect()->route('eqaccre.edit',$id);
        }else{
            return redirect()->route('eqaccre.edit',$id)->with('error','Cập nhật không thành công');
        }
    endif;
    }
    public function destroy($id){
        $user = Auth::user();
        $eqaccres = Action::findOrFail($id);
        if ($user->can('delete', $eqaccres)) {
            $eqaccres->delete();
            return redirect()->route('eqaccre.index')->with('success','Xóa thành công');
        }else{
          abort(403);
        }
    }
}