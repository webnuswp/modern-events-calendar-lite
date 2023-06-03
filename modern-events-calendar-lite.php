<?php
/**
*	Plugin Name: Modern Events Calendar Lite
*	Plugin URI: http://webnus.net/modern-events-calendar/
*	Description: An awesome plugin for events calendar
*	Author: Webnus
*	Version: 6.10.1.1
*   Text Domain: modern-events-calendar-lite
*   Domain Path: /languages
*	Author URI: http://webnus.net
**/

if(!defined('MECEXEC'))
{
    /** MEC Execution **/
    define('MECEXEC', 1);

    /** Directory Separator **/
    if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

    /** MEC Absolute Path **/
    define('MEC_ABSPATH', dirname(__FILE__).DS);

    /** Plugin Directory Name **/
    define('MEC_DIRNAME', basename(MEC_ABSPATH));

    /** Plugin File Name **/
    define('MEC_FILENAME', basename(__FILE__));

    /** Plugin Base Name **/
    define('MEC_BASENAME', plugin_basename(__FILE__)); // modern-events-calendar/mec.php

    /** Plugin Version **/
    define('MEC_VERSION', '6.10.1.1');

    /** API URL **/
    define('MEC_API_ACTIVATION', 'https://webnus.net/api/v3');
    define('MEC_API_UPDATE', 'https://api.webnus.site/v3');

    /** Include Webnus MEC class if not included before **/
    if(!class_exists('MEC')) require_once MEC_ABSPATH.'mec-init.php';

    /** Initialize Webnus MEC Plugin **/
    $MEC = MEC::instance();
    $MEC->init();

    require_once MEC_ABSPATH.'app/core/mec.php';
    do_action('mec_init');
}