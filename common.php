<?php

// extract serial from log
function extractSerial(string $line): ?string {
    if (preg_match('/\bserial=([^\s]+)/', $line, $matches)) {
        return $matches[1];
    }
    return null;
}

// extract specs
function extractEncodedSpecs(string $line): ?string {
    if (preg_match('/\bspecs=([^\s]+)/', $line, $specsMatch)) {
        return $specsMatch[1];
    }
    return null;
    }

// decode specs
function decodeSpecs(string $encodedSpecs): array {
    $compressed = base64_decode($encodedSpecs, true);
    if ($compressed === false) {
        return [
            'status' => 'Invalid base64 encoding',
            'data' => null,
        ];
    }

    $json = @gzdecode($compressed);
    if ($json === false) {
        return [
            'status' => 'gzip error',
            'data' => null,
        ];
        }

    $specs = json_decode($json, true);

    if(!is_array($specs) || json_last_error() !== JSON_ERROR_NONE) {
        return [
            'status' => 'invalid JSON',
            'data' => null,
        ];
    }
    return [
        'status' => 'success',
        'data' => $specs,
    ];   
}

// error stats
function createErrorStats() {
    return [
        'missing_serial' => 0,
        'missing_specs' => 0,
        'missing_mac' => 0,
        'missing_hardware_fields' => 0,
        'invalid_base64' => 0,
        'gzip_error' => 0,
        'invalid_json' => 0,
        'valid' => 0,
    ];
}

// count stats
function reportStatus(array &$stats, string $status) {
    if($status === 'success') {
        $stats['valid']++;
    } elseif(array_key_exists($status, $stats)) {
        $stats[$status]++;
    } else {
        // Handle unexpected status
        echo "Unexpected status: $status\n";
    }
}

// return stats 
function printErrorStats(array $stats) {
    echo "Data Quality Statistics:\n";
    foreach ($stats as $status => $count) {
        printf("%-20s: %d\n", $status. ':', $count);
    }
}
