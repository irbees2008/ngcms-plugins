<div class="frame-inside page-product">
   <div class="container">
            <div class="clearfix item-product {% if (entriesVariants[0].stock == 0) %}not-avail{% elseif (entriesVariants[0].stock == 5) or (entriesVariants[0].stock == 1) %}to-cart{% endif %}">
      <div class="right-product">
        <!--Start. Payments method form -->
        <div class="" data-mq-max="1280" data-mq-min="0" data-mq-target="#deliveryTabs">
          <div class="frame-delivery-payment"><dl><dt class="title f-s_0"><span class="icon_delivery">&nbsp;</span><span class="text-el">Доставка</span><span class="icon_info_t" data-rel="tooltip" data-placement="left" data-other-class="info-patch" data-title="При желании Вы можете сами забрать купленный товар в наших офисах<br></span> 
Бесплатная доставка от 2000 руб.">&nbsp;</span></dt><dd class="frame-list-delivery">
{{ system_flags.eshop.description_delivery }}
</dd><dt class="title f-s_0"><span class="icon_payment">&nbsp;</span><span class="text-el">Оплата</span><span class="icon_info_t" data-placement="left" data-rel="tooltip" data-other-class="info-patch" data-title="Сервис решает задачи по организации процесса приема платежей с помощью подключения всех (из возможного множества) платежных систем.">&nbsp;</span></dt><dd class="frame-list-payment">
{{ system_flags.eshop.description_order }}
<!--
<ul>
<li>Наличными при получении</li>
<li>Безналичный перевод</li>
<li>Приват 24</li>
<li>WebMoney</li>
</ul>
-->
</dd></dl></div>        </div>
        <!--End. Payments method form -->

        {% if (entriesRelated) %}
        <!-- Start. Similar Products-->
        <div class="items-products count-items1" id="similar">
    <div class="title-proposition-v">
        <div class="frame-title">
            <div class="title">Похожие товары</div>
        </div>
    </div>
    <div class="big-container">
        <div class="items-carousel carousel-js-css">
                        <div class="content-carousel container" data-mq-max="1280" data-mq-min="0" data-mq-target="#similarBott">
                <ul class="items items-default items-h-carousel items-product ">
                    
{% for entry in entriesRelated %}
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
                <!-- End. article & variant name & brand name -->
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
        
        <!-- End. Collect information about Variants, for future processing -->
</div>
<!-- Start. Remove buttons if compare-->
<!-- End. Remove buttons if compare-->

<!-- Start. For wishlist page-->
<!-- End. For wishlist page-->
</li>
{% endfor %}
                </ul>
            </div>
            <div class="group-button-carousel">
                <button type="button" class="prev arrow">
                    <span class="icon_arrow_p"></span>
                </button>
                <button type="button" class="next arrow">
                    <span class="icon_arrow_n"></span>
                </button>
            </div>
        </div>
    </div>
</div>
        <!-- End. Similar Products-->
        {% endif %}
      </div>
      
      <div class="left-product leftProduct">
        <div class="o_h">
         <div class="left-product-left is-add">
          <div class="inside">


<!-- Start. additional images-->
           <div class="vertical-carousel">
             <div class="frame-thumbs carousel-js-css jcarousel-container jcarousel-container-vertical iscarousel" style="position: relative; display: block;">
                            <div class="content-carousel">
               <div class="jcarousel-clip jcarousel-clip-vertical" style="position: relative;"><ul class="items-thumbs items jcarousel-list jcarousel-list-vertical" style="overflow: hidden; position: relative; top: 0px; margin: 0px; padding: 0px; left: 0px; height: 411px;">
               {% for entry in entriesImg %}
                <!-- Start. main image-->
                <li class="jcarousel-item jcarousel-item-vertical jcarousel-item-{{loop.index}} jcarousel-item-{{loop.index}}-vertical {% if loop.first %}active{% endif %}" jcarouselindex="{{loop.index}}" style="float: left; list-style: none; height: 308px;">
                    <a onclick="return false;" rel="useZoom: 'photoProduct'" href="{{home}}/uploads/eshop/products/{{id}}/{{entry.filepath}}" class="cloud-zoom-gallery" id="mainThumb">
                    <span class="photo-block">
                    <span class="helper"></span>
                    <img src="{{home}}/uploads/eshop/products/{{id}}/thumb/{{entry.filepath}}"  class="vImgPr">
                    </span>
                    </a>
                </li>
                {% endfor %}
                
                  </ul></div>
      </div>
      <div class="group-button-carousel">
       
      
    <button type="button" class="prev arrow jcarousel-prev jcarousel-prev-vertical jcarousel-prev-disabled jcarousel-prev-disabled-vertical" disabled="disabled" style="display: inline-block;">
        <span class="icon_arrow_p"></span>
      </button><button type="button" class="next arrow jcarousel-next jcarousel-next-vertical" style="display: inline-block;">
        <span class="icon_arrow_n"></span>
      </button></div>
  </div>
