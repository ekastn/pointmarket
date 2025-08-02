<?php

namespace App\Helpers;

use DateTime;

function formatDate($dateString) {
    if (!$dateString) return 'N/A';
    $date = new DateTime($dateString);
    return $date->format('d/m/Y H:i');
}