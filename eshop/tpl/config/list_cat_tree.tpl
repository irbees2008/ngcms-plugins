<script type="text/javascript">
jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
$(document).ready(function() {
	$('ul#cat-menu ul').each(function(i) { // Check each submenu:
		if ($.cookie('submenuMark-' + i)) {  // If index of submenu is marked in cookies:
			$(this).show().prev().removeClass('collapsed').addClass('expanded'); // Show it (add apropriate classes)
		}else {
			$(this).hide().prev().removeClass('expanded').addClass('collapsed'); // Hide it
		}
		$(this).prev().addClass('collapsible').click(function() { // Attach an event listener
			var this_i = $('ul#cat-menu ul').index($(this).next()); // The index of the submenu of the clicked link
			if ($(this).next().css('display') == 'none') {
				$(this).next().slideDown(200, function () { // Show submenu:
					$(this).prev().removeClass('collapsed').addClass('expanded');
					cookieSet(this_i);
				});
			}else {
				$(this).next().slideUp(200, function () { // Hide submenu:
					$(this).prev().removeClass('expanded').addClass('collapsed');
					cookieDel(this_i);
					$(this).find('ul').each(function() {
						$(this).hide(0, cookieDel($('ul#cat-menu ul').index($(this)))).prev().removeClass('expanded').addClass('collapsed');
					});
				});
			}
		return false; // Prohibit the browser to follow the link address
		});
	});
});
function cookieSet(index) {
	$.cookie('submenuMark-' + index, 'opened', {expires: null, path: '/'}); // Set mark to cookie (submenu is shown):
}
function cookieDel(index) {
	$.cookie('submenuMark-' + index, null, {expires: null, path: '/'}); // Delete mark from cookie (submenu is hidden):
}
</script>
<style>
	.tree_menu span.cat_active {font-weight: bold;text-decoration:none;text-decoration-color:#f0f8ff05;}
	.tree_menu span.cat_active a {text-decoration:none;text-decoration-color:#f0f8ff05;}
	.cat_bar {border: solid #ddd;}
	.cat_img img {width: 40px;height: 40px;border-radius: 50px;}
	ul.sample-menu { padding:0;margin:10px 15px; }
	ul.sample-menu li { padding:2px 0;margin:4px;list-style:none; }
	ul.sample-menu li ul { padding:0;margin:0 0 0 15px; }
	ul#cat-menu span { padding-left:16px; }
	ul#cat-menu span.collapsed { background:url('{{ admin_url }}/plugins/eshop/tpl/img/plus.gif') left 3px no-repeat; }
	ul#cat-menu span.expanded { background:url('{{ admin_url }}/plugins/eshop/tpl/img/minus.gif') left 3px no-repeat; }
	ul li .action-list {margin-left: auto;right: 60px;position: absolute;}
</style>

<form action="{{ php_self }}?mod=extra-config&plugin=eshop&action=list_cat" method="post" name="catz_bar">


{% if (entries) %}
	<div class="tree_menu">
		<ul id="cat-menu" class="sample-menu">
			{{ entries }}
		</ul>
	</div>
{% else %}
	<div class="tree_menu">
По вашему запросу ничего не найдено.
	</div>
{% endif %}

			
		<div class="card-footer">
			<div class="row">
				<div class="col-lg-6 mb-2 mb-lg-0"></div>

				<div class="col-lg-6">
					<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=eshop&action=add_cat" style="float:right;" class="btn btn-outline-success">Добавить категорию</a>
				</div>
			</div>
		</div>
	</div>		
</form>