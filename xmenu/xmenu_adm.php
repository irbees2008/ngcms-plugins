<?php
//
// Admin panel handler
//
class xmenu_adm_categories extends FilterAdminCategories
{
    function addCategoryForm(&$tvars)
    {
        global $mysql;
        // Получаем количество меню из конфига
        $menu_count = intval(extra_get_param('xmenu', 'menu_count')) ?: 9;
        $line = '';
        for ($i = 1; $i <= $menu_count; $i++) {
            $line .= '<label><input type=checkbox value="1" name="xmenu[' . $i . ']"> <b>' . $i . '</b></label> &nbsp; ';
        }
        $tvars['vars']['extend'] .= '<tr><td class="contentEntry1">Номера блоков меню</td><td class="contentEntry2">' . $line . '</td></tr>';
        return 1;
    }
    function addCategory(&$tvars, &$SQL)
    {
        $menu_count = intval(extra_get_param('xmenu', 'menu_count')) ?: 9;
        $line = '';
        if (isset($_REQUEST['xmenu']) && is_array($_REQUEST['xmenu'])) {
            for ($i = 1; $i <= $menu_count; $i++) {
                $line .= (isset($_REQUEST['xmenu'][$i]) && $_REQUEST['xmenu'][$i]) ? '#' : '_';
            }
        } else {
            $line = str_repeat('_', $menu_count);
        }
        $SQL['xmenu'] = $line;
        return 1;
    }
    function editCategoryForm($categoryID, $SQL, &$tvars)
    {
        global $mysql;
        $menu_count = intval(extra_get_param('xmenu', 'menu_count')) ?: 9;
        $line = '';
        $xmenu = $SQL['xmenu'] ?? str_repeat('_', $menu_count);
        for ($i = 1; $i <= $menu_count; $i++) {
            $line .= '<label><input type=checkbox value="1" name="xmenu[' . $i . ']"' . (($xmenu[$i - 1] == '#') ? ' checked' : '') . '> <b>' . $i . '</b></label> &nbsp; ';
        }
        $tvars['vars']['extend'] .= '<tr><td class="contentEntry1">Номера блоков меню</td><td class="contentEntry2">' . $line . '</td></tr>';
        return 1;
    }
    function editCategory($categoryID, $SQL, &$SQLnew, &$tvars)
    {
        $menu_count = intval(extra_get_param('xmenu', 'menu_count')) ?: 9;
        $line = '';
        if (isset($_REQUEST['xmenu']) && is_array($_REQUEST['xmenu'])) {
            for ($i = 1; $i <= $menu_count; $i++) {
                $line .= (isset($_REQUEST['xmenu'][$i]) && $_REQUEST['xmenu'][$i]) ? '#' : '_';
            }
        } else {
            $line = str_repeat('_', $menu_count);
        }
        $SQLnew['xmenu'] = $line;
        return 1;
    }
}
register_admin_filter('categories', 'xmenu', new xmenu_adm_categories);
