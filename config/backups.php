<?php

return [
    'disk' => env('BACKUPS_DISK', 'local'),
    'directory' => env('BACKUPS_DIRECTORY', 'backups'),
    'mysql_dump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
    'mysql_binary' => env('MYSQL_BINARY', 'mysql'),
];
