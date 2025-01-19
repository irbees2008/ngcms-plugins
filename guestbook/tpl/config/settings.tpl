<form method="post" action="">
        <fieldset class="admGroup">
            <legend class="title">{{ lang['gbconfig']['settings_title'] }}</legend>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_usmilies'] }}</th>
                            <td>
                                <select name="usmilies" class="form-control">
                                    <option value="1" {% if usmilies == '1' %} selected {% endif %}>{{ lang['gbconfig']['settings_yes'] }}</option>
                                    <option value="0" {% if usmilies == '0' %} selected {% endif %}>{{ lang['gbconfig']['settings_no'] }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_bbcodes'] }}</th>
                            <td>
                                <select name="ubbcodes" class="form-control">
                                    <option value="1" {% if ubbcodes == '1' %} selected {% endif %}>{{ lang['gbconfig']['settings_yes'] }}</option>
                                    <option value="0" {% if ubbcodes == '0' %} selected {% endif %}>{{ lang['gbconfig']['settings_no'] }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_minlength'] }}</th>
                            <td>
                                <input name="minlength" type="text" class="form-control" size="10" value="{{ minlength }}"/>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">
                                {{ lang['gbconfig']['settings_maxlength'] }}
                                <br><small>{{ lang['gbconfig']['settings_max_descr'] }}</small>
                            </th>
                            <td>
                                <input name="maxlength" type="text" class="form-control" size="10" value="{{ maxlength }}"/>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_perpage'] }}</th>
                            <td>
                                <input name="perpage" type="text" class="form-control" size="10" value="{{ perpage }}"/>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_order'] }}</th>
                            <td>
                                <select name="order" class="form-control">
                                    <option value="DESC" {% if order == 'DESC' %} selected {% endif %}>{{ lang['gbconfig']['settings_order_desc'] }}</option>
                                    <option value="ASC" {% if order == 'ASC' %} selected {% endif %}>{{ lang['gbconfig']['settings_order_asc'] }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_date'] }}</th>
                            <td>
                                <input name="date" type="text" class="form-control" size="10" value="{{ date }}"/>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_url'] }}</th>
                            <td>
                                <select name="url" class="form-control">
                                    <option value="0" {% if url == '0' %} selected {% endif %}>{{ lang['gbconfig']['settings_no'] }}</option>
                                    <option value="1" {% if url == '1' %} selected {% endif %}>{{ lang['gbconfig']['settings_yes'] }}</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>

        <fieldset class="admGroup">
            <legend class="title">{{ lang['gbconfig']['settings_access'] }}</legend>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_anonymous'] }}</th>
                            <td>
                                <select name="guests" class="form-control">
                                    <option value="1" {% if guests == '1' %} selected {% endif %}>{{ lang['gbconfig']['settings_yes'] }}</option>
                                    <option value="0" {% if guests == '0' %} selected {% endif %}>{{ lang['gbconfig']['settings_no'] }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_captcha'] }}</th>
                            <td>
                                <select name="ecaptcha" class="form-control">
                                    <option value="1" {% if ecaptcha == '1' %} selected {% endif %}>{{ lang['gbconfig']['settings_yes'] }}</option>
                                    <option value="0" {% if ecaptcha == '0' %} selected {% endif %}>{{ lang['gbconfig']['settings_no'] }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_public_key'] }}</th>
                            <td>
                                <input name="public_key" type="text" class="form-control" size="100" value="{{ public_key }}"/>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_private_key'] }}</th>
                            <td>
                                <input name="private_key" type="text" class="form-control" size="100" value="{{ private_key }}"/>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_approve'] }}</th>
                            <td>
                                <select name="approve_msg" class="form-control">
                                    <option value="1" {% if approve_msg == '1' %} selected {% endif %}>{{ lang['gbconfig']['settings_yes'] }}</option>
                                    <option value="0" {% if approve_msg == '0' %} selected {% endif %}>{{ lang['gbconfig']['settings_no'] }}</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>

        <fieldset class="admGroup">
            <legend class="title">{{ lang['gbconfig']['settings_admin'] }}</legend>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_perpage'] }}</th>
                            <td>
                                <input name="admin_count" type="text" class="form-control" size="10" value="{{ admin_count }}"/>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="align-middle">{{ lang['gbconfig']['settings_email'] }}</th>
                            <td>
                                <input name="send_email" type="text" class="form-control" size="100" value="{{ send_email }}"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>

        <div class="card-footer text-center">
            <input name="submit" type="submit" value="{{ lang['gbconfig']['settings_save'] }}" class="btn btn-outline-success"/>
        </div>
    </form>