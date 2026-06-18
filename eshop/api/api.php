<?php

include_once(__DIR__.'/functions.php');

class ApiEshop
{
    const STATUS_OK = 'OK';
    const STATUS_ERROR = 'ERROR';

    public $version;
    public $type;
    public $control;

    public $allowMethods = [
        'get_orders',
        'get_order_products',
        'get_features',
        'get_variants',

        'update_order_statuses',
        'update_variants',
        'update_products',
        'update_features',
    ];

    public function __construct($version)
    {
        $this->version = $version;
        $this->type = $_REQUEST['type'];
        $this->control = $_GET;
    }

    public function run()
    {

        $method = str_replace('-', '_', $this->type);
        if (!$this->checkApiMethod($method)) {
            $results = ['data' => [], 'status' => self::STATUS_ERROR, 'message' => 'Method does not exist'];
            $this->renderResults($results);
        }

        if (!$this->checkToken($this->control['token'])) {
            $results = ['data' => [], 'status' => self::STATUS_ERROR, 'message' => 'Incorrect token'];
            $this->renderResults($results);
        }

        $this->$method();
    }

    public function get_orders()
    {
        $conditions = [];
        $this->addDateTimeCondition($conditions);
        if (!empty($this->control['order_id'])) {
            $conditions[] = 'o.id = '.(int)$this->control['order_id'];
        }

        $orders = $this->getOrders($conditions);

        $this->encodeUtf8Array($orders);
        $results = ['data' => $orders, 'status' => self::STATUS_OK];
        $this->renderResults($results);
    }

    public function get_order_products()
    {
        $conditions = [];
        $this->addDateTimeCondition($conditions);
        if (!empty($this->control['order_id'])) {
            $conditions[] = 'o.id = '.(int)$this->control['order_id'];
        }

        $orders = array_keys($this->getOrders($conditions));
        $positions = $this->getPos($orders);

        $results = ['data' => $positions, 'status' => self::STATUS_OK];
        $this->renderResults($results);
    }

    public function get_features()
    {
        global $mysql;

        foreach ($mysql->select("SELECT * FROM ".prefix."_eshop_features ORDER BY position, id") as $frow) {
            $options[] = $frow;
        }

        $this->encodeUtf8Array($options);
        $results = ['data' => $options, 'status' => self::STATUS_OK];
        $this->renderResults($results);
    }

    public function get_variants()
    {

        $conditions = [];
        if (!empty($this->control['product_id'])) {
            $conditions[] = 'product_id = '.(int)$this->control['product_id'];
        }

        $variants = $this->getVariants($conditions);
        $this->encodeUtf8Array($variants);

        $results = ['data' => $variants, 'status' => self::STATUS_OK];
        $this->renderResults($results);
    }

    public function update_order_statuses()
    {
        global $mysql;

        $output = [];
        $params = $this->getParams();

        foreach ($params as $id => $item) {

            $checked = $this->checkRequiredParams($item, ['order_id', 'status']);
            if (!$checked) {
                $output[$id] = [
                    'id' => null,
                    'status' => self::STATUS_ERROR,
                    'message' => 'Please fill out all required fields',
                ];
                continue;
            }

            $order_id = $item['order_id'];
            $status = $item['status'];

            $checked = $this->checkOrderID($order_id);
            if (!$checked) {
                $output[$id] = [
                    'id' => null,
                    'status' => self::STATUS_ERROR,
                    'message' => 'Item with this ID does not exist',
                ];
                continue;
            }

            if (!in_array($status, ["0", "1"])) {
                $output[$id] = ['id' => null, 'status' => self::STATUS_ERROR, 'message' => 'Incorrect status'];
                continue;
            }

            $mysql->query("UPDATE ".prefix."_eshop_orders SET paid = ".(int)$status." WHERE id = ".(int)$order_id);
            $output[$id] = ['id' => $order_id, 'status' => self::STATUS_OK];

        }

        $this->encodeUtf8Array($output);
        $results = ['data' => $output, 'status' => self::STATUS_OK];
        $this->renderResults($results);
    }

