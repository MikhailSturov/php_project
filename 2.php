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

//----------------------------------------- Задача 2

function mySortForKey ($a, $b) {
    $d = [];
    foreach ($a as $key => $value) {
        try {
            if ( ! array_key_exists($b, $value) ) {
                throw new Exception("Ключ $b отсутствует в массиве $key");
            }
        } catch (Exception $e) {
            echo $e -> getMessage();
        }
        $d[$key] = $value[$b];
    }
    array_multisort($d, SORT_ASC, $a);
    $result = $a ;
    return $result;
}

?>