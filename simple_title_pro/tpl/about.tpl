<script type="text/javascript">
	function ChangeOption(selectedOption) {
		document.getElementById('about').style.display = (selectedOption == 'about') ? "block" : "none";
		document.getElementById('author').style.display = (selectedOption == 'author') ? "block" : "none";
		document.getElementById('acknowledgments').style.display = (selectedOption == 'acknowledgments') ? "block" : "none";
		document.getElementById('support').style.display = (selectedOption == 'support') ? "block" : "none";
	}
</script>
<input type="button" onmousedown="javascript:ChangeOption('about')" value="{{ lang['about.tab_about'] }}" class="button"/>
<input type="button" onmousedown="javascript:ChangeOption('author')" value="{{ lang['about.tab_authors'] }}" class="button"/>
<input type="button" onmousedown="javascript:ChangeOption('acknowledgments')" value="{{ lang['about.tab_acknowledgments'] }}" class="button"/>
<input type="button" onmousedown="javascript:ChangeOption('support')" value="{{ lang['about.tab_support'] }}" class="button"/>
<fieldset id="author" style="display: none;" class="admGroup">
	<legend class="title">{{ lang['about.tab_authors'] }}</legend>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<dl>
			<dt>
			<center><strong>Nail' Davydov</strong></center>
			</dt>
			<dt>
			<center><strong><a href="http://rozard.net" target="_blank">http://rozard.net</a></strong></center>
			</dt>
			<dt>
			<center><strong><a href="http://rozard.ngdemo.ru/" target="_blank">http://rozard.ngdemo.ru/</a></strong>
			</center>
			</dt><br/>
			<dt>
			<center>© 2011-2012 Nail' Davydov</center>
			</dt>ngcms.org
		</dl>ngcms.org
	</table>
</fieldset>
<fieldset id="about" class="admGroup">
	<legend class="title">{{ lang['about.tab_about'] }}</legend>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<dl>
			<dt>
			<center><strong>{{ lang['about.title'] }}</strong></center>
			</dt><br/>
			<dt>
			<center><a href="/engine/admin.php?mod=extra-config&plugin=simple_title_pro&action=license" target="_blank"><b>{{ lang['about.license'] }}</b></a></center>
			</dt><br/>
			<dt>
			<center>© 2011-2012 Nail' Davydov</center>
			</dt>
		</dl>
	</table>
</fieldset>
<fieldset id="acknowledgments" style="display: none;" class="admGroup">
	<legend class="title">{{ lang['about.tab_acknowledgments'] }}</legend>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<dl>
			<dt><strong>{{ lang['about.testers'] }}:</strong></dt>
			<dd>Александр -(<a href="http://ngcms.org/forum/profile.php?id=435">Север</a>)</dd>
			<dt><strong>{{ lang['about.testers'] }}:</strong></dt>
			<dd>- - (<a href="http://ngcms.org/forum/profile.php?id=65">tayzer</a>)</dd>
			<dt>
			<center>© 2011-2012 Nail' Davydov</center>
			</dt>
		</dl>
	</table>
</fieldset>
<fieldset id="support" style="display: none;" class="admGroup">
	<legend class="title">{{ lang['about.tab_support'] }}</legend>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<dl>
			<dt><strong>{{ lang['about.support_text'] }}
					<a href="http://ngcms.org/forum/viewtopic.php?id=2055" target="_blank"><b>simple_title_pro</b></a></strong>
			</dt>
			<dt>
			<center>© 2011-2012 Nail' Davydov</center>
			</dt>
		</dl>
	</table>
</fieldset>
