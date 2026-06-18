<?php

if (!defined('NGCMS')) {
    die('HAL');
}

include_once(__DIR__ . '/cache.php');
include_once(__DIR__ . '/functions.php');

class YMLCategory extends ImportConfig
{

    /**
     * Получаем список всех категорий
     * @return array
     */
    public function GetFromSite()
    {
        global $mysql;

        foreach ($mysql->select("SELECT * FROM " . prefix . "_eshop_categories ORDER BY position, id") as $category) {
            $this->Add2Session(
                $category['id'],
                $category['name'],
                $category['url'],
                $category['id'],
                $category['parent_id']
            );
        }

        if (!empty($_SESSION['cats'])) {
            $this->eco('Существующие категории: ' . count($_SESSION['cats']) . ' шт.<br>');
        } else {
            $this->eco('На сайте ещё нет категорий<br>');
        }
    }

    /**
     * Получаем категории из XML и добавляем их
     * @param $xml
     */
    public function GetFromXML($xml)
    {
        global $mysql, $parse;
        $update = 0;

        // Проверка на null или пустое значение
        if ($xml === null || !is_object($xml)) {
            $this->eco('XML категорий не найден или пуст.<br>');
            return;
        }

        foreach ($xml as $xml_cat) {
            $NAME = $xml_cat;
            $UF_ID = (int)$xml_cat->attributes()->id;
            $UF_PARENT_ID = (int)$xml_cat->attributes()->parentId;
            $description = (int)$xml_cat->attributes()->description;

            // Инициализация массива, если его нет
            if (!isset($_SESSION['cats_uf_ids']) || !is_array($_SESSION['cats_uf_ids'])) {
                $_SESSION['cats_uf_ids'] = array();
            }

            if (!in_array($UF_ID, $_SESSION['cats_uf_ids'])) {

                $URL = strtolower($parse->translit($NAME, 1, 1));
                $URL = str_replace("/", "-", $URL);

                if ($URL) {
                    if (!is_array(
                        $mysql->record(
                            "select id from " . prefix . "_eshop_categories where url = " . db_squote($URL) . " limit 1"
                        )
                    )) {

                        $mysql->query(
                            'INSERT INTO ' . prefix . '_eshop_categories (id, name, url, meta_title, parent_id, description)
                            VALUES
                            (   ' . db_squote($UF_ID) . ',
                                ' . db_squote($NAME) . ',
                                ' . db_squote($URL) . ',
                                ' . db_squote($NAME) . ',
                                ' . db_squote($UF_PARENT_ID) . ',
								' . db_squote($description) . '
                            )
                        '
                        );

                        $this->Add2Session(
                            $UF_ID,
                            $NAME,
                            $URL,
                            $UF_ID,
                            $UF_PARENT_ID,
                            $description
                        );

                        generate_catz_cache(true);

                        $this->eco('Добавлена категория: ' . $NAME . '<br>');
                        $update++;
                    }
                }
            }
        }

        // Безопасный подсчет элементов
        $xml_count = is_countable($xml) ? count($xml) : (is_object($xml) ? iterator_count($xml) : 0);
        $this->eco('Количество XML категорий: ' . $xml_count . ' шт.<br>');
        $this->eco('Добавлено ' . $update . ' категорий.<br>');
    }

    /**
     * Сохраняем все в сессию для пошаговости
     * @param $id
     * @param $name
     * @param $code
     * @param $uf_id
     * @param $uf_parent_id
     */
    public function Add2Session($id, $name, $code, $uf_id, $uf_parent_id)
    {
        $_SESSION['cats'][$id]['ID'] = $id;
        $_SESSION['cats'][$id]['NAME'] = $name;
        $_SESSION['cats'][$id]['CODE'] = $code;
        $_SESSION['cats'][$id]['UF_ID'] = $uf_id;
        $_SESSION['cats'][$id]['UF_PARENT_ID'] = $uf_parent_id;
        $_SESSION['cats_uf_ids'][] = $uf_id;
    }
}

