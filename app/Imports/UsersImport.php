<?php

namespace App\Imports;

use App\User;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    use Importable;

    public function model(array $row)
    {
        return new User([
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
