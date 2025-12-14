<form method="post" action="">
	<table class="table table-sm extra-config">
		<tr>
			<td colspan="2">
				<fieldset class="admGroup">
					<legend class="title">
						<b>Настройки авторизации
						</b>VK ID</legend>
					<table width="100%" border="0" class="content">
						<tbody>
							<tr>
								<td colspan="2" class="contentEntry2">
									<div class="alert alert-info" role="alert" style="margin:6px 0">
										<b>Инструкция:</b>
										<ol style="margin:8px 0 0 16px; padding-left: 16px;">
											<li>Создайте приложение VK ID (id.vk.com) и получите App ID и Secret.</li>
											<li>Укажите Redirect URI:
												<code>{{ home }}/plugin/auth_social/vkid/</code>
												(без query‑параметров).</li>
											<li>Рекомендуемый scope:
												<code>email</code>.</li>
										</ol>
									</div>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">App ID (Client ID)<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="vkid_client_id" type="text" size="50" value="{{ vkid_client_id }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">Client Secret<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="vkid_client_secret" type="text" size="50" value="{{ vkid_client_secret }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">Scope<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="vkid_scope" type="text" size="50" value="{{ vkid_scope|default('email') }}"/>
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<fieldset class="admGroup">
					<legend class="title">
						<b>Настройки авторизации
						</b>Yandex</legend>
					<table width="100%" border="0" class="content">
						<tbody>
							<tr>
								<td colspan="2" class="contentEntry2">
									<div class="alert alert-info" role="alert" style="margin:6px 0">
										<b>Инструкция:</b>
										<ol style="margin:8px 0 0 16px; padding-left: 16px;">
											<li>Создайте приложение в
												<a href="https://oauth.yandex.ru/client/new" target="_blank" rel="noopener">Яндекс OAuth</a>.</li>
											<li>Redirect URI:
												<code>{{ home }}/plugin/auth_social/?provider=yandex</code>
											</li>
											<li>Включите доступ к email (если требуется) и сохраните Client ID/Secret.</li>
										</ol>
									</div>
								</td>
							</tr>
							<!-- YANDEX -->
							<tr>
								<td class="contentEntry1" valign="top">Client ID<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="yandex_client_id" type="text" size="50" value="{{ yandex_client_id }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">Client Secret<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="yandex_client_secret" type="text" size="50" value="{{ yandex_client_secret }}"/>
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<fieldset class="admGroup">
					<legend class="title">
						<b>Настройки авторизации
						</b>Google</legend>
					<table width="100%" border="0" class="content">
						<tbody>
							<tr>
								<td colspan="2" class="contentEntry2">
									<div class="alert alert-info" role="alert" style="margin:6px 0">
										<b>Инструкция:</b>
										<ol style="margin:8px 0 0 16px; padding-left: 16px;">
											<li>В
												<a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">Google Cloud Console</a>
												создайте OAuth 2.0 Client ID.</li>
											<li>Authorized redirect URIs:
												<code>{{ home }}/plugin/auth_social/?provider=google</code>
											</li>
											<li>Сохраните Client ID и Client Secret и вставьте ниже.</li>
										</ol>
									</div>
								</td>
							</tr>
							<!-- GOOGLE -->
							<tr>
								<td class="contentEntry1" valign="top">Client ID<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="google_client_id" type="text" size="50" value="{{ google_client_id }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">Client Secret<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="google_client_secret" type="text" size="50" value="{{ google_client_secret }}"/>
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<fieldset class="admGroup">
					<legend class="title">
						<b>Настройки авторизации
						</b>Facebook</legend>
					<table width="100%" border="0" class="content">
						<tbody>
							<tr>
								<td colspan="2" class="contentEntry2">
									<div class="alert alert-info" role="alert" style="margin:6px 0">
										<b>Инструкция:</b>
										<ol style="margin:8px 0 0 16px; padding-left: 16px;">
											<li>Создайте приложение на
												<a href="https://developers.facebook.com/apps" target="_blank" rel="noopener">Facebook Developers</a>.</li>
											<li>Укажите Valid OAuth Redirect URIs:
												<code>{{ home }}/plugin/auth_social/?provider=facebook</code>
											</li>
											<li>Скопируйте App ID (Client ID) и App Secret (Client Secret) сюда.</li>
										</ol>
									</div>
								</td>
							</tr>
							<!-- FACKEBOOK -->
							<tr>
								<td class="contentEntry1" valign="top">Client ID<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="facebook_client_id" type="text" size="50" value="{{ facebook_client_id }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">Client Secret<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="facebook_client_secret" type="text" size="50" value="{{ facebook_client_secret }}"/>
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<fieldset class="admGroup">
					<legend class="title">
						<b>Настройки авторизации
						</b>GitHub</legend>
					<table width="100%" border="0" class="content">
						<tbody>
							<tr>
								<td colspan="2" class="contentEntry2">
									<div class="alert alert-info" role="alert" style="margin:6px 0">
										<b>Инструкция:</b>
										<ol style="margin:8px 0 0 16px; padding-left: 16px;">
											<li>Откройте
												<a href="https://github.com/settings/developers" target="_blank" rel="noopener">GitHub Developer settings</a>
												→ New OAuth App.</li>
											<li>Authorization callback URL:
												<code>{{ home }}/plugin/auth_social/?provider=github</code>
											</li>
											<li>Scopes:
												<code>read:user</code>,
												<code>user:email</code>
												(для получения e‑mail).</li>
											<li>Скопируйте Client ID/Secret в поля ниже.</li>
										</ol>
									</div>
								</td>
							</tr>
							<!-- GitHub -->
							<tr>
								<td class="contentEntry1" valign="top">Client ID<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="github_client_id" type="text" size="50" value="{{ github_client_id }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">Client Secret<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="github_client_secret" type="text" size="50" value="{{ github_client_secret }}"/>
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
	</table>
	<!--
			<tr>
			<td class="contentEntry1" valign=top>Redirect URI<br /></td>
			<td class="contentEntry2" valign=top>
			<input name="vk_redirect_uri" type="text" size="50" value="{{ vk_redirect_uri }}" />
			</td>
			</tr>
			-->
	<!--
			<tr>
			<td class="contentEntry1" valign=top>Redirect URI<br /></td>
			<td class="contentEntry2" valign=top>
			<input name="odnoklassniki_redirect_uri" type="text" size="50" value="{{ odnoklassniki_redirect_uri }}" />
			</td>
			</tr>
			-->
	<!--
			<tr>
			<td class="contentEntry1" valign=top>Redirect URI<br /></td>
			<td class="contentEntry2" valign=top>
			<input name="mailru_redirect_uri" type="text" size="50" value="{{ mailru_redirect_uri }}" />
			</td>
			</tr>
			-->
	<!--
			<tr>
			<td class="contentEntry1" valign=top>Redirect URI<br /></td>
			<td class="contentEntry2" valign=top>
			<input name="yandex_redirect_uri" type="text" size="50" value="{{ yandex_redirect_uri }}" />
			</td>
			</tr>
			-->
	<!--
			<tr>
			<td class="contentEntry1" valign=top>Redirect URI<br /></td>
			<td class="contentEntry2" valign=top>
			<input name="google_redirect_uri" type="text" size="50" value="{{ google_redirect_uri }}" />
			</td>
			</tr>
			-->
	<!--
			<tr>
			<td class="contentEntry1" valign=top>Redirect URI<br /></td>
			<td class="contentEntry2" valign=top>
			<input name="facebook_redirect_uri" type="text" size="50" value="{{ facebook_redirect_uri }}" />
			</td>
			</tr>
			-->
		<div class="card-footer text-center"> <button type="submit" name="submit" class="btn btn-outline-success">Сохранить изменения</button>
	</div>
</form>
