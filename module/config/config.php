<?php

/**
 * @package    icon-wizard
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

$GLOBALS['BE_FFL']['icon'] = 'Netzmacht\Contao\IconWizard\IconWidget';

$GLOBALS['TL_CONFIG']['iconWizardIconTemplate'] = '<i class="icon-%s"></i>';

if(TL_MODE == 'BE') {
	$GLOBALS['TL_CSS']['iconWizard'] = 'system/modules/icon-wizard/assets/wizard.css';
}
