<?php if (!defined('NGCMS')) die('HAL'); ?>
<div class="row">
    <div class="col-12">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-code mr-2"></i>YML импорт:
                    <strong><?= htmlspecialchars($filename) ?></strong>
                    <?php if ($data['shop_name']): ?>
                        <small class="text-muted ml-2">
                            магазин: <?= htmlspecialchars($data['shop_name']) ?>
                        </small>
                    <?php endif; ?>
                    <span class="badge badge-info ml-2">
                        <?= count($data['offers']) ?> товаров
                    </span>
                </h3>
                <div class="card-tools">
                    <a href="admin.php?mod=extra-config&plugin=csv_import"
                       class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left mr-1"></i>Назад
                    </a>
                </div>
            </div>

            <form method="post"
                  action="admin.php?mod=extra-config&plugin=csv_import&action=yml_run">
                <input type="hidden" name="file" value="<?= htmlspecialchars($filename) ?>">

                <div class="card-body">

                    <?php
                    $newsTargets = [
                        'skip'        => '— пропустить —',
                        'title'       => 'Заголовок (title)',
                        'short_story' => 'Краткое описание',
                        'full_story'  => 'Полное описание',
                        'alt_name'    => 'alt_name (URL)',
                        'tags'        => 'Теги / ключевые слова',
                    ];
                    $ymlFieldLabels = [
                        'name'              => 'Название (name)',
                        'price'             => 'Цена (price)',
                        'oldprice'          => 'Старая цена (oldprice)',
                        'vendor'            => 'Бренд (vendor)',
                        'model'             => 'Модель (model)',
                        'description'       => 'Описание (description)',
                        'barcode'           => 'Штрихкод (barcode)',
                        'vendorCode'        => 'Артикул (vendorCode)',
                        'count'             => 'Остаток (count)',
                        'weight'            => 'Вес (weight)',
                        'volume'            => 'Объём (volume)',
                        'typePrefix'        => 'Тип товара (typePrefix)',
                        'country_of_origin' => 'Страна производства',
                        'url'               => 'URL товара',
                        'currencyId'        => 'Валюта (currencyId)',
                    ];
                    $autoMap = [
                        'name'        => 'title',
                        'description' => 'short_story',
                        'vendor'      => isset($xfFields['brand'])     ? 'xf_brand'      : (isset($xfFields['vendor'])     ? 'xf_vendor'     : 'skip'),
                        'price'       => isset($xfFields['price'])     ? 'xf_price'      : 'skip',
                        'oldprice'    => isset($xfFields['oldprice'])  ? 'xf_oldprice'   : 'skip',
                        'model'       => isset($xfFields['model'])     ? 'xf_model'      : 'skip',
                        'barcode'     => isset($xfFields['barcode'])   ? 'xf_barcode'    : 'skip',
                        'vendorCode'  => isset($xfFields['vendorCode'])? 'xf_vendorCode' : (isset($xfFields['articul']) ? 'xf_articul' : 'skip'),
                        'weight'      => isset($xfFields['weight'])    ? 'xf_weight'     : 'skip',
                        'count'       => isset($xfFields['count'])     ? 'xf_count'      : 'skip',
                    ];
                    $exOffer = $data['offers'][0] ?? [];
                    ?>

                    <?php /* ── 1. Стандартные поля YML ─── */ ?>
                    <?php if ($data['fields']): ?>
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title">
                                <span class="badge badge-secondary mr-2">1</span>
                                Маппинг стандартных полей YML
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="180">Поле YML</th>
                                        <th>Пример</th>
                                        <th width="260">Поле NGCMS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['fields'] as $f):
                                        $label    = $ymlFieldLabels[$f] ?? $f;
                                        $example  = mb_substr((string)($exOffer[$f] ?? ''), 0, 80);
                                        $selected = $autoMap[$f] ?? 'skip';
                                    ?>
                                        <tr>
                                            <td>
                                                <code><?= htmlspecialchars($f) ?></code>
                                                <br><small class="text-muted"><?= htmlspecialchars($label) ?></small>
                                            </td>
                                            <td class="text-muted small"><?= htmlspecialchars($example) ?></td>
                                            <td>
                                                <select name="field_map[<?= htmlspecialchars($f) ?>]"
                                                        class="form-control form-control-sm">
                                                    <?php foreach ($newsTargets as $val => $lbl): ?>
                                                        <option value="<?= htmlspecialchars($val) ?>"
                                                            <?= $selected === $val ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($lbl) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                    <?php if (!empty($xfFields)): ?>
                                                        <optgroup label="── xfields ──">
                                                            <?php foreach ($xfFields as $xk => $xl): ?>
                                                                <option value="xf_<?= htmlspecialchars($xk) ?>"
                                                                    <?= $selected === 'xf_' . $xk ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($xl) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </optgroup>
                                                    <?php endif; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php /* ── 2. Param характеристики ─── */ ?>
                    <?php if (!empty($data['params'])): ?>
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title">
                                <span class="badge badge-secondary mr-2">2</span>
                                Маппинг &lt;param&gt; характеристик
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Param name</th>
                                        <th>Пример</th>
                                        <th width="260">Поле xfields</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['params'] as $pname):
                                        $example = mb_substr((string)($exOffer['params'][$pname] ?? ''), 0, 80);
                                        $pkey    = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $pname));
                                        $pSel    = 'skip';
                                        foreach ($xfFields as $xk => $xl) {
                                            if (strtolower($xk) === $pkey) { $pSel = 'xf_' . $xk; break; }
                                        }
                                    ?>
                                        <tr>
                                            <td><code>param:<?= htmlspecialchars($pname) ?></code></td>
                                            <td class="text-muted small"><?= htmlspecialchars($example) ?></td>
                                            <td>
                                                <select name="field_map[param:<?= htmlspecialchars($pname) ?>]"
                                                        class="form-control form-control-sm">
                                                    <option value="skip">— пропустить —</option>
                                                    <?php if (!empty($xfFields)): ?>
                                                        <optgroup label="── xfields ──">
                                                            <?php foreach ($xfFields as $xk => $xl): ?>
                                                                <option value="xf_<?= htmlspecialchars($xk) ?>"
                                                                    <?= $pSel === 'xf_' . $xk ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($xl) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </optgroup>
                                                    <?php endif; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php /* ── 3. Изображения ─── */ ?>
                    <?php
                    $hasPictures    = false;
                    foreach ($data['offers'] as $o) {
                        if (!empty($o['pictures'])) { $hasPictures = true; break; }
                    }
                    $imageXfOptions = [];
                    if (function_exists('xf_configLoad')) {
                        $xfc = xf_configLoad();
                        foreach ($xfc['news'] ?? [] as $k => $v) {
                            if (($v['type'] ?? '') === 'images') $imageXfOptions[$k] = $k . ' — ' . $v['title'];
                        }
                    }
                    ?>
                    <div class="card card-outline card-info mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title">
                                <span class="badge badge-info mr-2">3</span>
                                Изображения
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!$hasPictures): ?>
                                <p class="text-muted mb-0">
                                    <i class="fa fa-info-circle mr-1"></i>
                                    В файле нет элементов &lt;picture&gt;.
                                </p>
                            <?php else: ?>
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input"
                                           id="download_images" name="download_images" value="1" checked>
                                    <label class="form-check-label" for="download_images">
                                        Загружать изображения из &lt;picture&gt; на сервер
                                    </label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>XField для изображений <small>(тип: images)</small></label>
                                            <select name="image_xf_field" class="form-control">
                                                <option value="">— не выбрано —</option>
                                                <?php foreach ($imageXfOptions as $k => $l): ?>
                                                    <option value="<?= htmlspecialchars($k) ?>">
                                                        <?= htmlspecialchars($l) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <?php if (!$imageXfOptions): ?>
                                                    <?php foreach ($xfFields as $xk => $xl): ?>
                                                        <option value="<?= htmlspecialchars($xk) ?>">
                                                            <?= htmlspecialchars($xl) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <?php if (!$imageXfOptions): ?>
                                                <small class="text-warning">
                                                    <i class="fa fa-exclamation-triangle"></i>
                                                    Поля xfields типа «images» не найдены.
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Макс. изображений на товар</label>
                                            <input type="number" name="max_images" class="form-control"
                                                   value="3" min="1" max="10">
                                        </div>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Файлы сохраняются в <code>uploads/dsn/</code> и регистрируются в
                                    таблице изображений NGCMS. Повторный импорт не дублирует файлы.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php /* ── 4. Категории ─── */ ?>
                    <?php if (!empty($data['categories'])): ?>
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title">
                                <span class="badge badge-secondary mr-2">4</span>
                                Маппинг категорий
                                <small class="text-muted ml-1">
                                    (<?= count($data['categories']) ?> в YML)
                                </small>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Категория YML</th>
                                        <th>Категория NGCMS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['categories'] as $ymlId => $ymlName):
                                        $matched = 0;
                                        foreach ($cats as $cid => $cname) {
                                            $cleanCname = preg_replace('/\s*\(.*\)$/', '', html_entity_decode($cname));
                                            if (mb_strtolower(trim($ymlName)) === mb_strtolower(trim($cleanCname))) {
                                                $matched = $cid; break;
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($ymlName) ?>
                                                <small class="text-muted ml-1">(id: <?= htmlspecialchars($ymlId) ?>)</small>
                                            </td>
                                            <td>
                                                <select name="cat_map[<?= htmlspecialchars($ymlId) ?>]"
                                                        class="form-control form-control-sm">
                                                    <?php foreach ($cats as $cid => $cname): ?>
                                                        <option value="<?= intval($cid) ?>"
                                                            <?= $matched == $cid ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($cname) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php /* ── 5. Параметры импорта ─── */ ?>
                    <div class="card card-outline card-primary mb-0">
                        <div class="card-header py-2">
                            <h3 class="card-title">
                                <span class="badge badge-primary mr-2">5</span>
                                Параметры импорта
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Категория по умолчанию</label>
                                        <small class="text-muted d-block">(если не задана маппингом)</small>
                                        <select name="cat_id" class="form-control">
                                            <?php foreach ($cats as $cid => $cname): ?>
                                                <option value="<?= intval($cid) ?>">
                                                    <?= htmlspecialchars($cname) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Статус</label>
                                        <select name="approve" class="form-control">
                                            <option value="1">Опубликована</option>
                                            <option value="0">Черновик</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>На главной</label>
                                        <select name="mainpage" class="form-control">
                                            <option value="1">Да</option>
                                            <option value="0">Нет</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-center">
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox"
                                               name="update_mode" value="1" id="yml_update_mode">
                                        <label class="form-check-label" for="yml_update_mode">
                                            Обновлять существующие (по alt_name)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa fa-play mr-2"></i>Запустить YML импорт
                    </button>
                    <a href="admin.php?mod=extra-config&plugin=csv_import"
                       class="btn btn-secondary ml-2">Отмена</a>
                    <span class="text-muted ml-3 small">
                        Будет обработано товаров: <strong><?= count($data['offers']) ?></strong>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>