class YMLOffer extends YMLCategory
{

    /**
     * Добавляем элемент
     * @param $offer
     * @return bool
     */
    public function Add($offer, $name, $url)
    {
        global $mysql, $SYSTEM_FLAGS, $config;

        $description = $offer->description;
        $PROP = array();
        //$PROP['id'] = (int)$offer->attributes()->id;
        $PROP['name'] = $name;
        $PROP['url'] = $url;
        $PROP['meta_title'] = $name;
        // $PROP['body'] = str_replace('&nbsp;', ' ', $description);
        $PROP['body'] = $offer->description;
        $PROP['date'] = time() + ($config['date_adjust'] * 60);
        $PROP['editdate'] = time() + ($config['date_adjust'] * 60);
        $PROP['code'] = $offer->vendorCode;
        $PROP['annotation'] = '';

        $vnames = [];
        foreach ($PROP as $k => $v) {
            $vnames[] = $k . ' = ' . db_squote($v);
        }
        $result = $mysql->query('INSERT INTO ' . prefix . '_eshop_products SET ' . implode(', ', $vnames) . ' ');

        if ($result) {

            $qid = lastid('eshop_products');

            if (count($offer->picture) > 0) {
                $pictures = $this->xml2array($offer->picture);
                $inx_img = 0;
                foreach ($pictures as $picture) {
                    try {
                        // Сбрасываем таймаут для каждого изображения
                        @set_time_limit(60);

                        $rootpath = $_SERVER['DOCUMENT_ROOT'];
                        $url = substr($picture, 0, strpos($picture, '?') ? strpos($picture, '?') : strlen($picture));
                        $name = basename($url);
                        $file_path = $rootpath . "/uploads/eshop/products/temp/$name";

                        // Используем cURL для более быстрой загрузки с таймаутом
                        if (function_exists('curl_init')) {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                            $img_data = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);

                            if ($img_data === false || $http_code != 200) {
                                $this->eco('Не удалось загрузить изображение: ' . $url . '<br>');
                                continue;
                            }
                            file_put_contents($file_path, $img_data);
                        } else {
                            // Fallback на file_get_contents с контекстом
                            $context = stream_context_create([
                                'http' => [
                                    'timeout' => 30,
                                    'ignore_errors' => true
                                ]
                            ]);
                            $img_data = @file_get_contents($url, false, $context);
                            if ($img_data === false) {
                                $this->eco('Не удалось загрузить изображение: ' . $url . '<br>');
                                continue;
                            }
                            file_put_contents($file_path, $img_data);
                        }

                        $fileParts = pathinfo($file_path);
                        $extension = $fileParts['extension'];

                        $extensions = array_map('trim', explode(',', pluginGetVariable('eshop', 'ext_image')));

                        $pre_quality = pluginGetVariable('eshop', 'pre_quality');

                        if (!in_array($extension, $extensions)) {
                            return "0";
                        }

                        // CREATE THUMBNAIL
                        if ($extension == "jpg" || $extension == "jpeg") {
                            $src = @imagecreatefromjpeg($file_path);
                        } else {
                            if ($extension == "png") {
                                $src = @imagecreatefrompng($file_path);
                            } else {
                                $src = @imagecreatefromgif($file_path);
                            }
                        }

                        if ($src === false) {
                            $this->eco('Ошибка загрузки изображения: ' . $name . '<br>');
                            @unlink($file_path);
                            continue;
                        }

                        list($width, $height) = getimagesize($file_path);

                        $newwidth = pluginGetVariable('eshop', 'width_thumb');

                        if ($width > $newwidth) {
                            $newheight = ($height / $width) * $newwidth;
                            $tmp = imagecreatetruecolor($newwidth, $newheight);

                            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                            $thumbname = $rootpath . "/uploads/eshop/products/temp/thumb/$name";

                            if (file_exists($thumbname)) {
                                unlink($thumbname);
                            }

                            imagejpeg($tmp, $thumbname, ($pre_quality >= 10 && $pre_quality <= 100) ? $pre_quality : 100);

                            imagedestroy($src);
                            imagedestroy($tmp);
                        } else {
                            if ($extension == "jpg" || $extension == "jpeg") {
                                $src = @imagecreatefromjpeg($file_path);
                            } else {
                                if ($extension == "png") {
                                    $src = @imagecreatefrompng($file_path);
                                } else {
                                    $src = @imagecreatefromgif($file_path);
                                }
                            }

                            if ($src === false) {
                                $this->eco('Ошибка загрузки изображения: ' . $name . '<br>');
                                @unlink($file_path);
                                continue;
                            }

                            imagejpeg($src, $file_path, ($pre_quality >= 10 && $pre_quality <= 100) ? $pre_quality : 100);
                            $thumbname = $rootpath . "/uploads/eshop/products/temp/thumb/$name";
                            copy($file_path, $thumbname);

                            imagedestroy($src);
                        }

                        $newwidth = pluginGetVariable('eshop', 'pre_width');
                        if (isset($newwidth) && ($newwidth != '0')) {

                            if ($extension == "jpg" || $extension == "jpeg") {
                                $src = @imagecreatefromjpeg($file_path);
                            } else {
                                if ($extension == "png") {
                                    $src = @imagecreatefrompng($file_path);
                                } else {
                                    $src = @imagecreatefromgif($file_path);
                                }
                            }

                            if ($src === false) {
                                $this->eco('Ошибка загрузки изображения для pre_width: ' . $name . '<br>');
                                continue;
                            }

                            list($width, $height) = getimagesize($file_path);
                            $newheight = ($height / $width) * $newwidth;
                            $tmp = imagecreatetruecolor($newwidth, $newheight);
                            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                            $thumbname = $file_path;

                            imagejpeg(
                                $tmp,
                                $thumbname,
                                ($pre_quality >= 10 && $pre_quality <= 100) ? $pre_quality : 100
                            );

                            imagedestroy($src);
                            imagedestroy($tmp);
                        }

                        $img = $name;

                        $timestamp = time();
                        $iname = $timestamp . "-" . $img;

                        @mkdir($_SERVER['DOCUMENT_ROOT'] . '/uploads/eshop/products/' . $qid . '/', 0777);
                        @mkdir($_SERVER['DOCUMENT_ROOT'] . '/uploads/eshop/products/' . $qid . '/thumb', 0777);

                        $temp_name = $_SERVER['DOCUMENT_ROOT'] . '/uploads/eshop/products/temp/' . $img;
                        $current_name = $_SERVER['DOCUMENT_ROOT'] . '/uploads/eshop/products/' . $qid . '/' . $iname;
                        rename($temp_name, $current_name);

                        $temp_name = $_SERVER['DOCUMENT_ROOT'] . '/uploads/eshop/products/temp/thumb/' . $img;
                        $current_name = $_SERVER['DOCUMENT_ROOT'] . '/uploads/eshop/products/' . $qid . '/thumb/' . $iname;
                        rename($temp_name, $current_name);

                        $mysql->query("INSERT INTO " . prefix . "_eshop_images (`filepath`, `product_id`, `position`) VALUES ('$iname','$qid','$inx_img')");

                        $inx_img += 1;
                    } catch (Exception $ex) {
                        $this->eco('Ошибка: ' . $ex . '<br>');

                        return "0";
                    }
                }
            }

            $category_id = (int)$offer->categoryId;

            if ($category_id != 0) {
                $mysql->query("DELETE FROM " . prefix . "_eshop_products_categories  WHERE `product_id` = '$qid' AND `category_id` = '$category_id'");
                $mysql->query("INSERT INTO " . prefix . "_eshop_products_categories (`product_id`, `category_id`) VALUES ('$qid','$category_id')");
            }

            $price = $offer->price;
            $currencyId = $offer->currencyId;

            if ((int)$offer->quantity_in_stock) {
                $stock = "5";
            } else {
                $stock = "0";
            }

            $quantity = (int)$offer->quantity_in_stock;

            if (isset($price)) {
                foreach ($SYSTEM_FLAGS['eshop']['currency'] as $currency) {
                    if ($currencyId == "RUR") {
                        $currencyId = "RUB";
                    }

                    if ($currency['code'] == $currencyId) {
                        $price = $price / $SYSTEM_FLAGS['eshop']['currency'][0]['rate_from'] * $currency['rate_from'];
                    }
                }

                $mysql->query("DELETE FROM " . prefix . "_eshop_variants WHERE product_id='$qid'");
                $mysql->query("INSERT INTO " . prefix . "_eshop_variants (`product_id`, `price`, `stock`, `amount`) VALUES ('$qid', '$price', '$stock', '$quantity')");
            }

            //            $returnArr = array();
            //
            //            $returnArr[] = array_merge(['value' => $PROP['id']], array('name' => 'source_id'));
            //
            //            foreach ($offer->children() as $element) {
            //
            //                if (mb_strtolower($element->getName()) == 'param') {
            //                    $returnArr[] = array_merge(
            //                        ['value' => (string)$element],
            //                        $this->getElementAttributes($element)
            //                    );
            //                }
            //
            ////                if (mb_strtolower($element->getName()) == 'url') {
            ////                    $returnArr[] = array_merge(
            ////                        ['value' => (string)$element],
            ////                        array('name' => 'source_url')
            ////                    );
            ////                }
            //
            //            }

            //            foreach ($returnArr as $el) {
            //                $f_name = iconv('utf-8', 'windows-1251', $el['name']);
            //                $feature_row = $mysql->record(
            //                    "select * from ".prefix."_eshop_features where name = ".db_squote($f_name)." limit 1"
            //                );
            //                if (!is_array($feature_row)) {
            //                    $mysql->query('INSERT INTO '.prefix.'_eshop_features (name) VALUES ('.db_squote($f_name).')');
            //                    $rowID = lastid('eshop_features');
            //                    $f_key = $rowID;
            //                } else {
            //                    $f_key = $feature_row['id'];
            //                }
            //
            //                $f_value = iconv('utf-8', 'windows-1251', $el['value']);
            //                if ($f_value != "") {
            //
            //                    $mysql->query(
            //                        "DELETE FROM ".prefix."_eshop_options  WHERE `product_id` = '$qid' AND `feature_id` = '$f_key' "
            //                    );
            //
            //                    $mysql->query(
            //                        "INSERT INTO ".prefix."_eshop_options (`product_id`, `feature_id`, `value`) VALUES ('$qid','$f_key','$f_value')"
            //                    );
            //                }
            //
            //            }

            if ($offer->param) {

                foreach ($offer->param as $el) {
                    $f_name = $el['name'];
                    $feature_row = $mysql->record(
                        "select * from " . prefix . "_eshop_features where name = " . db_squote($f_name) . " limit 1"
                    );
                    if (!is_array($feature_row)) {
                        $mysql->query('INSERT INTO ' . prefix . '_eshop_features (name) VALUES (' . db_squote($f_name) . ')');
                        $rowID = lastid('eshop_features');
                        $f_key = $rowID;
                    } else {
                        $f_key = $feature_row['id'];
                    }

                    $f_value = $el[0];
                    if ($f_value != "") {
                        $mysql->query("DELETE FROM " . prefix . "_eshop_options  WHERE `product_id` = '$qid' AND `feature_id` = '$f_key' ");
                        $mysql->query("INSERT INTO " . prefix . "_eshop_options (`product_id`, `feature_id`, `value`) VALUES ('$qid','$f_key','$f_value')");
                    }
                }
            }
        }

