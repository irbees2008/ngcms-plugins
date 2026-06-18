<div class="forCenter" data-rel="#popupCart" style="left: 0px; width: 100%; position: absolute; height: 100%; z-index: 1105; top: 1px; display: block; display: none;">
<div class="drop-bask drop drop-style center active" id="popupCart" data-rel="" data-elrun="#popupCart" style="z-index: 1105; position: absolute; top: 136px; left: 275px; display: block;">

<div class="frame-bask frameBask p_r">
    <button type="button" class="icon_times_drop" data-closed="closed-js"></button>
            <div class="drop-header">
            <div class="title bask"><span>Товар добавлен к попупкам</span></div>
        </div>
        <div class="drop-content is-empty">
            <div class="inside-padd">
                <div class="msg f-s_0">
                    <div class="success">
                        <span class="icon_info"></span><span class="text-el">Вы добавили товар в корзину</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bask drop-footer">
            <div class="content-frame-foot notCart">
                <div class="clearfix inside-padd">
                    <div class="btn-form f_l" id="basket_back">
                        <button type="button" data-closed="closed-js">
                            <span class="text-el">Вернуться к покупкам</span>
                        </button>
                    </div>
                    <div class="btn-buy f_r">
                        <a href="{{home}}{{basket_link}}">
                            <span class="icon_cart_p"></span>
                            <span class="text-el">Оформить заказ</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="preloader" style="display: none;"></div>
</div>
</div>
</div>
<div class="overlayDrop" data-rel="#popupCart" style="position: absolute; width: 100%; left: 0px; top: 0px; opacity: 0.6; height: 3556px; z-index: 1103; background-color: rgb(0, 0, 0); display: none;"></div>



<div class="forCenter_fastOrder" data-rel=".drop-enter" style="left: 0px; width: 100%; position: absolute; height: 100%; z-index: 1105; top: 1px; display: block; display: none;"><div class="drop-enter drop drop-style center active" id="enter" data-rel="" data-elrun=".drop-enter" style="z-index: 1105; position: absolute; display: block; top: 62px; left: 478px;">
    <button type="button" class="icon_times_drop" data-closed="closed-js"></button>
    <div class="drop-header">
        <div class="title">Купить в один клик</div>
    </div>
    <div class="drop-content" style="overflow: hidden; padding: 0px; height: 210px; width: 395px;"><div class="jspContainer" style="width: 395px; height: 210px;"><div class="jspPane" style="padding: 0px; top: 0px; width: 395px;"><div class="inside-padd">
            <div class="horizontal-form" id="fastorder-frame">
                <label>
                    <span class="title">Имя</span>
                    <span class="frame-form-field">
                        <input type="text" name="name" value="{{ global.user.xfields_name }}">
                        <!--<span class="must">*</span>-->
                    </span>
                </label>
                <label>
                    <span class="title">Телефон</span>
                    <span class="frame-form-field">
                        <input type="text" name="phone" value="{{ global.user.xfields_phone }}">
                        <!--<span class="must">*</span>-->
                    </span>
                </label>
                
                <label>
                    <span class="title">Адрес доставки</span>
                    <span class="frame-form-field">
                        <input type="text" name="address" value="{{ global.user.xfields_address }}">
                        <!--<span class="must">*</span>-->
                    </span>
                </label>

                <div class="frame-label">
                    <span class="title">&nbsp;</span>
                    <div class="frame-form-field">
                        <div class="clearfix">
                            <span class="btn-form f_l" id="send_fastorder">
                                <button type="button" >
                                    <span class="icon_enter_drop"></span>
                                    <span class="text-el">Заказать</span>
                                </button>
                            </span>
                            <!--
                            <div class="f_r neigh-buttonform">
                                <span class="helper"></span>
                                <button type="button" class="d_l_1 isDrop" data-drop=".drop-forgot" data-source="http://fluid.imagecmsdemo.net/auth/forgot_password">Забыли Пароль?</button>
                            </div>
                            -->
                        </div>
                    </div>
                </div>
        </div>
    </div></div></div></div>

{% if (global.flags.isLogged == 0) %}
<div class="drop-footer">
    <div class="help-block">Если вы еще не зарегистрированы:</div>
    <a href="{{home}}/register/">Перейти к регистрации</a>
</div>
{% endif %}

</div></div>

<div class="overlayDrop_fastOrder" data-rel=".drop-enter" style="position: absolute; width: 100%; left: 0px; top: 0px; opacity: 0.6; height: 3556px; z-index: 1103; background-color: rgb(0, 0, 0); display: none;"></div>



<div class="forCenter_fastPrice" data-rel=".drop-enter" style="left: 0px; width: 100%; position: absolute; height: 100%; z-index: 1105; top: 1px; display: block; display: none;"><div class="drop-enter drop drop-style center active" id="enter" data-rel="" data-elrun=".drop-enter" style="z-index: 1105; position: absolute; display: block; top: 62px; left: 478px;">
    <button type="button" class="icon_times_drop" data-closed="closed-js"></button>
    <div class="drop-header">
        <div class="title">Сообщить о появлении</div>
    </div>
    <div class="drop-content" style="overflow: hidden; padding: 0px; height: 210px; width: 395px;"><div class="jspContainer" style="width: 395px; height: 210px;"><div class="jspPane" style="padding: 0px; top: 0px; width: 395px;"><div class="inside-padd">
            <div class="horizontal-form" id="fastprice-frame">
                <label>
                    <span class="title">Имя</span>
                    <span class="frame-form-field">
                        <input type="text" name="name" value="{{ global.user.xfields_name }}">
                        <!--<span class="must">*</span>-->
                    </span>
                </label>
                <label>
                    <span class="title">Телефон</span>
                    <span class="frame-form-field">
                        <input type="text" name="phone" value="{{ global.user.xfields_name }}">
                        <!--<span class="must">*</span>-->
                    </span>
                </label>
                
                <label>
                    <span class="title">Адрес доставки</span>
                    <span class="frame-form-field">
                        <input type="text" name="address" value="{{ global.user.xfields_address }}">
                        <!--<span class="must">*</span>-->
                    </span>
                </label>

                <div class="frame-label">
                    <span class="title">&nbsp;</span>
                    <div class="frame-form-field">
                        <div class="clearfix">
                            <span class="btn-form f_l" id="send_fastprice">
                                <button type="button" >
                                    <span class="icon_enter_drop"></span>
                                    <span class="text-el">Заказать</span>
                                </button>
                            </span>
                            <!--
                            <div class="f_r neigh-buttonform">
                                <span class="helper"></span>
                                <button type="button" class="d_l_1 isDrop" data-drop=".drop-forgot" data-source="http://fluid.imagecmsdemo.net/auth/forgot_password">Забыли Пароль?</button>
                            </div>
                            -->
                        </div>
                    </div>
                </div>
        </div>
    </div></div></div></div>
{% if (global.flags.isLogged == 0) %}
<div class="drop-footer">
    <div class="help-block">Если вы еще не зарегистрированы:</div>
    <a href="{{home}}/register/">Перейти к регистрации</a>
</div>
{% endif %}
</div></div>

<div class="overlayDrop_fastPrice" data-rel=".drop-enter" style="position: absolute; width: 100%; left: 0px; top: 0px; opacity: 0.6; height: 3556px; z-index: 1103; background-color: rgb(0, 0, 0); display: none;"></div>
