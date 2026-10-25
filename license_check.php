<?php 
$logFile = 'access.log';
$devicesBySerial = [];


if (file_exists($logFile)) {
    $handle = fopen($logFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {

        // get serial number
        if (!preg_match('/\bserial=([^\s]+)/', $line, $serialMatch)) {
                continue;
                }

        // extract, decode & unzip specs 
        if (!preg_match('/\bspecs=([A-Za-z0-9+\/=]+)/', $line, $specsMatch)) {
           continue;
        }

         $serial = $serialMatch[1];
         $encodedSpecs = $specsMatch[1];

         $compressed = base64_decode($encodedSpecs, true);

         if ($compressed === false) {
             continue;
         }

         $json = gzdecode($compressed);

         if ($json === false) {
             continue;
         }

         $specs = json_decode($json, true);

         if(!is_array($specs)) {
             continue;
         }

        // extract mac address from specs
        $mac = $specs['mac'] ?? null;

        if(!is_string($mac)) {
            continue;
        }
        
        // normalize mac address to lowercase and trim whitespace
        $mac = strtolower(trim($mac)); 

        // ignore invalid mac addresses
        if (!preg_match('/^([0-9a-f]{2}[:-]){5}[0-9a-f]{2}$/', $mac)) {
            continue;
        }
        // save device for license 
        $devicesBySerial[$serial][$mac] = true;
        
        }
        fclose($handle);
        $deviceCounts = [];

        //count devices per serial number
        foreach ($devicesBySerial as $serial => $devices) {
            $deviceCounts[$serial] = count($devices);
        }

        // focus on license active on several devices
        $violations = [];

        foreach ($deviceCounts as $serial => $count) {
            if ($count > 1) {
                $violations[$serial] = $count;
            }
        }

        arsort($violations);

        $top10Violations = array_slice($violations, 0, 10, true);

        echo "Top 10 Serial Numbers with Multiple Devices:\n";
        if(empty($top10Violations)) {
            echo "No violations found.\n";
        } else {
            foreach ($top10Violations as $serial => $count) {
                echo "$serial: $count devices\n";
            }
        }

    } else {
        echo "Error opening the file.";
    }
} else {
    echo "Log file does not exist.";
}
