<?php
// CLASS DEFINITION: User's profile filter
class p_uprofileFilter {
	// Show profile call :: preprocessor (call directly after profile fetch)
	function showProfilePre($userID, &$SQLrow) {
		return 1;
	}
	// Show profile call :: processor  (call after all processing is finished and before show)
	function showProfile($userID, $SQLrow, &$tvars) {
		return 1;
	}
	// Edit profile FORM call :: preprocessor (call directly after profile fetch)
	function editProfileFormPre($userID, &$SQLrow) {
		return 1;
	}
	// Edit profile FORM call :: processor  (call after all processing is finished and before show)
	function editProfileForm($userID, $SQLrow, &$tvars) {
		return 1;
	}
	// Edit profile call :: processor  (call after all processing is finished and before real SQL update)
	function editProfile($userID, $SQLrow, &$SQLnew) {
		return 1;
	}
	// Edit profile call :: notifier (call after successful editing )
	function editProfileNotify($userID, $SQLrow, &$SQLnew) {
		return 1;
	}
}
// Returns array
// 0 - Status [ 0 - no avatar, 1 - have avatar ]
// 1 - Avatar URL
function userGetAvatar($urow) {
	global $config, $TemplateCache;
	// Preload template configuration variables
	templateLoadVariables();
	// Use default <noavatar> file
	// - Check if noavatar is defined on template level
	$tplVars = $TemplateCache['site']['#variables'];
	$noAvatarURL = (isset($tplVars['configuration']) && is_array($tplVars['configuration']) && isset($tplVars['configuration']['noAvatarImage']) && $tplVars['configuration']['noAvatarImage']) ? (tpl_url . "/" . $tplVars['configuration']['noAvatarImage']) : (avatars_url . "/noavatar.gif");
	// If avatar is set
	if ($urow['avatar'] != '') {
		return array(1, avatars_url . '/' . ((preg_match('/^' . $urow['id'] . '\./', $urow['avatar'])) ? ($urow['id'] . '.') : '') . $urow['avatar']);
	}
	// Use GRAVATAR (if set)
	if ($config['avatars_gravatar']) {
		$avatar = 'http://www.gravatar.com/avatar/' . md5(strtolower($urow['mail'])) . '.jpg?s=' . $config['avatar_wh'] . '&d=' . urlencode($noAvatarURL);
		return array(1, $avatar);
	} else {
		$avatar = $noAvatarURL;
		return array(0, $avatar);
	}
}
