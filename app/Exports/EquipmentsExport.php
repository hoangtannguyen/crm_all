<?php

namespace App\Exports;

use App\Models\Equipment;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class EquipmentsExport implements FromCollection, WithHeadings , WithMapping ,ShouldAutoSize ,WithEvents
{

    /**
    * @return \Illuminate\Support\Collection
    */

    private $i = 1;
    protected $departments_id;
    protected $device_id;
    protected $cate_id;
    protected $status_id;
    protected $key;

    public function __construct($departments_id,$key,$cate_id,$device_id,$status_id) {
        $this->departments_id = $departments_id;
        $this->cate_id = $cate_id;
        $this->device_id = $device_id;
        $this->status_id = $status_id;
        $this->key = $key;
    }

    public function collection()
    {
        $departments_id = $this->departments_id;
        $cate_id = $this->cate_id;
        $device_id = $this->device_id;
        $status_id = $this->status_id;
        $key = $this->key;

        $departments_query = function ($query) use ($departments_id) {
            if($departments_id != ''){
                return $query->select('departments.id','departments.title')->where('departments.id',$departments_id);
            }else{
                return $query->select('departments.id','departments.title');
            }
        };

        $cate_query = function ($query) use ($cate_id) {
            if($cate_id != ''){
                return $query->select('equipment_cates.id','equipment_cates.title')->where('equipment_cates.id',$cate_id);
            }else{
                return $query->select('equipment_cates.id','equipment_cates.title');
            }
        };

        $device_query = function ($query) use ($device_id) {
            if($device_id != ''){
                return $query->select('devices.id','devices.title')->where('devices.id',$device_id);
            }else{
                return $query->select('devices.id','devices.title');
            }
        };

        $equipments = Equipment::query();

        if($key != ''){
            $equipments = $equipments->where(function ($query) use ($key) {
            $query->where('title','like','%'.$key.'%')
                ->orWhere('code','like','%'.$key.'%')
                ->orWhere('model','like','%'.$key.'%')
                ->orWhere('serial','like','%'.$key.'%')
                ->orWhere('manufacturer','like','%'.$key.'%')
                ->orWhere('origin','like','%'.$key.'%')
                ->orWhere('year_manufacture','like','%'.$key.'%')
                ->orWhere('year_use','like','%'.$key.'%');
            });
        }

        if($status_id != '') $equipments= $equipments->where('equipments.status','like','%'.$status_id.'%');
        if($departments_id != '') $equipments= $equipments->whereHas('equipment_department', $departments_query);
        if($cate_id != '') $equipments= $equipments->whereHas('equipment_cates', $cate_query);
        if($device_id != '') $equipments= $equipments->whereHas('equipment_device', $device_query);
        $equipments= $equipments->orderby('created_at','desc')->get(); 
        return $equipments;
    }



    public function headings() :array {
        return [
         "# STT",
         "Tên thiết bị",
         "Model",
         "Năm sản xuất",
         "Ngày nhập kho",
         "Nhóm thiết bị", 
         "Loại thiết bị", 
         "Đơn vị tính",
         "Người nhập",
         "Trạng thái",
         "Mức độ rủi ro",
         "Số lượng",
         "Hãng sản xuất",
         "Xuất xứ",
         "Mã thiết bị",
         "Số serial",
         "Đơn vị bảo trì",
         "Nhà cung cấp",
         "Đơn vị sửa chũa",
         "Khoa - Phòng Ban",
         "Ngày kiểm định lần đầu",
         "Thông số kỹ thuật",
         "Giá trị ban đầu",
         "Quy trình sử dụng",
         "Năm sử dụng",
         "CB phòng VT phụ trách",
         "CB sử dụng",
         "Ngày nhập thông tin",
         "Giá nhập",
         "Dự án thầu",
         "Ngày hết hạn bảo hành",
         "Cấu hình kỹ thuật",
         "Khấu hao hằng năm",
         "Ghi chú",
         "CB khoa phòng phụ trách",
         "CB được đào tạo",
        ];
    }

    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:AJ1'; 
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
            },
        ];
    }
    

    public function map($equipments): array {

        $statusEquipments = get_statusEquipments();
        $get_statusRisk = get_statusRisk();

        $user_use = '';
        $user_training = '';
  
        foreach ($equipments->equipment_user_use as $number_user_use => $equipment_user_use){
            if($number_user_use != 0) $user_use .=  ', '.$equipment_user_use->name;
                 else $user_use .=  $equipment_user_use->name;
        }

        foreach ($equipments->equipment_user_training as $number_user_training => $equipment_user_training){
            if($number_user_training != 0) $user_training .=  ', '.$equipment_user_training->name;
                 else $user_training .=  $equipment_user_training->name;
         }


        return [
            $this->i++,
            isset($equipments->title) ? $equipments->title :'',
            $equipments->model,
            $equipments->year_manufacture,
            $equipments->warehouse,
            isset($equipments->equipment_cates->title) ? $equipments->equipment_cates->title :'',
            isset($equipments->equipment_device->title) ? $equipments->equipment_device->title :'',
            isset($equipments->equipment_unit->title) ? $equipments->equipment_unit->title :'',
            isset($equipments->equipment_user->name) ? $equipments->equipment_user->name :'',
            isset($statusEquipments[$equipments->status]) ? $statusEquipments[$equipments->status] :'',
            isset($get_statusRisk[$equipments->risk]) ? $get_statusRisk[$equipments->risk] :'',
            $equipments->amount,
            $equipments->manufacturer,
            $equipments->origin,
            $equipments->code,
            $equipments->serial,
            isset($equipments->equipment_maintenance->title) ? $equipments->equipment_maintenance->title :'',
            isset($equipments->equipment_provider->title) ? $equipments->equipment_provider->title :'',
            isset($equipments->equipment_repair->title) ? $equipments->equipment_repair->title :'',
            isset($equipments->equipment_department->title) ? $equipments->equipment_department->title :'',
            $equipments->first_inspection,
            $equipments->specificat,
            $equipments->first_value,
            $equipments->process,
            $equipments->year_use,
            isset($equipments->equipment_user_charge->name) ? $equipments->equipment_user_charge->name :'',
            $user_use,
            $equipments->first_information,
            $equipments->import_price,
            $equipments->bid_project,   
            $equipments->warranty_date,
            $equipments->configurat,
            $equipments->depreciat,
            $equipments->note,
            isset($equipments->equipment_user_department_charge->name) ? $equipments->equipment_user_department_charge->name :'',
            $user_training,
        ];
    }


}
