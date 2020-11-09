<?php
/**
 * User: robbielove
 * Date: 5/11/20
 * Time: 10:03 pm
 */

use App\Imports\UsersImport;
use App\User;
use Garden\Cli\Cli;
use Garden\Cli\TaskLogger;
use Illuminate\Support\Str;

//ensure we are installed
//`composer install`;

//Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Artisan Application
|--------------------------------------------------------------------------
|
| When we run the console application, the current CLI command will be
| executed in this console and the response sent back to a terminal
| or another output device for the developers. Here goes nothing!
|
*/

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput,
    new Symfony\Component\Console\Output\ConsoleOutput
);

//Get CLI info
$arguments = collect($argv)->slice(1);

$cli = new Cli();
$cli->description('Accepts a CSV file as input (see command line directives below) and processes the CSV file. The parsed file data is to be inserted into a MySQL database.')
    ->opt('file:f', 'This is the name of the CSV to be parsed.', TRUE)
    ->opt('create_table:c', 'This will cause the MySQL users table to be built (and no further action will be taken)', FALSE)
    ->opt('dry_run:d', 'This will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database will not be altered', FALSE, 'boolean')
    ->opt('port:P', 'Port number to use.', FALSE, 'integer')
    ->opt('user:u', 'User for login if not current user.', TRUE)
    ->opt('host:h', 'Connect to host.', TRUE)
    ->opt('password:p', 'Password to use when connecting to server.');

$log = new TaskLogger();

// Parse and return cli args.
$args = $cli->parse($argv, TRUE);
$file = $args->getOpt('file', 'users.csv');
$create_table = $args->getOpt('create_table');
$dry_run = $args->getOpt('dry_run');
$port = $args->getOpt('port', env('DB_PORT', '3306'));
$user = $args->getOpt('user', env('DB_USERNAME', 'root'));
$host = $args->getOpt('host', env('DB_HOST', 'localhost'));
$password = $args->getOpt('password', env('DB_PASSWORD', '123'));
config(['database.connections.mysql.port' => $port]);
config(['database.connections.mysql.username' => $user]);
config(['database.connections.mysql.host' => $host]);
config(['database.connections.mysql.password' => $password]);
//Check if the file exists
if (!file_exists($file)) {
    die($log->error('Unable to process - The file ' . $file . ' doesn\'t exist'));
} else {
    $log->info($file . ' found');
}


//Force db refresh
`php artisan migrate:refresh --force`;

//Abort if we are just here to make the DB - it was 'made' before...
if ($create_table !== NULL) {
    die($log->info('MYSQL users table created (no further action taken)'));
} else {
    $log->info('MYSQL users table created');
}

$import = new UsersImport();
$users = User::all();
//try to make it a dry run if specified
$count = $import->toCollection($file)->first()->count();
if ($dry_run) {
    $log->info('Dry Run!');

    if (File::exists($file)) {
        $log->info($file . ' exists');
        $log->info($count . ' rows found in CSV');
    }
} else {
    $log->info('Production Run!');

    if (File::exists($file)) {
        $log->info($file . ' exists');
        $log->info($count . ' rows found in CSV');
        $sheets = collect();
        $import = (new UsersImport)->toCollection($file, NULL, \Maatwebsite\Excel\Excel::CSV);
        $import = $sheets->merge($import[0])->slice(1);
    }

    $rules = [
        'name' => 'required|max:255',
        'surname' => 'required|max:255',
        'email' => 'required|unique:users|max:255|email',
    ];

    foreach ($import as $user) {
        $log->info('User '. $user['name'] . ' ' . $user['surname'] . ' found');
        $created = new User();

        $created->fill([
            'name' => Str::ucfirst($user['name']),
            'surname' => Str::ucfirst($user['surname']),
            'email' => Str::lower($user['email']),
        ]);

        $userRequest = request();
        $userRequest = $userRequest->merge($created->attributesToArray());
//        dd($userRequest->all());
        try {
            $userRequest->validate($rules);
            $created->Save();
//            $import->import('import-users.xlsx');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $failures = $e->failures();

            foreach ($failures as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }
        }

        $log->info('User '. $created->name . ' ' . $created->surname . ' saved');
    }
}

foreach ($import->failures() as $failure) {
    $failure->row(); // row that went wrong
    $failure->attribute(); // either heading key (if using heading row concern) or column index
    $failure->errors(); // Actual error messages from Laravel validator
    $failure->values(); // The values of the row that has failed.
    dump($failure);
    $log->error('Error on row ' . $failure->row());
}


/*
|--------------------------------------------------------------------------
| Shutdown The Application
|--------------------------------------------------------------------------
|
| Once Artisan has finished running, we will fire off the shutdown events
| so that any final work may be done by the application before we shut
| down the process. This is the last thing to happen to the request.
|
*/

$kernel->terminate($input, $status);

exit($status);
