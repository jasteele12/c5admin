<?php
/*
* This file is part of the c5admin package.
*
* (c) Nicolas Lœuillet <nicolas.loeuillet@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

define('TARGET_FOLDER', '/home/coteo/www/websites/'); # don't forget trailing slash

if (!$_POST) {
    // Modify $core_array depending on your configuration
    $core_array = array (
        '/home/coteo/c5core/concrete5.5.2.1' => 'concrete 5.5.2.1'
    );
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>c5admin - Create easily and quickly a new Concrete5 website</title>
        <link rel="stylesheet" media="screen" href="ecran.css">
    </head>
    <body>
        <h1>c5admin</h1>
        <div>
            Please fill the fields below to generate a new Concrete5 website. Fields with * are required. 
        </div>
        <form action="install.php" method="post">
            <fieldset>
                <legend>Database</legend>
                <ul>
                    <li><label for="db_server">Server * :</label> <input required type="text" name="db_server" id="db_server" placeholder="localhost" /></li>
                    <li><label for="db_username">Username * :</label> <input required type="text" name="db_username" id="db_username" placeholder="root" /></li>
                    <li>
                        <label for="db_database">Database name * :</label> <input required type="text" name="db_database" id="db_database" placeholder="concrete5" />
                        <div class="info">Database must exists.</div>
                    </li>
                    <li><label for="db_password">Password :</label> <input type="password" name="db_password" id="db_password" placeholder="*****" /></li>
                </ul>
            </fieldset>
            <fieldset>
                <legend>Admin account</legend>
            <ul>
                <li><label for="admin_password">Password * :</label> <input required type="password" name="admin_password" id="admin_password" placeholder="*****" /></li>
                <li><label for="admin_email">Email * :</label> <input required type="email" name="admin_email" id="admin_email" placeholder="contact@mywebsite.com" /></li>
            </ul>
            </fieldset>
            <fieldset>
                <legend>Other parameters</legend>
                <ul>
                    <!--<li>starting point : <input type="text" name="starting_point" /><div class="info">the handle for the sample content starting point you wish to use. Optional. If none specified then "blank" will be used.</div></li>-->
                    <li>
                        <!--<label for="target">Target :</label> 
                        <select name="target" id="target">
                        <?php 
                        //foreach ($target_array as $path => $name) {
                        //    echo '<option value="'.$path.'">'.$name.'</option>';
                        //}
                        ?>
                        </select>-->
                        <label for="folder">Folder :</label> <input required type="text" name="folder" id="folder" />
                        <div class="info">Name of your website folder. The script will create the folder in <?=TARGET_FOLDER?>.</div>
                    </li>
                    <li><label for="site">Sitename :</label> <input type="text" name="site" id="site" value="my new concrete5 website" /></li>
                    <li>
                        <label for="core">Core :</label>
                        <select name="core" id="core">
                        <?php 
                        foreach ($core_array as $path => $name) {
                            echo '<option value="'.$path.'">'.$name.'</option>';
                        }
                        ?>
                        </select>
                        <div class="info">A path to the core you want to use. Optional. If none is specified it will be looked for within the target directory.</div>
                    </li>
                </ul>
            </fieldset>
            <input type="submit" value="Generate !" />
        </form>
    </body>
</html>
		
<?php
}
else {

    /*
     * Render unto Caesar that which is Caesar's,
     * the majority of lines below are from Andrew Embler
     * http://andrewembler.com/posts/installing-concrete5-from-the-command-line/
     */

    define('FILE_PERMISSIONS_MODE', 0777);
    define('APP_VERSION_CLI_MINIMUM', '5.5..1');

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
    //$INSTALL_STARTING_POINT = $_POST['starting_point'];
    $target = TARGET_FOLDER . $_POST['folder'] . '/';
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

    # Create the website folder
    if (!mkdir($target, 0755)) {
        die("ERROR: can't create ".$_POST['folder'].". This folder may exist or you don't have rights to create it.\n");
    }
    
    $corePath = $core . '/concrete';
    
    # Copy the concrete5 structure
    # FIXME This part is not quite good : folders depend on concrete5 version
    $toCreate = array (
        'blocks', 'config', 'controllers', 'css', 'elements', 'files', 
        'helpers', 'jobs', 'js', 'languages', 'libraries', 'mail', 'models',
        'packages', 'page_types', 'single_pages', 'themes', 'tools', 'updates'
    );
    foreach ($toCreate as $dir) {
        if (!mkdir($target . $dir, 0755)) {
            die("ERROR: Error creating ".$dir.".\n");
        }
    }
    
    $toCopy = array (
        'index.php', 'robots.txt'
    );
    foreach ($toCopy as $file) {
        if (!copy($core . '/' . $file, $target . $file)) {
            die("ERROR: Error copying ".$file.".\n");
        }
    }
    
    # chmod
    chmod($target . "files", 0777);
    chmod($target . "config", 0777);
    chmod($target . "packages", 0777);
    
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
                //print $r->getProgress() . '%: ' . $r->getText() . "\n";
                call_user_func(array($spl, $r->getMethod()));
            }
        } catch(Exception $ex) {
            print "ERROR: " . $ex->getMessage() . "\n";		
            $cnt->reset();
        }

        if (!isset($ex)) {
            Config::save('SEEN_INTRODUCTION', 1);
            print "Installation Complete!\n";

            symlink($corePath, DIR_BASE . '/concrete');
        }

    }
}
?>
