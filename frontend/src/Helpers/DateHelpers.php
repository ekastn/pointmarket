<?php

namespace App\Helpers;

use DateTime;

if (!function_exists('formatDate')) {
    function formatDate($dateString) {
        if (!$dateString) return 'N/A';
        $date = new DateTime($dateString);
        return $date->format('d/m/Y H:i');
    }
}
