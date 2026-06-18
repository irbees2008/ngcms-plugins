{% if not (formEntry.error) %}
<div class="frame-inside page-order">
        <div class="container">
                        <div class="f-s_0 title-order-view without-crumbs">
                <div class="frame-title">
                    <h1 class="title">Заказ №:<span class="number-order">{{ formEntry.id }}</span></h1>
                </div>
            </div>

            <!-- Start. Displays a information block about Order -->
        <div class="left-order">
                <!--                Start. User info block-->
                <table class="table-info-order">
                    <colgroup>
                    <col width="150">
                </colgroup>
                <tbody><tr>
                    <th>Имя:</th>
                    <td>{{ formEntry.name }}</td>
                </tr>
                <tr>
                    <th>E-mail:</th>
                    <td>{{ formEntry.email }}</td>
                </tr>
                                <tr>
                    <th>Телефон:</th>
                    <td>{{ formEntry.phone }}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <hr>
                    </td>
                </tr>
                <!-- Start. Delivery Method name -->
                <tr>
                    <th>Способ оплаты:</th>
                    <td>
                        {{ formEntry.payment_type.name }}
                    </td>
                </tr>

                <tr>
                    <th>Способ доставки:</th>
                    <td>
                        {{ formEntry.delivery_type.name }}
                    </td>
                </tr>


                <tr>
                    <th>Адрес доставки:</th>
                    <td>
                        {{ formEntry.address }}
                    </td>
                </tr>

                <tr>
                    <th>Комментарий:</th>
                    <td>{{ formEntry.comment }}</td>
                </tr>

                                <!--                End. User info block-->
                <tr>
                    <td colspan="2">
                        <hr>
                    </td>
                </tr>
                <tr>
                    <th>Дата заказа:</th>
                    <td>{{ formEntry.dt|date('d.m.Y H:i') }}</td>
                </tr>

                <!-- Start. Render payment button and payment description -->
                <!--
                <tr>
                    <th>Способ оплаты:</th>
                    <td>
                                                Оплата через Банк                                            </td>
                </tr>
                -->
                                <!--                Start. Order status-->
                <tr>
                    <th>Статус оплаты:</th>
                    <td>
                        <div class="d_i-b v-a_m" style="margin-right:10px;">
                            <span class="status-pay not-paid">{% if (formEntry.paid == 0) %}Не оплачен{% else %}Оплачен{% endif %}</span>
                        </div>
<!---->
                    <div class="d_i-b v-a_m">
                        <form method="get" action="{{ payment.link }}" target="_blank">
                            <input type="hidden" value="{{ formEntry.id }}" name="order_id">
                            <input type="hidden" value="{{ formEntry.uniqid }}" name="order_uniqid">
                            <input type="hidden" value="{{ payment.systems[2].name }}" name="payment_id">
                            <div class="btn-cart btn-cart-p">
                                <button type="submit"><span class="text-el">Оплатить</span></button>
                            </div>
                        </form>
                    </div>

<!--
        <div class="d_i-b v-a_m">
            <form method="get" action="" target="_blank">
                <input type="hidden" value="2" name="pm">
                <input type="hidden" value="true" name="getPdf">
                <input type="submit" value="Оплатить">
            </form>
        </div>
