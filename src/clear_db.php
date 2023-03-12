
<?php
require 'vendor/autoload.php';

use App\SQLiteConnection;

try {
    $pdo = (new SQLiteConnection())->connect();

    $sql = "DELETE FROM csv_import;";

    $pdo->exec($sql);

    echo "Operation complete! 'csv_import' records wiped. 🧹\n";

} catch (\PDOException $e) {

    echo "Operation failed! 'csv_import' records could not be deleted.\n";
    echo $e;
    
}