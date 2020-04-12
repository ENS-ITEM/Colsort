<?php
/**
 * ColSort
 */

class ColsortPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        // 'define_acl',
        'define_routes',
    );

    protected $_filters = array(
      'admin_navigation_main',
    );

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
