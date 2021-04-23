<?php 
use App\Models\Media;
use App\Models\Option;
use App\Models\User;
use App\Models\Department;
use App\Models\Liquidation;
use App\Models\Unit;
include('helpers/mediaCategory.php');
include('helpers/media.php');
include('helpers/post.php');
include('helpers/page.php');
if (! function_exists('get_option')) {
	function get_option($key){
		$option = Option::select('value')->where('key', $key)->first();
		if($option) return $option->value;
			else return NULL;
	}
}
if (! function_exists('format_dateCS')) {
	function format_dateCS($date, $not_full = null){
		if($not_full==null) return date_format($date,'Y-m-d H:i:s');
			else return date_format($date,'Y-m-d');
	}
}
if (! function_exists('image')) {
	function image($id, $w, $h, $alt=''){
        $allow = array('gif','png','jpg','jpeg','JPEG','svg','PNG','JPG', 'GIF','SVG');
		$img = Media::find($id);
		if($img && in_array($img->type,$allow))
			$html = ($img->type!="svg") ? '<img src="/image/'.$img->path.'/'.$w.'/'.$h.'" alt="'.$alt.'"/>' : '<img src="'.url('uploads').'/'.$img->path.'"/>';
		else
			$html = '<img src="/image/noImage.jpg/'.$w.'/'.$h.'" alt="'.$alt.'"/>';
		return $html;
	}
}
if (! function_exists('imageAuto')) {
	function imageAuto($id, $alt){
		$image = Media::find($id);
		if(!empty($image))
			$html = '<img src="'.url('uploads').'/'.$image->path.'" alt="'.$alt.'">';
		else
			$html = '<img src="'.url('uploads').'/noImage.jpg" alt="'.$alt.'"/>';
		return $html;
	}
}
if (! function_exists('imageAutoWord')) {
	function imageAutoWord($id){
		$image = Media::find($id);
		if(!empty($image))
			$html = url('uploads').'/'.$image->path;
		else
			$html = url('uploads').'/noImage.jpg';
		return $html;
	}
}
if(! function_exists('get_statusProvider')){
	function get_statusProvider(){
		return array(
			'provided' => 'Cung cấp',
			'repair' => 'Sửa chữa',
			'maintenance' => 'Bảo trì',
			'accreditation' => 'Kiểm định',
		);
	}
}
if(! function_exists('get_statusProjects')){
	function get_statusProjects(){
		return array(
			'active' => 'Đang thực hiện',
			'inactive' => 'Đã kết thúc',
		);
	}
}
if(! function_exists('get_statusEquipments')){
	function get_statusEquipments(){
		return array(
			'not_handed' => 'Mới',
			'active' => 'Đang sử dụng',
			'was_broken' => 'Đang báo hỏng',
			'corrected' => 'Đang sửa chữa',
			'inactive' => 'Đã ngưng sử dụng',
			'liquidated' => 'Đã thanh lý'
		);
	}
}
if(! function_exists('get_statusEquipmentFilter')){
	function get_statusEquipmentFilter(){
		return array(
			'not_handed' => 'Chưa bàn giao',
			'active' => 'Đang sử dụng',
			'was_broken' => 'Đang báo hỏng',
			'corrected' => 'Đang sửa chữa',
			'inactive' => 'Đã ngưng sử dụng',
			'liquidated' => 'Đã thanh lý'
		);
	}
}
if(! function_exists('get_statusAction')){
	function get_statusAction(){
		return array(
			'active' => 'Đang sử dụng',
			'inactive' => 'Hết sử dụng',
		);
	}
}
if(! function_exists('get_statusCorrected')){
	function get_statusCorrected(){
		return array(
			'active' => 'Đã sửa chữa , tình trạng sử dụng tốt',
			'inactive' => 'Không thể khắc phục, chuyển vào kho thanh lý',
		);
	}
}
if(! function_exists('get_RegularInspection')){
	function get_RegularInspection(){
		return array(
			'optional' => "Không bắt buộc",
			'1' => '1 tháng',
			'2' => '2 tháng',
			'3' => '3 tháng',
			'6' => '6 tháng',
			'12' => '12 tháng',
			'24' => '24 tháng',
			'36' => '36 tháng',
		);
	}
}
if(! function_exists('get_statusRisk')){
	function get_statusRisk(){
		return array(
			'A'=>'A',
			'B'=>'B',
			'C'=>'C',
			'D'=>'D',
		);
	}
}
if(! function_exists('get_statusTransfer')){
	function get_statusTransfer(){
		return array(
			'pendding' => 'Chưa xử lý',
			'public' => 'Đã xử lý',
			'cancel' => 'Hủy',
		);
	}
}
if(! function_exists('get_statusBallot')){
	function get_statusBallot(){
		return array(
			'pendding' => 'Chưa duyệt',
			'public' => 'Đã duyệt',
		);
	}
}
if(! function_exists('get_CompatibleEq')){
	function get_CompatibleEq(){
		return array(
			'supplies_can_equipment' => 'Vật tư có thể được sử dụng cho thiết bị',
			'spelled_by_device' => 'Vật tư kèm theo thiết bị',
		);
	}
}
if(! function_exists('random_color')){
	function random_color() {
	    return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
	}
}
if(! function_exists('generate_random_color')){
	function generate_random_color($amount) {
	  $_result = array();
	  for ($i = 0 ; $i < $amount; $i++) {
	    $_result[] = random_color();
	  }
	  return $_result;
	}
}
//number format
if (! function_exists('convert_currency')) {
	function convert_currency($number){
		return number_format($number,0,".",".");
	}
}
if(! function_exists('generate_frequency')){
	function generate_frequency(){
		return array(
			'1d' => __('hàng ngày'),
			'1w' => __('hàng tuần'),
			'2w' => __('2 tuần'),
			'3w' => __('3 tuần'),
			'1m' => __('hàng tháng'),
			'2m' => __('2 tháng'),
			'3m' => __('3 tháng'),
			'4m' => __('4 tháng'),
			'5m' => __('5 tháng'),
			'6m' => __('6 tháng'),
			'7m' => __('7 tháng'),
			'8m' => __('8 tháng'),
			'9m' => __('9 tháng'),
			'10m' => __('10 tháng'),
			'11m' => __('11 tháng'),
			'1y' => __('hàng năm'),
			'2y' => __('2 năm'),
		);
	}
}

