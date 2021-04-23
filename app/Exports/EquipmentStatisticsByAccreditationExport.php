<?php

namespace App\Exports;

use App\Models\Equipment;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class EquipmentStatisticsByAccreditationExport implements FromCollection, WithHeadings , WithMapping ,ShouldAutoSize ,WithEvents
{

    /**
    * @return \Illuminate\Support\Collection
    */

    private $i = 1;
    protected $key;
    public function __construct($key) {
        $this->key = $key;
    }
    public function collection()
    {
        $current_month = Carbon::now()->toDateString();
        $equipments = Equipment::query();
        if($this->key !='') $equipments= $equipments->where('equipments.title','like','%'.$this->key.'%');
        $equipments = $equipments->whereRaw('TIMESTAMPDIFF(MONTH, first_inspection, "'.$current_month.'")%regular_inspection = 0')
                        ->where('equipments.first_inspection','!=',null)->orderby('equipments.first_inspection','asc')->get();
        return $equipments;
    }

    public function headings() :array {
        return [
         "# STT",
         "Kiểm định điểm kỳ",
         "Ngày kiểm định lần đầu",
         "Tên TB",
         "Mã TB",
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
            $equipment->regular_inspection.' tháng',
            date('d-m-Y', strtotime($equipment->first_inspection)),
            $equipment->title != null ? $equipment->title : 'NULL',
            $equipment->code != null ? $equipment->code : 'NULL',
            isset($equipment->equipment_unit) ? $equipment->equipment_unit->title : 'NULL',
            $equipment->model != null ? $equipment->model : 'NULL',
            $equipment->serial != null ? $equipment->serial : 'NULL',
            $equipment->manufacturer != null ? $equipment->manufacturer : 'NULL',
            $equipment->origin != null ? $equipment->origin : 'NULL',
            $equipment->year_manufacture != null ? $equipment->year_manufacture : 'NULL',
            $equipment->year_use  != null ? $equipment->year_use : 'NULL',
            isset($statusEquipments[$equipment->status]) ? $statusEquipments[$equipment->status] :'',
            $equipment->import_price != null ? convert_currency($equipment->import_price) : '0',            
            $equipment->amount != null ? $equipment->amount : 'NULL',
            convert_currency($equipment->amount * $equipment->import_price),             
        ];
    }


}