<?php

namespace App\Imports;

use App\Models\Equipment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EquipmentsImport implements ToCollection ,WithStartRow ,WithValidation
{   
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function startRow(): int
    {
        return 4;
    }
    

    public function rules(): array
    {
        
        return [
            '3' => ['required',Rule::unique('equipments', 'serial')],
            /*'1' => ['required',Rule::unique('equipments', 'code')],*/
        ];
      
    }

    public function customValidationMessages()
    {
        return [
            '3.unique' => 'Trường serial đã tồn tại !',
            '3.required' => 'Vui lòng nhập trường serial !',
            /*'1.unique' => 'Trường mã thiết bị đã tồn tại !',
            '1.required' => 'Vui lòng nhập trường mã thiết bị !',*/
        ];
    }


    public function collection(Collection $rows)
    {
        $equi_array  = array();

            
        foreach ($rows as $key => $row) {

            if($rows[$key][0] != null && $rows[$key][3] != null){

                $department_id = request('department_id');
                $row['department_id'] = $department_id;

                $status = request('status');
                $row[12] = $status;

                    $res = Equipment::create([
                        'title' => $row[0],
                        'code' => $row[1],
                        'model' => $row[2],
                        'serial' => $row[3],
                        'manufacturer'=> $row[4],
                        'origin' => $row[5],
                        'year_manufacture' => $row[6],
                        'year_use' => $row[7],
                        'amount' => $row[8],
                        'status' => $row[9],
                        'note' => $row[10],
                        'status' => $row[12],
                        'warehouse' => $row[13],
                        'first_inspection' => $row[15],
                        'warranty_date' => $row[16],
                        'specificat' => $row[17],
                        'configurat' => $row[18],
                        'import_price' => $row[19],
                        'provider_id' => $row[20],
                        'process' => $row[21],
                        'user_id' => $row[25],
                        'cate_id' => $row[26],
                        'devices_id' => $row[27],
                        'first_value' => $row[28],
                        'depreciat' => $row[29],
                        'first_information' => $row[30],
                        'officer_charge_id' => $row[31],
                        'equipment_user_use' => $row[32],
                        'equipment_user_training' => $row[33],
                        'department_id' => $row['department_id'],
                    ]);     
                if($res) $equi_array[] = $res;
            }
        }
        return $equi_array;
    }
}
