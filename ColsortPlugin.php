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
        'config_form',
        'config',
        // 'define_acl',
        'define_routes',
    );

    protected $_filters = array(
      'admin_navigation_main',
    );

    protected $_options = array(
        'colsort_collections_order' => '{}',
        'colsort_append_items' => false,
    );

    public function hookInstall()
    {
        if (!plugin_is_active('CollectionTree')) {
            $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
            $flash->addMessage(__('This plugin requires the plugin "CollectionTree" to work.'));
        }
        if (!plugin_is_active('ItemOrder')) {
            $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
            $flash->addMessage(__('This plugin requires the plugin "ItemOrder" to work.'));
        }
        $this->_installOptions();
    }

    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];
        if (version_compare($oldVersion, '0.2', '<')) {
            $order = unserialize(get_option('sortcol_preferences')) ?: json_decode($this->_options['colsort_collections_order'], true);
            // Convert string keys to integer.
            $order = array_combine(array_map('intval', array_keys($order)), array_map('intval', array_values($order)));
            asort($order);
            set_option('colsort_collections_order', json_encode($order));
            delete_option('sortcol_preferences');

            $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
            $flash->addMessage(__('Une option a été ajoutée dans la configuration pour inclure les items ou non.'), 'info');
            set_option('colsort_append_items', true);
        }
    }

    public function hookUninstall()
    {
        $this->_uninstallOptions();
    }

    /**
     * Shows plugin configuration page.
     */
    public function hookConfigForm($args)
    {
        $view = get_view();
        echo $view->partial(
            'plugins/colsort-config-form.php'
        );
    }

    /**
     * Saves plugin configuration page.
     *
     * @param array Options set in the config form.
     */
    public function hookConfig($args)
    {
        $post = $args['post'];
        $post = array_intersect_key($post, $this->_options);
        foreach ($post as $optionKey => $optionValue) {
            set_option($optionKey, $optionValue);
        }
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
                    'action' => 'arbre-collections',
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
                    'action' => 'order-collections',
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
