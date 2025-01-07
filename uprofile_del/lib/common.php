<?php


function create_uprofile_del_urls()
{

 			$ULIB = new urlLibrary();
			$ULIB->loadConfig();
			$ULIB->registerCommand('uprofile_del', 'user_id',
				array ('vars' =>
						array(	'id' => array('matchRegex' => '\d+', 'descr' => array('russian' => 'ИД пользователя')),
						),
						'descr'	=> array ('russian' => 'Удалить свой аккаунт'),
				)
			);
			$ULIB->saveConfig();
			
			$UHANDLER = new urlHandler();
			$UHANDLER->loadConfig();
			$UHANDLER->registerHandler(0,
				array (
				'pluginName' => 'uprofile_del',
				'handlerName' => 'user_id',
				'flagPrimary' => true,
				'flagFailContinue' => false,
				'flagDisabled' => false,
				'rstyle' => 
				array (
				  'rcmd' => '/uprofile_del/user_id/{id}/',
				  'regex' => '#^/uprofile_del/user_id/(\\d+)/$#',
				  'regexMap' => 
				  array (
					1 => 'id',
				  ),
				  'reqCheck' => 
				  array (
				  ),
				  'setVars' => 
				  array (
				  ),
				  'genrMAP' => 
				  array (
					0 => 
					array (
					  0 => 0,
					  1 => '/uprofile_del/user_id/',
					  2 => 0,
					),
					1 => 
					array (
					  0 => 1,
					  1 => 'id',
					  2 => 0,
					),
					2 => 
					array (
					  0 => 0,
					  1 => '/',
					  2 => 0,
					),
				  ),
				),
			  )
			);
    $UHANDLER->saveConfig();
}

function remove_uprofile_del_urls()
{
    $ULIB = new urlLibrary();
    $ULIB->loadConfig();
    $ULIB->removeCommand('uprofile_del', 'user_id');
    $ULIB->saveConfig();
    $UHANDLER = new urlHandler();
    $UHANDLER->loadConfig();
    $UHANDLER->removePluginHandlers('uprofile_del', 'user_id');
    $UHANDLER->saveConfig();
}