</div>
<!-- End. additional images-->
<!-- Start. Photo block-->
<div id="wrap" style="top:0px;z-index:9999;position:relative;">
    {% if (entriesImg) %}
    {% for entry in entriesImg %}
        {% if loop.first %}
            <a rel="position: 'xBlock'" onclick="return false;" href="{{home}}/uploads/eshop/products/{{id}}/{{entry.filepath}}" class="frame-photo-title photoProduct cloud-zoom isDrop" id="photoProduct" data-drop="#photo" data-start="Product.initDrop" data-scroll-content="false" style="position: relative; display: block;">
                <span class="photo-block">
                    <span class="helper"></span>
                    <img src="{{home}}/uploads/eshop/products/{{id}}/thumb/{{entry.filepath}}" title="{{ name }}" class="vImgPr" style="display: block;">
                </span>
            </a>
            <div class="mousetrap" style="z-index: 9999; position: absolute; width: 179px; height: 323px; left: 0px; top: 0px; cursor: move; background-image: url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7);"></div>
        {% endif %}
    {% endfor %}
    {% else %}
                <a rel="position: 'xBlock'" onclick="return false;" href="{{home}}/engine/plugins/eshop/tpl/img/img_none.jpg" class="frame-photo-title photoProduct cloud-zoom isDrop" id="photoProduct" data-drop="#photo" data-start="Product.initDrop" data-scroll-content="false" style="position: relative; display: block;">
                <span class="photo-block">
                    <span class="helper"></span>
                    <img src="{{home}}/engine/plugins/eshop/tpl/img/img_none.jpg" title="{{ name }}" class="vImgPr" style="display: block;">
                </span>
            </a>
            <div class="mousetrap" style="z-index: 9999; position: absolute; width: 179px; height: 323px; left: 0px; top: 0px; cursor: move; background-image: url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7);"></div>
    {% endif %}
</div>

<div class="horizontal-carousel photo-main-carousel">
  <div class="group-button-carousel">
   <button type="button" class="prev arrow" style="display: none;">
    <span class="icon_arrow_p"></span>
  </button>
  <button type="button" class="next arrow" style="display: none;">
    <span class="icon_arrow_n"></span>
  </button>
</div>
</div>
<!-- End. Photo block-->


</div>

</div>
<div class="left-product-right">
  <div class="globalFrameProduct">
   <div class="f-s_0 title-product">
    <!-- Start. Name product -->
    <div class="frame-title">
     <h1 class="title">{{ name }} <a href="{{ edit_link }}" target="_blank">[E]</a></h1>
   </div>
   <!-- End. Name product -->
 </div>
 <!-- Start. frame for cloudzoom -->
 <div id="xBlock"></div>
 <!-- End. frame for cloudzoom -->
 <div class="o_h">
  <!-- Start. article & variant name & brand name -->
  <div class="f_l">
   <span class="frame-variant-name-code">
    <span class="frame-variant-code frameVariantCode">
     <span class="s-t">Код:</span>
     <span class="code js-code">
            {{ code }}          </span>
  </span>
</span>
</div>
<!-- End. article & variant name & brand name -->
<!-- Start. Star rating -->
<!--
<div class="f_l">
   <div class="frame-star">
       <div class="star">
        <div id="star_rating_5623" class="productRate star-small">
            <div style="width: 80%"></div>
        </div>
    </div>
   <button data-trigger="[data-href='#comment']" data-scroll="true" class="count-response d_l">
    4    отзыва  </button>
</div>
</div>
-->
<!-- End. Star rating-->
</div>

<div class="f-s_0 buy-block">
{% if entriesVariants|length > 1 %}
  <!-- Start. Check variant-->
    <div class="check-variant-product">
    <div class="lineForm">
     <select name="variant" id="variantSwitcher" onChange="change_variant(this)">
         {% for variant in entriesVariants %}
            <option value="{{ variant.id }}|{{ variant.price }}|{{ variant.compare_price }}|{{ variant.stock }}" data-variant="{{ variant.id }}" data-price="{{ variant.price }}" data-price="{{ variant.compare_price }}" data-stock="{{ variant.stock }}">
                {{ variant.name }}
            </option>
        {% endfor %}
      </select>
  </div>
</div>
<!-- End. Check variant-->
{% endif %}

