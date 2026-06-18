<?php
$handlerList = array (
  0 => 
  array (
    'pluginName' => 'uprofile',
    'handlerName' => 'edit',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/profile.html',
      'regex' => '#^/profile.html$#',
      'regexMap' => 
      array (
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
          1 => '/profile.html',
          2 => 0,
        ),
      ),
    ),
  ),
  1 => 
  array (
    'pluginName' => 'core',
    'handlerName' => 'login',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/login/',
      'regex' => '#^/login/$#',
      'regexMap' => 
      array (
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
          1 => '/login/',
          2 => 0,
        ),
      ),
    ),
  ),
  2 => 
  array (
    'pluginName' => 'core',
    'handlerName' => 'logout',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/logout/',
      'regex' => '#^/logout/$#',
      'regexMap' => 
      array (
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
          1 => '/logout/',
          2 => 0,
        ),
      ),
    ),
  ),
  3 => 
  array (
    'pluginName' => 'core',
    'handlerName' => 'registration',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/register/',
      'regex' => '#^/register/$#',
      'regexMap' => 
      array (
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
          1 => '/register/',
          2 => 0,
        ),
      ),
    ),
  ),
  4 => 
  array (
    'pluginName' => 'core',
    'handlerName' => 'activation',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/activate/[{userid}-{code}/]',
      'regex' => '#^/activate/(?:(\\d+)-(.+?)/){0,1}$#',
      'regexMap' => 
      array (
        1 => 'userid',
        2 => 'code',
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
          1 => '/activate/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 1,
          1 => 'userid',
          2 => 1,
        ),
        2 => 
        array (
          0 => 0,
          1 => '-',
          2 => 1,
        ),
        3 => 
        array (
          0 => 1,
          1 => 'code',
          2 => 1,
        ),
        4 => 
        array (
          0 => 0,
          1 => '/',
          2 => 1,
        ),
      ),
    ),
  ),
  5 => 
  array (
    'pluginName' => 'core',
    'handlerName' => 'lostpassword',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/lostpassword/[{userid}-{code}/]',
      'regex' => '#^/lostpassword/(?:(\\d+)-(.+?)/){0,1}$#',
      'regexMap' => 
      array (
        1 => 'userid',
        2 => 'code',
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
          1 => '/lostpassword/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 1,
          1 => 'userid',
          2 => 1,
        ),
        2 => 
        array (
          0 => 0,
          1 => '-',
          2 => 1,
        ),
        3 => 
        array (
          0 => 1,
          1 => 'code',
          2 => 1,
        ),
        4 => 
        array (
          0 => 0,
          1 => '/',
          2 => 1,
        ),
      ),
    ),
  ),
  6 => 
  array (
    'pluginName' => 'core',
    'handlerName' => 'plugin',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/plugin/{plugin}/[{handler}/]',
      'regex' => '#^/plugin/(.+?)/(?:(.+?)/){0,1}$#',
      'regexMap' => 
      array (
        1 => 'plugin',
        2 => 'handler',
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
          1 => '/plugin/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 1,
          1 => 'plugin',
          2 => 0,
        ),
        2 => 
        array (
          0 => 0,
          1 => '/',
          2 => 0,
        ),
        3 => 
        array (
          0 => 1,
          1 => 'handler',
          2 => 1,
        ),
        4 => 
        array (
          0 => 0,
          1 => '/',
          2 => 1,
        ),
      ),
    ),
  ),
  7 => 
  array (
    'pluginName' => 'news',
    'handlerName' => 'news',
    'flagPrimary' => true,
    'flagFailContinue' => true,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/news/{category}/{altname}.html',
      'regex' => '#^/news/(.+?)/(.+?).html$#',
      'regexMap' => 
      array (
        1 => 'category',
        2 => 'altname',
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
          1 => '/news/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 1,
          1 => 'category',
          2 => 0,
        ),
        2 => 
        array (
          0 => 0,
          1 => '/',
          2 => 0,
        ),
        3 => 
        array (
          0 => 1,
          1 => 'altname',
          2 => 0,
        ),
        4 => 
        array (
          0 => 0,
          1 => '.html',
          2 => 0,
        ),
      ),
    ),
  ),
  8 => 
  array (
    'pluginName' => 'news',
    'handlerName' => 'by.category',
    'flagPrimary' => true,
    'flagFailContinue' => true,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/news/{category}[/page-{page}]/',
      'regex' => '#^/news/(.+?)(?:/page-(\\d{1,4})){0,1}/$#',
      'regexMap' => 
      array (
        1 => 'category',
        2 => 'page',
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
          1 => '/news/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 1,
          1 => 'category',
          2 => 0,
        ),
        2 => 
        array (
          0 => 0,
          1 => '/page-',
          2 => 1,
        ),
        3 => 
        array (
          0 => 1,
          1 => 'page',
          2 => 1,
        ),
        4 => 
        array (
          0 => 0,
          1 => '/',
          2 => 0,
        ),
      ),
    ),
  ),
  9 => 
  array (
    'pluginName' => 'news',
    'handlerName' => 'by.category',
    'flagPrimary' => true,
    'flagFailContinue' => true,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/news/{category}/page-{page}/',
      'regex' => '#^/news/(.+?)/page-(\\d{1,4})/$#',
      'regexMap' => 
      array (
        1 => 'category',
        2 => 'page',
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
          1 => '/news/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 1,
          1 => 'category',
          2 => 0,
        ),
        2 => 
        array (
          0 => 0,
          1 => '/page-',
          2 => 0,
        ),
        3 => 
        array (
          0 => 1,
          1 => 'page',
          2 => 0,
        ),
        4 => 
        array (
          0 => 0,
          1 => '/',
          2 => 0,
        ),
      ),
    ),
  ),
  10 => 
  array (
    'pluginName' => 'static',
    'handlerName' => '',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/static/{altname}.html',
      'regex' => '#^/static/(.+?).html$#',
      'regexMap' => 
      array (
        1 => 'altname',
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
          1 => '/static/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 2,
          1 => 'altname',
          2 => 0,
        ),
        2 => 
        array (
          0 => 0,
          1 => '.html',
          2 => 0,
        ),
      ),
    ),
  ),
  11 => 
  array (
    'pluginName' => 'eshop',
    'handlerName' => 'order',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/eshop/order/',
      'regex' => '#^/eshop/order/$#',
      'regexMap' => 
      array (
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
          1 => '/eshop/order/',
          2 => 0,
        ),
      ),
    ),
  ),
  12 => 
  array (
    'pluginName' => 'eshop',
    'handlerName' => 'ebasket_list',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/eshop/ebasket_list/',
      'regex' => '#^/eshop/ebasket_list/$#',
      'regexMap' => 
      array (
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
          1 => '/eshop/ebasket_list/',
          2 => 0,
        ),
      ),
    ),
  ),
  13 => 
  array (
    'pluginName' => 'eshop',
    'handlerName' => 'xml_export',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/eshop/xml_export/',
      'regex' => '#^/eshop/xml_export/$#',
      'regexMap' => 
      array (
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
          1 => '/eshop/xml_export/',
          2 => 0,
        ),
      ),
    ),
  ),
  14 => 
  array (
    'pluginName' => 'eshop',
    'handlerName' => 'currency',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/eshop/currency/',
      'regex' => '#^/eshop/currency/$#',
      'regexMap' => 
      array (
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
          1 => '/eshop/currency/',
          2 => 0,
        ),
      ),
    ),
  ),
  15 => 
  array (
    'pluginName' => 'eshop',
    'handlerName' => 'compare',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/eshop/compare/',
      'regex' => '#^/eshop/compare/$#',
      'regexMap' => 
      array (
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
          1 => '/eshop/compare/',
          2 => 0,
        ),
      ),
    ),
  ),
  16 => 
  array (
    'pluginName' => 'eshop',
    'handlerName' => 'stocks',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/eshop/stocks/[page/{page}/]',
      'regex' => '#^/eshop/stocks/(?:page/(\\d{1,4})/){0,1}$#',
      'regexMap' => 
      array (
        1 => 'page',
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
          1 => '/eshop/stocks/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 0,
          1 => 'page/',
          2 => 1,
        ),
        2 => 
        array (
          0 => 1,
          1 => 'page',
          2 => 1,
        ),
        3 => 
        array (
          0 => 0,
          1 => '/',
          2 => 1,
        ),
      ),
    ),
  ),
  17 => 
  array (
    'pluginName' => 'news',
    'handlerName' => 'main',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/[page/{page}/]',
      'regex' => '#^/(?:page/(\\d{1,4})/){0,1}$#',
      'regexMap' => 
      array (
        1 => 'page',
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
          1 => '/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 0,
          1 => 'page/',
          2 => 1,
        ),
        2 => 
        array (
          0 => 1,
          1 => 'page',
          2 => 1,
        ),
        3 => 
        array (
          0 => 0,
          1 => '/',
          2 => 1,
        ),
      ),
    ),
  ),
  18 => 
  array (
    'pluginName' => 'eshop',
    'handlerName' => 'search',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/eshop/search/[page/{page}/]',
      'regex' => '#^/eshop/search/(?:page/(\\d{1,4})/){0,1}$#',
      'regexMap' => 
      array (
        1 => 'page',
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
          1 => '/eshop/search/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 0,
          1 => 'page/',
          2 => 1,
        ),
        2 => 
        array (
          0 => 1,
          1 => 'page',
          2 => 1,
        ),
        3 => 
        array (
          0 => 0,
          1 => '/',
          2 => 1,
        ),
      ),
    ),
  ),
  19 => 
  array (
    'pluginName' => 'eshop',
    'handlerName' => 'show',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/{alt}.html',
      'regex' => '#^/(.+?).html$#',
      'regexMap' => 
      array (
        1 => 'alt',
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
          1 => '/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 1,
          1 => 'alt',
          2 => 0,
        ),
        2 => 
        array (
          0 => 0,
          1 => '.html',
          2 => 0,
        ),
      ),
    ),
  ),
  20 => 
  array (
    'pluginName' => 'eshop',
    'handlerName' => '',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/[{alt}/][page/{page}/]',
      'regex' => '#^/(?:(.+?)/){0,1}(?:page/(\\d{1,4})/){0,1}$#',
      'regexMap' => 
      array (
        1 => 'alt',
        2 => 'page',
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
          1 => '/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 1,
          1 => 'alt',
          2 => 1,
        ),
        2 => 
        array (
          0 => 0,
          1 => '/',
          2 => 1,
        ),
        3 => 
        array (
          0 => 0,
          1 => 'page/',
          2 => 2,
        ),
        4 => 
        array (
          0 => 1,
          1 => 'page',
          2 => 2,
        ),
        5 => 
        array (
          0 => 0,
          1 => '/',
          2 => 2,
        ),
      ),
    ),
  ),
);
$handlerPrimary = array (
  'uprofile' => 
  array (
    'edit' => 
    array (
      0 => 0,
      1 => true,
    ),
  ),
  'core' => 
  array (
    'login' => 
    array (
      0 => 1,
      1 => true,
    ),
    'logout' => 
    array (
      0 => 2,
      1 => true,
    ),
    'registration' => 
    array (
      0 => 3,
      1 => true,
    ),
    'activation' => 
    array (
      0 => 4,
      1 => true,
    ),
    'lostpassword' => 
    array (
      0 => 5,
      1 => true,
    ),
    'plugin' => 
    array (
      0 => 6,
      1 => true,
    ),
  ),
  'news' => 
  array (
    'news' => 
    array (
      0 => 7,
      1 => true,
    ),
    'by.category' => 
    array (
      0 => 8,
      1 => true,
    ),
    'main' => 
    array (
      0 => 17,
      1 => true,
    ),
  ),
  'static' => 
  array (
    '' => 
    array (
      0 => 10,
      1 => true,
    ),
  ),
  'eshop' => 
  array (
    'order' => 
    array (
      0 => 11,
      1 => true,
    ),
    'ebasket_list' => 
    array (
      0 => 12,
      1 => true,
    ),
    'xml_export' => 
    array (
      0 => 13,
      1 => true,
    ),
    'currency' => 
    array (
      0 => 14,
      1 => true,
    ),
    'compare' => 
    array (
      0 => 15,
      1 => true,
    ),
    'stocks' => 
    array (
      0 => 16,
      1 => true,
    ),
    'search' => 
    array (
      0 => 18,
      1 => true,
    ),
    'show' => 
    array (
      0 => 19,
      1 => true,
    ),
    '' => 
    array (
      0 => 20,
      1 => true,
    ),
  ),
);