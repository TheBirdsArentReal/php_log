<?php 
$logFile = 'access.log';
$hardwareClasses = [];
$gzipErrors = 0;
$invalidEntries = 0;

if (file_exists($logFile)) {
    $handle = fopen($logFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {

        // get serial number
        if (!preg_match('/\bserial=([^\s]+)/', $line, $serialMatch)) {
                continue;
                }

        // extract, decode & unzip specs 
        if (!preg_match('/\bspecs=([^\s]+)/', $line, $specsMatch)) {
           $invalidEntries++;
           continue;
        }

         $serial = $serialMatch[1];
         $encodedSpecs = $specsMatch[1];

         $compressed = base64_decode($encodedSpecs, true);

         if ($compressed === false) {
             $invalidEntries++;
             continue;
         }

         $json = @gzdecode($compressed);

         if ($json === false) {
            $gzipErrors++;
             continue;
         }

        //parse json 
        $specs = json_decode($json, true);

         if(!is_array($specs)) {
             $invalidEntries++;
             continue;
         }


         // extract hardware classes from specs
         $architecture = trim((string)($specs['architecture'] ?? 'unknown'));
         $machine = trim((string)($specs['machine'] ?? 'unknown'));
         $cpu = trim((string)($specs['cpu'] ?? 'unknown'));

         //remove cpu whitespace and trailing slashes
         $cpu = rtrim($cpu, "/\t"); 

         // mark missing values as unknown
            if ($architecture === '') {
                $architecture = 'unknown';
            }
            if ($machine === '') {
                $machine = 'unknown';       
            }
            if ($cpu === '') {
                $cpu = 'unknown';
            }

         // define hardware class
         $classKey = json_encode([$architecture, $machine, $cpu]);

         $classLabel = "$architecture | $machine | $cpu";

         if (!isset($hardwareClasses[$classKey])) {
             $hardwareClasses[$classKey] = [
                 'label' => $classLabel,
                 'licenses' => []
             ];
         }

         //count licenses by hardware class
        $hardwareClasses[$classKey]['licenses'][$serial] = true; 
        }

        fclose($handle);
        
        foreach ($hardwareClasses as &$class) {
            $class['license_count'] = count($class['licenses']);
        }
        unset($class); // break the reference to avoid unexpected behavior

        //sort hardware classes by license count in descending order
        uasort($hardwareClasses, function ($a, $b) {
            return $b['license_count'] <=> $a['license_count'];
        });

        echo "count of unique hardware classes: " . count($hardwareClasses) . "\n";
        echo "Hardware Classes and active License Counts:\n";
        foreach ($hardwareClasses as $class) {
            printf("%s: %d active licenses\n", $class['label'], $class['license_count']);
        }

        echo "\nCount of unique hardware classes: " . count($hardwareClasses) . "\n";
        echo "Invalid entries: $invalidEntries\n";
        echo "Gzip decode errors: $gzipErrors\n";

    } else {
        echo "Error opening the file.";
    }
} else {
    echo "Log file does not exist.";
}
