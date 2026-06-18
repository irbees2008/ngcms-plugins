<script src="{{ tpl_url }}/js/cusel-min-2.5.js"></script>
<script src="{{ tpl_url }}/js/_product.js"></script>

<div class="frame-inside page-cart pageCart">
    <div class="container">
        <div class="js-empty empty ">
            <div class="f-s_0 title-cart without-crumbs">
                <div class="frame-title">
                    <h1 class="title">Оформление заказа</h1>
                </div>
            </div>
            <div class="msg layout-highlight layout-highlight-msg">
                <div class="info">
                    <span class="icon_info"></span>
                    <span class="text-el">Корзина пуста</span>
                </div>
            </div>
        </div>
                <div class="js-no-empty no-empty">

            <form method="post" action="{{basket_link}}" class="clearfix">
                <div class="left-cart">
                    
                    <div class="f-s_0 title-cart without-crumbs clearfix">
                        <div class="frame-title f_l">
                            <h1 class="title">Оформление заказа</h1>
                        </div>
                        <!--
                        <span class="old-buyer f_r">
                            <span class="s-t">Если вы зарегистрированы — </span>
                            <button type="button" data-trigger="#loginButton">
                                <span class="d_l text-el">авторизуйтесь</span>
                            </button>
                        </span>
                        -->
                                            </div>
                    <div class="horizontal-form order-form big-title">
                        <div class="inside-padd">
                            {% if (formEntry.error) %}
                            <div class="groups-form">
                                <div class="msg">
                                    <div class="error">
                                        <span class="icon_error"></span>
                                        <span class="text-el">{% for error in formEntry.error %}<p>{{ error }}</p>{% endfor %}</span>
                                    </div>
                                </div>
                            </div>
                            {% endif %}

                            <div class="groups-form">
                                <label>
                                    <span class="title">
                                        <span class="p_r">
                                            Имя                                                                                         <span class="must">*</span>
                                                                                    </span>
                                    </span>
                                    <span class="frame-form-field">
                                        <input type="text" value="{% if (formEntry.error) %}{{ formEntry.name }}{% else %}{{ global.user.xfields_name }}{% endif %}" name="userInfo[fullName]">
                                    </span>
                                </label>
                                <label>
                                    <span class="title">
                                        <span class="p_r">
                                            Email                                                                                        <span class="must">*</span>
                                                                                    </span>
                                    </span>
                                    <span class="frame-form-field">
                                        <input type="text" value="{% if (formEntry.error) %}{{ formEntry.email }}{% else %}{{ global.user.mail }}{% endif %}" name="userInfo[email]">
                                    </span>
                                </label>
                                <label>
                                 <span class="title">
                                    <span class="p_r">
                                       Телефон <span class="must">*</span>
                                                                          </span>
                               </span>
                               <span class="frame-form-field">
                                <input type="text" name="userInfo[phone]" id="cart-phone" value="{% if (formEntry.error) %}{{ formEntry.phone }}{% else %}{{ global.user.xfields_phone }}{% endif %}" class="">
                            </span>
                        </label>

                    </div>
                    <div class="groups-form">

                        <div class="frame-label">
                            <span class="title">Способ оплаты</span>
                            <div class="frame-form-field check-variant-delivery">

                                <div class="check-variant-product">
                                    <div class="lineForm">
                                        <select name="payment_type" id="payment_type">
                                            {% for variant in entriesPaymentTypes %}
                                                <option value="{{ variant.id }}">
                                                    {{ variant.name }}
                                                </option>
                                            {% endfor %}
                                        </select>
                                    </div>
                                </div>
                                <!--
                                <div class="frame-radio">
                                    <div class="frame-label">
                                        <span class="niceRadio b_n">
                                            <input type="radio"
                                            name="paymentMethodId"
                                            value="5"
                                            />
                                        </span>
                                        <div class="name-count">
                                            <span class="text-el">Наложенным платежем</span>
                                         </div>
                                    </div>

                                    <div class="frame-label">
                                        <span class="niceRadio b_n">
                                            <input type="radio"
                                            name="paymentMethodId"
                                            value="5"
                                            />
                                        </span>
                                        <div class="name-count">
                                            <span class="text-el">Visa/Mastercard</span>
                                         </div>
                                    </div>
                                </div>
                                -->
                            </div>
                        </div>

                        <div class="frame-label" id="frameDelivery">
                            <span class="title">Способ доставки</span>
                            <div class="frame-form-field check-variant-delivery">

                                <div class="check-variant-product">
                                    <div class="lineForm">
                                        <select name="delivery_type" id="delivery_type">
                                            {% for variant in entriesDeliveryTypes %}
                                                <option value="{{ variant.id }}">
                                                    {{ variant.name }}
                                                </option>
                                            {% endfor %}
                                        </select>
                                    </div>
                                </div>
                                <!--
                                <div class="frame-radio">
                                    <div class="frame-label">
                                        <span class="niceRadio b_n">
                                            <input type="radio"
                                            name="deliveryMethodId"
                                            value="5"
                                            />
                                        </span>
                                        <div class="name-count">
                                            <span class="text-el">Адресная доставка курьером</span>
                                                                            <span class="icon_ask" data-rel="tooltip" data-title="<p>Сроки доставки: 1-2 дня</p>"></span>
                                                                        </div>
                                        <div class="help-block">
                                             <div>Бесплатно при заказе от: 2.000 $</div>
                                                                        </div>
                                    </div>

                                    <div class="frame-label">
                                        <span class="niceRadio b_n">
                                            <input type="radio"
                                            name="deliveryMethodId"
                                            value="6"
                                            />
                                        </span>
                                        <div class="name-count">
                                            <span class="text-el">Доставка экспресс службой</span>
                                                                            <span class="icon_ask" data-rel="tooltip" data-title="<p>Сроки доставки 2-3 дня</p>"></span>
                                                                        </div>
                                        <div class="help-block">
                                                                        согласно тарифам перевозчиков                                                            </div>
                                    </div>
                                </div>
                                -->
                            </div>
                        </div>

                       <!-- Start. Delivery  address block and comment-->
                       <label>
                        <span class="title">
                            <span class="p_r">
                                Адрес доставки
                            </span>
                        </span>
                        <span class="frame-form-field">
                            <input name="userInfo[deliverTo]" type="text" value="{% if (formEntry.error) %}{{ formEntry.address }}{% else %}{{ global.user.xfields_address }}{% endif %}">
                        </span>
                    </label>

                    <!-- End. Delivery  address block and comment-->


                </div>


        <div class="groups-form">

            <!--
            <div class="frame-payment p_r f-s_0" style="margin-bottom: 0;">
                <div id="framePaymentMethod">
                    <div class="frame-label">
                        <span class="title">Способ оплаты</span>
                        <div class="frame-form-field check-variant-delivery">

                            <div class="frame-radio">
                                <div class="frame-label">
                                    <span class="niceRadio b_n">
                                        <input type="radio"
                                        name="paymentMethodId"
                                        value="5"
                                        />
                                    </span>
                                    <div class="name-count">
                                        <span class="text-el">Наложенным платежем</span>
                                     </div>
                                </div>

                                <div class="frame-label">
                                    <span class="niceRadio b_n">
                                        <input type="radio"
                                        name="paymentMethodId"
                                        value="5"
                                        />
                                    </span>
                                    <div class="name-count">
                                        <span class="text-el">Visa/Mastercard</span>
                                     </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="preloader d_n_"></div>
            </div>
             -->

            <div class="frame-label">
                <div class="frame-form-field">
                    <button type="button" class="m-b_5 isDrop" data-drop=".hidden-comment" data-place="inherit" data-overlay-opacity="0"><span class="d_l_1">Добавить комментарий к заказу</span></button>
                    <div class="hidden-comment drop" data-rel="" data-elrun=".hidden-comment" style="display: none;">
                        <textarea name="userInfo[commentText]">{{ formEntry.vnames.comment }}</textarea>
                    </div>
                </div>
            </div>

            <div class="frame-label">
                <span class="title">&nbsp;</span>
                <span class="frame-form-field">
                    <div class="btn-buy">
                        <button type="submit" id="submitOrder">
                            <span class="text-el">Оформить заказ</span>
                        </button>
                    </div>
                </span>
            </div>
        </div>
    </div>
