<?php
namespace App\Http\Controllers\backends;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Department;
use App\Models\Provider;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
class UserAdminController extends Controller {
    public function index(Request $request){
        $user = Auth::user();
        if($user->can('users.show_all')){
            $s = $request->s;
            $role = $request->role;
            $users = User::query();
            if($role != "") $users = $users->role($role);
            if($s != "") $users = $users->where('name','like', '%'.$s.'%');
            $users = $users->latest()->paginate(12);
            $data = [
                'users' => $users,
                's' => $s,
                'role' => $role,
                'roles' => Role::all(),
            ];
            return view('backends.users.list',$data);
        }else{
            $user = User::findOrFail(Auth::id());
            $departments = Department::select('id','title')->get();
            $data = [
                'user' => $user,
                'roles' => Role::all(),
                'departments' => $departments
            ];
            return view('backends.users.edit', $data);
        }
    }
    public function create(Request $request){
        $user = Auth::user();
        if($user->can('users.create')){
            $departments = Department::select('id','title')->get();
            $data = [
            'roles' => Role::all(),
            'departments' => $departments
            ];
            return view('backends.users.create', $data);
        }else{abort(403);}
    }
    public function store(Request $request, CreatesNewUsers $creator){
        event(new Registered($user = $creator->create($request->all())));
        if($user){
            $request->session()->flash('success', 'Create Successful!');
            if($request->role != '') {
                $check_exist = Role::where('name', $request->role)->first();
                if($check_exist) $user->assignRole($request->role);
            else {
                $request->session()->flash('error', 'Role '.$request->role.' not exist!');
                return redirect()->route('admin.users');
                }
            }
        }else{
            $request->session()->flash('error', 'Has error!');
            return redirect()->route('admin.user_create');
        }
        return redirect()->route('admin.users');
    }
    public function edit(Request $request, $id){
        $users = Auth::user();
        if($users->can('users.show_all')){
            $user = User::findOrFail($id);
            $departments = Department::select('id','title')->get();
            $data = [
                'user' => $user,
                'roles' => Role::all(),
                'departments' => $departments
            ];
            return view('backends.users.edit', $data);
        }else{abort(403);}
    }
    public function update(Request $request, $id){
        $user = User::findOrFail($id);
        $rules = [
            'phone'=>['required',Rule::unique('users')->ignore($user->id)],
            'email'=>['required','email',Rule::unique('users')->ignore($user->id)],
            'displayname'=>'required',
            'department_id'=>'required',
        ];
        $messages = [
            'phone.required'=>'Please input phone number!',
            'phone.unique'=>'Phone had exist!',
            'email.required'=>'Please input email!',
            'email.unique'=>'Email had exist!',
            'displayname.required'=>'Please input Display name!',
            'department_id'=>'Please input department!',
        ];
        if($request->password != ''){
            $rules['password'] = 'required|min:8|max:32';
            $rules['confirmPassword'] = 'required|same:password';
            $messages['password.required'] = 'Please input password!';
            $messages['password.min'] = 'Password min is 8 characters!';
            $messages['password.max'] = 'Password min is 32 characters!';
            $messages['confirmPassword.required'] = 'Please confirm password!';
            $messages['confirmPassword.same'] = 'Password confirm not match!';
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){
            return redirect()->route('admin.user_edit',['id'=>$id])->withErrors($validator)->withInput();
        }else{
            $user->phone = $request['phone'];
            $user->email = $request['email'];
            $user->address = $request['address'];
            $user->image = $request['image'];
            $user->displayname = $request['displayname'];
            $user->department_id = $request['department_id'];
            $user->gender = $request['gender'];
            $user->birthday = $request['birthday'];
            $user->is_disabled = isset($request['is_disabled']) ? $request['is_disabled'] : '0';
            if($request->password != '') $user->password = bcrypt($request->password);
            if($user->save()) {
                if($request->role != '') {
                    $check_exist = Role::where('name', $request->role)->first();
                    if($check_exist) $user->syncRoles([$request->role]);
                    else {
                        $request->session()->flash('error', 'Role '.$request->role.' not exist!');
                        return redirect()->route('admin.users');
                    }
                }
                //dd($user);
                $request->session()->flash('success', 'Update Successful!');
                return redirect()->route('admin.users');
            }else{
                $request->session()->flash('error', 'Has error!');
                return redirect()->route('admin.user_edit',['id'=>$id]);
            }
        }
    }
    public function delete(Request $request, $id){
        $user = User::findOrFail($id);
        $request->session()->flash('success', 'Delete Successful!');
        $user->delete();
        return redirect()->route('admin.users');
    }
    public function deleteChoose(Request $request){
        $items = explode(",",$request->items);
        if(count($items)>0){
            $request->session()->flash('success', 'Delete Successful!');
            User::destroy($items);
        }else{
            $request->session()->flash('error', 'Has error!');
        }
        return redirect()->route('admin.users');
    }
    public function indexActivity(Request $request){
        $users = Auth::user();
        if($users->can('users.diary')){
            $keyword = isset($request->key) ? $request->key : '';
            $activitys_key = isset($request->activity_key) ? $request->activity_key : '';
            $users_key = isset($request->user_key) ? $request->user_key : '';
            $user_name = User::select('id','name')->get();
            $data_link = array();
            $activities = Activity::query();
            if($keyword != ''){
                $activities = $activities->whereHas(function ($query) use ($keyword) {
                $query->where('description','like','%'.$keyword.'%')
                    ->orWhere('created_at','like','%'.$keyword.'%');
                });
            }
            if($users_key != ''){
                $activities = $activities->where('causer_id',$users_key);
                $data_link['user_key'] = $users_key;
            }
            if($activitys_key != ''){
                $activities = $activities->where('description',$activitys_key);
                $data_link['activity_key'] = $activitys_key;
            }
            $activities = $activities->orderBy('created_at', 'desc')->paginate(10);
            return view('backends.users.activity',compact('activities','keyword','users_key','user_name','activitys_key','data_link'));
        }else{abort(403);}
    }
    public function destroyActivity($id)
    {
        $activities = Activity::findOrFail($id);
        $activities->delete();
        return redirect()->route('admin.index_activity')->with('success','Xóa thành công');
    }
    public function deleteChooseActivity(Request $request){
        $items = explode(",",$request->items);
        if(count($items)>0){
            $request->session()->flash('success', 'Xóa thành công!');
            Activity::destroy($items);
        }else{
            $request->session()->flash('error', 'Có lỗi!');
        }
        return redirect()->route('admin.index_activity');
    }
    public function updatePassword(Request $request){
        $this->validate($request,[
            'oldPass'=>'required',
            'newPass'=>'required',
            'confirmPass'=>'required',
            ],[
            'oldPass.required'=>'Bạn chưa nhập mật khẩu cũ!',
            'newPass.required'=>'Bạn chưa nhập mật khẩu mới!',
            'confirmPass.required'=>'Bạn chưa nhập lại mật khẩu!',
        ]);
        $user = Auth::User();
        $checkPass = password_verify($request->oldPass, $user->password);
        if($checkPass){
            $user->password = bcrypt($request->newPass);
            $user->save();
            $request->session()->flash('success','Thay đổi mật khẩu thành công!');
            return redirect()->route('admin.dashboard');
        }else{           
            $request->session()->flash('error','Mật khẩu cũ không đúng!');
            return redirect()->back();
        }
    }
    public function createPermission(Request $request, $permission) {
        return Permission::firstOrCreate(['name' => $permission]);
    }
    public function yourProfile($id){
        $user = User::findOrFail($id);
        $departments = Department::select('id','title')->get();
        $data = [
            'user' => $user,
            'departments' => $departments
        ];
        return view('backends.users.profile', $data);
    }
    public function updateProfile(Request $request, $id){
        $user = User::findOrFail($id);
        $rules = [
            'phone'=>['required',Rule::unique('users')->ignore($user->id)],
            'email'=>['required','email',Rule::unique('users')->ignore($user->id)],
            'displayname'=>'required',
            'department_id'=>'required',
        ];
        $messages = [
            'phone.required'=>'Please input phone number!',
            'phone.unique'=>'Phone had exist!',
            'email.required'=>'Please input email!',
            'email.unique'=>'Email had exist!',
            'displayname.required'=>'Please input Display name!',
            'department_id'=>'Please input department!',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        }else{
            $user->phone = $request['phone'];
            $user->email = $request['email'];
            $user->address = $request['address'];
            $user->image = $request['image'];
            $user->displayname = $request['displayname'];
            $user->department_id = $request['department_id'];
            $user->gender = $request['gender'];
            $user->birthday = $request['birthday'];
            if($user->save() && $user->wasChanged()) {
                $request->session()->flash('success', 'Cập nhật thành công');
                return redirect()->back();
            }else{
                $request->session()->flash('error', 'Cập nhật thất bại!');
                return redirect()->back();
            }
        }
    }

}