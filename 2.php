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

//----------------------------------------- Задача 3

function importXml($a) {
    $con = new mysqli("localhost","admin","", "test_samson");
    if ($con -> connect_error) {
        die("Connection failed: " . $con -> connect_error);
    }
    echo "Connection successfully";

    $xmlFile = simplexml_load_file("$a");
    $arrProducts = [];
    $i = 0;
    foreach ($xmlFile -> Товар as $product) {
        $arrProducts[$i]['code'] = intval($product['Код']);
        $arrProducts[$i]['name'] = htmlentities($product['Название']);
        $arrPriceType = [];
        $arrPrice = [];
        foreach ($product -> Цена as $price) {
            $j = 0;
            $arrPriceType[] = htmlentities($price['Тип'][$j]);
            $arrPrice[] = htmlentities($price[$j]);
            $arrProducts[$i]['priceType'] = $arrPriceType;
            $arrProducts[$i]['price'] = $arrPrice;
            $j++;
        }
        unset($j);
        foreach ($product -> Свойства as $properties) {
            $arrProperty = [];
            foreach ($properties as $property) {
                $j = 0;
                $arrProperty[] = htmlentities($property[$j]);
                $arrProducts[$i]['property'] = $arrProperty;
                $j++;
            }
            unset($j);
        }
        foreach ($product -> Разделы as $categories) {
            $arrCategory = [];
            foreach ($categories as $category) {
                $j = 0;
                $arrCategory[] = htmlentities($category[$j]);
                $arrProducts[$i]['category'] = $arrCategory;
                $j++;
            }
            unset($j);
        }
        $i++;
    }
    unset($i);

    // Импорт в БД.
    $count = count($arrProducts);
    for ($j = 0; $j < $count; $j++) {

        // Импорт в таблицу a_product
        $code = $arrProducts[$j]['code'];
        $name = $arrProducts[$j]['name'];
        $result = mysqli_query($con, "INSERT INTO a_product (`code`, `name`) VALUES ('$code', '$name')");
        if (!$result) {
            $error = mysqli_error($con);
            print("Ошибка : " . $error);
        }
        else {
            $idProduct = mysqli_insert_id($con);
            $arrProducts[$j]['product_id'] = $idProduct;
        }

        // Импорт в таблицу a_price
        $productId = $arrProducts[$j]['product_id'];
        $priceCount = count($arrProducts[$j]['price']);
        for ($i = 0; $i < $priceCount; $i++) {
            $priceType = $arrProducts[$j]['priceType'][$i];
            $price = $arrProducts[$j]['price'][$i];
            $result = mysqli_query($con, "INSERT INTO a_price (`product_id`, `price_type`, `price`) VALUES ('$productId', '$priceType', '$price')");
            if (!$result) {
                $error = mysqli_error($con);
                print("Ошибка : " . $error);
            }
        }
        unset($i);

        // Импорт в таблицу a_property
        $propertyCount = count($arrProducts[$j]['property']);
        for ($i = 0; $i < $propertyCount; $i++) {
            $propertyValue = $arrProducts[$j]['property'][$i];
            $result = mysqli_query($con, "INSERT INTO a_property (`product_id`, `property_value`) VALUES ('$productId', '$propertyValue')");
            if (!$result) {
                $error = mysqli_error($con);
                print("Ошибка : " . $error);
            }
        }
        unset($i);

        // Импорт в таблицу a_category
        $categoryCount = count($arrProducts[$j]['category']);
        $category = [];
        for ($i = 0; $i < $categoryCount; $i++) {
            $categoryName = $arrProducts[$j]['category'][$i];
            $sql = mysqli_query($con, "SELECT `name` FROM `a_category`");
            while ( $rec = mysqli_fetch_assoc($sql)) {
                $category[] = $rec['name'];
            }
            if (! in_array($categoryName, $category)) {
                $result = mysqli_query($con, "INSERT INTO a_category (`name`) VALUES ('$categoryName')");
                if (!$result) {
                    $error = mysqli_error($con);
                    print("Ошибка : " . $error);
                }
            }
        }
        unset($i);

        $categoryArr = [];
        $idArr = [];
        $sql = mysqli_query($con, "SELECT `id`,`name` FROM `a_category`");
        while ( $rec = mysqli_fetch_assoc($sql)) {
            $categoryArr[] = $rec['name'];
            $idArr[]= $rec['id'];
        }
        $arr = array_combine($idArr, $categoryArr);

        // Импорт в таблицу product_category
        foreach ($arrProducts[$j]['category'] as $cat) {
            if (in_array($cat, $arr)) {
                $categoryId = array_search($cat, $arr);
                $result = mysqli_query($con, "INSERT INTO product_category VALUES ('$productId', '$categoryId')");
                if (!$result) {
                    $error = mysqli_error($con);
                    print("Ошибка : " . $error);
                }
            }
        }
    }
    mysqli_close($con);
}

