<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;

class MedicinesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $company = Company::updateOrCreate(
            ['name' => $row[7]],
            [
                'strength' => $row[6],
                'slug' => \Str::slug($row[7]),
                'status' => ACTIVE_STATUS,
            ]
        );

        return new Product([
            'name'         => $row[1],
            'slug'         => $row[3] ?? \Str::slug($row[1]),
            'type'         => $row[4] ?? '',
            'strength' => $row[6] ?? '',
            'company_id'   => $company->id,
            'unit_price'   => 0,
            'box_per_pic'  => 0,
            'stock'        => 0,
        ]);
    }
}
