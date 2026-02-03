-- Добавление поля module в таблицу комментариев для поддержки комментариев к галерее
-- Дата: 31 января 2026

ALTER TABLE `xttest_comments`
ADD COLUMN `module` VARCHAR(50) NULL DEFAULT '' AFTER `reg`;

-- Индекс для ускорения выборки комментариев по модулю
ALTER TABLE `xttest_comments`
ADD INDEX `idx_post_module` (`post`, `module`);
