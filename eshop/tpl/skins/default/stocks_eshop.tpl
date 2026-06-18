{% if (entries) %}
<div class="frame-inside">
    <div class="container">
                <div class="right-catalog"  style="width:100% !important;margin-left: 0;">
                            <div class="f-s_0 title-category">
                    <div class="frame-title">
                        <h1 class="title"><span class="s-t">Товары по акции</span></h1>
                    </div>
                    <!--<span class="count">(Найдено 18 товаров)</span>-->
                </div>
                <!--
                                        <div class="frame-header-category">
        <div class="header-category f-s_0">
            <div class="inside-padd">

                <div class="frame-sort d_i-b v-a_t">
                    <div class="lineForm">
                        <div class="cusel sort" id="cuselFrame-sort" style="width:176px" tabindex="0"><div class="cuselFrameRight"></div><div class="cuselText">Акции</div><div class="cusel-scroll-wrap" style="display: none; visibility: visible;"><div class="jScrollPaneContainer" style="height: 192px; width: 180px;"><div class="cusel-scroll-pane" id="cusel-scroll-sort" style="overflow: hidden; width: 180px; height: 192px; padding: 0px;">
                                                                                        <span val="action" class="cuselActive">Акции</span>
                                                            <span val="price">От дешевых к дорогим</span>
                                                            <span val="price_desc">От дорогих к дешевым</span>
                                                            <span val="hit">Популярные</span>
                                                            <span val="rating">Рейтинг</span>
                                                            <span val="hot">Новинки</span>
                                                            <span val="views">По количеству просмотров</span>
                                                            <span val="name">По названию (А-Я)</span>
                                                    </div></div></div><input type="hidden" id="sort" name="order" value="action"></div>
                    </div>
                </div>

                <div class="frame-count-onpage d_i-b v-a_t">
                                                                                    <div class="lineForm">

                                                <div class="cusel" id="cuselFrame-sort2" style="width:159px" tabindex="0"><div class="cuselFrameRight"></div><div class="cuselText">12 товаров на странице</div><div class="cusel-scroll-wrap" style="display: none; visibility: visible;"><div class="cusel-scroll-pane" id="cusel-scroll-sort2">
                                                            <span selected="selected" val="12" class="cuselActive">12 товаров на странице</span>
                                                            <span val="24">24 товара на странице</span>
                                                            <span val="48">48 товаров на странице</span>
                                                    </div></div><input type="hidden" id="sort2" name="user_per_page" value="12"></div>
                    </div>
                </div>

                <nav class="frame-catalog-view d_i-b v-a_t">
                    <ul class="tabs tabs-list-table" data-elchange="#items-catalog-main" data-cookie="listtable">
                        <li class="active">
                            <button type="button" data-href="tablemini" data-title="Мини таблица" data-rel="tooltip">
                                <span class="icon_tablemini_cat"></span><span class="text-el">Мини таблица</span>
                            </button>
                        </li>
                        <li class="">
                            <button type="button" data-href="list" data-title="Список" data-rel="tooltip">
                                <span class="icon_list_cat"></span><span class="text-el">Список</span>
                            </button>
                        </li>
                                            </ul>
                </nav>
            </div>
            
        </div>
    </div>
               -->
               <!-- Start.If count products in category > 0 then show products list and pagination links -->
                                <ul class="animateListItems items items-catalog items-product tablemini" id="items-catalog-main">
                    <!-- Include template for one product item-->
                    


{% for entry in entries %}
<li class="globalFrameProduct {% if (entry.variants[0].stock == 0) or (entry.variants[0].stock == 1) %}not-avail{% elseif (entry.variants[0].stock == 5) %}to-cart{% endif %}" data-pos="top">
    <!-- Start. Photo & Name product -->
    <a href="{{entry.fulllink}}" class="frame-photo-title">
        <span class="photo-block">
            <span class="helper"></span>
            {% if (entry.images[0].filepath) %}<img src='{{home}}/uploads/eshop/products/{{entry.id}}/thumb/{{entry.images[0].filepath}}' class="vImg">{% else %}<img src='{{home}}/engine/plugins/eshop/tpl/img/img_none.jpg' class="vImg">{% endif %}
                                            </span>
        <span class="title">{{ entry.name }}</span>
    </a>
    <!-- End. Photo & Name product -->
    <div class="description">
        <!-- Start. article & variant name & brand name -->
                <div class="frame-variant-name-code">
                        <span class="frame-variant-code frameVariantCode">Артикул:
                <span class="code js-code">
                                        {{ entry.code }}                                    </span>
            </span>
                    </div>
                <!-- End. article & variant name & brand name -->
                <!--
                        <div class="frame-star f-s_0">
                <div class="star">
        <div id="star_rating_5623" class="productRate star-small">
            <div style="width: 80%"></div>
        </div>
    </div>
            <a href="http://fluid.imagecmsdemo.net/shop/product/mobilnyi-telefon-fly-e141-tv-dual-sim-black#comment" class="count-response">
                4                отзыва            </a>
        </div>
        -->
                        <!-- Start. Prices-->
        <div class="frame-prices f-s_0">
                                                <!-- Start. Product price-->
            <span class="current-prices f-s_0">
                <span class="price-new">
                    <span>
                    {% if (entry.variants[0].price) %}
