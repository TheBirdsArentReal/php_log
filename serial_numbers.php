<?php
/* open file fopen()
 extract serial numbers fgets() preg_match()
 count access $count[$serial]++
 sort result arsort() array slice()
 echo top 10
 fclose() */

$logFile = 'serial_numbers.log';
$counts = [];

if (file_exists($logFile)) {
    $handle = fopen($logFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {

        //extract serial number using regex and count occurrences
            if (preg_match('/\bserial=([^\s]+)/', $line, $matches)) {
                $serial = $matches[1];
                if (!isset($counts[$serial])) {
                    $counts[$serial] = 0;
                }
                $counts[$serial]++;
            }
        }
        fclose($handle);
    } else {
        echo "Error opening the file.";
    }
} else {
    echo "Log file does not exist.";
}

// sort by descending order and get top 10
arsort($counts);
$topSerials = array_slice($counts, 0, 10, true);

//show results
echo "Top 10 Serial Numbers:";
foreach ($topSerials as $serial => $count) {
    echo "$serial: $count";
}
