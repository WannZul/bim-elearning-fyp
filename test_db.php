<?php
include 'includes/db_connect.php';

if ($conn) {
    echo "<h2 style='color:green;'>✅ BERJAYA! Database connected.</h2>";
    
    // Test table wujud ke tak
    $tables = mysqli_query($conn, "SHOW TABLES");
    echo "<h3>Table yang ada dalam database:</h3><ul>";
    while ($row = mysqli_fetch_array($tables)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<h2 style='color:red;'>❌ GAGAL connect.</h2>";
}
?>