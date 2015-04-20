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
require(dirname(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])))) . '/initialize.php');


$controller = new \Netzmacht\Contao\IconWizard\WizardController();
$controller->run();
