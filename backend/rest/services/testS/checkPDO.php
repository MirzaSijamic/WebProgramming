<?php
// check_pdo.php

$drivers = PDO::getAvailableDrivers();
print_r($drivers);
?>