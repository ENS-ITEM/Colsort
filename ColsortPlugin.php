<?php
/**
 * ColSort
 */

class ColsortPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'upgrade',
        'uninstall',
        // 'define_acl',
        'define_routes',
    );

    protected $_filters = array(
      'admin_navigation_main',
    );

    protected $_options = array(
        'colsort_collections_order' => 'a:0:{}',
    );

    public function hookInstall()
    {
        if (!plugin_is_active('CollectionTree')) {
            $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
            $flash->addMessage(__('This plugin requires the plugin "CollectionTree" to work.'));
        }
        $this->_installOptions();
    }

    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];
        if (version_compare($oldVersion, '0.2', '<')) {
            set_option('colsort_collections_order',
                get_option('sortcol_preferences') ?: $this->_options['colsort_collections_order']);
            delete_option('sortcol_preferences');
        }
    }

    public function hookUninstall()
    {
        $this->_uninstallOptions();
    }

    public function hookDefineRoutes($args)
    {
        $router = $args['router'];

        $router->addRoute(
            'colsort_display_collections',
            new Zend_Controller_Router_Route(
                'arbre-collections',
                array(
                    'module' => 'colsort',
                    'controller' => 'index',
                    'action' => 'affichecollections',
                )
            )
        );
        $router->addRoute(
            'colsort_order_collections',
            new Zend_Controller_Router_Route(
                'tri-collections',
                array(
                    'module' => 'colsort',
                    'controller' => 'page',
                    'action' => 'ordercollections',
                )
            )
        );
    }

    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Tri Collections'),
            'uri' => url('tri-collections'),
            // 'resource' => 'UiTemplates_Page',
        );
        return $nav;
    }
}