    public function update_variants()
    {
        global $mysql;

        $output = [];
        $params = $this->getParams();

        $mapParams = [
            'id' => 'id',
            'product_id' => 'product_id',
            'name' => 'name',
            'count' => 'amount',
            'price' => 'price',
            'price_old' => 'compare_price',
            'sku' => 'sku',
        ];

        $required = ['update' => ['id'], 'insert' => ['product_id']];

        foreach ($params as $id => $item) {
            if (!empty($item['id'])) {
                $checked = $this->checkRequiredParams($item, $required['update']);
                if (!$checked) {
                    $output[$id] = [
                        'id' => null,
                        'status' => self::STATUS_ERROR,
                        'message' => 'Please fill out all required fields',
                    ];
                    continue;
                }
                $checked = $this->checkVariantID($item['id']);
                if (!$checked) {
                    $output[$id] = [
                        'id' => null,
                        'status' => self::STATUS_ERROR,
                        'message' => 'Item with this ID does not exist',
                    ];
                    continue;
                }
                unset($item['product_id']);
                $params = $this->getParamsArray($item, $mapParams);
                $vnames = $this->generateUpdateArray($params);

                if (!empty($vnames)) {
                    $mysql->query('UPDATE '.prefix.'_eshop_variants SET '.implode(', ',$vnames).' WHERE id = \''.(int)$item['id'].'\' ');
                    $output[$id] = ['id' => $item['id'], 'status' => self::STATUS_OK];
                }
            } else {
                $checked = $this->checkRequiredParams($item, $required['insert']);
                if (!$checked) {
                    $output[$id] = [
                        'id' => null,
                        'status' => self::STATUS_ERROR,
                        'message' => 'Please fill out all required fields',
                    ];
                    continue;
                }
                $checked = $this->checkProductID($item['product_id']);
                if (!$checked) {
                    $output[$id] = [
                        'id' => null,
                        'status' => self::STATUS_ERROR,
                        'message' => 'Item with this ID does not exist',
                    ];
                    continue;
                }

                $params = $this->getParamsArray($item, $mapParams);
                $vnames = $this->generateInsertArray($params);
                if (!empty($vnames)) {
                    $mysql->query("INSERT INTO ".prefix."_eshop_variants (".implode(",",array_keys($vnames)).") VALUES (".implode(",", array_values($vnames)).")");
                    $qid = lastid('eshop_variants');
                    $output[$id] = ['id' => $qid, 'status' => self::STATUS_OK];
                }
            }
        }

        $this->encodeUtf8Array($output);
        $results = ['data' => $output, 'status' => self::STATUS_OK];
        $this->renderResults($results);
    }

    public function update_products()
    {

        global $mysql;

        $output = [];
        $params = $this->getParams();

        $mapParams = [
            'id' => 'id',
            'name' => 'name',
            'short_description' => 'annotation',
            'description' => 'body',
            'vendor_code' => 'code',
        ];

        $required = ['update' => ['id'], 'insert' => ['name']];

        foreach ($params as $id => $item) {
            if (!empty($item['id'])) {
                $checked = $this->checkRequiredParams($item, $required['update']);
                if (!$checked) {
                    $output[$id] = [
                        'id' => null,
                        'status' => self::STATUS_ERROR,
                        'message' => 'Please fill out all required fields',
                    ];
                    continue;
                }
                $checked = $this->checkProductID($item['id']);
                if (!$checked) {
                    $output[$id] = [
                        'id' => null,
                        'status' => self::STATUS_ERROR,
                        'message' => 'Item with this ID does not exist',
                    ];
                    continue;
                }
                $params = $this->getParamsArray($item, $mapParams);
                $vnames = $this->generateUpdateArray($params);
                if (!empty($vnames)) {
                    $mysql->query('UPDATE '.prefix.'_eshop_products SET '.implode(', ',$vnames).' WHERE id = \''.(int)$item['id'].'\' ');
                    $output[$id] = ['id' => $item['id'], 'status' => self::STATUS_OK];
                }
            } else {
                $checked = $this->checkRequiredParams($item, $required['insert']);
                if (!$checked) {
                    $output[$id] = [
                        'id' => null,
                        'status' => self::STATUS_ERROR,
                        'message' => 'Please fill out all required fields',
                    ];
                    continue;
                }
                $checked = $this->checkProductURL(['name' => $item['name']]);
                if ($checked) {
                    $output[$id] = [
                        'id' => null,
                        'status' => self::STATUS_ERROR,
                        'message' => 'Item with the same URL already exist',
                    ];
                    continue;
                }
                $item['url'] = $this->generateURLbyName($item['name']);
                $mapParams['url'] = 'url';
                $params = $this->getParamsArray($item, $mapParams);
                $vnames = $this->generateInsertArray($params);
                if (!empty($vnames)) {
                    $mysql->query("INSERT INTO ".prefix."_eshop_products (".implode(",", array_keys($vnames)).") VALUES (".implode(",",array_values($vnames)).")");
                    $qid = lastid('eshop_products');
                    $output[$id] = ['id' => $qid, 'status' => self::STATUS_OK];
                }
            }
        }

        $this->encodeUtf8Array($output);
        $results = ['data' => $output, 'status' => self::STATUS_OK];
        $this->renderResults($results);
    }

