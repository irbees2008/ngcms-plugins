<style>
	.btn-link {
		margin-left: 3px;
		vertical-align: middle;
		text-decoration: none;
	}
</style>
 <form method="post" action="" name="form">
        <fieldset class="admGroup">
            <legend class="title">{{ lang['gbconfig']['message_edit_title'] }} {{ field.name }}</legend>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th scope="row"><label>{{ lang['gbconfig']['message_date'] }}</label></th>
                            <td><input type="text" id="cdate" name="cdate" value="{{ postdate|date('j.m.Y H:i') }}" class="form-control"/></td>
                        </tr>
                        <tr>
                            <th scope="row"><label>{{ lang['gbconfig']['message_ip'] }}</label></th>
                            <td>{{ ip }}</td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>{{ lang['gbconfig']['message_author'] }}
                                    <b style="color:red">{{ lang['gbconfig']['message_required'] }}</b>
                                </label>
                            </th>
                            <td><input type="text" name="author" value="{{ author }}" class="form-control"/></td>
                        </tr>
                        {% for field in fields %}
                            <tr>
                                <th scope="row">
                                    <label>{{ field.name }} {% if field.required %}
                                        <b style="color:red">{{ lang['gbconfig']['message_required'] }}</b>{% endif %}</label>
                                </th>
                                <td>
                                    <input type="text" name="{{ field.id }}" value="{{ field.value }}" class="form-control" {% if field.required %}required{% endif %}/>
                                </td>
                            </tr>
                        {% endfor %}
                        <tr>
                            <th scope="row">
                                <label>{{ lang['gbconfig']['message_content'] }}
                                    <b style="color:red">{{ lang['gbconfig']['message_required'] }}</b>
                                </label>
                            </th>
                            <td>
                                <textarea name="message" rows="8" class="form-control">{{ message }}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>{{ lang['gbconfig']['message_answer'] }}</label></th>
                            <td>
                                <textarea name="answer" rows="8" class="form-control">{{ answer }}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>{{ lang['gbconfig']['message_status'] }}</label></th>
                            <td>
                                <select name="status" class="form-control bfstatus">
                                    <option value="1" {% if status == '1' %}selected{% endif %}>{{ lang['gbconfig']['message_active'] }}</option>
                                    <option value="0" {% if status == '0' %}selected{% endif %}>{{ lang['gbconfig']['message_inactive'] }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-center">
                                <span class="right_s">
                                    <button type="reset" class="btn btn-secondary mr-2">{{ lang['gbconfig']['message_reset'] }}</button>
                                    <button name="submit" type="submit" class="btn btn-primary mr-2">{{ lang['gbconfig']['message_submit'] }}</button>
                                    <a onclick="return confirm('{{ lang['gbconfig']['message_confirm'] }}');" href="?mod=extra-config&plugin=guestbook&action=delete_message&id={{ id }}" class="btn btn-danger btn-link">
                                        <span>{{ lang['gbconfig']['message_delete'] }}</span>
                                    </a>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>

        {% if social %}
        <fieldset class="admGroup">
            <legend class="title">{{ lang['gbconfig']['message_social_title'] }}</legend>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <tbody>
                        {% if social.Vkontakte %}
                            <tr>
                                <th scope="row"><label>{{ lang['gbconfig']['message_vkontakte'] }}</label></th>
                                <td>
                                    <a href="{{ social.Vkontakte.link }}">{{ lang['gbconfig']['message_social_profile'] }}</a>
                                    <a href="{{ social.Vkontakte.photo }}">{{ lang['gbconfig']['message_social_avatar'] }}</a>
                                    <a onclick="return confirm('{{ lang['gbconfig']['message_social_confirm'] }}');" href="{{ php_self }}?mod=extra-config&plugin=guestbook&action=delete_social&id={{ id }}&soc=Vkontakte" class="btn btn-link text-danger">
                                        {{ lang['gbconfig']['message_social_delete'] }}
                                    </a>
                                </td>
                            </tr>
                        {% endif %}
                        {% if social.Facebook %}
                            <tr>
                                <th scope="row"><label>{{ lang['gbconfig']['message_facebook'] }}</label></th>
                                <td>
                                    <a href="{{ social.Facebook.link }}">{{ lang['gbconfig']['message_social_profile'] }}</a>
                                    <a href="{{ social.Facebook.photo }}">{{ lang['gbconfig']['message_social_avatar'] }}</a>
                                    <a onclick="return confirm('{{ lang['gbconfig']['message_social_confirm'] }}');" href="{{ php_self }}?mod=extra-config&plugin=guestbook&action=delete_social&id={{ id }}&soc=Facebook" class="btn btn-link text-danger">
                                        {{ lang['gbconfig']['message_social_delete'] }}
                                    </a>
                                </td>
                            </tr>
                        {% endif %}
                        {% if social.Google %}
                            <tr>
                                <th scope="row"><label>{{ lang['gbconfig']['message_google'] }}</label></th>
                                <td>
                                    <a href="{{ social.Google.link }}">{{ lang['gbconfig']['message_social_profile'] }}</a>
                                    <a href="{{ social.Google.photo }}">{{ lang['gbconfig']['message_social_avatar'] }}</a>
                                    <a onclick="return confirm('{{ lang['gbconfig']['message_social_confirm'] }}');" href="{{ php_self }}?mod=extra-config&plugin=guestbook&action=delete_social&id={{ id }}&soc=Google" class="btn btn-link text-danger">
                                        {{ lang['gbconfig']['message_social_delete'] }}
                                    </a>
                                </td>
                            </tr>
                        {% endif %}
                        {% if social.Instagram %}
                            <tr>
                                <th scope="row"><label>{{ lang['gbconfig']['message_instagram'] }}</label></th>
                                <td>
                                    <a href="{{ social.Instagram.link }}">{{ lang['gbconfig']['message_social_profile'] }}</a>
                                    <a href="{{ social.Instagram.photo }}">{{ lang['gbconfig']['message_social_avatar'] }}</a>
                                    <a onclick="return confirm('{{ lang['gbconfig']['message_social_confirm'] }}');" href="{{ php_self }}?mod=extra-config&plugin=guestbook&action=delete_social&id={{ id }}&soc=Instagram" class="btn btn-link text-danger">
                                        {{ lang['gbconfig']['message_social_delete'] }}
                                    </a>
                                </td>
                            </tr>
                        {% endif %}
                    </tbody>
                </table>
            </div>
        </fieldset>
        {% endif %}
    </form>
<script type="text/javascript">
	$("#cdate").datetimepicker({currentText: "DD.MM.YYYY HH:MM"});
</script>
