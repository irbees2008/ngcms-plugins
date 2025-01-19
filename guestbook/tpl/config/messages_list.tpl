 <form action="/engine/admin.php?mod=extra-config&plugin=guestbook&action=modify" method="post" name="check_messages">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col" style="width: 5%;">{{ lang['gbconfig']['message_id'] }}</th>
                        <th scope="col" style="width: 15%;">{{ lang['gbconfig']['message_date'] }}</th>
                        <th scope="col" style="width: 20%;">{{ lang['gbconfig']['message_content'] }}</th>
                        <th scope="col" style="width: 20%;">{{ lang['gbconfig']['message_answer'] }}</th>
                        <th scope="col" style="width: 10%;">{{ lang['gbconfig']['message_ip'] }}</th>
                        <th scope="col" style="width: 10%;">{{ lang['gbconfig']['message_status'] }}</th>
                        <th colspan="2" style="width: 5%;" class="text-center">{{ lang['gbconfig']['message_action'] }}</th>
                        <th class="text-center align-middle">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="master_box" onclick="javascript:check_uncheck_all(check_messages)"/>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {% for entry in entries %}
                        <tr>
                            <td class="align-middle">{{ entry.id }}</td>
                            <td class="align-middle">{{ entry.postdate|date("d.m.Y H:i:s") }}</td>
                            <td class="align-middle">{{ entry.message }}</td>
                            <td class="align-middle">{{ entry.answer }}</td>
                            <td class="align-middle">{{ entry.ip }}</td>
                            <td class="align-middle">
                                {% if entry.status == '1' %}{{ lang['gbconfig']['message_active'] }}
                                {% elseif entry.status == '0' %}{{ lang['gbconfig']['message_inactive'] }}
                                {% endif %}
                            </td>
                            <td class="align-middle text-center">
                                <a href="?mod=extra-config&plugin=guestbook&action=edit_message&id={{ entry.id }}" title="{{ lang['gbconfig']['message_edit'] }}">
                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                </a>
                            </td>
                            <td class="align-middle text-center">
                                <a onclick="return confirm('{{ lang['gbconfig']['message_confirm'] }}');" href="?mod=extra-config&plugin=guestbook&action=delete_message&id={{ entry.id }}" title="{{ lang['gbconfig']['message_delete'] }}">
                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                </a>
                            </td>
                            <td class="align-middle text-center">
                                <div class="form-check">
                                    <input name="selected_message[]" value="{{ entry.id }}" class="form-check-input" type="checkbox"/>
                                </div>
                            </td>
                        </tr>
                    {% else %}
                        <tr>
                            <td colspan="10" class="text-center align-middle">{{ lang['gbconfig']['message_noent'] }}</td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>

        <div class="row mt-3">
            <div class="col text-right">
                <div style="text-align: left;">
                    <span>{{ lang['gbconfig']['message_options'] }}</span>
                    <select name="subaction" class="form-control" style="font: 12px Verdana, Courier, Arial; width: 230px;">
                        <option value="">{{ lang['gbconfig']['message_opt_default'] }}</option>
                        <option value="mass_approve">{{ lang['gbconfig']['message_opt_activate'] }}</option>
                        <option value="mass_forbidden">{{ lang['gbconfig']['message_opt_deactivate'] }}</option>
                        <option value="" style="background-color: #E0E0E0;" disabled="disabled">{{ lang['gbconfig']['message_opt_separator'] }}</option>
                        <option value="mass_delete">{{ lang['gbconfig']['message_opt_delete'] }}</option>
                    </select>
                    <button type="submit" class="btn btn-primary mt-2">{{ lang['gbconfig']['message_opt_submit'] }}</button>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col text-center">
                <p class="h5">{{ pagesss }}</p>
            </div>
        </div>
    </form>