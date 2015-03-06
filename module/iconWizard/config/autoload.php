<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @package IconWizard
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Netzmacht',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Controller
	'Netzmacht\IconWizardController' => 'system/modules/iconWizard/controller/IconWizardController.php',

	// Widgets
	'Netzmacht\IconWizard'           => 'system/modules/iconWizard/widgets/IconWizard.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'be_iconwizard' => 'system/modules/iconWizard/templates',
));