    public function update_features()
    {

        global $mysql;

        $output = [];
        $params = $this->getParams();

        $mapParams = [
            'id' => 'feature_id',
            'product_id' => 'product_id',
            'value' => 'value',
        ];

        $required = ['update' => ['id', 'product_id', 'value'], 'insert' => ['id', 'product_id', 'value']];

        foreach ($params as $id => $item) {
            if (!empty($item['id'])) {

                $checked = $this->checkRequiredParams($item, $required['update']);
                if (!$checked) {
                    $output[$id] = [
                        'id' => null,
                        'status' => self::STATUS_ERROR,
                        'message' => 'Please fill out all required fields',
                    ];
                    continue;
                }
                $checked = $this->checkFeatureID($item['id']);
                if (!$checked) {
                    $output[$id] = [
                        'id' => null,
                        'status' => self::STATUS_ERROR,
                        'message' => 'Item with this ID does not exist',
                    ];
                    continue;
                }

                $checked = $this->checkProductID($item['product_id']);
                if (!$checked) {
                    $output[$id] = [
                        'id' => null,
                        'status' => self::STATUS_ERROR,
                        'message' => 'Item with this ID does not exist',
                    ];
                    continue;
                }

                $checked = $this->checkOptionID($item['id'], $item['product_id']);
                if ($checked) {
                    $feature_id = $item['id'];
                    $product_id = $item['product_id'];
                    unset($item['id'], $item['product_id']);
                    $params = $this->getParamsArray($item, $mapParams);
                    $vnames = $this->generateUpdateArray($params);
                    if (!empty($vnames)) {
                        $mysql->query('UPDATE '.prefix.'_eshop_options SET '.implode(', ',$vnames).' WHERE '.$mapParams["id"].' = \''.(int)$feature_id.'\' AND '.$mapParams["product_id"].' = \''.(int)$product_id.'\'');
                        $output[$id] = ['id' => null, 'status' => self::STATUS_OK];
                    }
                } else {
                    $params = $this->getParamsArray($item, $mapParams, true);
                    $vnames = $this->generateInsertArray($params);
                    if (!empty($vnames)) {
                        $mysql->query("INSERT INTO ".prefix."_eshop_options (".implode(",",array_keys($vnames)).") VALUES (".implode(",",array_values($vnames)).")");
                        $output[$id] = ['id' => null, 'status' => self::STATUS_OK];
                    }
                }
            }
        }

        $this->encodeUtf8Array($output);
        $results = ['data' => $output, 'status' => self::STATUS_OK];
        $this->renderResults($results);
    }