        //        die();


    }

    /**
     * Обновляем элемент
     * @param $id
     * @param $offer
     * @return bool1
     */
    public function Update($id, $offer, $name)
    {
        global $mysql, $SYSTEM_FLAGS, $config;

        $description = $offer->description;
        $PROP = array();
        //        $PROP['id'] = (int)$offer->attributes()->id;
        $PROP['body'] = str_replace('&nbsp;', ' ', $description);
        $PROP['editdate'] = time() + ($config['date_adjust'] * 60);
        //        $PROP['code'] = (string)$offer->vendorCode;
        $PROP['name'] = $name;

        $vnames = array();
        foreach ($PROP as $k => $v) {
            $vnames[] = $k . ' = ' . db_squote($v);
        }


        $qid = (int)$id;

        $result = $mysql->query('UPDATE ' . prefix . '_eshop_products SET ' . implode(', ', $vnames) . ' WHERE id = "' . $qid . '" LIMIT 1');

        $price = $offer->price;
        $currencyId = $offer->currencyId;

        if ((int)$offer->quantity_in_stock) {
            $stock = "5";
        } else {
            $stock = "0";
        }

        $quantity = (int)$offer->quantity_in_stock;

        if (isset($price)) {
            foreach ($SYSTEM_FLAGS['eshop']['currency'] as $currency) {
                if ($currencyId == "RUR") {
                    $currencyId = "RUB";
                }

                if ($currency['code'] == $currencyId) {
                    $price = $price / $SYSTEM_FLAGS['eshop']['currency'][0]['rate_from'] * $currency['rate_from'];
                }
            }

            $mysql->query("DELETE FROM " . prefix . "_eshop_variants WHERE product_id='$qid'");
            $mysql->query("INSERT INTO " . prefix . "_eshop_variants (`product_id`, `price`, `stock`, `amount`) VALUES ('$qid', '$price', '$stock', '$quantity')");
        }

        $mysql->query("DELETE FROM " . prefix . "_eshop_options  WHERE `product_id` = '$qid'");


        if ($offer->param) {

            foreach ($offer->param as $el) {
                $f_name = $el['name'];
                $feature_row = $mysql->record(
                    "select * from " . prefix . "_eshop_features where name = " . db_squote($f_name) . " limit 1"
                );
                if (!is_array($feature_row)) {
                    $mysql->query('INSERT INTO ' . prefix . '_eshop_features (name) VALUES (' . db_squote($f_name) . ')');
                    $rowID = lastid('eshop_features');
                    $f_key = $rowID;
                } else {
                    $f_key = $feature_row['id'];
                }

                $f_value = $el[0];

                $mysql->query("DELETE FROM " . prefix . "_eshop_options  WHERE `product_id` = '$qid' AND `feature_id` = '$f_key' ");

                if ($f_value != "") {
                    $mysql->query("INSERT INTO " . prefix . "_eshop_options (`product_id`, `feature_id`, `value`) VALUES ('$qid','$f_key','$f_value')");
                }
            }
        }



        //        die();


    }
}

