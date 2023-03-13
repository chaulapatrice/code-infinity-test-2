
<?php
require 'vendor/autoload.php';

use App\SQLiteConnection;

try {
    $pdo = (new SQLiteConnection())->connect();

    $sql = "CREATE TABLE IF NOT EXISTS csv_import (
                id INTEGER PRIMARY KEY,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                initials TEXT NOT NULL,
                age INT NOT NULL,
                date_of_birth DATE NOT NULL
            );";

    $pdo->exec($sql);

    echo "Operation complete! 'csv_import' database table creation successful. 🚀 \n";

} catch (\PDOException $e) {

    echo "Operation failed! 'csv_import' database table creation failed.\n";
    echo $e;
    
}
