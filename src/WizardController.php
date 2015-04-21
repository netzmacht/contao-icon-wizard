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
 * Class WizardController.
 *
 * @package Netzmacht\Contao\IconWizard
 */
class WizardController extends \Backend
{
    /**
     * Dca definition reference.
     *
     * @var array
     */
    private $dca;

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
     * Run the controller.
     *
     * @throws \RuntimeException If an invalid call is made.
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function run()
    {
        $table = \Input::get('table');
        $field = \Input::get('field');
        $name  = \Input::get('name');
        $rowId = \Input::get('id');

        $dataContainer = $this->initializeDataContainer($table, $field);
        $this->loadRow($table, $rowId, $dataContainer);

        $template = $this->prepareTemplate();

        $template->table = $table;
        $template->field = $field;
        $template->name  = $name;
        $template->icon  = $dataContainer->activeRecord->$field;
        $template->icons = $this->generateIcons($field);

        $template->output();
    }

    /**
     * Prepare the template.
     * 
     * @return \BackendTemplate
     * @SuppressWarnings(PHPMD.Superglobals)
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
     * Initialize the data container driver.
     *
     * @param string $table The table name.
     * @param string $field The field name.
     *
     * @return \DataContainer
     * @throws \RuntimeException If the field does not exists.
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function initializeDataContainer($table, $field)
    {
        // Define the current ID
        if (!defined('CURRENT_ID')) {
            define('CURRENT_ID', $table ? \Session::getInstance()->get('CURRENT_ID') : $field);
        }

        static::loadDataContainer($table);

        $this->dca     = &$GLOBALS['TL_DCA'][$table];
        $driverClass   = 'DC_' . $this->dca['config']['dataContainer'];
        $dataContainer = new $driverClass($table);

        $dataContainer->field = $field;

        if (!isset($this->dca['fields'][$field]) || $this->dca['fields'][$field]['inputType'] != 'icon') {
            throw new \RuntimeException('Invalid call. Field does not exists or is not an icon wizard');
        }

        $values = $dataContainer->activeRecord->$field;

        // Call the load_callback
        if (is_array($this->dca['fields'][$field]['load_callback'])) {
            foreach ($this->dca['fields'][$field]['load_callback'] as $callback) {
                $values = $this->triggerCallback($callback, array($values, $dataContainer));
            }
        }

        // support options callback
        if (isset($this->dca['fields'][$field]['options_callback'])) {
            $this->dca['fields'][$field]['options'] = $this->triggerCallback(
                $this->dca['fields'][$field]['options_callback'],
                array($dataContainer)
            );
        }

        return $dataContainer;
    }

    /**
     * Load the data row.
     *
     * @param string         $table         The table name.
     * @param int            $rowId         The row id.
     * @param \DataContainer $dataContainer The data container.
     *
     * @return void
     * @throws \RuntimeException If no data row is found.
     */
    private function loadRow($table, $rowId, $dataContainer)
    {
        $dataContainer->activeRecord = $this->Database
            ->prepare(sprintf('SELECT * FROM %s WHERE id=?', $table))
            ->limit(1)
            ->execute($rowId);

        if ($dataContainer->activeRecord->numRows != 1) {
            throw new \RuntimeException('Selected entry does not exists');
        }
    }

    /**
     * Generate the icons.
     *
     * @param string $field The icons.
     *
     * @return array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function generateIcons($field)
    {
        $icons = array();

        $iconTemplate = isset($this->dca['fields'][$field]['eval']['iconTemplate']) ?
            $this->dca['fields'][$field]['eval']['iconTemplate'] :
            $GLOBALS['TL_CONFIG']['iconWizardIconTemplate'];

        foreach ((array) $this->dca['fields'][$field]['options'] as $groupName => $groupIcons) {
            foreach ($groupIcons as $icon) {
                $icons[$groupName][] = array(
                    'title'     => $icon,
                    'generated' => sprintf($iconTemplate, $icon),
                );
            }
        }

        return $icons;
    }

    /**
     * Trigger callback.
     *
     * @param array|callable $callback  Callback to trigger.
     * @param array          $arguments Callback arguments.
     *
     * @return mixed
     */
    private function triggerCallback($callback, $arguments)
    {
        if (is_array($callback)) {
            $this->import($callback[0]);
            return call_user_func_array(array($this->$callback[0], $callback[1]), $arguments);
        } elseif (is_callable($callback)) {
            return call_user_func_array($callback, $arguments);
        }

        return null;
    }
}
