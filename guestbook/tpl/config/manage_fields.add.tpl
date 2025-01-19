<form action="?mod=extra-config&plugin=guestbook&action=insert_field" method="POST" name="fieldForm">
        <fieldset class="admGroup">
            <legend class="title">{{ lang['gbconfig']['f_add_title'] }}</legend>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th scope="row" style="width: 20%;">{{ lang['gbconfig']['f_id'] }}</th>
                            <td><input type="text" name="id" class="form-control" size="50"/></td>
                        </tr>
                        <tr>
                            <th scope="row" style="width: 20%;">{{ lang['gbconfig']['f_name'] }}</th>
                            <td><input type="text" name="name" class="form-control" size="50"/></td>
                        </tr>
                        <tr>
                            <th scope="row" style="width: 20%;">{{ lang['gbconfig']['f_placeholder'] }}</th>
                            <td><input type="text" name="placeholder" class="form-control" size="50"/></td>
                        </tr>
                        <tr>
                            <th scope="row" style="width: 20%;">{{ lang['gbconfig']['f_default_value'] }}</th>
                            <td><input type="text" name="default_value" class="form-control" size="50"/></td>
                        </tr>
                        <tr>
                            <th scope="row" style="width: 20%;">{{ lang['gbconfig']['f_required'] }}</th>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" name="required" class="form-check-input"/>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>
        <div class="card-footer text-center">
            <button type="submit" name="submit" class="btn btn-outline-success">{{ lang['gbconfig']['btn_add_field'] }}</button>
        </div>
    </form>