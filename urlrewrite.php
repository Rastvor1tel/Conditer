<?php
$arUrlRewrite=array (
  1 => 
  array (
    'CONDITION' => '#^/online/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1',
    'ID' => NULL,
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  14 => 
  array (
    'CONDITION' => '#^/video/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1&videoconf',
    'ID' => 'bitrix:im.router',
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  11 => 
  array (
    'CONDITION' => '#^={SITE_DIR."personal/buyer/"}#',
    'RULE' => '',
    'ID' => 'bitrix:sale.personal.profile',
    'PATH' => '/personal/buyer/index.php',
    'SORT' => 100,
  ),
  3 => 
  array (
    'CONDITION' => '#^\\/?\\/mobileapp/jn\\/(.*)\\/.*#',
    'RULE' => 'componentName=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/mobileapp/jn.php',
    'SORT' => 100,
  ),
  5 => 
  array (
    'CONDITION' => '#^/bitrix/services/ymarket/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/bitrix/services/ymarket/index.php',
    'SORT' => 100,
  ),
  2 => 
  array (
    'CONDITION' => '#^/online/(/?)([^/]*)#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  10 => 
  array (
    'CONDITION' => '#^/sotbitrest/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/bitrix/services/sotbitrest/index.php',
    'SORT' => 100,
  ),
  13 => 
  array (
    'CONDITION' => '#^/documents/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/documents/index.php',
    'SORT' => 100,
  ),
  12 => 
  array (
    'CONDITION' => '#^/orders/#',
    'RULE' => '',
    'ID' => 'bitrix:sale.personal.order',
    'PATH' => '/orders/index.php',
    'SORT' => 100,
  ),
  4 => 
  array (
    'CONDITION' => '#^/rest/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/bitrix/services/rest/index.php',
    'SORT' => 100,
  ),
);
