<html>
<body>
<div style="font: normal 11px verdana, sans-serif;">
<h3>Уважаемый администратор!</h3>
Был сделан заказ на сайте: {{ home }}.<br/>
Тип: {% if (vnames.type == 1) %}Обычный{% elseif (vnames.type == 2) %}Купить в один клик{% elseif (vnames.type == 3) %}Узнать о наличии{% endif %}
<br/>
<h4>Параметры формы:</h4>
<table width="100%" cellspacing="1" cellpadding="1">
<tr><td style="background: #E0E0E0;"><b>Имя:</b></td><td style="background: #EFEFEF;">{{ vnames.name }}</td></tr>
<tr><td style="background: #E0E0E0;"><b>E-mail:</b></td><td style="background: #EFEFEF;">{{ vnames.email }}</td></tr>
<tr><td style="background: #E0E0E0;"><b>Телефон:</b></td><td style="background: #EFEFEF;">{{ vnames.phone }}</td></tr>
<tr><td style="background: #E0E0E0;"><b>Адрес доставки:</b></td><td style="background: #EFEFEF;">{{ vnames.address }}</td></tr>
<tr><td style="background: #E0E0E0;"><b>Комментарий:</b></td><td style="background: #EFEFEF;">{{ vnames.comment }}</td></tr>
<tr><td style="background: #E0E0E0;"><b>Дата заказа:</b></td><td style="background: #EFEFEF;">{{ vnames.date|date('d.m.Y H:i') }}</td></tr>
</table>
<br/>

{% if (recs > 0) %}
<h3 style="color: #000; font: bold 14px/50px 'Roboto', sans-serif; text-transform: uppercase;">
Корзина заказа:
</h3>

<table cellpadding="1" cellspacing="1" width="100%">
    <thead>
        <tr valign="top">
            <td style="background: #000000;color: #FFF;font: bold 12px/30px 'Roboto', sans-serif;text-align: center;text-transform: uppercase;" width="15%">
                Фото
            </td>
            <td style="border-spacing: 0px;border: 0px;background: #000000; color: #FFF; font: bold 12px/30px 'Roboto', sans-serif; text-align: center; text-transform: uppercase;" width="40%">
                Наименование
            </td>
            <td style="background: #000000; color: #fff; font: bold 12px/30px 'Roboto', sans-serif; text-align: center; text-transform: uppercase;" width="20%">
                Стоимость
            </td>
            <td style="font-weight:bold;background: #080808; color: #fff; font: bold 12px/30px 'Roboto', sans-serif; text-align: center; text-transform: uppercase;" width="5%">
                Количество
            </td>
            <td style="font-weight:bold;background: #080808; color: #fff; font: bold 12px/30px 'Roboto', sans-serif; text-align: center; text-transform: uppercase;" width="5%">
                Сумма
            </td>
        </tr>
    </thead>
    <tbody>
        {% for entry in entries %}
        <tr style="background:#FFFFFF;" valign="top">
            <td style="background: #E6E6E6; color: #000; font: bold 14px/30px 'Roboto', sans-serif; text-align: center; text-transform: uppercase;">
                {% if (entry.image_filepath) %}<img src='{{home}}/uploads/eshop/products/{{entry.id}}/thumb/{{entry.xfields.item.image_filepath}}' height="100px" >{% else %}<img src='{{home}}/engine/plugins/eshop/tpl/img/img_none.jpg' height="100px" >{% endif %}
            </td>
            <td style="background: #E6E6E6; color: #000; font: bold 12px/30px 'Roboto', sans-serif; text-align: center; text-transform: uppercase;">
                <a href="{{ entry.news_url }}" target="_blank">{{ entry.title }}</a>
            </td>
            <td style="background: #E6E6E6; color: #000; font: bold 14px/30px 'Roboto', sans-serif; text-align: center; text-transform: uppercase;">
                {{ entry.price }}
            </td>
            <td style="background: #E6E6E6; color: #000; font: bold 14px/30px 'Roboto', sans-serif; text-align: center; text-transform: uppercase;">
                {{ entry.count }}
            </td>
            <td style="background: #E6E6E6; color: #000; font: bold 14px/30px 'Roboto', sans-serif; text-align: center; text-transform: uppercase;">
                {{ entry.sum }}
            </td>
        </tr>
        {% endfor %}
    </tbody>
    <tfoot>
        <tr style="background:#FFFFFF;" valign="top">
            <td colspan="4" style="border-spacing: 0px;border: 0px;background: #E6E6E6;color: #000;font: bold 14px/35px 'Roboto', sans-serif;text-align: right;text-transform: uppercase;">
                Итого к оплате :
            </td>
            <td align="right" style="border-spacing: 0px;background: #000;color: #FFF;font: bold 20px/30px 'Roboto', sans-serif;text-align: center;text-transform: uppercase;border: 3px solid #E6E6E6;">
                {{ total }}
            </td>
        </tr>
    </tfoot>
</table>
{% else %}
Корзина пуста!
{% endif %}

<br/>
<br/>
---<br/>
С уважением,<br/>
почтовый робот Вашего сайта (работает на базе <b><font color="#90b500">N</font><font color="#5a5047">ext</font> <font color="#90b500">G</font><font color="#5a5047">eneration</font> CMS</b> - http://ngcms.org/)
</div>

</body>
</html>
