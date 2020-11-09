<?php

namespace App\Imports;

use App\User;
use Garden\Cli\TaskLogger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsFailures, SkipsErrors;

    protected $log;

    public function __construct()
    {
        $this->log = New TaskLogger();
    }

    public function collection(Collection $rows)
    {
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
            '*.name' => 'required|max:255',
            '*.surname' => 'required|max:255',
            '*.email' => 'required|unique:users|max:255|email',
        ];
    }

    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->log->error('There was an issue importing the attribute ' . $failure->attribute() . ' on row ' . $failure->row());
            foreach ($failure->errors() as $error) {
                $this->log->error($error);
            }
        }
    }

    /**
     * @param \Throwable $e
     */
    public function onError(\Throwable $e)
    {
//        return $e;
    }

}
