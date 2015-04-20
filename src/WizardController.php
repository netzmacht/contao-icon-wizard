<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\IconWizard;

/**
 * Class WizardController.
 *
 * @package Netzmacht\Contao\IconWizard
 */
class WizardController extends \Backend
{
    /**
     * Construct.
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
        $table = \Input::get('table');
        $field = \Input::get('field');
        $name  = \Input::get('name');
        $id    = \Input::get('id');

        $dataContainer = $this->initializeDataContainer($table, $field);

        if(!isset($GLOBALS['TL_DCA'][$table]['fields'][$field])
            || $GLOBALS['TL_DCA'][$table]['fields'][$field]['inputType'] != 'icon') {
            throw new \RuntimeException('Invalid call. Field does not exists or is not an icon wizard');
        }

        $dataContainer->activeRecord = $this->Database
            ->prepare(sprintf('SELECT %s FROM %s WHERE id=?', $field, $table))
            ->limit(1)
            ->execute($id);

        if($dataContainer->activeRecord->numRows != 1) {
            throw new \RuntimeException('Selected entry does not exists');
        }

        $iconTemplate = isset($GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['iconTemplate']) ?
            $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['iconTemplate'] :
            $GLOBALS['TL_CONFIG']['iconWizardIconTemplate'];

        $icons  = array();
        $values = $dataContainer->activeRecord->$field;

        // Call the load_callback
        if (is_array($GLOBALS['TL_DCA'][$table]['fields'][$field]['load_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$table]['fields'][$field]['load_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $values = $this->$callback[0]->$callback[1]($values, $dataContainer);
                }
                elseif (is_callable($callback)) {
                    $values = $callback($values, $dataContainer);
                }
            }
        }

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

        $template = $this->prepareTemplate();
        $template->table = $table;
        $template->field = $field;
        $template->name  = $name;
        $template->icon  = $dataContainer->activeRecord->$field;
        $template->icons = $icons;

        $template->output();
    }

    /**
     * Prepare the template.
     * 
     * @return \BackendTemplate
     */
    private function prepareTemplate()
    {
        $template = new \BackendTemplate('be_iconwizard');

        $template->headline          = $GLOBALS['TL_LANG']['MSC']['iconWizard'][1];
        $template->searchLabel       = $GLOBALS['TL_LANG']['MSC']['iconWizardSearch'][0];
        $template->searchPlaceholder = $GLOBALS['TL_LANG']['MSC']['iconWizardSearch'][1];
        $template->reset             = $GLOBALS['TL_LANG']['MSC']['iconWizardReset'][0];
        $template->resetTitle        = $GLOBALS['TL_LANG']['MSC']['iconWizardReset'][1];
        $template->theme             = $this->getTheme();
        $template->base              = $this->Environment->base;
        $template->language          = $GLOBALS['TL_LANGUAGE'];
        $template->title             = $GLOBALS['TL_CONFIG']['websiteTitle'];
        $template->charset           = $GLOBALS['TL_CONFIG']['characterSet'];
        $template->pageOffset        = \Input::cookie('BE_PAGE_OFFSET');
        $template->error             = (\Input::get('act') == 'error') ? $GLOBALS['TL_LANG']['ERR']['general'] : '';
        $template->skipNavigation    = $GLOBALS['TL_LANG']['MSC']['skipNavigation'];
        $template->request           = ampersand($this->Environment->request);
        $template->top               = $GLOBALS['TL_LANG']['MSC']['backToTop'];
        $template->expandNode        = $GLOBALS['TL_LANG']['MSC']['expandNode'];
        $template->collapseNode      = $GLOBALS['TL_LANG']['MSC']['collapseNode'];
        
        return $template;
    }

    /**
     * Iniitalize the data container driver.
     *
     * @param string $table The table name.
     * @param string $field The field name.
     *
     * @return \DataContainer
     */
    private function initializeDataContainer($table, $field)
    {
        // Define the current ID
        if (!defined('CURRENT_ID')) {
            define('CURRENT_ID', (\Input::get('table') ? \Session::getInstance()->get('CURRENT_ID') : \Input::get('id')));
        }

        static::loadDataContainer($table);

        $driverClass   = 'DC_' . $GLOBALS['TL_DCA'][$table]['config']['dataContainer'];
        $dataContainer = new $driverClass($table);
        $dataContainer->field = $field;

        return $dataContainer;
    }
}
