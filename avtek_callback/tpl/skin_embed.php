<?php
// Skin variables available:
// $css, $formHtml, $js, $form (array), $formId, $settings
?>
<div class="avtek-cb-skin avtek-cb-skin-embed">
    <?php echo $css; ?>
    <div class="avtek-cb-skin-title" style="font-weight:700;margin:0 0 10px">
        <?php echo avtek_cb_h((string)($form['title'] ?? '')); ?>
    </div>
    <?php echo $formHtml; ?>
    <?php echo $js; ?>
</div>
<?php /*
    Примечание: атрибуты data-avtek-cb-* теперь расставляются на основной <form> в avtek_cb_render_form().
    Если требуется глобальный обработчик без инлайнового JS — подключите avtek_cb_front.js в шаблоне сайта.
*/ ?>