    public function getPos($orders)
    {
        global $mysql;

        $conditions = [];
        if (!empty($orders)) {
            $orders_comma_separated = implode(",", $orders);
            array_push($conditions, "order_id IN (".$orders_comma_separated.") ");
        }

        $basket = [];
        $total = 0;
        foreach ($mysql->select("SELECT * FROM ".prefix."_eshop_order_basket ".(count($conditions) ? "WHERE ".implode(" AND ",$conditions) : ""),1) as $rec) {
            $rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
            $rec['xfields'] = unserialize($rec['linked_fld']);
            unset($rec['linked_fld']);
            $basket[] = $rec;
        }

        $orders = [];
        foreach ($basket as $position) {
            $total += round($position['price'] * $position['count'], 2);
            $orders[$position['order_id']]['positions'][] = $position;
        }

        $purchases = [];
        foreach ($mysql->select("SELECT * FROM ".prefix."_eshop_purchases ".(count($conditions) ? "WHERE ".implode(" AND ",$conditions) : ""),1) as $prow) {
            $prow['info'] = json_decode($prow['info'], true);
            foreach ($prow['info'] as $k_info => $v_info) {
                $prow['info_string'] .= $k_info." => ".iconv("utf-8", "windows-1251", $v_info)."<br/>";
            }
            $purchases[] = $prow;
        }

        foreach ($orders as $id => $order) {
            $orders[$id]['purchases'] = $purchases;
            $orders[$id]['total'] = $total;
        }

        return $orders;
    }

    public function getOrders($conditions)
    {
        global $mysql;

        $orders = [];
        $fSort = " ORDER BY o.id";
        $sqlQ = "SELECT * , o.id AS order_id , o.name AS name FROM ".prefix."_eshop_orders o LEFT JOIN ".prefix."_users u ON o.author_id = u.id ".(count($conditions) ? "WHERE ".implode(" AND ",$conditions) : "").$fSort;

        foreach ($mysql->select($sqlQ) as $row) {
            $orders[$row['order_id']] = $row;
        }

        return $orders;
    }

    public function getVariants($conditions)
    {
        global $mysql;
        $variants = [];
        $fSort = " ORDER BY id DESC";
        $sqlQ = "SELECT * FROM ".prefix."_eshop_variants ".(count($conditions) ? "WHERE ".implode(" AND ",$conditions) : "").$fSort;

        foreach ($mysql->select($sqlQ) as $row) {
            $variants[] = $row;
        }

        return $variants;
    }

    public function addDateTimeCondition(&$conditions)
    {

        $fDateStart = $this->control['from'];
        $fDateEnd = $this->control['to'];

        if ($fDateStart && $fDateEnd) {
            $fDateEndWithTime = $fDateEnd." 23:59:59";
            array_push($conditions,"dt BETWEEN UNIX_TIMESTAMP(STR_TO_DATE(".db_squote($fDateStart).",'%Y-%m-%d')) AND UNIX_TIMESTAMP(STR_TO_DATE(".db_squote($fDateEndWithTime).",'%Y-%m-%d %H:%i:%s'))");
        } elseif ($fDateStart) {
            array_push($conditions,"dt BETWEEN UNIX_TIMESTAMP(STR_TO_DATE(".db_squote($fDateStart).",'%Y-%m-%d')) AND NOW()");
        } elseif ($fDateEnd) {
            $fDateEndWithTime = $fDateEnd." 23:59:59";
            array_push($conditions,"dt BETWEEN UNIX_TIMESTAMP(STR_TO_DATE('01.01.1970','%Y-%m-%d')) AND UNIX_TIMESTAMP(STR_TO_DATE(".db_squote($fDateEndWithTime).",'%Y-%m-%d %H:%i:%s'))");
        }
    }

    public function getParamsArray($item, $mapParams, $includeID = false)
    {
        $params = [];
        foreach ($item as $id => $value) {
            if (('id' == $id) && !$includeID) {
                continue;
            }
            $key = $mapParams[$id];
            $params[$key] = iconv("utf-8", "windows-1251", $value);
        }

        return $params;
    }

    public function generateUpdateArray($params)
    {
        $output = [];
        foreach ($params as $key => $value) {
            $output[] = $key.' = '.db_squote($value);
        }

        return $output;
    }

