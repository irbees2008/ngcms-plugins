{{ entries.error }}
<form method="post" action="">
	<div class="card-body">
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">ID</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="id" value="{{ entries.id }}" disabled="disabled" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Дата</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="dt" value="{{ entries.dt|date('d.m.Y H:i') }}" disabled="disabled" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">IP</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="ip" value="{{ entries.ip }}" disabled="disabled" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Тип</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="type" value="{% if (entries.type == 1) %}Обычный{% elseif (entries.type == 2) %}Купить в один клик{% elseif (entries.type == 3) %}Узнать о наличии{% endif %}" disabled="disabled" class="form-control"/>
			</div>
		</div>	
		{% if (entries.author_id) %}
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Пользователь</label>
			<div class="col-lg-9">
				<a href="{{ entries.profile_link }}">{{ entries.author }}</a>
			</div>
		</div>	
		{% endif %}
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Имя</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="name" value="{{ entries.name }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Телефон</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="phone" value="{{ entries.phone }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Email</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="email" value="{{ entries.email }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Способ оплаты</label>
			<div class="col-lg-9">
                <select name="payment_type_id" class="form-control">
                    {% for v in entries.entriesPaymentTypes %}
                        <option value="{{ v.id }}" {% if (entries.payment_type_id == v.id) %}selected{% endif %}>{{ v.name }}</option>
                    {% endfor %}
                </select>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Способ доставки</label>
			<div class="col-lg-9">
                <select name="delivery_type_id" class="form-control">
                    {% for v in entries.entriesDeliveryTypes %}
                        <option value="{{ v.id }}" {% if (entries.delivery_type_id == v.id) %}selected{% endif %}>{{ v.name }}</option>
                    {% endfor %}
                </select>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Адрес</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="address" value="{{ entries.address }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Комментарий</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="comment" value="{{ entries.comment }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Оплачен?</label>
			<div class="col-lg-9">
                <select name="paid" class="form-control"/>
                    <option value="0" {% if (entries.paid == 0) %}selected="selected"{% endif %}>Нет</option>
                    <option value="1" {% if (entries.paid == 1) %}selected="selected"{% endif %}>Да</option>
                </select>
			</div>
		</div>
		
	</div>
		
	<div class="table-responsive">
		<table class="table table-bordered table-striped">
			<thead class="thead-dark">
                <tr class="contHead" align="left">
                    <td width="5%">ID</td>
                    <td width="15%">Изображение</td>
                    <td width="40%">Название</td>
                    <td width="20%">Вариант</td>
                    <td width="10%">Количество</td>
                    <td width="10%">Текущая цена</td>
                </tr>
            </thead>
            <tbody>
                {% for entry in entries.basket %}
                    <tr align="left">
                        <td width="5%" class="contentEntry1">{{ entry.id }}</td>
                        <td width="15%" class="contentEntry1">
							<a href="{{ entry.xfields.item.view_link }}">
								<img alt="" src="{% if (entry.xfields.item.image_filepath) %}{{ home }}/uploads/eshop/products/{{ entry.xfields.item.id }}/thumb/{{ entry.xfields.item.image_filepath }}{% else %}{{ home }}/engine/plugins/eshop/tpl/img/img_none.jpg{% endif %}" width="100" height="100">
							</a>
                        </td>
                        <td width="40%" class="contentEntry1"><a href="{{ entry.xfields.item.view_link }}">{{ entry.title }}</a></td>
                        <td width="10%" class="contentEntry1">{{ entry.xfields.item.v_name }}</td>
                        <td width="10%" class="contentEntry1">{{ entry.count }} шт.</td>
                        <td width="10%" class="contentEntry1">{{ entry.price }} {{ system_flags.eshop.currency[0].sign }}</td>
                    </tr>
                {% endfor %}
			</tbody>
		</table>
	</div>

		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Cтоимость товаров:</label>
			<div class="col-lg-9">
				{{ entries.basket_total|number_format(2, '.', '') }} {{ system_flags.eshop.currency[0].sign }}
			</div>
		</div>
		
    {% if (entries.purchases) %}
	<div class="table-responsive">
		<table class="table table-bordered table-striped">
			<thead class="thead-dark">
                <tr class="contHead" align="left">
                   <td width="10%">ID оплаты</td>
                   <td width="15%">Дата оплаты</td>
                   <td width="75%">Информация</td>
                </tr>
            </thead>
            <tbody>
            {% for purchase in entries.purchases %}
                <tr align="left">
                   <td width="5%" class="contentEntry1">{{ purchase.id }}</td>
                   <td width="15%" class="contentEntry1">{{ purchase.dt|date('d.m.Y H:i') }}</td>
                   <td width="80%" class="contentEntry1">{{ purchase.info_string }}</td>
                </tr>
            {% endfor %}
			</tbody>
		</table>
	</div>
    {% endif %}

			<div class="card-footer">
				<div class="row">
					<div class="col-lg-6 mb-2 mb-lg-0"></div>
					<div class="col-lg-6">
						<input type="submit" name="submit" value="Сохранить" class="btn btn-outline-success"/>
					</div>
				</div>
			</div>
</form>