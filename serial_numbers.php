<?php

require_once 'common.php';
$logFile = 'access.log';


if (file_exists($logFile)) {
    $handle = fopen($logFile, 'r');
    if ($handle) {

       $accessCounts = [];
       $totalLines = 0;
       $missingSerial = 0;

        while (($line = fgets($handle)) !== false) {
      
           $totalLines++;

           // extract serial number
           $serial = extractSerial($line);
           if ($serial === null) {
               $missingSerial++;
               continue;
           }

           $accessCounts[$serial] = ($accessCounts[$serial] ?? 0) + 1;
        }

        fclose($handle);
    } else {
        echo "Error opening the file.";
    }
} else {
    echo "Log file does not exist.";
}

// sort by descending order
arsort($accessCounts);

echo "Top 10 Serial Numbers:\n";

$rank = 0;

foreach ($accessCounts as $serial => $count) {
    $rank++;
    echo "$rank. $serial: $count\n";
    if ($rank >= 10) {
        break;
    }
}

//show data summary
echo "\nSummary:\n";
echo "\nTotal lines processed: $totalLines\n"; 
echo "Lines without serial numbers: $missingSerial\n";
echo "Unique serial numbers found: " . count($accessCounts) . "\n";
