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
        try {
            if ($product['Код'] && $product['Код'] != '' && $product['Название'] && $product['Название'] != '') {
                $arrProducts[$i]['code'] = intval($product['Код']);
                $arrProducts[$i]['name'] = htmlentities($product['Название']);
            } else {
                throw new Exception('Отсутствует атрибут или тег');
            }
            $arrPriceType = [];
            $arrPrice = [];
            if (isset($product->Цена)) {
                foreach ($product->Цена as $price) {
                    if ($price['Тип'] && $price['Тип'] != '' && isset($price) && $price != '') {
                        $j = 0;
                        $arrPriceType[] = htmlentities($price['Тип'][$j]);
                        $arrPrice[] = htmlentities($price[$j]);
                        $arrProducts[$i]['priceType'] = $arrPriceType;
                        $arrProducts[$i]['price'] = $arrPrice;
                        $j++;
                    } else if (empty($arrPriceType) || empty($arrPrice)) {
                        throw new Exception('Отсутствует атрибут или тег');
                    }
                }
                unset($j);
            } else {
                throw new Exception('Отсутствует атрибут или тег');
            }
            if (isset($product->Свойства)) {
                foreach ($product->Свойства as $properties) {
                    $arrProperty = [];
                    if (isset($properties)) {
                        foreach ($properties as $property) {
                            if (isset($property)) {
                                $j = 0;
                                if ($property != '') {
                                    $arrProperty[] = htmlentities($property[$j]);
                                    $arrProducts[$i]['property'] = $arrProperty;
                                }
                                $j++;
                            } else {
                                throw new Exception('Отсутствует атрибут или тег');
                            }
                            unset($j);
                        }
                    } else {
                        throw new Exception('Отсутствует атрибут или тег');
                    }
                }
                if (empty($arrProperty))  {
                    throw new Exception('Отсутствует атрибут или тег');
                }
            } else {
                throw new Exception('Отсутствует атрибут или тег');
            }
            if (isset($product->Разделы)) {
                foreach ($product->Разделы as $categories) {
                    $arrCategory = [];
                    if (isset($categories)) {
                        foreach ($categories as $category) {
                            if (isset($category)) {
                                $j = 0;
                                if ($category != '') {
                                    $arrCategory[] = htmlentities($category[$j]);
                                    $arrProducts[$i]['category'] = $arrCategory;
                                }
                                $j++;
                            }
                            else {
                                throw new Exception('Отсутствует атрибут или тег');
                            }
                            unset($j);
                        }
                    } else {
                        throw new Exception('Отсутствует атрибут или тег');
                    }
                }
                if (empty($arrCategory))  {
                    throw new Exception('Отсутствует атрибут или тег');
                }
            } else {
                throw new Exception('Отсутствует атрибут или тег');
            }
            $i++;
        } catch (Exception $e) {
            echo "Ошибка: " . $e->getMessage();
            foreach ($arrProducts as $k => $v) {
                if (!$arrProducts[$k]['code'] || !$arrProducts[$k]['name'] || !$arrProducts[$k]['priceType'] || !$arrProducts[$k]['price'] || !$arrProducts[$k]['property'] || !$arrProducts[$k]['category']) {
                    unset($arrProducts[$k]);
                }
            }
        }
    }
    unset($i);

