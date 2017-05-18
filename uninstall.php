<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if ( ! defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
define("GOV_PG_MAL_DIR", __DIR__);
define("GOV_PG_MAL_INIT_FILE", __FILE__);
include __DIR__."/classes/GOV_Class.php";
include __DIR__."/classes/GOV_Mal.php";
$GOV_MAL_REMOVE = new GOV_mal();
$GOV_MAL_REMOVE->do_uninstall();