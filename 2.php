<?php

//----------------------------------------- Задача 1

function convertString($a, $b) {
    $offset = 0;
    $len = strlen($b);
    $e = strrev("$b");
    $c = substr_count ($a, $b);
    if ($c >= 2) {
        for ($i = 0 ; $i <= 1 ; $i++) {
            $pos = stripos($a, $b, $offset + 1);
            $offset = $pos;
            $f = $pos;
        }
        $result = substr_replace ("$a", "$e", "$f", "$len");
    }
    return $result;
}

?>