// Импорт в БД.
    $count = count($arrProducts);
    for ($j = 0; $j < $count; $j++) {
        $code = $arrProducts[$j]['code'];
        $sql = mysqli_query($con, "SELECT `id` FROM `a_product` WHERE `code` = '$code'");
        $res = mysqli_fetch_assoc($sql)['id'];
        if (!$res) {
            // Импорт в таблицу a_product
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
        } else {
            // Обновление записей в таблице a_product
            $name = $arrProducts[$j]['name'];
            $result = mysqli_query($con, "UPDATE a_product SET name='$name' WHERE id = '$res'");
            if (!$result) {
                $error = mysqli_error($con);
                print("Ошибка : " . $error);
            }

            // Обновление записей в таблице a_price
            $productId = $res;
            $priceCount = count($arrProducts[$j]['price']);
            $priceBase = [];
            $selectPriceBase = mysqli_query($con, "SELECT `id`, `price_type`, `price` FROM `a_price` WHERE `product_id` = '$productId'");
            while ( $rec = mysqli_fetch_assoc($selectPriceBase)) {
                $priceBase['id'][] = $rec['id'];
                $priceBase['priceType'][] = $rec['price_type'];
                $priceBase['price'][] = $rec['price'];
            }
            $priceBaseCount = count($priceBase['price']);
            if ($priceCount == $priceBaseCount) {
                for ($i = 0; $i < $priceCount; $i++) {
                    $priceType = $arrProducts[$j]['priceType'][$i];
                    $price = $arrProducts[$j]['price'][$i];
                    $id = $priceBase['id'][$i];
                    $result = mysqli_query($con, "UPDATE a_price SET price_type ='$priceType', price = '$price' WHERE id = '$id'");
                    if (!$result) {
                        $error = mysqli_error($con);
                        print("Ошибка : " . $error);
                    }
                }
                unset($i);
            } else if ($priceCount > $priceBaseCount) {
                for ($i = 0; $i < $priceBaseCount; $i++) {
                    $priceType = $arrProducts[$j]['priceType'][$i];
                    $price = $arrProducts[$j]['price'][$i];
                    $id = $priceBase['id'][$i];
                    $result = mysqli_query($con, "UPDATE a_price SET price_type ='$priceType', price = '$price' WHERE id = '$id'");
                    if (!$result) {
                        $error = mysqli_error($con);
                        print("Ошибка : " . $error);
                    }
                }
                for ($i = $priceBaseCount; $i < $priceCount; $i++) {
                    $priceType = $arrProducts[$j]['priceType'][$i];
                    $price = $arrProducts[$j]['price'][$i];
                    $id = $priceBase['id'][$i];
                    $result = mysqli_query($con, "INSERT INTO a_price (`product_id`, `price_type`, `price`) VALUES ('$productId', '$priceType', '$price')");
                    if (!$result) {
                        $error = mysqli_error($con);
                        print("Ошибка : " . $error);
                    }
                }
                unset($i);
            } else if ($priceCount < $priceBaseCount) {
                for ($i = 0; $i < $priceCount; $i++) {
                    $priceType = $arrProducts[$j]['priceType'][$i];
                    $price = $arrProducts[$j]['price'][$i];
                    $id = $priceBase['id'][$i];
                    $result = mysqli_query($con, "UPDATE a_price SET price_type ='$priceType', price = '$price' WHERE id = '$id'");
                    if (!$result) {
                        $error = mysqli_error($con);
                        print("Ошибка : " . $error);
                    }
                }
                $result = mysqli_query($con, "DELETE FROM a_price WHERE `product_id` = '$productId' AND `id` > '$id'");
            }
            // Обновление записей в таблице a_property
            $propertyCount = count($arrProducts[$j]['property']);
            $propertyBase = [];
            $selectPropertyBase = mysqli_query($con, "SELECT `id`, `property_value` FROM `a_property` WHERE `product_id` = '$productId'");
            while ( $rec = mysqli_fetch_assoc($selectPropertyBase)) {
                $propertyBase['id'][] = $rec['id'];
                $propertyBase['propertyValue'][] = $rec['property_value'];
            }
            $propertyBaseCount = count($propertyBase['propertyValue']);
            if ($propertyCount == $propertyBaseCount) {
                for ($i = 0; $i < $propertyCount; $i++) {
                    $propertyValue = $arrProducts[$j]['property'][$i];
                    $id = $propertyBase['id'][$i];
                    $result = mysqli_query($con, "UPDATE a_property SET property_value ='$propertyValue' WHERE id = '$id'");
                    if (!$result) {
                        $error = mysqli_error($con);
                        print("Ошибка : " . $error);
                    }
                }
                unset($i);
            }   else if ($propertyCount > $propertyBaseCount) {
                for ($i = 0; $i < $propertyBaseCount; $i++) {
                    $propertyValue = $arrProducts[$j]['property'][$i];
                    $id = $propertyBase['id'][$i];
                    $result = mysqli_query($con, "UPDATE a_property SET property_value ='$propertyValue' WHERE id = '$id'");
                    if (!$result) {
                        $error = mysqli_error($con);
                        print("Ошибка : " . $error);
                    }
                }
                for ($i = $propertyBaseCount; $i < $propertyCount; $i++) {
                    $propertyValue = $arrProducts[$j]['property'][$i];
                    $id = $propertyBase['id'][$i];
                    $result = mysqli_query($con, "INSERT INTO a_property (`product_id`, `property_value`) VALUES ('$productId', '$propertyValue')");
                    if (!$result) {
                        $error = mysqli_error($con);
                        print("Ошибка : " . $error);
                    }
                }
                unset($i);
            }   else if ($propertyCount < $propertyBaseCount) {
                for ($i = 0; $i < $propertyCount; $i++) {
                    $propertyValue = $arrProducts[$j]['property'][$i];
                    $id = $propertyBase['id'][$i];
                    $result = mysqli_query($con, "UPDATE a_property SET property_value ='$propertyValue' WHERE id = '$id'");
                    if (!$result) {
                        $error = mysqli_error($con);
                        print("Ошибка : " . $error);
                    }
                }
                $result = mysqli_query($con, "DELETE FROM a_property WHERE `product_id` = '$productId' AND `id` > '$id'");
            }

            // Обновление записей в таблице a_category
            $categoryCount = count($arrProducts[$j]['category']);
            $category = [];
            for ($i = 0; $i < $categoryCount; $i++) {
                $categoryName = $arrProducts[$j]['category'][$i];
                $selectCategoryBase = mysqli_query($con, "SELECT `name` FROM `a_category`");
                while ( $rec = mysqli_fetch_assoc($selectCategoryBase)) {
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

            // Обновление записей в таблице property_category
            $categoryArr = [];
            $idArr = [];
            $selectCategoryBase = mysqli_query($con, "SELECT `id`,`name` FROM `a_category`");
            while ( $rec = mysqli_fetch_assoc($selectCategoryBase)) {
                $categoryArr[] = $rec['name'];
                $idArr[]= $rec['id'];
            }
            $arr = array_combine($idArr, $categoryArr);
            $deleteProductCategory = mysqli_query($con, "DELETE FROM product_category WHERE `product_id` = '$productId'");
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
    $xml = new DOMDocument('1.0', 'windows-1251');
    $xml -> preserveWhiteSpace = false;
    $xml -> formatOutput = true;
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
    $nodeProducts = $xml->createElement('Товары');
    foreach ($idProduct as $value) {
        $nodeProduct = $xml->createElement('Товар');
        $sql = mysqli_query($con, "SELECT `code`, `name` FROM `a_product` WHERE `id` = '$value'");
        $item = mysqli_fetch_assoc($sql);
        $code = $item['code'];
        $name = $item['name'];
        $nodeProductAttr_1 = $xml->createAttribute('Код');
        $nodeProductAttr_1 -> value = "$code";
        $nodeProduct->appendChild($nodeProductAttr_1);
        $nodeProductAttr_2 = $xml->createAttribute('Название');
        $nodeProductAttr_2 -> value = "$name";
        $nodeProduct->appendChild($nodeProductAttr_2);
        $nodeProducts -> appendChild($nodeProduct);
        $sql = mysqli_query($con, "SELECT `price_type`, `price`  FROM `a_price` WHERE `product_id` = '$value'");
        while ( $rec = mysqli_fetch_assoc($sql)) {
            $priceType = $rec['price_type'];
            $price = $rec['price'];
            $nodePrice = $xml->createElement('Цена', "$price");
            $nodePriceAttr_1 = $xml->createAttribute('Тип');
            $nodePriceAttr_1 -> value = "$priceType";
            $nodePrice->appendChild($nodePriceAttr_1);
            $nodeProduct -> appendChild($nodePrice);
        }
        $sql = mysqli_query($con, "SELECT `property_value`  FROM `a_property` WHERE `product_id` = '$value'");
        $nodeProperties = $xml->createElement('Свойствa');
        $nodeProduct -> appendChild($nodeProperties);
        while ( $rec = mysqli_fetch_assoc($sql)) {
            $property = $rec['property_value'];
            $nodeProperty = $xml->createElement('Свойство', "$property");
            $nodeProperties -> appendChild($nodeProperty);
        }
        $sql = mysqli_query($con, "SELECT `category_id` FROM `product_category` WHERE `product_id` = '$value'");
        $nodeCategories = $xml->createElement('Разделы');
        $nodeProduct -> appendChild($nodeCategories);
        while ( $rec = mysqli_fetch_assoc($sql) ) {
            $id = $rec['category_id'];
            $sql2 = mysqli_query($con, "SELECT `name` FROM `a_category` WHERE `id` = '$id'");
            $category = mysqli_fetch_assoc($sql2)['name'];
            $nodeCategory = $xml->createElement('Раздел', "$category");
            $nodeCategories -> appendChild($nodeCategory);
        }
        $i++;
    }
    $xml -> appendChild($nodeProducts);
    $xml -> save("$a");
    mysqli_close($con);
}

?>