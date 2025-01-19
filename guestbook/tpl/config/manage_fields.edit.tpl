   <form action="?mod=extra-config&plugin=guestbook&action=update_field&id={{ field.id }}" method="POST" name="fieldForm">
        <fieldset class="admGroup">
            <legend class="title">{{ lang['gbconfig']['f_edit_title'] }} {{ field.name }}</legend>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th scope="row" style="width: 20%;">{{ lang['gbconfig']['f_id'] }}</th>
                            <td>{{ field.id }}</td>
                        </tr>
                        <tr>
                            <th scope="row" style="width: 20%;">{{ lang['gbconfig']['f_name'] }}</th>
                            <td><input type="text" name="name" value="{{ field.name }}" class="form-control" size="50"/></td>
                        </tr>
                        <tr>
                            <th scope="row" style="width: 20%;">{{ lang['gbconfig']['f_placeholder'] }}</th>
                            <td><input type="text" name="placeholder" value="{{ field.placeholder }}" class="form-control" size="50"/></td>
                        </tr>
                        <tr>
                            <th scope="row" style="width: 20%;">{{ lang['gbconfig']['f_default_value'] }}</th>
                            <td><input type="text" name="default_value" value="{{ field.default_value }}" class="form-control" size="50"/></td>
                        </tr>
                        <tr>
                            <th scope="row" style="width: 20%;">{{ lang['gbconfig']['f_required'] }}</th>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" name="required" class="form-check-input" value="1" {% if field.required %}checked{% endif %}/>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>
        <div class="card-footer text-center">
            <button type="submit" name="submit" class="btn btn-outline-success">{{ lang['gbconfig']['btn_edit_field'] }}</button>
        </div>
    </form>