<script>
variant = "";
variant_id = {{ entriesVariants[0].id }};
variant_price = {{ entriesVariants[0].price }};
variant_compare_price = {{ entriesVariants[0].compare_price }};
variant_stock = {{ entriesVariants[0].stock }};
</script>

<div class="frame-prices-buy-wish-compare">
 <div class="frame-for-photo-popup">
  <div class="frame-prices-buy f-s_0">
   <!-- Start. Prices-->
   <div class="frame-prices f-s_0">
    <!-- Start. Check for discount-->
            <!-- End. Check for discount-->
    <!-- Start. Check old price-->
        <!-- End. Check old price-->
    <!-- Start. Product price-->
        <span class="current-prices f-s_0">
    {% for variant in entriesVariants %}
        {% if (loop.index == 1) %}
            <span class="price-new">
               <span>
              {% if (variant) %}
                <span class="price priceVariant">{{ (variant.price * system_flags.eshop.currency[0].rate_from / system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</span> {{ system_flags.eshop.current_currency.sign }}
                
              {% endif %}
              </span>
            </span>

            {% if (not (variant.compare_price == '0.00')) and (not (variant.compare_price == '')) %}
                <span class="price-add">
                  <span>
                    <s>(<span class="price addCurrPrice">{{ (variant.compare_price * system_flags.eshop.currency[0].rate_from / system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</span> {{ system_flags.eshop.current_currency.sign }})</s>
                  </span>
                </span>
            {% endif %}
        {% endif %}
    {% endfor %}
      </span>
    <!-- End. Product price-->
</div>
<!-- End. Prices-->
<div class="funcs-buttons">
{% if (entriesVariants[0].stock == 5) %}
<!-- Start. Collect information about Variants, for future processing -->
<div class="frame-count-buy js-variant-5853 js-variant">
<div class="frame-count frameCount">
    <div class="number js-number" data-title="Количество на складе 1">
        <input type="text" name="quantity" value="1" class="plusMinus plus-minus" data-title="Только цифры" data-min="1" data-max="1">
    </div>
</div>
<div class="btn-buy btn-cart d_n">
    <button type="button" data-id="{{ id }}" data-variant="{{ entriesVariants[0]['id'] }}" class="btnBuy">
    <span class="icon_cleaner icon_cleaner_buy"></span>
    <span class="text-el">В корзине</span>
    </button>
</div>
<div class="btn-buy-p btn-buy btn-green" style="margin-right: 3px;">
    <button type="button" class="btnBuy orderBut" data-id="{{ id }}" data-variant="{{ entriesVariants[0]['id'] }}" style="padding: 0 3px;">
    <span class="icon_cleaner icon_cleaner_buy"></span>
    <span class="text-el">Купить</span>
    </button>
</div>

<div class="btn-buy-p btn-buy" style="margin-right: 3px;">
   <button type="button" class="btnBuy fastOrderBut" data-id="{{ id }}" data-variant="{{ entriesVariants[0]['id'] }}" style="padding: 0 3px;">
   <span class="icon_cleaner icon_cleaner_buy"></span>
   <span class="text-el">Купить в один клик</span>
 </button>
</div>

<div class="label-is-aviable">
  <span class="icon-is-aviable"></span>
  <span class="text-el">Есть в наличии</span>
</div>

</div>

{% elseif (entriesVariants[0].stock == 1) %}
<!-- Start. Collect information about Variants, for future processing -->
<div class="frame-count-buy js-variant-5853 js-variant">

<div class="btn-buy-p btn-buy btn-green" style="margin-right: 3px;">
    <button type="button" class="btnBuy fastPriceBut" data-id="{{ id }}" data-variant="{{ entriesVariants[0]['id'] }}" style="padding: 0 3px;">
    <span class="icon_cleaner icon_cleaner_buy"></span>
    <span class="text-el">Сообщить о появлении</span>
    </button>
</div>

<div class="label-is-aviable">
  <span class="icon-is-aviable"></span>
  <span class="text-el">На заказ</span>
</div>

</div>
{% else %}
<div class="d_i-b v-a_m">
 <div class="js-variant-5858 js-variant">
  <div class="btn-not-avail">
   <button type="button" class="infoBut fastPriceBut" data-id="{{ id }}" data-variant="{{ entriesVariants[0]['id'] }}" >
   <span class="icon-but"></span>
   <span class="text-el">Сообщить о появлении</span>
 </button>
</div>
<div class="label-is-aviable">
  <span class="icon-is-aviable"></span>
  <span class="text-el">{% if (entriesVariants[0].stock == 0) %}Нет в наличии{% endif %}</span>
</div>
</div>
</div>
{% endif %}

</div>
<!-- End. Collect information about Variants, for future processing -->
</div>
</div>
<!-- Start. Wish List & Compare List buttons -->
<div class="frame-wish-compare-list f-s_0 d_i-b v-a_m">
  <div class="frame-btn-comp v-a_bl">
   <div class="btn-compare">
    <div class="btnCompare toCompare {% if (compare) %}active{% endif %}" data-id="{{id}}" type="button" data-title="Cравнить" data-firtitle="Cравнить" data-sectitle="В сравнении" data-rel="tooltip">
    <span class="niceCheck nstcheck {% if (compare) %}active{% endif %}">
      <input type="checkbox" {% if (compare) %}cheked="cheked"{% endif %}>
    </span>
    <span class="text-el d_l">Cравнить</span>
  </div>
</div>
</div>
<!--
<div class="frame-btn-wish v-a_bl js-variant-5853 js-variant" data-id="5853">
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
</script></div>-->
</div>
</div>

</div>
<!-- Start. Description -->
<div class="short-desc">
 {{ annotation }}
 </div>
<!--  End. Description -->

<div class="ratebox2 pull-right" data-id="{{id}}">
    {{ likes_form }}
</div>

<!--Start .Share-->
<div class="social-product">
  <div class="social-like d_i-b">
   <ul class="items items-social"><li>   <div id="fb-root" class=" fb_reset"><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div><iframe name="fb_xdm_frame_http" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" title="Facebook Cross Domain Communication Frame" aria-hidden="true" tabindex="-1" id="fb_xdm_frame_http" src="http://static.ak.facebook.com/connect/xd_arbiter/TlA_zCeMkxl.js?version=41#channel=f2ae943f4c&amp;origin=http%3A%2F%2Ffluid.imagecmsdemo.net" style="border: none;"></iframe><iframe name="fb_xdm_frame_https" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" title="Facebook Cross Domain Communication Frame" aria-hidden="true" tabindex="-1" id="fb_xdm_frame_https" src="https://s-static.ak.facebook.com/connect/xd_arbiter/TlA_zCeMkxl.js?version=41#channel=f2ae943f4c&amp;origin=http%3A%2F%2Ffluid.imagecmsdemo.net" style="border: none;"></iframe></div></div><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div></div></div></div>
                        <script async="async" defer="defer">(function(d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) return;
                        js = d.createElement(s); js.id = id;
                        js.src = "//connect.facebook.net/en_EN/all.js#xfbml=1";
                        fjs.parentNode.insertBefore(js, fjs);
                        }(document, "script", "facebook-jssdk"));</script>
                        <div class="fb-like fb_iframe_widget" data-send="false" data-layout="button_count" data-width="60" data-show-faces="true" fb-xfbml-state="rendered" fb-iframe-plugin-query="app_id=&amp;container_width=0&amp;href=http%3A%2F%2Ffluid.imagecmsdemo.net%2Fshop%2Fproduct%2Fmobilnyi-telefon-fly-e141-tv-dual-sim-black&amp;layout=button_count&amp;locale=en_US&amp;sdk=joey&amp;send=false&amp;show_faces=true&amp;width=60"><span style="vertical-align: bottom; width: 77px; height: 20px;"><iframe name="f376d476" width="60px" height="1000px" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" title="fb:like Facebook Social Plugin" src="http://www.facebook.com/plugins/like.php?app_id=&amp;channel=http%3A%2F%2Fstatic.ak.facebook.com%2Fconnect%2Fxd_arbiter%2FTlA_zCeMkxl.js%3Fversion%3D41%23cb%3Df2ad9c4bfc%26domain%3Dfluid.imagecmsdemo.net%26origin%3Dhttp%253A%252F%252Ffluid.imagecmsdemo.net%252Ff2ae943f4c%26relation%3Dparent.parent&amp;container_width=0&amp;href=http%3A%2F%2Ffluid.imagecmsdemo.net%2Fshop%2Fproduct%2Fmobilnyi-telefon-fly-e141-tv-dual-sim-black&amp;layout=button_count&amp;locale=en_US&amp;sdk=joey&amp;send=false&amp;show_faces=true&amp;width=60" style="border: none; visibility: visible; width: 77px; height: 20px;" class=""></iframe></span></div></li><li><!-- Place this tag in your head or just before your close body tag. -->
                        <script type="text/javascript" src="https://apis.google.com/js/plusone.js" gapi_processed="true">
                          {lang: 'ru', parsetags: 'explicit'}
                        </script>

                        <!-- Place this tag where you want the +1 button to render. -->
                        <div id="___plusone_0" style="text-indent: 0px; margin: 0px; padding: 0px; border-style: none; float: none; line-height: normal; font-size: 1px; vertical-align: baseline; display: inline-block; width: 90px; height: 20px; background: transparent;"><iframe frameborder="0" hspace="0" marginheight="0" marginwidth="0" scrolling="no" style="position: static; top: 0px; width: 90px; margin: 0px; border-style: none; left: 0px; visibility: visible; height: 20px;" tabindex="0" vspace="0" width="100%" id="I0_1449598757601" name="I0_1449598757601" src="https://apis.google.com/u/0/se/0/_/+1/fastbutton?usegapi=1&amp;size=medium&amp;hl=ru&amp;origin=http%3A%2F%2Ffluid.imagecmsdemo.net&amp;url=http%3A%2F%2Ffluid.imagecmsdemo.net%2Fshop%2Fproduct%2Fmobilnyi-telefon-fly-e141-tv-dual-sim-black&amp;gsrc=3p&amp;ic=1&amp;jsh=m%3B%2F_%2Fscs%2Fapps-static%2F_%2Fjs%2Fk%3Doz.gapi.ru.Q73LU7xpV1E.O%2Fm%3D__features__%2Fam%3DAQ%2Frt%3Dj%2Fd%3D1%2Ft%3Dzcms%2Frs%3DAGLTcCNSJ7B_IS-p3BqVNcSqwO2hhO5mug#_methods=onPlusOne%2C_ready%2C_close%2C_open%2C_resizeMe%2C_renderstart%2Concircled%2Cdrefresh%2Cerefresh&amp;id=I0_1449598757601&amp;parent=http%3A%2F%2Ffluid.imagecmsdemo.net&amp;pfname=&amp;rpctoken=47406993" data-gapiattached="true" title="+1"></iframe></div>

                        <!-- Place this render call where appropriate. -->
                        <script type="text/javascript">gapi.plusone.go();</script></li><li><iframe id="twitter-widget-0" scrolling="no" frameborder="0" allowtransparency="true" class="twitter-share-button twitter-share-button-rendered twitter-tweet-button" title="Twitter Tweet Button" src="http://platform.twitter.com/widgets/tweet_button.781f4f0ed6951ea86890554cec78adec.en.html#_=1449598759207&amp;count=horizontal&amp;dnt=false&amp;id=twitter-widget-0&amp;lang=en&amp;original_referer=http%3A%2F%2Ffluid.imagecmsdemo.net%2Fshop%2Fproduct%2Fmobilnyi-telefon-fly-e141-tv-dual-sim-black&amp;size=m&amp;text=5623%20-%20%D0%9C%D0%BE%D0%B1%D0%B8%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9%20%D1%82%D0%B5%D0%BB%D0%B5%D1%84%D0%BE%D0%BD%20Fly%20E141%20TV%20Dual%20SIM%20Black%20-%20%D0%9C%D0%BE%D0%B1%D0%B8%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5%20%D1%82%D0%B5%D0%BB%D0%B5%D1%84%D0%BE%D0%BD%D1%8B%20%2F%20ImageCMS&amp;type=share&amp;url=http%3A%2F%2Ffluid.imagecmsdemo.net%2Fshop%2Fproduct%2Fmobilnyi-telefon-fly-e141-tv-dual-sim-black" style="position: static; visibility: visible; width: 61px; height: 20px;"></iframe>
                    <script async="async" defer="defer">!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id))
                    {js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></li></ul> </div>
 <div class="social-tell d_i-b">
   <script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
                <div class="yashare-auto-init" data-yasharel10n="ru" data-yasharelink="" data-yasharetype="none" data-yasharequickservices="vkontakte,facebook,twitter,"><span class="b-share"><a rel="nofollow" target="_blank" title="ВКонтакте" class="b-share__handle b-share__link b-share-btn__vkontakte" href="https://share.yandex.net/go.xml?service=vkontakte&amp;url=http%3A%2F%2Ffluid.imagecmsdemo.net%2Fshop%2Fproduct%2Fmobilnyi-telefon-fly-e141-tv-dual-sim-black&amp;title=5623%20-%20%D0%9C%D0%BE%D0%B1%D0%B8%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9%20%D1%82%D0%B5%D0%BB%D0%B5%D1%84%D0%BE%D0%BD%20Fly%20E141%20TV%20Dual%20SIM%20Black%20-%20%D0%9C%D0%BE%D0%B1%D0%B8%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5%20%D1%82%D0%B5%D0%BB%D0%B5%D1%84%D0%BE%D0%BD%D1%8B%20%2F%20ImageCMS" data-service="vkontakte"><span class="b-share-icon b-share-icon_vkontakte"></span></a><a rel="nofollow" target="_blank" title="Facebook" class="b-share__handle b-share__link b-share-btn__facebook" href="https://share.yandex.net/go.xml?service=facebook&amp;url=http%3A%2F%2Ffluid.imagecmsdemo.net%2Fshop%2Fproduct%2Fmobilnyi-telefon-fly-e141-tv-dual-sim-black&amp;title=5623%20-%20%D0%9C%D0%BE%D0%B1%D0%B8%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9%20%D1%82%D0%B5%D0%BB%D0%B5%D1%84%D0%BE%D0%BD%20Fly%20E141%20TV%20Dual%20SIM%20Black%20-%20%D0%9C%D0%BE%D0%B1%D0%B8%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5%20%D1%82%D0%B5%D0%BB%D0%B5%D1%84%D0%BE%D0%BD%D1%8B%20%2F%20ImageCMS" data-service="facebook"><span class="b-share-icon b-share-icon_facebook"></span></a><a rel="nofollow" target="_blank" title="Twitter" class="b-share__handle b-share__link b-share-btn__twitter" href="https://share.yandex.net/go.xml?service=twitter&amp;url=http%3A%2F%2Ffluid.imagecmsdemo.net%2Fshop%2Fproduct%2Fmobilnyi-telefon-fly-e141-tv-dual-sim-black&amp;title=5623%20-%20%D0%9C%D0%BE%D0%B1%D0%B8%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9%20%D1%82%D0%B5%D0%BB%D0%B5%D1%84%D0%BE%D0%BD%20Fly%20E141%20TV%20Dual%20SIM%20Black%20-%20%D0%9C%D0%BE%D0%B1%D0%B8%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5%20%D1%82%D0%B5%D0%BB%D0%B5%D1%84%D0%BE%D0%BD%D1%8B%20%2F%20ImageCMS" data-service="twitter"><span class="b-share-icon b-share-icon_twitter"></span></a></span></div> </div>
</div>
<!-- End. Share -->
</div>

</div>
</div>





<div class="tabs-product-out f-s_0">
 <!-- Start. Tabs block-->
 <ul class="tabs tabs-data tabs-product">
    <li class="active"><button data-href="#first">Характеристики</button></li>
      <li class=""><button data-href="#second">описание</button></li>
      <!--Output of the block comments-->
    <li class="">
    <button type="button" data-href="#comment">
      <span class="icon_comment-tab"></span>
      <span class="text-el">
        <span id="cc">отзывы</span>
      </span>
    </button>
  </li>
      <li class="tab-deliv">
    <button data-href="#tabDeliv">
      Доставка и оплата    </button>
  </li>
  </ul>
<div class="frame-tabs-ref frame-tabs-product">
  <!--             Start. Characteristic-->
    <div id="first" class="active" style="display: block;">
    <div class="inside-padd">
     <div class="characteristic">
      <table>
       <tbody>
        {% for feature in entriesFeatures %}
            <tr>
              <th><span class="text-el">{{ feature.name }}</span></th>
              <td><span class="text-el">{{ feature.value }}</span></td>
            </tr>
        {% endfor %}
      </tbody>
    </table>
  </div>
</div>
</div>
<!--                    End. Characteristic-->
<div id="second" class="visited" style="display: none;">
  <div class="inside-padd">
    <div class="text">{{ body }}</div>
  </div>
</div>
<div id="comment" class="" style="display: none;">
  <div class="inside-padd forComments p_r"><style> .comments:after,.comments .func-button-comment:after{visibility:hidden;display:block;font-size:0;content:".";clear:both;height:0}.func-button-comment{font-size:0}.comments{max-width:915px}.comments .main-form-comments .frame-form-field{margin-left:0}.comments .author-comment{font-size:12px;font-weight:bold}.comments .frame-list-comments > .sub-1{margin:0}.page-text .comments .frame-list-comments > .sub-1{max-height:none}.comments .frame-comment p{margin-bottom:0px}.comments .frame-list-comments.sub-2{float:none;width:auto}#comment .comments .frame-list-comments.sub-2{margin-left:115px;padding-bottom:10px}.page-text .comments .frame-list-comments.sub-2{max-height:none!important;height:auto!important}.page-text .btn-all-comments{display:none !important}.comments .btn-all-comments{margin-left:0;margin-top:5px}.comments .btn > *,.comments input.btn{padding:0 !important}.comments .frame-list-comments .btn.active{box-shadow:none}.comments .icon_comment{background-image:none;width:0;height:0;margin-right:0!important;overflow:hidden}.comments .frame-list-comments li{border-top:0;padding:0}.comments .frame-list-comments > .sub-1 > li{margin-top:13px;margin-bottom:0;border-top:1px solid transparent;padding-top:21px}.comments .frame-list-comments > .sub-1 > li:before{display:none}.comments .frame-list-comments > .sub-1 > li:first-child{margin-top:0;border-top:0px;padding-top:0}.comments .frame-list-comments.sub-2 + .s-all-comments{margin-left:33px}.comments .frame-list-comments.sub-2 > li{margin-top:20px;position:relative}.comments .frame-list-comments.sub-2 > li:first-child{margin-top:12px}.comments .frame-list-comments.sub-2 > li:before{content:"";border-width:9px;border-style:solid;top:-9px;left:20px;position:absolute;border-color:transparent}.comments .global-frame-comment-sub2{padding:15px 20px 15px}.comments .funcs-buttons-comment + .frame-list-comments{padding-top:1px}.comments .like > button span span,.comments .dis-like > button span span{font-weight:normal}.comments .like,.comments .dis-like{height:12px;line-height:12px}.comments .author-data-comment-sub1{float:left;width:100px}.comments .s-all-comments{margin-top:7px}.comments .date-comment{display:block;font-size:11px;padding-top:1px}.author-data-comment-sub2 .date-comment{display:inline-block}.comments .btn-form input{padding:0 25px !important}.comments .frame-drop-comment{padding-bottom:0;display:none;width:auto}.frame-comment-sub1{margin:0 170px 0 115px}.comments .frame-list-comments .frame-drop-comment{padding:15px 0 0;border-top:1px solid transparent;margin-top:20px}.comments .frame-list-comments{padding-bottom:20px}.comments .title-comment{margin-bottom:6px;font-size:18px;font-weight:bold}.comments label{display:block}.comments label,.comments .frame-label{margin-bottom:8px;display:block}.comments label .title,.comments .frame-label .title{display:block;margin-bottom:5px}.comments .main-form-comments .title{float:none;width:auto}.comments .frame-form-field{position:relative;display:block}.comments textarea{height:110px;padding-top:5px}.comments .btn{display:inline-block;cursor:pointer;text-decoration:none;position:relative}.comments .btn > *,.comments input.btn{padding:5px 15px 5px}.comments .btn.active{cursor:default}.comments .like{border-right:1px solid transparent;padding-right:7px;margin-right:7px}.like .d_l_1,.dis-like .d_l_1{font-size:11px;border-bottom:none}.comments .frame-form-field{}.comments .mark-pr{margin-bottom:4px}.comments .mark-pr .title{font-weight:bold}.comments .mark-pr > .star-small{position:relative;top:2px}.author-data-comment-sub2{margin-bottom:5px}.comments .frame-mark{float:right;width:150px}.comments .comments-main-form{overflow:visible !important;padding-top:17px}.comments .comments-main-form.noComments{float:none}.comments .comments-main-form .star{vertical-align:middle;top:-2px;margin-right:0}.comments .comments-main-form .star-big{margin-top:0}.forComments{position:relative}#comment .preloader{top:15px} </style><div class="comments" id="comments">
            <div class="frame-list-comments">
        <ul class="sub-1 product-comment showHidePart">
        </ul>
        <!--
        <button class="t-d_n f-s_0 s-all-d ref d_n_" data-trigger="[data-href='#comment']" data-scroll="true">
            <span class="icon_arrow"></span>
            <span class="text-el">Смотреть все ответы</span>
        </button>
        -->
            </div>

{{ comments_form }}

</div>

<!--
<div class="d_n" id="useModeration">
    <div class="usemoderation">
        <div class="msg">
            <div class="success">
                Ваш комментарий будет опубликован после модерации администратором
            </div>
        </div>
    </div>
</div>
-->
</div>
</div>
<!--Block Accessories Start-->
<!--End. Block Accessories-->

<div id="tabDeliv" style="">
  <div class="inside-padd">
    <div class="" id="deliveryTabs"></div>
  </div>
</div>

</div>
<!-- End. Tabs block-->
</div>







</div>
</div>
</div>
</div>


<!-- Start. Photo Popup Frame-->
<div class="drop drop-style globalFrameProduct" id="photo"></div>
<script type="text/template" id="framePhotoProduct">
<button type="button" class="icon_times_drop" data-closed="closed-js"></button>
<div class="drop-header">
<div class="title"><%- obj.title %></div>
</div>
<div class="drop-content">
<div class="inside-padd">
<span class="helper"></span>
<img src="<%- obj.mainPhoto %>" alt="<%- obj.title %>"/>
</div>
<div class="horizontal-carousel" id="photoButton">
<div class="group-button-carousel">
<button type="button" class="prev arrow">
<span class="icon_arrow_p"></span>
</button>
<button type="button" class="next arrow">
<span class="icon_arrow_n"></span>
</button>
</div>
</div>
</div>
<div class="drop-footer">
<div class="inside-padd">
<div class="horizontal-carousel">
<div class="frame-fancy-gallery frame-thumbs">
<div class="fancy-gallery carousel-js-css">
<div class="content-carousel">
<ul class="items-thumbs items">
<%= obj.frame.find(obj.galleryContent).html() %>
</ul>
</div>
<div class="group-button-carousel">
<button type="button" class="prev arrow">
<span class="icon_arrow_p"></span>
</button>
<button type="button" class="next arrow">
<span class="icon_arrow_n"></span>
</button>
</div>
</div>
</div>
</div>
</div>
<%= obj.frame.find(obj.footerContent).html()%>
</div>
</div>

</script>
<!-- End. Photo Popup Frame-->

<!-- Start. JS vars-->
<script type="text/javascript">
var hrefCategoryProduct = "";
</script>
<script type="text/javascript">
var productPhotoDrop = true;
var productPhotoCZoom = isTouch ? undefined : true;
</script>

<!-- End. JS vars-->

<script src="{{ tpl_url }}/js/cusel-min-2.5.js"></script>
<script src="{{ tpl_url }}/js/cloud-zoom.1.0.3.min.js"></script>
<script src="{{ tpl_url }}/js/_product.js"></script>

<div style="display: none;">
    {% for entry in entriesImg %}
        {% if loop.first %}
        <img src="{{home}}/uploads/eshop/products/{{id}}/{{entry.filepath}}" class="vImgPr">
        {% endif %}
        <img src="{{home}}/uploads/eshop/products/{{id}}/{{entry.filepath}}">
    {% endfor %}
</div>

<script>
$(document).ready(function() {

    $(".fastOrderBut").click(function(e){
        $(".forCenter_fastOrder").css("display", "block");
        $(".overlayDrop_fastOrder").css("display", "block");
        e.preventDefault();
    });

    $(".fastPriceBut").click(function(e){
        $(".forCenter_fastPrice").css("display", "block");
        $(".overlayDrop_fastPrice").css("display", "block");
        e.preventDefault();
    });
          
    $("#send_fastorder").click(function(e) {

        var id = $(this).attr('data-id');
        var count = $("input[name='quantity']").attr('value');

        var name = $("#fastorder-frame").find("input[name='name']").val();
        var phone = $("#fastorder-frame").find("input[name='phone']").val();
        var address = $("#fastorder-frame").find("input[name='address']").val();
        
        if( variant_id == "" || variant_id == undefined) {
            variant_id = $(this).attr('data-variant');
        }
        
        if( variant_id == "" || variant_id == undefined) {
            if(document.getElementById("variantSwitcher") !== null) {
                variant = $("#variantSwitcher").attr('value').split('|');
                parse_variant_str(variant);
            }
        }
        
        rpcEshopRequest('eshop_ebasket_manage', {'action': 'add_fast', 'ds':1, 'id':id, 'count':count, 'type': '2', 'name': name, 'phone': phone, 'address': address, 'variant_id': variant_id }, function (resTX) {
            $("div#fastorder-frame").html("<label><div align='center'>Заказ добавлен. В ближайшее время вам перезвонит наш манеджер.</div></label>");
        });

    });
    
    $("#send_fastprice").click(function(e) {

        var id = $(this).attr('data-id');
        var count = $("input[name='quantity']").attr('value');

        var name = $("#fastprice-frame").find("input[name='name']").val();
        var phone = $("#fastprice-frame").find("input[name='phone']").val();
        var address = $("#fastprice-frame").find("input[name='address']").val();
        
        if( variant_id == "" || variant_id == undefined) {
            variant_id = $(this).attr('data-variant');
        }
        
        if( variant_id == "" || variant_id == undefined) {
            if(document.getElementById("variantSwitcher") !== null) {
                variant = $("#variantSwitcher").attr('value').split('|');
                parse_variant_str(variant);
            }
        }
        
        rpcEshopRequest('eshop_ebasket_manage', {'action': 'add_fast', 'ds':1, 'id':id, 'count':count, 'type': '3', 'name': name, 'phone': phone, 'address': address, 'variant_id': variant_id }, function (resTX) {
            $("div#fastprice-frame").html("<label><div align='center'>Спасибо. В ближайшее время вам перезвонит наш манеджер.</div></label>");
        });

    });
    
    br.storage.prependUnique('page_stack', {{ id }}, 25);

});
</script>
