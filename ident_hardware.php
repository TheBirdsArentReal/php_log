<?php 

require_once 'common.php';

$logFile = 'access.log';
$hardwareClasses = [];
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

        $architecture = $specs['architecture'] ?? null;
        $machine = $specs['machine'] ?? null;
        $cpu = $specs['cpu'] ?? null;

         // dont mix missing data 
         if (
            !is_string($architecture) || trim($architecture) === '' ||
            !is_string($machine) || trim($machine) === '' ||
            !is_string($cpu) || trim($cpu) === ''
         ){
            $stats['missing_hardware_fields']++;
            continue;
         }

         // define hardware class
         $classKey = json_encode([$architecture, $machine, $cpu]);

         if($classKey === false) {
            $stats['invalid_hardware_class']++;
            continue;
         }

        if (!isset($hardwareClasses[$classKey])) {
            $hardwareClasses[$classKey] = [
                'architecture' => $architecture,
                'machine' => $machine,
                'cpu' => $cpu,
                'licenses' => [],
            ];
        }

         //count licenses by hardware class
        $hardwareClasses[$classKey]['licenses'][$serial] = true; 
        }

        fclose($handle);

        foreach($hardwareClasses as &$hardwareClass) {
            $hardwareClass['license_count'] = count($hardwareClass['licenses']);
            unset($hardwareClass['licenses']);
        }
        unset($hardwareClass);

        usort($hardwareClasses, function($a, $b) {
            return $b['license_count'] <=> $a['license_count'];
        });

        echo "\nHardware Classes by active License Count:\n";

        $rank = 0;

        foreach ($hardwareClasses as $class) {
            $rank++;
            echo "$rank. Architecture: {$class['architecture']}, Machine: {$class['machine']}, CPU: {$class['cpu']}, Active Licenses: {$class['license_count']}\n";
        }

        echo "\nFound " . count($hardwareClasses) . " unique hardware classes.\n";

        printErrorStats($stats);

    } else {
        echo "Error opening the file.";
    }
} else {
    echo "Log file does not exist.";
}
