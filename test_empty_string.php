<?php

$test = '';

if (!empty($test)) {
    echo "Not empty: '$test'\n";
} else {
    echo "Empty - will convert to NULL\n";
}

$result = !empty($test) ? trim($test) : null;
var_dump($result);
