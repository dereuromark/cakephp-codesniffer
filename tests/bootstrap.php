<?php
/**
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
require dirname(__DIR__) . '/vendor/cakephp/cakephp/src/basics.php';
require dirname(__DIR__) . '/vendor/autoload.php';

define('DS', DIRECTORY_SEPARATOR);
define('APP', sys_get_temp_dir());
define('ROOT', dirname(__DIR__));
Cake\Core\Configure::write('App', [
	'namespace' => 'App'
]);
