<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Console Application',

	// preloading 'log' component
	'preload'=>array('log'),
    'import'=>array(
        'application.models.*',
		'application.components.*',
    ),

	// application components
	'components'=>array(
        'db'=>array(
            'class'=>'system.db.CDbConnection',
            'connectionString' => 'mysql:host=127.0.0.1;dbname=acmMdC',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => '12345',
            'charset' => 'utf8',
            'tablePrefix' => 'c_',
            'autoConnect' => false,
            'persistent' => true,
        ),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
	),
    'commandMap' => array(
        'cron' => array(
            'class' => 'application.components.CronCommand',
            'dbConnection' => 'db',
        ),
    ),
);