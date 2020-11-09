# Catalyst IT

## CLi Script

This is a PHP script that is executed from the command line, which accepts a CSV file as input (see command line directives below) and processes the CSV file. The parsed file data is to be inserted into a MySQL database if all the data contained in the CSV file is valid.

An error message will be output to explain the issue and the command can be run again once the CSV file has been amended.

## Install

The script requires dependencies to work. You can either run `php composer install` or uncomment this line in the script (L15) before running the script.

## Database destruction

Due to the nature of this script and the requirements - the database will always be cleared first and then the tables re-created.

THIS MEANS ALL YOUR PREVIOUS DATA WILL BE DELETED!

## CLI help

### CLI

Run `php user_upload.php --help`

### Arguments

 ```
usage: user_upload.php [<options>]
 
 Accepts a CSV file as input (see command line directives below) and processes
 the CSV file. The parsed file data is to be inserted into a MySQL database.
 
 OPTIONS
   --create_table, -c   This will cause the MySQL users table to be built (and no
                        further action will be taken)
   --dry_run, -d        This will be used with the --file directive in case we
                        want to run the script but not insert into the DB. All
                        other functions will be executed, but the database will
                        not be altered
   --file, -f           This is the name of the CSV to be parsed.
   --help, -?           Display this help.
   --host, -h           Connect to host.
   --password, -p       Password to use when connecting to server.
   --port, -P           Port number to use.
   --user, -u           User for login if not current user.
```

### Composer namespace conflict

There is a conflict with the composer namespace which causes strange output to occur first before the script output - for example it might complain about the file option not existing - these messages can be ignored.

eg.
```
php user_upload.php --file=users.csv --host=localhost --user=root

                                       
  The "--file" option does not exist.  
   

[2020-11-09 02:11:24] users.csv found
[2020-11-09 02:11:24] MYSQL users table created
...                                    
```