    public function generateInsertArray($params)
    {
        $output = [];
        foreach ($params as $key => $value) {
            $output[$key] = db_squote($value);
        }

        return $output;
    }

    public function checkRequiredParams($item, $required)
    {

        if (empty(array_intersect_key($item, array_flip($required)))) {
            return false;
        }

        foreach ($item as $id => $param) {
            if (in_array($id, $required)) {
                if ($param == "") {
                    return false;
                }
            }
        }

        return true;
    }

    public function checkProductURL($SQL)
    {
        global $mysql, $parse;

        if (empty($SQL['url'])) {
            $SQL['url'] = $this->generateURLbyName($SQL['name']);
        }

        if ($SQL["url"]) {
            if (is_array($mysql->record("SELECT id FROM ".prefix."_eshop_products WHERE url = ".db_squote($SQL["url"])." LIMIT 1"))) {
                return true;
            }
        }

        return false;
    }

    public function checkProductID($id)
    {
        global $mysql;

        if ($id) {
            if (is_array($mysql->record("SELECT id FROM ".prefix."_eshop_products WHERE id = ".(int)$id." LIMIT 1"))) {
                return true;
            }
        }

        return false;
    }

    public function checkProductExternalID($external_id)
    {
        if ($external_id) {
            if (is_array($this->getProductByExternalID($external_id))) {
                return true;
            }
        }

        return false;
    }

    public function getProductByExternalID($external_id)
    {
        global $mysql;
        $row = $mysql->record("SELECT * FROM ".prefix."_eshop_products WHERE external_id = ".(int)$external_id." LIMIT 1");

        return $row;
    }

    public function removeVariantsByProductID($product_id)
    {
        global $mysql;
        $mysql->query("DELETE FROM ".prefix."_eshop_variants WHERE product_id = ".(int)$product_id);
    }

    public function removeVariantsWithoutExteranalID($product_id)
    {
        global $mysql;
        $mysql->query("DELETE FROM ".prefix."_eshop_variants WHERE external_id = '' AND product_id = ".(int)$product_id);
    }

    public function updateProductByExternalID($external_id, $vnames)
    {
        global $mysql;
        if (!empty($vnames)) {
            $mysql->query('UPDATE '.prefix.'_eshop_products SET '.implode(', ',$vnames).' WHERE external_id = \''.(int)$external_id.'\' ');

            return true;
        }

        return false;
    }

    public function addVariant($vnames)
    {
        global $mysql;
        if (!empty($vnames)) {
            $mysql->query("INSERT INTO ".prefix."_eshop_variants (".implode(",", array_keys($vnames)).") VALUES (".implode(",",array_values($vnames)).")");
            $qid = lastid('eshop_variants');

            return $qid;
        }

        return false;
    }

    public function addProduct($pnames)
    {
        global $mysql;
        if (!empty($pnames)) {
            $mysql->query("INSERT INTO ".prefix."_eshop_products (".implode(",",array_keys($pnames)).") VALUES (".implode(",",array_values($pnames)).")");
            $qid = lastid('eshop_products');

            return $qid;
        }

        return false;
    }


    public function checkVariantID($id)
    {
        global $mysql;

        if ($id) {
            if (is_array(
                $mysql->record("SELECT id FROM ".prefix."_eshop_variants WHERE id = ".(int)$id." LIMIT 1"))) {
                return true;
            }
        }

        return false;
    }

    public function checkFeatureID($id)
    {
        global $mysql;

        if ($id) {
            if (is_array(
                $mysql->record("SELECT id FROM ".prefix."_eshop_features WHERE id = ".(int)$id." LIMIT 1"))) {
                return true;
            }
        }

        return false;
    }

    public function checkOptionID($id, $product_id)
    {
        global $mysql;

        if ($id) {
            if (is_array(
                $mysql->record("SELECT * FROM ".prefix."_eshop_options WHERE feature_id = ".(int)$id." AND product_id = ".(int)$product_id."  LIMIT 1"))) {
                return true;
            }
        }

        return false;
    }