if(! function_exists('generate_maint_action')){
	function generate_maint_action(){
		return array(
			'C' => 'Kiểm tra',
			'I' => 'Kiểm định',
			'M' => 'Bảo dưỡng',
		);
	}
}
if(! function_exists('acceptanceRepair')){
	function acceptanceRepair(){
		return array(
			'create' => 'Tạo lịch sửa chữa',
			'fixing' => 'Đang sửa chữa',
			'not_accepted' => 'Sửa xong, chưa nghiệm thu',
			'accepted' => 'Sửa xong, đã nghiệm thu',
			'unknown' => 'Không xác định',
		);
	}
}
if(! function_exists('getStatusLiquidation')){
	function getStatusLiquidation(){
		return array(
			'waiting' => 'Chờ thanh lý',
			'liquidated' => 'Đã thanh lý',
		);
	}
}
if(! function_exists('getActivity')){
	function getActivity(){
		return array(
			'created' => 'Thêm mới',
			'updated' => 'Cập nhật',
			'deleted' => 'Xóa',
			'login' => 'Đăng nhập hệ thống',
			'was_broken' => 'Thiết bị đang báo hỏng ',
			'active' => 'Thiết bị đang sử dụng ',
			'inactive' => 'Thiết bị đã ngưng sử dụng',
			'liquidated' => 'Thiết bị đã thanh lý',
			'corrected' => 'Đã lên lịch sửa chữa',
		);
	};
}
if(! function_exists('getUserById')){
	function getUserById($id){
		$user = User::where('id',$id)->select('id','name')->first();
		return $user->name;
	};
}

if(! function_exists('getDepartmentById')){
	function getDepartmentById($id){
		$department = Department::where('id',$id)->select('id','title')->first();
		return $department->title;
	};
}

if(! function_exists('getConvertStatus')){
	function getConvertStatus(){
		return array(
			'device_failed' => 'Thiết bị được báo hỏng',
		);
	};
}
if(! function_exists('getUnit')){
	function getUnit(){
		return Unit::select('title','id')->pluck('id','title')->toArray();
	};
}
if(! function_exists('getStatusSupplie')){
	function getStatusSupplie(){
		return array(
			'1' => 'Mới',
			'2' => 'Đang sử dụng tốt',
			'3' => 'Đang hỏng',
			'4' => 'Ngưng sử dụng',
			'5' => 'Đã thanh lý',
			'6' => 'Điều chuyển',
			'7' => 'Kém phẩm chất',
		);
	};
}
if(! function_exists('convertPermission')){
	function convertPermission(){
		return array(
			'read' => 'Xem',
			'create' => 'Thêm',
			'update' => 'Sửa',
			'delete' => 'Xóa',
			'show_all' => 'Xem tất cả',
			'supplie' => 'Vật tư',
			'equipment' => 'Thiết bị',
			'status' => 'Trạng thái',
			'export' => 'Xuất excel',
			'create_supplie' => 'Thêm VT',
			'show' => 'Xem hồ sơ',
			'approved' => 'Duyệt',
			'create_amount' => 'Thêm số lượng',
			'create_input' => 'Nhập vật tư',
			'hand' => 'Bàn giao thiết bị',
			'update_status' => 'Cập nhật trạng thái',
			'history_status' => 'Lịch sử trạng thái',
			'liquidation' => 'Thanh lý thiết bị',
			'info' => 'Thông tin',
			'config' => 'Thiết lập tính năng',
			'roles' => 'Roles and Permissions',
			'equipment' => 'Nhập thiết bị',
			'supplie' => 'Nhập vật tư',
			'repair' => 'Yêu cầu sửa chữa',
			'liquidation' => 'Thanh lý thiết bị',
			'maintenance' => 'Yêu cầu bảo dưỡng',
			'transfer' => 'Điều chuyển',
			'supplie_department' => 'Vật tư theo khoa',
			'department' => 'Khoa',
			'classify' => 'Loại, nhóm, trạng thái',
			'year' => 'Năm',
			'risk' => 'Mức độ rủi ro',
			'project' => 'Dự án',
			'warranty_date' => 'Hết hạn bảo hành',
			'supplies' => 'Vật tư',
			'accreditation' => 'Kiểm định',
			'diary' => 'Nhật ký hoạt động',
		);
	}
}
// convert time elapsed
if( ! function_exists('timeElapsedString')){
	function timeElapsedString($datetime, $full = false) {
		$now = new DateTime;
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);

		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		$string = array(
			'y' => 'năm',
			'm' => 'tháng',
			'w' => 'tuần',
			'd' => 'ngày',
			'h' => 'giờ',
			'i' => 'phút',
			's' => 'giây',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v;
			} else {
				unset($string[$k]);
			}
		}
		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' trước' : 'vừa nãy';
	}
}