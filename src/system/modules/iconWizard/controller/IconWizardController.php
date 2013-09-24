<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   iconWizard
 * @author    netzmacht creative David Molineus
 * @license   MPL/2.0
 * @copyright 2013 netzmacht creative David Molineus
 */

namespace Netzmacht;

/**
 * Class IconWizardController provides controller for modal iframe
 * @thanks to 4ward.media 2013, inspired by Stylepicker4ward
 * @package Netzmacht
 */
class IconWizardController extends \Backend
{

	/**
	 * constructor
	 */
	public function __construct()
	{
		$this->import('BackendUser', 'User');
		$this->import('Database');
		parent::__construct();

		$this->User->authenticate();

		$this->loadLanguageFile('default');
		$this->loadLanguageFile('modules');
	}


	/**
	 * @throws \RuntimeException
	 */
	public function run()
	{
		$this->Template = new \BackendTemplate('be_iconwizard');
		$this->Template->headline = $GLOBALS['TL_LANG']['MSC']['iconWizard'][1];

		$table = \Input::get('table');
		$field =  \Input::get('field');
		$id = \Input::get('id');

		$this->loadDataContainer($table);

		if(!isset($GLOBALS['TL_DCA'][$table]['fields'][$field]) || $GLOBALS['TL_DCA'][$table]['fields'][$field]['inputType'] != 'icon') {
			throw new \RuntimeException('Invalid call. Field does not exists or is not an icon wizard');
		}

		$result = $this->Database->prepare(sprintf('SELECT %s FROM %s WHERE id=?', $field, $table))->limit(1)->execute($id);

		if($result->numRows != 1) {
			throw new \RuntimeException('Selected entry does not exists');
		}

		$iconTemplate = isset($GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['iconTemplate']) ?
			$GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['iconTemplate'] :
			$GLOBALS['TL_CONFIG']['iconWizardIconTemplate'];

		$icons = array();

		// support options callback
		if(isset($GLOBALS['TL_DCA'][$table]['fields'][$field]['options_callback'])) {
			$callback = $GLOBALS['TL_DCA'][$table]['fields'][$field]['options_callback'];

			$this->import($callback[0]);
			$GLOBALS['TL_DCA'][$table]['fields'][$field]['options'] = $this->$callback[0]->$callback[1]();
		}

		foreach($GLOBALS['TL_DCA'][$table]['fields'][$field]['options'] as $groupName => $groupIcons) {
			foreach($groupIcons as $icon) {
				$icons[$groupName][] = array(
					'title' => $icon,
					'generated' => sprintf($iconTemplate, $icon),
				);
			}
		}

		$this->Template->searchLabel = $GLOBALS['TL_LANG']['MSC']['iconWizardSearch'][0];
		$this->Template->searchPlaceholder = $GLOBALS['TL_LANG']['MSC']['iconWizardSearch'][1];

		$this->Template->reset = $GLOBALS['TL_LANG']['MSC']['iconWizardReset'][0];
		$this->Template->resetTitle = $GLOBALS['TL_LANG']['MSC']['iconWizardReset'][1];

		$this->Template->theme = $this->getTheme();
		$this->Template->base = $this->Environment->base;
		$this->Template->language = $GLOBALS['TL_LANGUAGE'];
		$this->Template->title = $GLOBALS['TL_CONFIG']['websiteTitle'];
		$this->Template->charset = $GLOBALS['TL_CONFIG']['characterSet'];
		$this->Template->pageOffset = \Input::cookie('BE_PAGE_OFFSET');
		$this->Template->error = (\Input::get('act') == 'error') ? $GLOBALS['TL_LANG']['ERR']['general'] : '';
		$this->Template->skipNavigation = $GLOBALS['TL_LANG']['MSC']['skipNavigation'];
		$this->Template->request = ampersand($this->Environment->request);
		$this->Template->top = $GLOBALS['TL_LANG']['MSC']['backToTop'];
		$this->Template->expandNode = $GLOBALS['TL_LANG']['MSC']['expandNode'];
		$this->Template->collapseNode = $GLOBALS['TL_LANG']['MSC']['collapseNode'];

		$this->Template->table = $table;
		$this->Template->field = $field;
		$this->Template->icon = $result->$field;
		$this->Template->icons = $icons;

		$this->Template->output();
	}

}