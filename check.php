<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'biblioteca_vision', 3306);
$res = $conn->query('SHOW TABLES');
while ($row = $res->fetch_array()) {
    echo "TABLE: " . $row[0] . "\n";
    $cols = $conn->query("SHOW COLUMNS FROM " . $row[0]);
    while ($col = $cols->fetch_array()) {
        echo " - " . $col[0] . " (" . $col[1] . ")\n";
    }
}
