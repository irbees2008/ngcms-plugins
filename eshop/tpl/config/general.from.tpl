<form method="post" action="">
    <tr>
        <td colspan=2>
            <fieldset class="admGroup">
                <legend class="title">Настройки</legend>
                <table width="100%" border="0" class="content table table-sm">
                    <tr>
                        <td class="contentEntry1" valign=top>Выберите каталог из которого плагин будет брать шаблоны для отображения<br/>
						<span class="text-muted text-size-small hidden-xs"><b>Шаблон сайта</b> - плагин будет пытаться взять шаблоны из общего шаблона сайта; в случае недоступности - шаблоны будут взяты из собственного каталога плагина<br /><b>Плагин</b> - шаблоны будут браться из собственного каталога плагина</span>
						</td>
                        <td class="contentEntry2" valign=top>{{ entries.localsource }}</td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Выберите активный скин<br/>
						<span class="text-muted text-size-small hidden-xs">Выбранный скин будет использоваться при установке <b>Плагин</b> в предыдущем поле</span>
						</td>
                        <td class="contentEntry2" valign=top>{{ entries.localskin }}</td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Выберать версию MySQL<br/>
						<span class="text-muted text-size-small hidden-xs"><b>Нет</b> - будет использоваться версия 5.х<br/><b>Да</b> - будет использоваться 8.х</span>
						</td>
                        <td class="contentEntry2" valign=top>{{ entries.mysql }}</td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Отображения списка категорий в админки<br/>
						<span class="text-muted text-size-small hidden-xs"><b>Нет</b> - будет использоваться вывод таблицей<br/><b>Да</b> - будет использоваться деревовидный вывод категорий (с запоминанем последнего действия)</span>
						</td>
                        <td class="contentEntry2" valign=top>{{ entries.cat_tree }}</td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Количество продуктов на странице<br/></td>
                        <td class="contentEntry2" valign=top><input name="count" type="text"
                                                                    title="Количество продуктов на странице" size="4"
                                                                    value="{{ entries.count }}"/></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Количество продуктов на странице поиска<br/></td>
                        <td class="contentEntry2" valign=top><input name="count_search" type="text"
                                                                    title="Количество продуктов на странице поиска"
                                                                    size="4" value="{{ entries.count_search }}"/></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Количество продуктов на странице c акциями<br/></td>
                        <td class="contentEntry2" valign=top><input name="count_stocks" type="text"
                                                                    title="Количество продуктов на странице c акциями"
                                                                    size="4" value="{{ entries.count_stocks }}"/></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Учёт просмотра объявлений?<br/></td>
                        <td class="contentEntry2" valign=top><select
                                    name="views_count" class="custom-select">{{ entries.views_count }}</select></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Двухсторонние связанные товары?<br/></td>
                        <td class="contentEntry2" valign=top><select
                                    name="bidirect_linked_products" class="custom-select">{{ entries.bidirect_linked_products }}</select></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Премодерация отзывов?<br/></td>
                        <td class="contentEntry2" valign=top><select
                                    name="approve_comments" class="custom-select">>{{ entries.approve_comments }}</select></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Порядок сортировки отзывов?<br/></td>
                        <td class="contentEntry2" valign=top><select
                                    name="sort_comments" class="custom-select">{{ entries.sort_comments }}</select></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Добавлять страницы категорий и продукции в GSMG?<br/></td>
                        <td class="contentEntry2" valign=top><select
                                    name="integrate_gsmg" class="custom-select">{{ entries.integrate_gsmg }}</select></td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>

    <tr>
        <td colspan=2>
            <fieldset class="admGroup">
                <legend class="title">Изображения товаров</legend>
                <table width="100%" border="0" class="content">
                    <tr>
                        <td class="contentEntry1" valign=top>Ширина уменьшенной копии<br/></td>
                        <td class="contentEntry2" valign=top><input name="width_thumb" type="text"
                                                                    title="Ширина уменьшенной копии" size="20"
                                                                    value="{{ entries.width_thumb }}"/></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Разрешенные расширения для изображений<br/>
                            <small>Формат записи <b>jpg, jpeg, gif, png</b></small>
                        </td>
                        <td class="contentEntry2" valign=top><input name="ext_image" type="text"
                                                                    title="Разрешенные разширения для изображений"
                                                                    size="50" value="{{ entries.ext_image }}"/></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Ширина при загрузке<br/></td>
                        <td class="contentEntry2" valign=top><input name="pre_width" type="text"
                                                                    title="Ширина при загрузке" size="20"
                                                                    value="{{ entries.pre_width }}"/></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Качество при загрузке<br/></td>
                        <td class="contentEntry2" valign=top><input name="pre_quality" type="text"
                                                                    title="Качество при загрузке" size="20"
                                                                    value="{{ entries.pre_quality }}"/></td>
                    </tr>
                    <!--