//----------------------------------------- Задача 4

function exportXml($a, $b) {
    $con = new mysqli("localhost","admin","", "test_samson");
    if ($con -> connect_error) {
        die("Connection failed: " . $con -> connect_error);
    }
    echo "Connection successfully";
    $xml = simplexml_load_file("$a");
// id всех необходимых категорий
    $sql = mysqli_query($con, "SELECT `id` FROM `a_category` WHERE `name` = '$b'");
    $result = mysqli_fetch_assoc($sql)['id'];
    $idCategory[] = $result;
    $sql = mysqli_query($con, "SELECT `id` FROM `a_category` WHERE `parent_id` = '$result'");
    while ( $rec = mysqli_fetch_assoc($sql)) {
        $idCategory[] = $rec['id'];
    }
// id всех необходимых товаров
    foreach ($idCategory as $value) {
        $sql = mysqli_query($con, "SELECT `product_id` FROM `product_category` WHERE `category_id` = '$value'");
        while ( $rec = mysqli_fetch_assoc($sql)) {
            $idProduct[] = $rec['product_id'];
        }
    }

    $i = 0;
    foreach ($idProduct as $value) {
        $nodeProduct = $xml->addChild('Товар');
        $sql = mysqli_query($con, "SELECT `code`, `name` FROM `a_product` WHERE `id` = '$value'");
        $item = mysqli_fetch_assoc($sql);
        $code = $item['code'];
        $name = $item['name'];
        $nodeProduct->addAttribute('Код', $code);
        $nodeProduct->addAttribute('Название', $name);
        $sql = mysqli_query($con, "SELECT `price_type`, `price`  FROM `a_price` WHERE `product_id` = '$value'");
        while ( $rec = mysqli_fetch_assoc($sql)) {
            $priceType = $rec['price_type'];
            $price = $rec['price'];
            $nodePrice = $nodeProduct->addChild('Цена', $price);
            $nodePrice->addAttribute('Тип', $priceType);
        }
        $sql = mysqli_query($con, "SELECT `property_value`  FROM `a_property` WHERE `product_id` = '$value'");
        $nodeProperty = $nodeProduct->addChild('Свойства');
        while ( $rec = mysqli_fetch_assoc($sql)) {
            $property = $rec['property_value'];
            $nodeProperty->addChild('Свойство', $property);
        }
        $sql = mysqli_query($con, "SELECT `category_id` FROM `product_category` WHERE `product_id` = '$value'");
        $nodeCategory = $nodeProduct->addChild('Разделы');
        while ( $rec = mysqli_fetch_assoc($sql) ) {
            $id = $rec['category_id'];
            $sql2 = mysqli_query($con, "SELECT `name` FROM `a_category` WHERE `id` = '$id'");
            $category = mysqli_fetch_assoc($sql2)['name'];
            $nodeCategory->addChild('Раздел', $category);
        }
        $xml->asXML("$a");
        $i++;
    }
    mysqli_close($con);
}

?>