-- Исправление проблемы с datetime полями в таблице ng_news для плагина nsched
-- Выполните этот скрипт, если получаете ошибку при установке плагина nsched

-- УНИВЕРСАЛЬНОЕ РЕШЕНИЕ: работает как для новых, так и для существующих полей

-- Добавляем поля если их нет (безопасная операция)
ALTER TABLE `ng_news`
ADD COLUMN IF NOT EXISTS `nsched_activate` INT(10) NOT NULL DEFAULT 0;

ALTER TABLE `ng_news`
ADD COLUMN IF NOT EXISTS `nsched_deactivate` INT(10) NOT NULL DEFAULT 0;

-- Если поля уже существовали с типом datetime, очищаем проблемные значения
UPDATE `ng_news` SET `nsched_activate` = 0
WHERE `nsched_activate` IN ('0000-00-00 00:00:00', '0', 0);

UPDATE `ng_news` SET `nsched_deactivate` = 0
WHERE `nsched_deactivate` IN ('0000-00-00 00:00:00', '0', 0);

-- Приводим к правильному типу (безопасная операция)
ALTER TABLE `ng_news`
MODIFY COLUMN `nsched_activate` INT(10) NOT NULL DEFAULT 0,
MODIFY COLUMN `nsched_deactivate` INT(10) NOT NULL DEFAULT 0;

-- 3. Проверка результата
SELECT COUNT(*) as total_records,
       COUNT(CASE WHEN nsched_activate > 0 THEN 1 END) as activate_scheduled,
       COUNT(CASE WHEN nsched_deactivate > 0 THEN 1 END) as deactivate_scheduled
FROM `ng_news`;
