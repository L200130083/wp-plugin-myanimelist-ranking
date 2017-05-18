<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * @package Gov My Aniem List Ranking
 * @version 1.0
 */
/*
Plugin Name: Gov MAL Ranking
Description: Gov My Aniem List Ranking
Plugin URI: http://www.zippyanime.net
Author: Governor
Version: 1.0
Author URI: http://nyocode.com
Text Domain: Gov-Mal-Ranking
*/
define("GOV_PG_MAL_DIR", __DIR__);
define("GOV_PG_MAL_INIT_FILE", __FILE__);
include __DIR__."/classes/GOV_Class.php";
include __DIR__."/classes/GOV_Mal.php";
$GOV_MAL = new GOV_mal();
$GOV_MAL->run();

?>
