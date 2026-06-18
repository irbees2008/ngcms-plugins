<?php
header('Content-type: text/html; charset=cp1251');

$yml_url = "https://sporttop.com.ua/sporttop_fitness.xml";

// Load CORE module
include_once __DIR__.'/../../core.php';

include_once(__DIR__.'/functions');

include_once(__DIR__.'/import.class.php');


$file = file_get_contents($yml_url);
$xml = new SimpleXMLElement($file);
unset($file);

$ctg = new YMLCategory();
$ctg->GetFromSite();
$ctg->GetFromXML($xml->shop->categories->category);

$ofs = new YMLOffer();

foreach ($xml->shop->offers->offer as $key => $offer) {
    $oif = (int)$offer->attributes()->id;

    $name = iconv('utf-8', 'windows-1251', (string)$offer->name);

    if ($name == "") {
        $name = iconv('utf-8', 'windows-1251', trim((string)$offer->model." ".(string)$offer->barcode));
    }

    if ($name != "") {
        $url = strtolower($parse->translit($name, 1, 1));
        $url = str_replace("/", "-", $url);
    }

    if ($url) {
        
        $vendorCode = iconv('utf-8', 'windows-1251', (string)$offer->vendorCode);
        
        if ($vendorCode){        
            $prd_row = $mysql->record(
                "SELECT * FROM ".prefix."_eshop_products WHERE code = ".db_squote($vendorCode)." LIMIT 1"
            );
        }
        else{
            $prd_row = $mysql->record(
                "SELECT * FROM ".prefix."_eshop_products WHERE url = ".db_squote($url)." LIMIT 1"
            );
        }
        if (!is_array($prd_row)) {
            $oid = $ofs->Add($offer, $name, $url);
            echo 'Добавлен товар: '.$name.'<br>';
        } else {
            $oid = $ofs->Update($prd_row['id'], $offer, $name);
            echo 'Обновлен товар: '.$name.'<br>';
        }
    }
}

generate_catz_cache(true);
generate_features_cache(true);


file_put_contents(__DIR__."/log.txt", 'work '.time().'\n', FILE_APPEND);