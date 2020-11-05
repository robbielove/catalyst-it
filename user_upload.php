<?php
/**
 * User: robbielove
 * Date: 5/11/20
 * Time: 10:03 pm
 */

use Garden\Cli\Cli;
use Garden\Cli\TaskLogger;

//ensure we are installed
//`composer install`;
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$cli = new Cli();
$cli->description('Accepts a CSV file as input (see command line directives below) and processes the CSV file. The parsed file data is to be inserted into a MySQL database.')
    ->opt('file:f', 'This is the name of the CSV to be parsed.', TRUE)
    ->opt('create_table', 'This will cause the MySQL users table to be built (and no further action will be taken)', FALSE)
    ->opt('dry_run', 'This will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won\'t be altered
', FALSE)
    ->opt('port:P', 'Port number to use.', FALSE, 'integer')
    ->opt('user:u', 'User for login if not current user.', TRUE)
    ->opt('host:h', 'Connect to host.', TRUE)
    ->opt('password:p', 'Password to use when connecting to server.');

$log = new TaskLogger();

// Parse and return cli args.
$args = $cli->parse($argv, TRUE);
$file = $args->getOpt('file', 'users.csv');
$create_table = $args->getOpt('create_table', FALSE);

//Check if the file exists
if (!file_exists($file)) {
    die($log->error('Unable to process - The file ' . $file . ' doesn\'t exist'));
} else {
    $log->info($file . ' found');
}

//Abort if we are just here to make the DB - it was 'made' before...
if ($create_table) {
    die($log->info('MYSQL users table created (no further action taken)'));
} else {
    $log->info('MYSQL users table created');
}

//dd($args, $arguments);
