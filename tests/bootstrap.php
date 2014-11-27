<?php
define('DS', DIRECTORY_SEPARATOR);
if (!defined('WINDOWS')) {
	if (DS == '\\' || substr(PHP_OS, 0, 3) === 'WIN') {
		define('WINDOWS', true);
	} else {
		define('WINDOWS', false);
	}
}
define('ROOT', dirname(__DIR__));
define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('APP', sys_get_temp_dir());
define('APP_DIR', 'src');
define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . APP_DIR . DS);
define('WWW_ROOT', ROOT . DS . 'webroot' . DS);
define('CONFIG', dirname(__FILE__) . DS . 'config' . DS);

require ROOT . '/vendor/cakephp/cakephp/src/basics.php';
require ROOT . '/vendor/autoload.php';

require CAKE . 'Core/ClassLoader.php';

Cake\Core\Configure::write('App', [
	'namespace' => 'App'
]);
Cake\Core\Configure::write('debug', true);

$Tmp = new Cake\Filesystem\Folder(TMP);
$Tmp->create(TMP . 'cache/models', 0770);
$Tmp->create(TMP . 'cache/persistent', 0770);
$Tmp->create(TMP . 'cache/views', 0770);