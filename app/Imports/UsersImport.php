<?php

namespace App\Imports;

use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;

class UsersImport implements OnEachRow
{
    use Importable;

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

//        dd($row);
        Validator::make($row->toArray(), $this->rules())->validate();
        $group = Group::firstOrCreate([
            'name' => $row['name'],
            'surname' => $row['surname'],
            'email' => $row['email'],
        ]);

        $group->users()->create([
            'name' => $row['name'],
            'surname' => $row['surname'],
            'email' => $row['email'],
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'email' => 'required|unique:users|max:255',
        ];
    }


}
