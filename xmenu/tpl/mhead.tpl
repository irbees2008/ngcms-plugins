<script>
	function xmenu_click(id) {
for (var i = 0; i < 10; i++) {
var elem = document.getElementById('go_' + i);
var menu = document.getElementById('menu_' + i);

if (elem) 
elem.className = (i == id) ? 'active' : 'passive';

if (menu) 
menu.style.display = (i == id) ? 'block' : 'none';

}

// Сохраняем выбранную вкладку в cookie (опционально)
document.cookie = "xmenu_active=" + id + "; path=/";
}
</script>

