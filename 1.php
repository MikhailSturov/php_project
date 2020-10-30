<?php   

//------------------------------------------Задача 1

function findSimple($a, $b) {
    if ($a > $b) {
        $c = $a;
        $a = $b;
        $b = $c;
    }
    for ($i = $a ; $i<=$b ; $i++) {
        if ($i < 2)
        $checkSimple = false;
        else {
            $checkSimple = true;
            for ($j=2 ; $j < $i ; $j++) {
                if ($i % $j === 0)
                $checkSimple = false;
            }
                if ($checkSimple === true)
                $arrSimple[] = $i;
        }
    }
    return $arrSimple;
}

//------------------------------------------Задача 2

$inArr = [1,2,3,4,5,6]; 

function createTrapeze($a) {

    $keysArr = ['a','b','c'];
    foreach($a as $value){
        $b[] = $value;
        if (count($keysArr) == count($b)) {
            $result[] = array_combine($keysArr, $b);
            $b = [];
        }
    }
    return $result;
}

//------------------------------------------Задача 3

$trapeze = createTrapeze($inArr);

function squareTrapeze($a) {
 
    foreach ($a as $value) {
        $b = $value;
        $s = ($b["a"] + $b["b"]) * $b["c"] / 2;
        $b['s'] = $s;
        $result[] = $b; 
        $b = [];
    }
    return $result;
}

//------------------------------------------Задача 4

$trapezeSquare = squareTrapeze($trapeze);

function getMaxSquare($a) {
    foreach ($a as $value) {
        $b = $value;
        $s[] = $b["s"];
    }
    foreach ($s as $i) {
        $max = max($max, $i);
    }
    return $max;
}

$maxSquare = getMaxSquare($trapezeSquare);

function getSizeForLimit($a, $b) {
    $square = array_column($a, 's');
    array_multisort ($square , SORT_ASC , $a);  
    foreach ($a as $value) {
        $j = $value;
        $s = $j["s"];
        if ($s > $b)
        continue;
        else {
            $result = $j;
        }
    }
    return $result;
}

//------------------------------------------Задача 5

function getMin($a) {
    $min = null;
    for ($i = 0 ; $i < count($a) ; $i++) {
        if($a[$i] < $min or $min === null) {
            $min = $a[$i];
        }
    }
    return $min;
}

?>

<?php 

//------------------------------------------Задача 6

function printTrapeze($a) {
    $color = 'style = "background-color:lightgray"';
?>
    <table style = "border-collapse:collapse">
        <tr>
            <th>№ Трапеции</th>
            <th>
                <span>Основание a</span>
            </th>
            <th>
                <span>Основание b</span>
            </th>
            <th>
                <span>Высота</span>
            </th>
            <th>
                <span >Площадь</span>
            </th>
        </tr>
<?php
        foreach ($a as $k => $value) {
            $s =  $value["s"];          
?>
        <tr 
            <?php 
                if (is_float($s / 2)) {
                    echo $color;
                }
            ?> 
        >
            <td>
                <p><?php echo $k + 1 ?></p>
            </td>
            <td>
                <span><?php echo $value["a"] ?></span>
            </td>
            <td>
                <span><?php echo $value["b"] ?></span>
            </td>
            <td>
                <span><?php echo $value["c"] ?></span>
            </td>
            <td >
                <span><?php echo $value["s"] ?></span>
            </td>
        </tr>
<?php } ?>

</table>

<?php } ?>


<?php

echo printTrapeze($trapezeSquare);

//------------------------------------------Задача 7

abstract class BaseMath {
    
    public function exp1 ($a, $b, $c) {
        return $a * ($b ** $c);
    }

    public function exp2 ($a, $b, $c) {
        return ($a / $b) ** $c;
    }

    abstract public function getValue ();
}

//------------------------------------------Задача 8

class F1 extends BaseMath {

    public function __construct($a, $b, $c) {
        $this -> a = $a;
        $this -> b = $b;
        $this -> c = $c;
    }

    public function getValue () {
        return $this->exp1($this->a, $this->b, $this->c) + ($this->exp2($this->a, $this->b,$this->c) % 3) ** min($this->a,$this->b, $this->c);
    }
}

$result = new F1(20, 4.5, 2);
echo $result -> getValue();

?>