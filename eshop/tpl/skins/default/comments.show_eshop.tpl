    {% for entry in entries %}
                <li>
                <input type="hidden" name="comment_item_id" value="{{entry.id}}">
                <div class="o_h global-frame-comment-sub1">
                    <div class="author-data-comment author-data-comment-sub1">
                        <span class="f-s_0 frame-autor-comment"><span class="icon_comment"></span><span class="author-comment">{{entry.author}}</span></span>
                        <span class="date-comment">
                            <span class="day">{{entry.date|date('d')}} </span>
                            <span class="month">{{entry.date|date('m')}} </span>
                            <span class="year">{{entry.date|date('Y')}} </span>
                        </span>
                    </div>
                    <!--
                    <div class="frame-mark">
                        <div class="mark-pr">
                            <div class="star-small d_i-b">
                                <div class="productRate star-small">
                                    <div style="width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="func-button-comment">
                            <span class="btn like">
                                <button type="button" class="usefullyes" data-comid="{{entry.id}}">
                                    <span class="icon_like"></span>
                                    <span class="text-el d_l_1">Полезно <span class="yesholder96">(2)</span></span>
                                </button>
                            </span>
                            <span class="btn dis-like">
                                <button type="button" class="usefullno" data-comid="{{entry.id}}">
                                    <span class="icon_dislike"></span>
                                    <span class="text-el d_l_1">Не полезно <span class="noholder96">(5)</span></span>
                                </button>
                            </span>
                        </div>
                    </div>
                    -->
                    <div class="frame-comment-sub1">
                        <div class="frame-comment">
                            <p>{{entry.commenttext}}</p>
                        </div>
                        <!--
                        <div class="btn">
                            <button type="button" data-rel="cloneAddPaste" data-parid="{{entry.id}}">
                                <span class="icon_comment"></span>
                                <span class="text-el d_l_1 f-s_11">Ответить</span>
                            </button>
                        </div>
                        -->
                    </div>
                </div>
                <!--
                <div data-place="{{entry.id}}"></div>
                <div class="btn-all-comments">
                    <button type="button"><span class="text-el" data-hide="<span class=&quot;d_l_1&quot;>Скрыть</span> &#8593;" data-show="<span class=&quot;d_l_1&quot;>Смотреть все ответы</span> &#8595;"></span></button>
                </div>
                -->
            </li>
    {% endfor %}
