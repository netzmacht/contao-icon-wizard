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
 * Class IconWizard
 * @package Netzmacht
 */
class IconWizard extends \TextField
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';

	/**
	 * @var
	 */
	protected $arrIcons;

	/**
	 * @var
	 */
	protected $strIconTemplate;


	/**
	 * set default iconTemplate
	 * @param null $arrAttributes
	 */
	public function __construct($arrAttributes=null)
	{
		$this->iconTemplate = $GLOBALS['TL_CONFIG']['iconWizardIconTemplate'];
		parent::__construct($arrAttributes);
	}


	/**
	 * @param string $key
	 * @return mixed|string
	 */
	public function __get($key)
	{
		switch($key) {
			case 'icon':
				return $this->varValue;
				break;

			case 'icons':
			case 'options':
				return $this->arrIcons;
				break;

			case 'iconTemplate':
				return $this->strIconTemplate;
				break;

			default:
				return parent::__get($key);
				break;
		}
	}


	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		switch($key) {
			case 'icon':
				$this->varValue = $value;
				break;

			case 'icons':
			case 'options':
				$this->arrIcons = $value;
				break;

			case 'iconTemplate':
				$this->strIconTemplate = $value;
				break;

			default:
				parent::__set($key, $value);
				break;
		}
	}


	/**
	 * @param mixed $value
	 * @return mixed
	 */
	protected function validator($value)
	{
		$value = parent::validator($value);

		if($this->hasErrors()) {
			return;
		}
		elseif($value == '')
		{
			if($this->mandatory) {
				$this->addError($this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel)));
			}
		}
		elseif(!$this->iconExists($value)) {
			$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['iconNotFound'], $this->strLabel));
		}

		return $value;
	}


	/**
	 * @return string
	 */
	public function generate()
	{
		$url = sprintf('system/modules/iconWizard/public/popup.php?table=%s&amp;field=%s&amp;name=ctrl_%s&amp;id=%s',
			\Input::get('table'), $this->strField, $this->name, \Input::get('id')
		);

		return sprintf(
			'<div class="iconWizard"><input type="hidden" name="%s" id="ctrl_%s" value="%s"%s'
			. '<span class="icon">%s</span> <span class="title">%s</span>'
			. ' <a href="%s" onclick="Backend.getScrollOffset();Backend.openModalIframe({url:\'%s\', width: %s, title: \'%s\'});return false"'
			. ' title="%s" class="tl_submit">%s</a></div>',
			$this->name,
			$this->name,
			$this->icon,
			$this->strTagEnding,
			$this->icon ? sprintf($this->iconTemplate, $this->icon) : '',
			$this->icon ? $this->icon : '-',
			$url,
			$url,
			790,
			$GLOBALS['TL_LANG']['MSC']['iconWizard'][0],
			$GLOBALS['TL_LANG']['MSC']['iconWizardlink'][1],
			$GLOBALS['TL_LANG']['MSC']['iconWizardlink'][0]
		);
	}


	/**
	 * check if icon exists
	 *
	 * @param $icon
	 * @return bool
	 *
	 */
	protected function iconExists($icon)
	{
		foreach($this->icons as $n => $group)
		{
			foreach($group as $entry) {
				if(!is_array($entry)) {
					continue;
				}

				if($entry['value'] == $icon) {
					return true;
				}
			}
		}

		return false;
	}
}