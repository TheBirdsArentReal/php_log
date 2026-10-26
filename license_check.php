<?php 

require_once 'common.php';

$logFile = 'access.log';
$devicesBySerial = [];
$stats = createErrorStats();


if (file_exists($logFile)) {
    $handle = fopen($logFile, 'r');
    if ($handle) {

        while (($line = fgets($handle)) !== false) {

            $serial = extractSerial($line);
            if ($serial === null) {
                $stats['missing_serial']++;
                continue;
            }

            $encodedSpecs = extractEncodedSpecs($line);
            if ($encodedSpecs === null) {
                $stats['missing_specs']++;
                continue;
            }

            $result = decodeSpecs($encodedSpecs);
            reportStatus($stats, $result['status']);

            if ($result['status'] !== 'success') {
                continue;
            }

            $specs = $result['data'];
            $mac = $specs['mac'] ?? null;

            if (!is_string($mac)) {
                $stats['missing_mac']++;
                continue;
            }

            // save mac once per serial number
            $devicesBySerial[$serial][$mac] = true;
        }
            
    
    fclose($handle);
    } else {
        echo "Error opening the file.";
    }
} else {
    echo "Log file does not exist.";
}
     
    // focus on license active on several devices
    $violations = [];

    foreach ($devicesBySerial as $serial => $devices) {
        $deviceCount = count($devices);
        if ($deviceCount > 1) {
            $violations[$serial] = $deviceCount;
        }
    }

    arsort($violations);

    echo "\nSerial Numbers with Multiple Devices:\n";

    $rank = 0;

    foreach ($violations as $serial => $count) {
        $rank++;
        echo "$rank. $serial: $count devices\n";
        if ($rank >= 10) {
            break;
        }
    }

    echo "\nSerial numbers on multiple devices: " . count($violations) . "\n";

    printErrorStats($stats);

   