<tr>
<td class="contentEntry1" valign=top>Максимальный размер загружаемого изображения<br /><small>Размер в мегабайтах</small></td>
<td class="contentEntry2" valign=top><input name="max_image_size" type="text" title="Максимальный размер загружаемого изображения" size="20" value="{{ entries.max_image_size }}" /></td>
</tr>
<tr>
<td class="contentEntry1" valign=top>Максимальный ширина загружаемого изображения<br /><small>Указывается в пикселях</small></td>
<td class="contentEntry2" valign=top><input name="width" type="text" title="Максимальный ширина загружаемого изображения" size="20" value="{{ entries.width }}" /></td>
</tr>
<tr>
<td class="contentEntry1" valign=top>Максимальный высота загружаемого изображения<br /><small>Указывается в пикселях</small></td>
<td class="contentEntry2" valign=top><input name="height" type="text" title="Максимальный высота загружаемого изображения" size="20" value="{{ entries.height }}" /></td>
</tr>
-->
                </table>
            </fieldset>
        </td>
    </tr>

    <tr>
        <td colspan=2>
            <fieldset class="admGroup">
                <legend class="title">Изображения категорий</legend>
                <table width="100%" border="0" class="content">
                    <tr>
                        <td class="contentEntry1" valign=top>Ширина уменьшенной копии<br/></td>
                        <td class="contentEntry2" valign=top><input name="catz_width_thumb" type="text"
                                                                    title="Ширина уменьшенной копии" size="20"
                                                                    value="{{ entries.catz_width_thumb }}"/></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Разрешенные расширения для изображений<br/>
                            <small>Формат записи <b>jpg, jpeg, gif, png</b></small>
                        </td>
                        <td class="contentEntry2" valign=top><input name="catz_ext_image" type="text"
                                                                    title="Разрешенные разширения для изображений"
                                                                    size="50" value="{{ entries.catz_ext_image }}"/>
                        </td>
                    </tr>
                    <!--
<tr>
<td class="contentEntry1" valign=top>Максимальный размер загружаемого изображения<br /><small>Размер в мегабайтах</small></td>
<td class="contentEntry2" valign=top><input name="catz_max_image_size" type="text" title="Максимальный размер загружаемого изображения" size="20" value="{{ entries.catz_max_image_size }}" /></td>
</tr>
<tr>
<td class="contentEntry1" valign=top>Максимальный ширина загружаемого изображения<br /><small>Указывается в пикселях</small></td>
<td class="contentEntry2" valign=top><input name="catz_width" type="text" title="Максимальный ширина загружаемого изображения" size="20" value="{{ entries.catz_width }}" /></td>
</tr>
<tr>
<td class="contentEntry1" valign=top>Максимальный высота загружаемого изображения<br /><small>Указывается в пикселях</small></td>
<td class="contentEntry2" valign=top><input name="catz_height" type="text" title="Максимальный высота загружаемого изображения" size="20" value="{{ entries.catz_height }}" /></td>
</tr>
-->
                </table>
            </fieldset>
        </td>
    </tr>

    <tr>
        <td colspan=2>
            <fieldset class="admGroup">
                <legend class="title">Оповещения</legend>
                <table width="100%" border="0" class="content">
                    <tr>
                        <td class="contentEntry1" valign=top>Оповещение о заказах<br/></td>
                        <td class="contentEntry2" valign=top><input name="email_notify_orders" type="text"
                                                                    title="Оповещение о заказах" size="100"
                                                                    value="{{ entries.email_notify_orders }}"/></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Оповещение о комментариях<br/></td>
                        <td class="contentEntry2" valign=top><input name="email_notify_comments" type="text"
                                                                    title="Оповещение о комментариях" size="100"
                                                                    value="{{ entries.email_notify_comments }}"/></td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Обратный адрес оповещений<br/></td>
                        <td class="contentEntry2" valign=top><input name="email_notify_back" type="text"
                                                                    title="Обратный адрес оповещений" size="100"
                                                                    value="{{ entries.email_notify_back }}"/></td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>

    <tr>
        <td colspan=2>
            <fieldset class="admGroup">
                <legend class="title">Описания</legend>
                <table width="100%" border="0" class="content">
                    <tr>
                        <td class="contentEntry1" valign=top>Описание доставки<br/></td>
                        <td class="contentEntry2" valign=top><textarea rows="10" cols="45"
                                                                       name="description_delivery">{{ entries.description_delivery }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Описание покупки<br/></td>
                        <td class="contentEntry2" valign=top><textarea rows="10" cols="45"
                                                                       name="description_order">{{ entries.description_order }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="contentEntry1" valign=top>Телефоны магазина<br/></td>
                        <td class="contentEntry2" valign=top><textarea rows="10" cols="45"
                                                                       name="description_phones">{{ entries.description_phones }}</textarea>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>

			<div class="card-footer">
				<div class="text-center">
					<button type="submit" name="submit" class="btn btn-outline-warning">Сохранить..</button>
				</div>
			</div>
	
</form>