class ImportConfig
{
    public $iblock_id = 6;
    public $debug = true;

    public function eco($data)
    {
        if ($this->debug === true) {
            echo $data;
        }
    }


    public function translitIt($str)
    {
        $str = Translit::transliterate($str);
        $str = Translit::asURLSegment($str);

        return $str;
    }


    public function addToFiles($key, $url)
    {

        $tempName = tempnam('/tmp', 'php_files');
        $originalName = basename(parse_url($url, PHP_URL_PATH));

        $imgRawData = file_get_contents($url);
        file_put_contents($tempName, $imgRawData);
        $info = getimagesize($tempName);

        $_FILES[$key] = array(
            'name' => $originalName,
            'type' => $info['mime'],
            'tmp_name' => $tempName,
            'error' => 0,
            'size' => strlen($imgRawData),
        );
    }

    /**
     * Gets lement attributes.
     *
     * @param \SimpleXMLElement $element
     *
     * @return array
     */
    public function getElementAttributes(\SimpleXMLElement $element)
    {
        $returnArr = [];

        foreach ($element->attributes() as $attrName => $attrValue):
            $returnArr[strtolower($attrName)] = $attrValue;
        endforeach;

        return $returnArr;
    }

    /**
     * function xml2array
     */
    public function xml2array($xmlObject, $out = array())
    {
        foreach ((array)$xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? xml2array($node) : $node;
        }

        return $out;
    }
}

