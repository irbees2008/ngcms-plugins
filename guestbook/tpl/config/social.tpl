<form
	method="post" action="">
	<!-- VK -->
	<fieldset class="admGroup">
		<legend class="title">{{ lang['gbconfig']['social_vkontakte'] }}</legend>
		<table width="100%" border="0" class="content">
			<tr>
				<td class="contentEntry1" valign="top">{{ lang['gbconfig']['social_client_id'] }}</td>
				<td class="contentEntry2" valign="top">
					<input name="vk_client_id" type="text" size="50" value="{{ vk_client_id }}"/>
				</td>
			</tr>
			<tr>
				<td class="contentEntry1" valign="top">{{ lang['gbconfig']['social_client_secret'] }}</td>
				<td class="contentEntry2" valign="top">
					<input name="vk_client_secret" type="text" size="50" value="{{ vk_client_secret }}"/>
				</td>
			</tr>
		</table>
	</fieldset>

	<!-- FACEBOOK -->
	<fieldset class="admGroup">
		<legend class="title">{{ lang['gbconfig']['social_facebook'] }}</legend>
		<table width="100%" border="0" class="content">
			<tr>
				<td class="contentEntry1" valign="top">{{ lang['gbconfig']['social_client_id'] }}</td>
				<td class="contentEntry2" valign="top">
					<input name="facebook_client_id" type="text" size="50" value="{{ facebook_client_id }}"/>
				</td>
			</tr>
			<tr>
				<td class="contentEntry1" valign="top">{{ lang['gbconfig']['social_client_secret'] }}</td>
				<td class="contentEntry2" valign="top">
					<input name="facebook_client_secret" type="text" size="50" value="{{ facebook_client_secret }}"/>
				</td>
			</tr>
		</table>
	</fieldset>

	<!-- GOOGLE -->
	<fieldset class="admGroup">
		<legend class="title">{{ lang['gbconfig']['social_google'] }}</legend>
		<table width="100%" border="0" class="content">
			<tr>
				<td class="contentEntry1" valign="top">{{ lang['gbconfig']['social_client_id'] }}</td>
				<td class="contentEntry2" valign="top">
					<input name="google_client_id" type="text" size="50" value="{{ google_client_id }}"/>
				</td>
			</tr>
			<tr>
				<td class="contentEntry1" valign="top">{{ lang['gbconfig']['social_client_secret'] }}</td>
				<td class="contentEntry2" valign="top">
					<input name="google_client_secret" type="text" size="50" value="{{ google_client_secret }}"/>
				</td>
			</tr>
		</table>
	</fieldset>

	<!-- INSTAGRAM -->
	<fieldset class="admGroup">
		<legend class="title">{{ lang['gbconfig']['social_instagram'] }}</legend>
		<table width="100%" border="0" class="content">
			<tr>
				<td class="contentEntry1" valign="top">{{ lang['gbconfig']['social_client_id'] }}</td>
				<td class="contentEntry2" valign="top">
					<input name="instagram_client_id" type="text" size="50" value="{{ instagram_client_id }}"/>
				</td>
			</tr>
			<tr>
				<td class="contentEntry1" valign="top">{{ lang['gbconfig']['social_client_secret'] }}</td>
				<td class="contentEntry2" valign="top">
					<input name="instagram_client_secret" type="text" size="50" value="{{ instagram_client_secret }}"/>
				</td>
			</tr>
		</table>
	</fieldset>

	<div class="card-footer text-center">

		<input name="submit" type="submit" value="{{ lang['gbconfig']['settings_save'] }}" class="btn btn-outline-success"/>

	</div>

</form>
