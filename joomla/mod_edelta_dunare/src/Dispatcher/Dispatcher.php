<?php

namespace EdeltaDunare\Module\EdeltaDunare\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

/**
 * Dispatcher for mod_edelta_dunare.
 *
 * @since 1.0.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Prepare module data for the layout.
     *
     * @return  array  Layout data including admin params and API data.
     *
     * @since   1.0.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $params = $data['params'];

        $data['module_port']         = (int) $params->get('port', 2);
        $data['module_days']         = (int) $params->get('days', 30);
        $data['module_display']      = (string) $params->get('display', 'both');
        $data['module_border']       = (string) $params->get('borderColor', '#436741');
        $data['module_api']          = rtrim((string) $params->get('api_base', 'https://api.edelta.ro'), '/');
        $data['module_show_backlink'] = (int) $params->get('show_backlink', 1);

        // Fetch (and cache) the data from the public API.
        $data['module_result'] = $this->getHelperFactory()
            ->getHelper('EdeltaDunareHelper')
            ->getData($data['module_port'], $data['module_days'], $data['module_api']);

        return $data;
    }
}
