<?php

namespace App\Imports;

use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError
{
    use Importable, SkipsErrors;

    public function collection(Collection $rows)
    {
        Validator::make($rows->toArray(), $this->rules())->validate();

        foreach ($rows as $row) {
            return User::create([
                'name' => Str::ucfirst($row['name']),
                'surname' => Str::ucfirst($row['surname']),
                'email' => Str::lower($row['email']),
            ]);
        }
    }


    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'email' => 'required|unique:users,email|max:255|email',
        ];
    }

}
