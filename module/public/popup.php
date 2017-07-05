<?php

/**
 * @package    icon-wizard
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */


/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
define('TL_SCRIPT', 'system/modules/iconwizard/public/popup.php');

// Contao 3.
$file = dirname(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])))) . '/initialize.php';
if (file_exists($file)) {
    require $file;
}

// Contao 4.
$file = dirname(dirname(dirname(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])))))) . '/system/initialize.php';
if (file_exists($file)) {
    require $file;
} else {
    throw new \RuntimeException('Could not find initialize.php');
}


$controller = new \Netzmacht\Contao\IconWizard\WizardController();
$controller->run();
