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
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsFailures, SkipsErrors;

    protected $log;

    public function __construct()
    {
        $this->log = New TaskLogger();
    }

    public function model(array $row)
    {
            $this->log->info($row['name'] . ' '. $row['surname'] . ' - <' . $row['email'] . '> imported');
            return User::create([
                'name' => Str::ucfirst($row['name']),
                'surname' => Str::ucfirst($row['surname']),
                'email' => Str::lower($row['email']),
            ]);
    }


    public function rules(): array
    {
        return [
            '*.name' => 'required|max:255',
            '*.surname' => 'required|max:255',
            '*.email' => 'required|unique:users,email|max:255|email',
        ];
    }

    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->log->error('There was an issue importing the attribute "' . $failure->attribute() . '" on row ' . $failure->row() . ':');
            foreach ($failure->errors() as $error) {
                $this->log->error($failure->values()[$failure->attribute()] . ' - ' . $error);
            }
            $this->log->error('The row was not imported.');
        }
    }

    /**
     * @param \Throwable $e
     */
    public function onError(\Throwable $e)
    {
        $this->log->error($e);
    }

}
