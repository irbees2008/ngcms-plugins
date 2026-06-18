{% if (entries) %}
<div class="horizontal-carousel">
    <section class="special-proposition frame-view-products">
      <div class="title-proposition-v">
        <div class="frame-title">
          <div class="title">
            Вы уже смотрели
          </div>
        </div>
      </div>
      <div class="carousel-js-css items-carousel jcarousel-container jcarousel-container-horizontal iscarousel" style="position: relative; display: block;">
        <div class="content-carousel container">
          <div class="jcarousel-clip jcarousel-clip-horizontal" style="position: relative;">
            <ul class="items items-catalog items-h-carousel items-product jcarousel-list jcarousel-list-horizontal" style="overflow: hidden; position: relative; top: 0px; margin: 0px; padding: 0px; left: 0px;" id="viewed_list">
                {% for entry in entries %}
                              <li class="globalFrameProduct to-cart jcarousel-item jcarousel-item-horizontal jcarousel-item-{{loop.index}} jcarousel-item-{{loop.index}}-horizontal" data-pos="top" jcarouselindex="{{loop.index}}" style="float: left; list-style: none; width: 411px;">
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
                                  <!--
                                  <div class="frame-star f-s_0">
                                    <div class="star">
                                      <div id="star_rating_1105" class="productRate star-small">
                                        <div style="width: 100%">
                                        </div>
                                      </div>
                                    </div>
                                    <a href="{{home}}/shop/product/mobilnyi-telefon-lg-nexus-4-e960-black#comment" class="count-response">
                                      5                отзывов            
                                    </a>
                                  </div>
                                  -->
                                  <!-- Start. Prices-->
                                  <div class="frame-prices f-s_0">
                                    <!-- Start. Product price-->
                                    <span class="current-prices f-s_0">
                                      <span class="price-new">
                                        <span>
                                          {% if (entry.variants[0].price) %}
                                          <span class="price priceVariant">
                                            {{ (entry.variants[0].price * system_flags.eshop.currency[0].rate_from / system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}
                                          </span>
                                          {{ system_flags.eshop.current_currency.sign }}
                                          {% endif %}
                                        </span>
                                      </span>
                                      {% if (not (entry.variants[0].compare_price == '0.00')) and (not (entry.variants[0].compare_price == '')) %}
                                      <span class="price-add">
                                        <span>
                                          <span class="price addCurrPrice">
                                            <s>{{ (entry.variants[0].compare_price * system_flags.eshop.currency[0].rate_from / system_flags.eshop.current_currency.rate_from)|number_format(2, '.', '') }}</s>
                                          </span>
                                          <s>{{ system_flags.eshop.current_currency.sign }}</s>
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
        </div>
        <div class="group-button-carousel">
          <button type="button" class="prev arrow jcarousel-prev jcarousel-prev-horizontal jcarousel-prev-disabled jcarousel-prev-disabled-horizontal" disabled="disabled" style="display: inline-block;">
            <span class="icon_arrow_p">
            </span>
          </button>
          <button type="button" class="next arrow jcarousel-next jcarousel-next-horizontal" style="display: inline-block;">
            <span class="icon_arrow_n">
            </span>
          </button>
        </div>
      </div>
    </section>
</div>
{% endif %}