    public function checkOrderID($id)
    {
        global $mysql;

        if ($id) {
            if (is_array(
                $mysql->record("SELECT id FROM ".prefix."_eshop_orders WHERE id = ".(int)$id." LIMIT 1"))) {
                return true;
            }
        }

        return false;
    }

    public function generateURLbyName($name)
    {
        global $parse;

        return strtolower($parse->translit(iconv("utf-8", "windows-1251", $name), 1, 0));
    }

    public function encodeUtf8Array(&$items)
    {
        array_walk_recursive(
            $items,
            function (&$value, $key) {
                $value = iconv("windows-1251", "utf-8", $value);
            }
        );
    }

    public function getParams()
    {
        $data = file_get_contents("php://input");
        $params = json_decode($data, 1);

        if (empty($params)) {
            $results = ['data' => [], 'status' => self::STATUS_ERROR, 'message' => 'Empty request'];
            $this->renderResults($results);
        }

        return $params;
    }

    public function checkToken($token)
    {
        global $mysql;

        if ($token) {
            if (is_array(
                $mysql->record("SELECT id FROM ".prefix."_eshop_api WHERE token = ".db_squote($token)." LIMIT 1"))) {
                return true;
            }
        }

        return false;
    }

    public function prepareItemArray($item, $itemKeys)
    {
        $newItem = [];

        foreach ($item as $key => $value) {
            if (in_array($key, $itemKeys)) {
                $newItem[$key] = $value;
            }
        }

        return $newItem;
    }

    public function updateVariantV1($product_id, $vnames)
    {

        $this->removeVariantsByProductID($product_id);

        return $this->addVariant($vnames);
    }

    public function updateVariant($product_id, $vnames)
    {
        $variants = $this->getVariantsByProductId($product_id);

        return $this->updateVariants($variants, $vnames);
    }

    public function updateVariants($variants, $vnames)
    {
        global $mysql;
        if (!empty($variants)) {
            foreach ($variants as $variant) {
                $mysql->query('UPDATE '.prefix.'_eshop_variants SET '.implode(', ',$vnames).' WHERE id = \''.(int)$variant['id'].'\' ');
            }

            return true;
        }

        return false;
    }

    public function getVariantsByProductId($product_id)
    {
        $variants = [];
        if ($product_id) {
            $conditions[] = 'product_id = '.(int)$product_id;
            $variants = $this->getVariants($conditions);
        }

        return $variants;
    }

    public function getVariantsByExternalId($external_id)
    {
        $variants = [];
        if ($external_id) {
            $conditions[] = "external_id = '".$external_id."'";
            $variants = $this->getVariants($conditions);
        }

        return $variants;
    }


    public function getEntityRow($table, $id)
    {
        global $mysql;

        $row = $mysql->record('SELECT * FROM '.$table.' WHERE id = '.(int)$id);

        return $row;
    }


    public function setActiveProducts($value)
    {
        global $mysql;

        $mysql->query('UPDATE '.prefix.'_eshop_products SET active = '. (int)$value);
    }

    public function setActive(&$item)
    {
        $item['active'] = 1;
    }

    public function setStock(&$item)
    {
        if (empty($item['stock'])) {
            if ($item['count'] > 0) {
                $item['stock'] = 5;
            } else {
                $item['stock'] = 0;
            }
        }
    }

    public function setError($id, $status, $message)
    {
        return [
            'id' => $id,
            'status' => $status,
            'message' => $message,
        ];
    }

    public function setResult($id, $status)
    {
        return [
            'id' => $id,
            'status' => $status,
        ];
    }

    public function checkApiMethod($method)
    {

        if (method_exists($this, $method) && in_array($method, $this->allowMethods)) {
            return true;
        } else {
            return false;
        }

    }

    public function renderResults($data)
    {

        global $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW;

        $SUPRESS_TEMPLATE_SHOW = 1;
        $SUPRESS_MAINBLOCK_SHOW = 1;

        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode($data);
        exit();
    }

}