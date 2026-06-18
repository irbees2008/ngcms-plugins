<?php if (!defined('NGCMS')) die('HAL'); ?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-table mr-2"></i>Маппинг колонок:
                    <strong><?= htmlspecialchars($filename) ?></strong>
                </h3>
                <div class="card-tools">
                    <a href="admin.php?mod=extra-config&plugin=csv_import"
                       class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left mr-1"></i>Назад
                    </a>
                </div>
            </div>

            <form method="post"
                  action="admin.php?mod=extra-config&plugin=csv_import&action=run">
                <input type="hidden" name="file"      value="<?= htmlspecialchars($filename) ?>">
                <input type="hidden" name="delimiter" value="<?= htmlspecialchars($delimiter) ?>">
                <input type="hidden" name="has_header" value="<?= $hasHeader ? 1 : 0 ?>">

                <div class="card-body">

                    <?php /* ─── Общие параметры ─── */ ?>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Категория по умолчанию</label>
                                <select name="cat_id" class="form-control">
                                    <?php foreach ($cats as $id => $name): ?>
                                        <option value="<?= intval($id) ?>">
                                            <?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label>Статус</label>
                                <select name="approve" class="form-control">
                                    <option value="1">Опубликована</option>
                                    <option value="0">Черновик</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label>На главной</label>
                                <select name="mainpage" class="form-control">
                                    <option value="1">Да</option>
                                    <option value="0">Нет</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="update_mode" value="1" id="update_mode">
                                <label class="form-check-label" for="update_mode">
                                    Обновлять существующие (по alt_name)
                                </label>
                            </div>
                        </div>
                    </div>

                    <?php /* ─── Таблица маппинга ─── */ ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="40">#</th>
                                    <?php if ($hasHeader): ?>
                                        <th>Колонка CSV</th>
                                    <?php endif; ?>
                                    <?php for ($pi = 0; $pi < min(3, count($preview)); $pi++): ?>
                                        <th>Пример <?= ($pi + 1) ?></th>
                                    <?php endfor; ?>
                                    <th>Поле назначения</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $colCount  = isset($preview[0]) ? count($preview[0]) : 0;
                                $newsFields = [
                                    'skip'        => '— пропустить —',
                                    'title'       => 'Заголовок (обязательно)',
                                    'short_story' => 'Краткое описание',
                                    'full_story'  => 'Полное описание',
                                    'alt_name'    => 'alt_name (URL)',
                                    'category'    => 'ID категории',
                                    'tags'        => 'Теги / ключевые слова',
                                ];
                                for ($i = 0; $i < $colCount; $i++):
                                    $header = $hasHeader ? strtolower(trim($preview[0][$i] ?? '')) : '';
                                ?>
                                    <tr>
                                        <td class="text-center text-muted small"><?= $i ?></td>
                                        <?php if ($hasHeader): ?>
                                            <td><strong><?= htmlspecialchars($preview[0][$i] ?? '') ?></strong></td>
                                        <?php endif; ?>
                                        <?php foreach (array_slice($preview, 0, 3) as $pi => $row): ?>
                                            <td class="text-muted small">
                                                <?= htmlspecialchars(mb_substr($row[$i] ?? '', 0, 40)) ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td>
                                            <select name="map[<?= $i ?>]" class="form-control form-control-sm">
                                                <?php foreach ($newsFields as $val => $label): ?>
                                                    <option value="<?= htmlspecialchars($val) ?>"
                                                        <?= ($header === $val) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($label) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <?php if (!empty($xfFields)): ?>
                                                    <optgroup label="── xfields ──">
                                                        <?php foreach ($xfFields as $key => $lbl): ?>
                                                            <option value="xf_<?= htmlspecialchars($key) ?>"
                                                                <?= ($header === $key) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($lbl) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endif; ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-play mr-1"></i>Запустить импорт
                    </button>
                    <a href="admin.php?mod=extra-config&plugin=csv_import"
                       class="btn btn-secondary ml-2">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>
