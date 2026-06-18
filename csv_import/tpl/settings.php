<?php if (!defined('NGCMS')) die('HAL'); ?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-cog mr-2"></i>Настройки по умолчанию
                </h3>
            </div>
            <form method="post"
                action="admin.php?mod=extra-config&plugin=csv_import&action=settings_save">
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fa fa-file-text-o mr-1"></i>CSV</h3>
                                </div>
                                <div class="card-body">

                                    <div class="form-group">
                                        <label>Разделитель по умолчанию</label>
                                        <select name="default_delimiter" class="form-control">
                                            <?php
                                            $curDelim = pluginGetVariable('csv_import', 'default_delimiter') ?: ';';
                                            $delimOpts = [';' => '; (точка с запятой)', ',' => ', (запятая)', "\t" => 'Tab'];
                                            foreach ($delimOpts as $v => $l): ?>
                                                <option value="<?= htmlspecialchars($v) ?>"
                                                    <?= ($curDelim === $v) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($l) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Статус импортируемых записей</label>
                                        <select name="default_approve" class="form-control">
                                            <?php $curApprove = (string)(pluginGetVariable('csv_import', 'default_approve') ?? '1'); ?>
                                            <option value="1" <?= ($curApprove === '1') ? 'selected' : '' ?>>Опубликована</option>
                                            <option value="0" <?= ($curApprove === '0') ? 'selected' : '' ?>>Черновик</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Показывать на главной странице</label>
                                        <select name="default_mainpage" class="form-control">
                                            <?php $curMainpage = (string)(pluginGetVariable('csv_import', 'default_mainpage') ?? '1'); ?>
                                            <option value="1" <?= ($curMainpage === '1') ? 'selected' : '' ?>>Да</option>
                                            <option value="0" <?= ($curMainpage === '0') ? 'selected' : '' ?>>Нет</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fa fa-code mr-1"></i>YML</h3>
                                </div>
                                <div class="card-body">

                                    <div class="form-group">
                                        <label>Поле xfields для изображений</label>
                                        <select name="yml_image_xfield" class="form-control">
                                            <?php $curXf = (string)(pluginGetVariable('csv_import', 'yml_image_xfield') ?? ''); ?>
                                            <option value="">— не загружать —</option>
                                            <?php foreach ($xfImageFields as $k => $l): ?>
                                                <option value="<?= htmlspecialchars($k) ?>"
                                                    <?= ($curXf === $k) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($l) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Только поля типа <strong>images</strong></small>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Максимум изображений на запись</label>
                                        <select name="yml_max_images" class="form-control">
                                            <?php $curMax = (string)(pluginGetVariable('csv_import', 'yml_max_images') ?? '3'); ?>
                                            <?php foreach ([1, 2, 3, 5, 10] as $n): ?>
                                                <option value="<?= $n ?>"
                                                    <?= ($curMax === (string)$n) ? 'selected' : '' ?>>
                                                    <?= $n ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save mr-1"></i>Сохранить настройки
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
