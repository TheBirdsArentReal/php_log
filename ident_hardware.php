<?php 
$logFile = 'access.log';
$Hardwareclasses = [];
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


         /* define Hardware 
            architecture
            machine
            cpu */


         //count licenses by hardware class



        fclose($handle);
        }

    } else {
        echo "Error opening the file.";
    }
} else {
    echo "Log file does not exist.";
}
