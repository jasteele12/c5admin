<?php
/*
--db-server= - specifies the location of your database server.
--db-username= - specifies the user for your database. --db-password= - specifies the password for your database user.
--db-database= - specifies the database to use for concrete5.
--admin-password= - password to use for your admin user.
--admin-email= - email to use for your admin user.
--starting-point= - the handle for the sample content starting point you wish to use. Optional. If none specified then "blank" will be used.
--target= - A path to where you want concrete5 installed. Optional. If omitted the current directory will be assumed.
--site= - specifies the site name.
--core= - A path to the core you want to use. Optional. If none is specified it will be looked for within the target directory. 



structure
core : /var/www/c5admin/core/5521/
website : /var/www/c5admin/websites/site1/
fichier d'install : /var/www/c5admin/install.php


Copie des répertoires vides pour le site

cp blocks/ config/ controllers/ css/ elements/ files/ helpers/ jobs/ js/ languages/ libraries/ mail/ models/ packages/ page_types/ single_pages/ themes/ tools/ updates/ index.php robots.txt -Rp /var/www/c5admin/websites/site1/ 

chmod 777 files
chmod 777 config
chmod 777 packages

fichier /concrete/controller/install.php, ligne à commenter : 
if (PHP_SAPI != 'cli') {
	$this->redirect('/');
}

création de l'alias

*/
if (!$_POST) {
?>
<form action="install.php" method="post">
<ul>
<li>db server : <input type="text" name="db_server" /></li>
<li>db username : <input type="text" name="db_username" /></li>
<li>db database : <input type="text" name="db_database" /></li>
<li>db password : <input type="text" name="db_password" /></li>
<li>admin password : <input type="text" name="admin_password" /></li>
<li>admin email : <input type="text" name="admin_email" /></li>
<!--<li>starting point : <input type="text" name="starting_point" /></li>-->
<li>target : <input type="text" name="target" value="/var/www/c5admin/websites/site1" /></li>
<li>sitename : <input type="text" name="site" value="my concrete5 website" /></li>
<li>core : <input type="text" name="core" value="/var/www/c5admin/core/5521/concrete" /></li>
<li><input type="submit" />
</ul>
</form>
<?php
}
else {
	define('FILE_PERMISSIONS_MODE', 0777);
	define('APP_VERSION_CLI_MINIMUM', '5.5..1');
	define('PHP_SAPI', 'cli');

	error_reporting(0);
	ini_set('display_errors', 0);
	define('C5_EXECUTE', true);

	# configuration
	$DB_SERVER = $_POST['db_server'];
	$DB_USERNAME = $_POST['db_username'];
	$DB_PASSWORD = $_POST['db_password'];
	$DB_DATABASE = $_POST['db_database'];
	$INSTALL_ADMIN_PASSWORD = $_POST['admin_password'];
	$INSTALL_ADMIN_EMAIL = $_POST['admin_email'];
	$INSTALL_STARTING_POINT = $_POST['starting_point'];
	$target = $_POST['target'];
	$site = $_POST['site'];
	$core = $_POST['core'];

	if (!$INSTALL_STARTING_POINT) {
		$INSTALL_STARTING_POINT = 'blank';
	}

	if ($target) {
		if (substr($target, 0, 1) == '/') {
			define('DIR_BASE', $target);
		} else { 
			define('DIR_BASE', dirname(__FILE__) . '/' . $target);
		}
	} else {
		define('DIR_BASE', dirname(__FILE__));
	}

	if ($core) {
		if (substr($core, 0, 1) == '/') {
			$corePath = $core;	
		} else {
			$corePath = dirname(__FILE__) . '/' . $core;
		}
	} else {
		$corePath = DIR_BASE . '/concrete';
	}

	if (!file_exists($corePath . '/config/version.php')) {
		die("ERROR: Invalid concrete5 core.\n");
	} else {
		include($corePath . '/config/version.php');
	}

	if (file_exists(DIR_BASE . '/config/site.php')) {
		die("ERROR: concrete5 is already installed.\n");
	}		

	## Startup check ##	
	require($corePath . '/config/base_pre.php');

	## Load the base config file ##
	require($corePath . '/config/base.php');


	## Load the database ##
	Loader::database();

	## Load required libraries ##
	Loader::library("cache");
	Loader::library('object');
	Loader::library('log');
	Loader::library('localization');
	Loader::library('request');
	Loader::library('events');
	Loader::library('model');
	Loader::library('item_list');
	Loader::library('view');
	Loader::library('controller');
	Loader::library('file/types');
	Loader::library('block_view');
	Loader::library('block_view_template');
	Loader::library('block_controller');
	Loader::library('attribute/view');
	Loader::library('attribute/controller');

	// UNCOMMENT FOR 5.5.2+
	# require($corePath . '/startup/file_permission_config.php');

	## Load required models ##
	Loader::model('area');
	Loader::model('global_area');
	Loader::model('attribute/key');
	Loader::model('attribute/value');
	Loader::model('attribute/category');
	Loader::model('attribute/set');
	Loader::model('attribute/type');
	Loader::model('block');
	Loader::model('custom_style');
	Loader::model('file');
	Loader::model('file_version');
	Loader::model('block_types');
	Loader::model('collection');
	Loader::model('collection_version');
	Loader::model('collection_types');
	Loader::model('config');
	Loader::model('groups');
	Loader::model('layout');  
	Loader::model('package');
	Loader::model('page');
	Loader::model('page_theme');
	Loader::model('composer_page');
	Loader::model('permissions');
	Loader::model('user');
	Loader::model('userinfo');
	Loader::model('task_permission');
	Loader::model('stack/model');

	## Setup timzone support
	require($corePath . '/startup/timezone.php'); // must be included before any date related functions are called (php 5.3 +)

	## Startup check, install ##	
	require($corePath . '/startup/magic_quotes_gpc_check.php');

	## Default routes for various content items ##
	require($corePath . '/config/theme_paths.php');

	## Load session handlers
	require($corePath . '/startup/session.php');

	## Startup check ##	
	require($corePath . '/startup/encoding_check.php');

	$cnt = Loader::controller("/install");
	$cnt->on_start();
	$fileWriteErrors = clone $cnt->fileWriteErrors;
	$e = Loader::helper('validation/error');

	// handle required items
	if (!$cnt->get('imageTest')) {
		$e->add(t('GD library must be enabled to install concrete5.'));
	}
	if (!$cnt->get('mysqlTest')) {
		$e->add($cnt->getDBErrorMsg());
	}
	if (!$cnt->get('xmlTest')) {
		$e->add(t('SimpleXML and DOM must be enabled to install concrete5.'));
	}
	if (!$cnt->get('phpVtest')) {
		$e->add(t('concrete5 requires PHP 5.2 or greater.'));
	}

	if (is_object($fileWriteErrors)) {
		$e->add($fileWriteErrors);
	}

	$_POST['SAMPLE_CONTENT'] = $INSTALL_STARTING_POINT;
	$_POST['DB_SERVER'] = $DB_SERVER;
	$_POST['DB_USERNAME'] = $DB_USERNAME;
	$_POST['DB_PASSWORD'] = $DB_PASSWORD;
	$_POST['DB_DATABASE'] = $DB_DATABASE;
	if ($site) {
		$_POST['SITE'] = $site;
	} else {
		$_POST['SITE'] = 'concrete5 Site';
	}
	$_POST['uPassword'] = $INSTALL_ADMIN_PASSWORD;
	$_POST['uPasswordConfirm'] = $INSTALL_ADMIN_PASSWORD;
	$_POST['uEmail'] = $INSTALL_ADMIN_EMAIL;

	if (version_compare($APP_VERSION, APP_VERSION_CLI_MINIMUM, '<')) {
		$e->add('Your version of concrete5 must be at least ' . APP_VERSION_CLI_MINIMUM . ' to use this installer.');
	}

	if ($e->has()) {
		foreach($e->getList() as $ei) {
			print "ERROR: " . $ei . "\n";
		}	
		die;
	}

	$cnt->configure($e);

	if ($e->has()) {
		foreach($e->getList() as $ei) {
			print "ERROR: " . $ei . "\n";
		}	
	} else {
		$spl = Loader::startingPointPackage($INSTALL_STARTING_POINT);
		require(DIR_CONFIG_SITE . '/site_install.php');
		require(DIR_CONFIG_SITE . '/site_install_user.php');
		$routines = $spl->getInstallRoutines();

		try {
			foreach($routines as $r) {
				print $r->getProgress() . '%: ' . $r->getText() . "\n";
				call_user_func(array($spl, $r->getMethod()));
			}
		} catch(Exception $ex) {
			print "ERROR: " . $ex->getMessage() . "\n";		
			$cnt->reset();
		}
	
		if (!isset($ex)) {
			Config::save('SEEN_INTRODUCTION', 1);
			print "Installation Complete!\n";
		}
	
	}
}
?>