-->
                                            </td>
                </tr>
                <!--                End. Order status-->

                <!-- End. Render payment button and payment description -->
            </tbody></table>
        </div>
        <!-- End. Displays a information block about Order -->
        <div class="right-order">

    <div class="frame-bask frame-bask-order">
        <div class="title-default">
            <div class="frame-title d_b clearfix">
                <div class="title f_l">Мой заказ</div>
            </div>
        </div>

        <div id="orderDetails" class="p_r">

            {% if (recs > 0) %}
            <table class="table-order">

                <tbody>
                    {% for entry in entries %}
                    <tr class="items items-bask cart-product items-product">
                        <td class="frame-items">
                            <a class="frame-photo-title" href="{{entry.xfields.item.view_link}}"><span class="photo-block"><span class="helper"></span> <img alt="" src="{% if (entry.xfields.item.image_filepath) %}{{home}}/uploads/eshop/products/{{entry.xfields.item.id}}/thumb/{{entry.xfields.item.image_filepath}}{% else %}{{home}}/engine/plugins/eshop/tpl/img/img_none.jpg{% endif %}"></span> <span class="title">{{ entry.title }}</span></a>
                            <div class="description">{{ entry.xfields.item.v_name }}</div>
                        </td>
                        <td>
                            <div class="frame-frame-count">
                                <div class="frame-count">
                                    <span class="plus-minus">{{ entry.count }}</span>
                                    <span class="text-el">шт.</span>
                                </div>
                            </div>
                        </td>
                        <td class="frame-cur-sum-price">
                            <div class="frame-prices f-s_0">
                                <span class="current-prices f-s_0">
                                    <span class="price-new">
                                        <span>
                                            <span class="price">{{ (entry.price * system_flags.eshop.currency[0].rate_from / system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</span> {{ system_flags.eshop.current_currency.sign }}
                                        </span>
                                    </span>
                                </span>
                            </div>
                        </td>
                        <!--
                        <td>
                            <div class="frame-frame-count">
                                <div class="frame-count">
                                    <span class="plus-minus">
                                        <div class="input-group quantity-control" unselectable="on" style="-webkit-user-select: none;">
                                            <a rel="nofollow" class="input-group-addon cart_quantity_down" title="Уменьшить">-</a>
                                            <input size="2" type="text" autocomplete="off" class="cart_quantity_input" name="count_{{ entry.id }}" value="{{ entry.count }}" data-id="{{ entry.id }}" data-linked_ds="{{ entry.linked_ds }}" data-linked_id="{{ entry.linked_id }}" data-price="{{ entry.price }}"">
                                            <a rel="nofollow" class="input-group-addon cart_quantity_up" title="Добавить">+</a>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="frame-cur-price">
                            <div class="frame-prices f-s_0">
                                <span class="current-prices f-s_0"><span class="price-new"><span><span class="price">{{ entry.price }}</span> $</span></span></span>
                            </div>
                        </td>
                        <td class="frame-cur-sum-price">
                            <div class="frame-prices f-s_0">
                                <span class="current-prices f-s_0"><span class="price-new"><span><span class="price">{{ (entry.price * entry.count)|number_format(2, '.', ' ') }}</span> $</span></span></span>
                            </div>
                        </td>
                        -->
                    </tr>
                    {% endfor %}
                </tbody>

                <tfoot class="gen-info-price">
                    <tr>
                        <td colspan="3">
                            <span class="s-t f_l">Cтоимость товаров:</span>
                            <div class="f_r">
                                <span class="price f-w_b">{{ (total * system_flags.eshop.currency[0].rate_from / system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</span> {{ system_flags.eshop.current_currency.sign }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <span class="s-t f_l">Доставка:</span>
                            <div class="f_r">
                                <span class="text-el s-t">Бесплатно</span>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="gen-sum-order footer-bask">
                <div class="inside-padd clearfix">
                    <span class="title f_l">К оплате:</span>
                    <span class="frame-prices f_r">
                        <span class="current-prices f-s_0">
                            <span class="price-new">
                                <span>

                                    <span id="finalAmount" class="price">{{ (total * system_flags.eshop.currency[0].rate_from / system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</span> {{ system_flags.eshop.current_currency.sign }}

                                </span>
                            </span>
                            <!--
                            <span class="price-add">
                                <span>
                                    (<span id="finalAmountAdd" class="price">5.295</span> €)
                                </span>
                            </span>
                            -->
                        </span>
                    </span>
                </div>
            </div>
            <div class="preloader" style="display: none;"></div>
            {% else %}
            Ваша корзина пуста!
            {% endif %}

        </div>
    </div>


</div>
</div>
</div>

{% else %}
<br/>
<div class="frame-inside">
    <div class="container">
                <div class="right-catalog" style="width:100% !important;margin-left: 0;">
                                        <div class="msg layout-highlight layout-highlight-msg">
                    <div class="info">
                        <span class="icon_info"></span>
                        <span class="text-el">
                        {% for err in formEntry.error %}
                        {{ err }}<br/>
                        {% endfor %}
                        </span>
                    </div>
                </div>
        </div>
    </div>
</div>
{% endif %}
