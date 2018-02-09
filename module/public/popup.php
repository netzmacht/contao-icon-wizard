<?php

/**
 * @package    icon-wizard
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Fritz Michael Gschwantner <fmg@inspiredminds.at>
 * @copyright  2013-2018 netzmacht David Molineus
 * @license    LGPL 3.0-or-later
 * @filesource
 */

/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
define('TL_SCRIPT', 'system/modules/icon-wizard/public/popup.php');

// Contao 3.
$file = dirname(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])))) . '/initialize.php';
if (file_exists($file)) {
    require $file;
} else {
    // Contao 4.
    $file = dirname(dirname(dirname(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])))))) . '/system/initialize.php';
    if (file_exists($file)) {
        require $file;
    } else {
        throw new \RuntimeException('Could not find initialize.php');
    }
}

$controller = new \Netzmacht\Contao\IconWizard\WizardController();
$controller->run();
