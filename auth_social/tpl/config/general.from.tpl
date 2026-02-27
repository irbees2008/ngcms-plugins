<form method="post" action="">
	<table class="table table-sm extra-config">
		<tr>
			<td colspan="2">
				<fieldset class="admGroup">
					<legend class="title">
						<b>{{ lang['auth_social:legend.title'] }}</b>
						{{ lang['auth_social:provider.vkid'] }}</legend>
					<table width="100%" border="0" class="content">
						<tbody>
							<tr>
								<td colspan="2" class="contentEntry2">
									<div class="alert alert-info" role="alert" style="margin:6px 0">
										<b>{{ lang['auth_social:instruction.title'] }}</b>
										<ol style="margin:8px 0 0 16px; padding-left: 16px;">
											<li>{{ lang['auth_social:vk.step1'] }}</li>
											<li>{{ lang['auth_social:vk.step2.prefix'] }}
												<code>{{ home }}/plugin/auth_social/vkid/</code>
												{{ lang['auth_social:vk.step2.suffix'] }}</li>
											<li>{{ lang['auth_social:vk.step3.prefix'] }}
												<code>email</code>.</li>
										</ol>
									</div>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">{{ lang['auth_social:label.app_id'] }}<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="vkid_client_id" type="text" size="50" value="{{ vkid_client_id }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">{{ lang['auth_social:label.client_secret'] }}<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="vkid_client_secret" type="text" size="50" value="{{ vkid_client_secret }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">{{ lang['auth_social:label.scope'] }}<br/></td>
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
						<b>{{ lang['auth_social:legend.title'] }}</b>
						{{ lang['auth_social:provider.yandex'] }}</legend>
					<table width="100%" border="0" class="content">
						<tbody>
							<tr>
								<td colspan="2" class="contentEntry2">
									<div class="alert alert-info" role="alert" style="margin:6px 0">
										<b>{{ lang['auth_social:instruction.title'] }}</b>
										<ol style="margin:8px 0 0 16px; padding-left: 16px;">
											<li>{{ lang['auth_social:yandex.step1']|raw }}</li>
											<li>{{ lang['auth_social:yandex.step2.prefix'] }}
												<code>{{ home }}/plugin/auth_social/?provider=yandex</code>
											</li>
											<li>{{ lang['auth_social:yandex.step3'] }}</li>
										</ol>
									</div>
								</td>
							</tr>
							<!-- YANDEX -->
							<tr>
								<td class="contentEntry1" valign="top">{{ lang['auth_social:label.client_id'] }}<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="yandex_client_id" type="text" size="50" value="{{ yandex_client_id }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">{{ lang['auth_social:label.client_secret'] }}<br/></td>
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
						<b>{{ lang['auth_social:legend.title'] }}</b>
						{{ lang['auth_social:provider.google'] }}</legend>
					<table width="100%" border="0" class="content">
						<tbody>
							<tr>
								<td colspan="2" class="contentEntry2">
									<div class="alert alert-info" role="alert" style="margin:6px 0">
										<b>{{ lang['auth_social:instruction.title'] }}</b>
										<ol style="margin:8px 0 0 16px; padding-left: 16px;">
											<li>{{ lang['auth_social:google.step1']|raw }}</li>
											<li>{{ lang['auth_social:google.step2.prefix'] }}
												<code>{{ home }}/plugin/auth_social/?provider=google</code>
											</li>
											<li>{{ lang['auth_social:google.step3'] }}</li>
										</ol>
									</div>
								</td>
							</tr>
							<!-- GOOGLE -->
							<tr>
								<td class="contentEntry1" valign="top">{{ lang['auth_social:label.client_id'] }}<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="google_client_id" type="text" size="50" value="{{ google_client_id }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">{{ lang['auth_social:label.client_secret'] }}<br/></td>
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
						<b>{{ lang['auth_social:legend.title'] }}</b>
						{{ lang['auth_social:provider.facebook'] }}</legend>
					<table width="100%" border="0" class="content">
						<tbody>
							<tr>
								<td colspan="2" class="contentEntry2">
									<div class="alert alert-info" role="alert" style="margin:6px 0">
										<b>{{ lang['auth_social:instruction.title'] }}</b>
										<ol style="margin:8px 0 0 16px; padding-left: 16px;">
											<li>{{ lang['auth_social:facebook.step1']|raw }}</li>
											<li>{{ lang['auth_social:facebook.step2.prefix'] }}
												<code>{{ home }}/plugin/auth_social/?provider=facebook</code>
											</li>
											<li>{{ lang['auth_social:facebook.step3'] }}</li>
										</ol>
									</div>
								</td>
							</tr>
							<!-- FACKEBOOK -->
							<tr>
								<td class="contentEntry1" valign="top">{{ lang['auth_social:label.client_id'] }}<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="facebook_client_id" type="text" size="50" value="{{ facebook_client_id }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">{{ lang['auth_social:label.client_secret'] }}<br/></td>
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
						<b>{{ lang['auth_social:legend.title'] }}</b>
						{{ lang['auth_social:provider.github'] }}</legend>
					<table width="100%" border="0" class="content">
						<tbody>
							<tr>
								<td colspan="2" class="contentEntry2">
									<div class="alert alert-info" role="alert" style="margin:6px 0">
										<b>{{ lang['auth_social:instruction.title'] }}</b>
										<ol style="margin:8px 0 0 16px; padding-left: 16px;">
											<li>{{ lang['auth_social:github.step1']|raw }}</li>
											<li>{{ lang['auth_social:github.step2.prefix'] }}
												<code>{{ home }}/plugin/auth_social/?provider=github</code>
											</li>
											<li>{{ lang['auth_social:github.step3']|raw }}</li>
											<li>{{ lang['auth_social:github.step4'] }}</li>
										</ol>
									</div>
								</td>
							</tr>
							<!-- GitHub -->
							<tr>
								<td class="contentEntry1" valign="top">{{ lang['auth_social:label.client_id'] }}<br/></td>
								<td class="contentEntry2" valign="top">
									<input name="github_client_id" type="text" size="50" value="{{ github_client_id }}"/>
								</td>
							</tr>
							<tr>
								<td class="contentEntry1" valign="top">{{ lang['auth_social:label.client_secret'] }}<br/></td>
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
		<div class="card-footer text-center"> <button type="submit" name="submit" class="btn btn-outline-success">{{ lang['auth_social:button.save'] }}</button>
	</div>
</form>
