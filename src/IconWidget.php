<?php

/**
 * @package    icon-wizard
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\IconWizard;


/**
 * IconWidget class.
 *
 * @package Netzmacht
 */
class IconWidget extends \TextField
{
    /**
     * The template name.
     *
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * The icons.
     *
     * @var array
     */
    protected $arrIcons;

    /**
     * The icon template pattern.
     *
     * @var string
     */
    protected $strIconTemplate;


    /**
     * Set default iconTemplate.
     *
     * @param array|null $attributes The widget attributes.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct($attributes = null)
    {
        $this->iconTemplate = $GLOBALS['TL_CONFIG']['iconWizardIconTemplate'];

        parent::__construct($attributes);
    }

    /**
     * Get the attributes.
     *
     * @param string $key The param key.
     *
     * @return mixed
     */
    public function __get($key)
    {
        switch($key) {
            case 'icon':
                return $this->varValue;

            case 'icons':
            case 'options':
                return $this->arrIcons;

            case 'iconTemplate':
                return $this->strIconTemplate;

            default:
                return parent::__get($key);
        }
    }

    /**
     * Set the magic value.
     *
     * @param string $key   The attribute key.
     * @param mixed  $value The attribute value.
     *
     * @return void
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
     * Call the validator.
     *
     * @param mixed $value The value.
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function validator($value)
    {
        $value = parent::validator($value);

        if ($this->hasErrors()) {
            return null;
        } elseif ($value == '') {
            if ($this->mandatory) {
                $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
            }
        } elseif (!$this->iconExists($value)) {
            $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['iconNotFound'], $this->strLabel));
        }

        return $value;
    }

    /**
     * Generate the widget.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function generate()
    {
        $url = sprintf(
            'system/modules/icon-wizard/public/popup.php?table=%s&amp;field=%s&amp;name=ctrl_%s&amp;id=%s',
            \Input::get('table'),
            $this->strField,
            $this->name,
            \Input::get('id')
        );

        $template = <<<HTML
<div class="iconWizard"><input type="hidden" name="%s" id="ctrl_%s" value="%s"%s
<span class="icon">%s</span> <span class="title">%s</span><a href="%s"
onclick="Backend.getScrollOffset();Backend.openModalIframe({url:'%s', width: %s, title: '%s'});return false"
 title="%s" class="tl_submit">%s</a></div>
HTML;

        return sprintf(
            $template,
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
     * Check if icon exists.
     *
     * @param string $icon The icon name.
     *
     * @return bool
     */
    protected function iconExists($icon)
    {
        foreach ($this->icons as $group) {
            foreach ($group as $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                if ($entry['value'] === $icon) {
                    return true;
                }
            }
        }

        return false;
    }
}
