<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli('localhost', 'root', '', 'db_lms');
$conn->set_charset('utf8mb4');

require_once __DIR__ . '/fungsi.php';