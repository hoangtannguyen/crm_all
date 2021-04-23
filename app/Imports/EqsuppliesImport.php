<?php

namespace App\Imports;

use App\Models\Eqsupplie;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class EqsuppliesImport implements ToCollection ,WithStartRow ,WithValidation
{   
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        
        return [
            '10' => ['required',Rule::unique('equipment_supplies', 'serial')],
        ];
      
    }

    public function customValidationMessages()
    {
        return [
            '10.unique' => 'Trường serial đã tồn tại !',
            '10.required' => 'Vui lòng nhập trường serial !',
        ];
    }

    
    public function collection(Collection $rows)
    {
        $units = getUnit();
        $equi_array  = array();
        foreach ($rows as $key => $row) 
        {
            $project_id = request('project_id');
            $row['project_id'] = $project_id;
            $warehouse = $row[6] != null ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($row[6]))->format('Y-m-d') : '';
            $first_information = $row[7] != null ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($row[7]))->format('Y-m-d') : '';
            Eqsupplie::create([
                'title' =>  $row[1],
                'unit_id' => $units[$row[2]],
                'project_id' => $row['project_id'],
                'import_price' => $row[3],
                'amount' => $row[4],
                'warehouse' => $warehouse,
                'first_information' => $first_information,
                'model' => $row[9],
                'serial' => $row[10],
                'manufacturer' => $row[11],
                'year_manufacture' => $row[14],
                'expiry' => $row[15],
                'specificat' => $row[17],
                'configurat' => $row[18],
                'status' => $row[19],
                'note' => $row[20],
            ]);
        }
    }
}
