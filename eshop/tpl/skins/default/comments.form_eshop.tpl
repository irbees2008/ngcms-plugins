<div class="drop comments-main-form  active inherit" style="display: block;">
        <div class="frame-comments layout-highlight">
            <div class="title-comment">
                <div class="title">Оставить отзыв</div>
            </div>
            <!-- Start of new comment fild -->
            <div class="form-comment main-form-comments">
                <div class="inside-padd">
                    
                    <div class="error_text">
                        <div class="msg js-msg">
                            <div class="error error">
                                <span class="icon_info"></span>
                                <div class="text-el"><p>Поле Комментарий является обязательным.</p></div>
                            </div>
                        </div>
                    </div>
                    
                        <div class="mainPlace"></div>

                        <div class="frame-label">
                            <div class="frame-form-field o_h">
                                {% if not (global.user) %}
                                <label style="width: 36%;float:left;margin-right: 4%;">
                                    <span class="frame-form-field">
                                        <input type="text" name="comment_author" id="comment_author" value="" placeholder="Ваше имя">
                                    </span>
                                </label>
                                <label style="width: 36%;float:left;margin-right: 2%;">
                                    <span class="frame-form-field">
                                        <input type="text" name="comment_email" id="comment_email" value="" placeholder="Электронная почта">
                                    </span>
                                </label>
                                {% endif %}
                                <!-- Start star reiting -->
                                <!--
                                <div class="f_l" style="line-height: 32px;">
                                    <span class="d_i-b v-a_m">Поставьте оценку</span>
                                    <div class="d_i-b v-a_m">
                                        <div class="star">
                                            <div class="productRate star-big clicktemprate">
                                                <div class="for_comment" style="width: 0%"></div>
                                                <input class="ratec" name="ratec" type="hidden" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                -->
                                <!-- End star reiting -->

                            </div>
                        </div>


                        <label>
                            <span class="frame-form-field">
                                <textarea name="comment_text" id="comment_text" class="comment_text" placeholder="Текст отзыва"></textarea>
                            </span>
                        </label>

                        
                        <div class="frame-label">
                            <span class="frame-form-field">
                                <div class="btn-form">
                                    <input type="button" value="Комментировать" id="add_comment" data-id="{{id}}">
                                </div>
                            </span>
                        </div>
                </div>
                <!-- End of new comment fild -->
            </div>
        </div>
</div>

<script>
$(document).ready(function(){

    $("#add_comment").click(function() {

        rpcEshopRequest('eshop_comments_add', { 'comment_author' : $('#comment_author').val(), 'comment_email' : $('#comment_email').val(), 'comment_text' : $('#comment_text').val(), 'product_id' : {{id}} }, function (resTX) {
            if ((resTX['data']['eshop_comments']>0)&&(resTX['data']['eshop_comments'] < 100)) {
                $(".error_text").html("<div class='msg js-msg'><div class='error error'><span class='icon_info'></span><div class='text-el'><p>"+resTX['data']['eshop_comments_text']+"</p></div></div></div>");
                $(".product-comment").html(""+resTX['data']['eshop_comments_show']+"");
            } else {
                $(".error_text").html("");
                $("#comment_text").val("");
                $(".product-comment").html(""+resTX['data']['eshop_comments_show']+"");
            }
        });

    });

    rpcEshopRequest('eshop_comments_show', {'product_id' : {{id}}}, function (resTX) {
        $(".error_text").html("");
        $(".product-comment").html(""+resTX['data']['eshop_comments_show']+"");
    });

});

</script>