<span class="price priceVariant">{{ (entry.variants[0].price * system_flags.eshop.currency[0].rate_from / system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</span> {{ system_flags.eshop.current_currency.sign }}
                    {% endif %}
                    </span>
                    
                </span>
                {% if (not (entry.variants[0].compare_price == '0.00')) and (not (entry.variants[0].compare_price == '')) %}
                <span class="price-add">
                    <span>
<span class="price addCurrPrice"><s>{{ (entry.variants[0].compare_price * system_flags.eshop.currency[0].rate_from / system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</s></span> <s>{{ system_flags.eshop.current_currency.sign }}</s>
                    </span>
                </span>
                {% endif %}
                            </span>
                        <!-- End. Product price-->
        </div>
        <!-- End. Prices-->
                        
                <div class="funcs-buttons frame-without-top" style="position: relative; top: 0px;">
            <div class="no-vis-table">
                {% if (entry.variants[0].stock == 0) or (entry.variants[0].stock == 1) %}
                <div class="js-variant-5834 js-variant">
                <div class="alert-exists">Нет в наличии</div>
                <div class="btn-not-avail">
                <button class="infoBut isDrop" type="button" data-drop=".drop-report">
                </button>
                </div>
                </div>
                {% elseif (entry.variants[0].stock == 5) %}
                <!-- Start. Collect information about Variants, for future processing -->
                                                                                                <div class="frame-count-buy js-variant-5853 js-variant">
                        <div class="btn-buy btn-cart d_n">
                            <button type="button" data-id="5853" class="btnBuy">
                            <span class="icon_cleaner icon_cleaner_buy"></span>
                            <span class="text-el">В корзине</span>
                        </button>
                    </div>
                    <div class="btn-buy">
                        <button type="button" class="btnBuy orderBut" data-id="{{ entry.id }}">
                        <span class="icon_cleaner icon_cleaner_buy"></span>
                        <span class="text-el">Купить</span>
                    </button>
                </div>
        </div>{% endif %}
            </div>
</div>
<!-- End. Collect information about Variants, for future processing -->
<div class="frame-without-top" style="position: relative; top: 40px;">
    <!-- Wish List & Compare List buttons -->
    
    <div class="frame-wish-compare-list no-vis-table t-a_j">
                <div class="frame-btn-comp">
            <div class="btn-compare">
                <div class="toCompare btnCompare {% if (entry.compare) %}active{% endif %}" data-id="{{entry.id}}" type="button" data-title="Сравнить" data-firtitle="Сравнить" data-sectitle="В сравнении" data-rel="tooltip">
                <span class="niceCheck nstcheck {% if (entry.compare) %}active{% endif %}">
                    <input type="checkbox" {% if (entry.compare) %}cheked="cheked"{% endif %}>
                </span>
                <span class="text-el d_l">Сравнить</span>
            </div>
        </div>
    </div>
            <!-- Start. Wish list buttons -->
        <!--
        <div class="frame-btn-wish js-variant-5853 js-variant d_i-b_">
        <div class="btnWish btn-wish" data-id="5853">
    <button class="toWishlist isDrop" type="button" data-rel="tooltip" data-title="В избранные" data-drop="#dropAuth">
        <span class="icon_wish"></span>
        <span class="text-el d_l">В избранные</span>
    </button>
    <button class="inWishlist" type="button" data-rel="tooltip" data-title="В избранныx" style="display: none;">
        <span class="icon_wish"></span>
        <span class="text-el d_l">В избранныx</span>
    </button>
</div>
<script>
langs["Create list"] = 'Создать список';
langs["Wrong list name"] = 'Неверное имя списка';
langs["Already in Wish List"] = 'Уже в Списке Желаний';
langs["List does not chosen"] = 'Список не обран';
langs["Limit of Wish List finished "] = 'Лимит списков пожеланий исчерпан';
</script>    </div>
        -->
        <!-- End. wish list buttons -->
    </div>
<!-- End. Wish List & Compare List buttons -->
</div>
<!-- End. Collect information about Variants, for future processing -->
<div class="frame-without-top" style="position: relative; top: 62px;">
    <div class="no-vis-table">
        <!--Start. Description-->
                <div class="short-desc">
             {{entry.annotation|truncateHTML(30,'...')}}        </div>
                <!-- End. Description-->
    </div>
</div>
</div>
<!-- Start. Remove buttons if compare-->
<!-- End. Remove buttons if compare-->

<!-- Start. For wishlist page-->
<!-- End. For wishlist page-->
<div class="decor-element" style="height: 300px; width: 100%; position: absolute; right: auto; left: 0px; bottom: auto; top: 15px;"></div>
</li>
{% endfor %}
                </ul>
                
                {% if (pages.true) %}
                <!-- render pagination-->
                <div class="pagination"><ul class="f-s_0">

                {% if (prevlink.true) %}
                <li class="prev-page">
                    {{ prevlink.link }}
                </li>
                {% endif %}

                {{ pages.print }}

                {% if (nextlink.true) %}
                <li class="next-page">
                    {{ nextlink.link }}
                </li>
                {% endif %}

                </ul></div>
                {% endif %}
        </div>
    </div>
</div>

{% else %}

<div class="frame-inside">
    <div class="container">
                <div class="right-catalog" style="width:100% !important;margin-left: 0;">
                                        <div class="msg layout-highlight layout-highlight-msg">
                    <div class="info">
                        <span class="icon_info"></span>
                        <span class="text-el">Акционных товаров не найдено</span>
                    </div>
                </div>
                                                <!--Start. Pagination -->
                        <!-- End pagination -->
        </div>
    </div>
</div>

{% endif %}