final class Translit
{
    /**
     * Укр/Рус символы
     *
     * @var array
     * @access private
     * @static
     */
    static private $cyr = array(
        'Щ',
        'Ш',
        'Ч',
        'Ц',
        'Ю',
        'Я',
        'Ж',
        'А',
        'Б',
        'В',
        'Г',
        'Д',
        'Е',
        'Ё',
        'З',
        'И',
        'Й',
        'К',
        'Л',
        'М',
        'Н',
        'О',
        'П',
        'Р',
        'С',
        'Т',
        'У',
        'Ф',
        'Х',
        'Ь',
        'Ы',
        'Ъ',
        'Э',
        'Є',
        'Ї',
        'І',
        'щ',
        'ш',
        'ч',
        'ц',
        'ю',
        'я',
        'ж',
        'а',
        'б',
        'в',
        'г',
        'д',
        'е',
        'ё',
        'з',
        'и',
        'й',
        'к',
        'л',
        'м',
        'н',
        'о',
        'п',
        'р',
        'с',
        'т',
        'у',
        'ф',
        'х',
        'ь',
        'ы',
        'ъ',
        'э',
        'є',
        'ї',
        'і',
    );

    /**
     * Латинские соответствия
     *
     * @var array
     * @access private
     * @static
     */
    static private $lat = array(
        'Shh',
        'Sh',
        'Ch',
        'C',
        'Ju',
        'Ja',
        'Zh',
        'A',
        'B',
        'V',
        'G',
        'D',
        'Je',
        'Jo',
        'Z',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'R',
        'S',
        'T',
        'U',
        'F',
        'Kh',
        'Y',
        'Y',
        '',
        'E',
        'Je',
        'Ji',
        'I',
        'shh',
        'sh',
        'ch',
        'c',
        'ju',
        'ja',
        'zh',
        'a',
        'b',
        'v',
        'g',
        'd',
        'je',
        'jo',
        'z',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'r',
        's',
        't',
        'u',
        'f',
        'kh',
        'y',
        'y',
        '',
        'e',
        'je',
        'ji',
        'i',
    );

