<form method="post" action="">
        <input type="hidden" name="mod" value="extra-config"/>
        <input type="hidden" name="plugin" value="guestbook"/>
        <input type="hidden" name="action" value="save_fields"/>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col" style="width: 10%;">{{ lang['gbconfig']['f_id'] }}</th>
                        <th scope="col" style="width: 15%;">{{ lang['gbconfig']['f_name'] }}</th>
                        <th scope="col" style="width: 30%;">{{ lang['gbconfig']['f_placeholder'] }}</th>
                        <th scope="col" style="width: 30%;">{{ lang['gbconfig']['f_default_value'] }}</th>
                        <th scope="col" style="width: 10%;">{{ lang['gbconfig']['f_required'] }}</th>
                        <th colspan="2" scope="col" style="width: 5%;" class="text-center">{{ lang['gbconfig']['actions_title'] }}</th>
                    </tr>
                </thead>
                <tbody>
                    {% for entry in entries %}
                        <tr class="align-middle">
                            <td>{{ entry.id }}</td>
                            <td>{{ entry.name }}</td>
                            <td>{{ entry.placeholder }}</td>
                            <td>{{ entry.default_value }}</td>
                            <td>{% if entry.required %}{{ lang['gbconfig']['settings_yes'] }}{% else %}{{ lang['gbconfig']['settings_no'] }}{% endif %}</td>
                            <td class="text-center">
                                <a href="?mod=extra-config&plugin=guestbook&action=edit_field&id={{ entry.id }}" title="{{ lang['gbconfig']['actions_edit'] }}">
                                     <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                <a onclick="return confirm('{{ lang['gbconfig']['actions_confirm'] }} {{ entry.id }}?');" href="?mod=extra-config&plugin=guestbook&action=drop_field&id={{ entry.id }}" title="{{ lang['gbconfig']['actions_drop'] }}">
                                     <i class="fa fa-trash-o" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>

        <div class="card-footer text-center">
            <a href="?mod=extra-config&plugin=guestbook&action=add_field" class="btn btn-outline-success">{{ lang['gbconfig']['btn_add_field'] }}</a>
        </div>
    </form>