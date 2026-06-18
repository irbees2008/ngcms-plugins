<html>
<body>
<div style="font: normal 11px verdana, sans-serif;">
<h3>Уважаемый администратор!</h3>
Был добавлен новый отзыв на товар на сайте: {{ home }}.<br/>
Страница продукта: <a href="{{ vproduct.fulllink }}" target="_blank">{{ vproduct.title }}</a>
<br/>
<h4>Отзыв:</h4>
<table width="100%" cellspacing="1" cellpadding="1">
<tr><td style="background: #E0E0E0;"><b>Имя:</b></td><td style="background: #EFEFEF;">{{ vnames.name }}</td></tr>
<tr><td style="background: #E0E0E0;"><b>E-mail:</b></td><td style="background: #EFEFEF;">{{ vnames.mail }}</td></tr>
<tr><td style="background: #E0E0E0;"><b>Отзыв:</b></td><td style="background: #EFEFEF;">{{ vnames.text }}</td></tr>
</table>
<br/>

---<br/>
С уважением,<br/>
почтовый робот Вашего сайта (работает на базе <b><font color="#90b500">N</font><font color="#5a5047">ext</font> <font color="#90b500">G</font><font color="#5a5047">eneration</font> CMS</b> - http://ngcms.org/)
</div>

</body>
</html>
