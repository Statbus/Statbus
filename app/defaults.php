<?php

//Production safe defaults
//DO NOT EDIT THIS FILE!

$settings = [];

$settings['debug'] = false;

// Path settings
$settings['root'] = dirname(__DIR__);
$settings['temp'] = $settings['root'] . '/tmp';
$settings['public'] = $settings['root'] . '/public';

//If your application is not in the root directory of your webserver, set the
//relative path from the webserver root here
$settings['basepath'] = false;

$settings['app'] = [
  'name' => 'Statbus',
  'timezone' => 'UTC',
  'github' => 'nfreader/banbus',
];

$settings['twig'] = [
  'paths' => [
    __DIR__ . '/../templates',
  ],
  'options' => [
    'debug' => false,
    'cache_enabled' => true,
    'cache_path' => $settings['temp'] . '/twig',
  ],
];

// Error handler
$settings['error'] = [
  'display_error_details' => false,
  'log_errors' => false,
  'log_error_details' => false,
];

$settings['session'] = [
  'name' => 'statbus',
  'cache_expire' => 0,
  'cookie_lifetime' => 2592000,
  'gc_maxlifetime' => 604800
];

$settings['db'] = [
    'method' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'encoding' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    // Enable identifier quoting
    'quoteIdentifiers' => true,
    // Set to null to use MySQL servers timezone
    'timezone' => null,
    // Disable meta data cache
    'cacheMetadata' => false,
    // Disable query logging
    'log' => false,
    // PDO options
    'flags' => [
        PDO::ATTR_PERSISTENT               => true,
        PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_OBJ,
        PDO::ATTR_STRINGIFY_FETCHES        => false,
        PDO::ATTR_EMULATE_PREPARES         => false
    ]
];

$settings['alt_db'] = [
  'method' => 'mysql',
  'host' => 'localhost',
  'port' => 3306,
  'encoding' => 'utf8mb4',
  'collation' => 'utf8mb4_unicode_ci',
  'timezone' => null,
  'cacheMetadata' => false,
  'log' => false,
  'flags' => [
        PDO::ATTR_PERSISTENT               => true,
        PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_OBJ,
        PDO::ATTR_STRINGIFY_FETCHES        => false,
        PDO::ATTR_EMULATE_PREPARES         => false
    ]
];

$settings['results_per_page'] = 60;

$settings['servers'] = null;
$settings['ranks'] = null;

$settings['site_perms'] = [
  'tgdb' => 'ADMIN'
];

$settings['auth'] = [
  'discord' => [
    'clientId' => '',
    'clientSecret' => '',
    'scope' => ['identify', 'email'],
  ],
  'forum' => [
    'clientId' => '',
    'clientSecret' => '',
    'scope' => ['user','user.linked_accounts'],
  ]
];

return $settings;