</div>

</div>
<div class="right-cart">
    <div class="frame-bask frame-bask-order">
        <div class="title-default">
            <div class="frame-title d_b clearfix">
                <div class="title f_l">Мой заказ</div>
                <!--
                <div class="f_r">
                    <button type="button" class="d_l_1 editCart">Редактировать</button>
                </div>
                -->
            </div>
        </div>

        <div id="orderDetails" class="p_r">

            {% if (recs > 0) %}
            <table class="table-order">
                

                <tbody>
                    {% for entry in entries %}
                    <tr class="items items-bask cart-product items-product">
                        <td class="frame-remove-bask-btn">
                            <button type="button" class="icon_times_cart cart_quantity_delete" data-id="{{ entry.id }}" data-linked_ds="{{ entry.linked_ds }}" data-linked_id="{{ entry.linked_id }}"></button>
                        </td>
                        
                        <td class="frame-items">
                            <a class="frame-photo-title" href="{{entry.xfields.item.view_link}}"><span class="photo-block"><span class="helper"></span> <img alt="" src="{% if (entry.xfields.item.image_filepath) %}{{home}}/uploads/eshop/products/{{entry.xfields.item.id}}/thumb/{{entry.xfields.item.image_filepath}}{% else %}{{home}}/engine/plugins/eshop/tpl/img/img_none.jpg{% endif %}"></span> <span class="title">{{ entry.title }}</span></a>
                            <div class="description">{{ entry.xfields.item.v_name }}</div>
                        </td>
                        <td>
                            <div class="frame-frame-count">
                                <div class="frame-count">
                                    <span class="plus-minus">
                                        <div class="input-group quantity-control" unselectable="on" style="-webkit-user-select: none;">
                                            <a rel="nofollow" class="input-group-addon cart_quantity_down" title="Уменьшить">-</a>
                                            <input size="2" type="text" autocomplete="off" class="cart_quantity_input" name="count_{{ entry.id }}" value="{{ entry.count }}" data-id="{{ entry.id }}" data-linked_ds="{{ entry.linked_ds }}" data-linked_id="{{ entry.linked_id }}" data-price="{{ (entry.price) }}">     
                                            <a rel="nofollow" class="input-group-addon cart_quantity_up" title="Добавить">+</a>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </td>
                        <!--
                        <td>
                            <div class="frame-frame-count">
                                <div class="frame-count">
                                    <span class="plus-minus">{{ entry.count }}</span>
                                    <span class="text-el">шт.</span>
                                </div>
                            </div>
                        </td>
                        -->
                        <td class="frame-cur-sum-price frame-price">
                            <div class="frame-prices f-s_0">
                                <span class="current-prices f-s_0">
                                    <span class="price-new">
                                        <span>
                                            <span class="price">{{ (entry.price* system_flags.eshop.currency[0].rate_from / system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</span> {{ system_flags.eshop.current_currency.sign }}
                                        </span>
                                    </span>
                                </span>
                            </div>
                        </td>
                        <td class="frame-cur-sum-price frame-sum">
                            <div class="frame-prices f-s_0">
                                <span class="current-prices f-s_0">
                                    <span class="price-new">
                                        <span>
                                            <span class="price">{{ (entry.sum * system_flags.eshop.currency[0].rate_from / system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</span> {{ system_flags.eshop.current_currency.sign }}
                                        </span>
                                    </span>
                                </span>
                            </div>
                        </td>
                        
                        <!--
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
                <!-- 
                <tfoot class="gen-info-price">
                    <tr>
                        <td colspan="3">
                            <span class="s-t f_l">Cтоимость товаров:</span>
                            <div class="f_r">
                                <span class="price f-w_b">{{ (total * system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</span> {{ system_flags.eshop.current_currency.sign }}
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
                -->
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

</form>
</div>
</div>
</div>

<script>
$(document).ready(function(){

    function update_updown_total(click_this, count) {
        var price = parseFloat(click_this.attr("data-price").trim());
        
        if(isNaN(parseInt(count)) || parseInt(count) <= 0) {
            count = 1;
        }

        var id = click_this.attr("data-id");
        var linked_ds = click_this.attr("data-linked_ds");
        var linked_id = click_this.attr("data-linked_id");
        
        rpcEshopRequest('eshop_ebasket_manage', {'action': 'update_count',  'id':id, 'linked_ds':linked_ds, 'linked_id':linked_id,'count':count }, function (resTX) {
            click_this.val(count);
            
            var total = parseFloat(count * price).toFixed(2);
            click_this.parent().parent().parent().parent().parent().parent().find("td[class='frame-cur-sum-price frame-sum']").find("span[class='price']").text(total);

            var sum = 0;
            $("td[class='frame-cur-sum-price frame-sum'").each(function() {
                sum = sum + parseFloat($(this).find("span[class='price']").text());
            });
            $("#finalAmount").text(sum.toFixed(2));
        });

    }
    
    $('.cart_quantity_up').on('click', function(e){
        var click_this = $(this).parent().find('input');
        var count = parseInt(click_this.val()) + 1;
        update_updown_total(click_this, count);
    });
    
    $('.cart_quantity_down').on('click', function(e){
        var click_this = $(this).parent().find('input');
        var count = parseInt(click_this.val()) - 1;
        update_updown_total(click_this, count);
    });
    
    $(document).on('change', '.cart_quantity_input',  function(e) {
        var count = parseInt($(this).val());
        update_updown_total($(this), count);
    });
    
    $('.cart_quantity_delete').on('click', function(e){
        var id = $(this).attr("data-id");
        var linked_ds = $(this).attr("data-linked_ds");
        var linked_id = $(this).attr("data-linked_id");
        
        rpcEshopRequest('eshop_ebasket_manage', {'action': 'delete', 'id':id, 'linked_ds':linked_ds, 'linked_id':linked_id }, function (resTX) {
            location.reload();
        });

    });
    

});
</script>

<style>

.quantity-control .cart_quantity_up, .quantity-control .cart_quantity_down {
    text-decoration: none;
    background: none repeat scroll 0 0 #f5f5f5;
    border: 0 none;
    border-radius: 0 !important;
    cursor: pointer;
    font-size: 15px;
    height: 30px !important;
    line-height: 43px;
    overflow: hidden;
    padding: 5px 10px;
    text-align: center;
    vertical-align: top;
    width: 35px !important;
    color: #A6A6A6;
}

.cart_quantity_input {
    text-decoration: none;
    background: none repeat scroll 0 0 #f5f5f5;
    border: 0 none;
    border-radius: 0 !important;
    cursor: pointer;
    font-size: 15px;
    height: 30px !important;
    line-height: 43px;
    overflow: hidden;
    padding: 0 10px;
    text-align: center;
    vertical-align: top;
    width: 30px !important;
    margin-top: 6px !important;
    color: #A6A6A6;
    height: 27px;
    line-height: 27px;
    padding: 0;
    text-align: center;
    width: 10px;
    border-radius: 0px;
}

.quantity-control .cart_quantity_up:hover {
    background: none repeat scroll 0 0 #52A537;
    color: #FFFFFF;
}

.quantity-control .cart_quantity_down:hover {
    background: none repeat scroll 0 0 #E44545;
    color: #FFFFFF;
}

.input-group .quantity-control {
    display: table-cell;
}

</style>
