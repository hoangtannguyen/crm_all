<?php



namespace App\Exports;



use App\Models\Equipment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class EquipmentStatisticsByFacultyExport implements FromCollection, WithHeadings , WithMapping ,ShouldAutoSize ,WithEvents

{

    /**

    * @return \Illuminate\Support\Collection

    */
    private $i = 1;
    protected $departments_id;
    protected $status_id;
    protected $key;

    public function __construct($departments_id,$status_id,$key) {

        $this->departments_id = $departments_id;

        $this->status_id = $status_id;

        $this->key = $key;

    }

    public function collection()
    {

        $departments_id = $this->departments_id;

        $equipments_query = function ($query) use ($departments_id) {

            if($departments_id != ''){

                return $query->select('departments.id','departments.title')

                            ->where('departments.id',$departments_id);

            }else{

                return $query->select('departments.id','departments.title');

            }

        };

        $equipments = Equipment::query();

        if($this->key !='') $equipments= $equipments->where('equipments.title','like','%'.$this->key.'%');

        if($this->status_id != '') $equipments= $equipments->where('equipments.status','like','%'.$this->status_id.'%');

        $equipments = $equipments->with(['equipment_department'=>$equipments_query])

                            ->has('equipment_department')

                            ->whereHas('equipment_department', $equipments_query)

                            ->orderby('equipments.department_id','asc')->get();

        return $equipments;

    }

   

    public function headings() :array {

        return [

         "# STT",

         "Khoa",

         "Nhóm TB",

         "Mã TB",

         "Tên TB",

         "DVT", 

         "Model", 

         "S/N",

         "Hãng XS",

         "Nước XS",

         "Năm SX",

         "Năm SD",

         "Tình trạng",

         "Đơn giá",

         "Số lượng",

         "Thành tiền",

        ];

    }



    public function registerEvents(): array

    {

        return [

            AfterSheet::class    => function(AfterSheet $event) {

                $cellRange = 'A1:P1'; 

                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);

                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);

            },

        ];

    }

    



    public function map($equipment): array {

        $statusEquipments = get_statusEquipments();

        return [

            $this->i++,

            isset($equipment->equipment_department) ? $equipment->equipment_department->title : 'NULL',

            isset($equipment->equipment_cates) ? $equipment->equipment_cates->title : 'NULL',

            $equipment->code != null ? $equipment->code : 'NULL',

            $equipment->title != null ? $equipment->title : 'NULL',

            isset($equipment->equipment_unit) ? $equipment->equipment_unit->title : 'NULL',

            $equipment->model != null ? $equipment->model : 'NULL',

            $equipment->serial != null ? $equipment->serial : 'NULL',

            $equipment->manufacturer != null ? $equipment->manufacturer : 'NULL',

            $equipment->origin != null ? $equipment->origin : 'NULL',

            $equipment->year_manufacture != null ? $equipment->year_manufacture : 'NULL',

            $equipment->year_use  != null ? $equipment->year_use : 'NULL',

            isset($statusEquipments[$equipment->status]) ? $statusEquipments[$equipment->status] :'NULL',

            $equipment->import_price != null ? convert_currency($equipment->import_price) : '0',            

            $equipment->amount != null ? $equipment->amount : '0',

            convert_currency($equipment->amount * $equipment->import_price),            

        ];

    }





}