    /**
     * Приватный конструктор класса
     * не дает создавать объект этого класса
     *
     * @access private
     */
    private function __construct() {}

    /**
     * Статический метод транслитерации
     *
     * @param string
     * @return string
     * @access public
     * @static
     */

    static public function transliterate($string, $wordSeparator = '', $clean = false)
    {
        for ($i = 0; $i < count(self::$cyr); $i++) {
            $string = str_replace(self::$cyr[$i], self::$lat[$i], $string);
        }

        $string = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[jJ]e/", "\${1}e", $string);
        $string = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[jJ]/", "\${1}y", $string);
        $string = preg_replace("/([eyuioaEYUIOA]+)[Kk]h/", "\${1}h", $string);
        $string = preg_replace("/^kh/", "h", $string);
        $string = preg_replace("/^Kh/", "H", $string);

        $string = trim($string);

        if ($wordSeparator) {
            $string = str_replace(' ', $wordSeparator, $string);
            $string = preg_replace('/[' . $wordSeparator . ']{2,}/', '', $string);
        }

        if ($clean) {
            $string = strtolower($string);
            $string = preg_replace('/[^-_a-z0-9]+/', '', $string);
        }

        return $string;
    }

    /**
     * Приведение к УРЛ
     *
     * @return string
     * @access public
     * @static
     */
    static public function asURLSegment($string)
    {
        return strtolower(self::transliterate($string, '_', true));
    }
}
