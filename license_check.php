<?php 
$logFile = 'access.log';
$counts = [];

if (file_exists($logFile)) {
    $handle = fopen($logFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {

        // decode base64 encoded specs & unzip

        // match serial number to mac from specs

        // count mac adresses per serial number
        
        }
        fclose($handle);
    } else {
        echo "Error opening the file.";
    }
} else {
    echo "Log file does not exist.";
}
