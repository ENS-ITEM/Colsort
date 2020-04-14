<?php
class Colsort_IndexController extends Omeka_Controller_AbstractActionController
{
    protected $orderedCollections = array();
    protected $includeItems = false;

    protected $tree = '';

    private $hasUser = false;

    public function arbreCollectionsAction()
    {
        $this->orderedCollections = json_decode(get_option('colsort_collections_order'), true) ?: array();
        $this->includeItems = (bool) get_option('colsort_append_items');

        $this->view->tree = $this->collectionTreeFullList();
        return true;
    }

    /**
     * Build a nested HTML unordered list of the full collection tree, starting
     * at root collections.
     *
     * Copy of \CollectionTree_View_Helper_CollectionTreeFullList:collectionTreeFullList(),
     * except sort of root collections and method used to get the sub-trees.
     *
     * @param bool $linkToCollectionShow
     * @return string|null
     */
    protected function collectionTreeFullList($linkToCollectionShow = true)
    {
        $rootCollections = get_db()->getTable('CollectionTree')->getRootCollections();
        // Return NULL if there are no root collections.
        if (!$rootCollections) {
            return null;
        }

        $rootCollections = array_replace(
            array_intersect_key(array_filter($this->orderedCollections), $rootCollections),
            $rootCollections
        );

        $collectionTable = get_db()->getTable('Collection');
        $html = '<div id="collection-tree"><ul class="">' . PHP_EOL;
        foreach ($rootCollections as $rootCollection) {
            $html .= '<li>';
            if ($linkToCollectionShow) {
                $html .= link_to_collection(null, array('class' => 'collection'), 'show', $collectionTable->find($rootCollection['id']));
            } else {
                $html .= $rootCollection['name'] ? $rootCollection['name'] : __('[Untitled]');
            }
            $descendantTree = get_db()->getTable('CollectionTree')->getDescendantTree($rootCollection['id']);
            $plus = '';
            $items = '';
            $sub = $this->collectionTreeList($descendantTree, $linkToCollectionShow);
            if ($sub) {
                $plus = ' <span class="montrer">+</span>';
            }
            if ($this->includeItems && isset($rootCollection['id'])) {
                $plus = ' <span class="montrer">+</span>';
                $items = $this->fetch_items($rootCollection['id']);
            }
            $html .= $plus;
            $html .= $sub;
            $html .= $items;
            $html .= '</li>' . PHP_EOL;
        }
        $html .= '</ul></div>' . PHP_EOL;
        return $html;
    }

    /**
     * Recursively build a nested HTML unordered list from the provided
     * collection tree.
     *
     * Copy of \CollectionTree_View_Helper_CollectionTreeList:collectionTreeList(),
     * except the order and the inclusion of items if specified.
     *
     * @see \CollectionTreeTable::getCollectionTree()
     * @see \CollectionTreeTable::getAncestorTree()
     * @see \CollectionTreeTable::getDescendantTree()
     * @param array $collectionTree
     * @param bool $linkToCollectionShow
     * @param bool $linkToCurrentCollectionShow Require option $linkToCollectionShow.
     * @return string
     */
    protected function collectionTreeList($collectionTree, $linkToCollectionShow = true, $linkToCurrentCollection = false)
    {
        if (!$collectionTree) {
            return;
        }

        $collections = array();
        $noCollections = array();
        $no = 0;
        foreach ($collectionTree as $collection) {
            if (isset($collection['id'])) {
                $collections[$collection['id']] = $collection;
            } else {
                $noCollections['no_' . ++$no] = $collection;
            }
        }
        $collectionTree = array_replace(
            array_intersect_key(array_filter($this->orderedCollections), $collections),
            $collections
        ) + $noCollections;

        $linkToCurrentCollection = $linkToCollectionShow && $linkToCurrentCollection;

        $collectionTable = get_db()->getTable('Collection');
        $html = '<ul class="collections">' . PHP_EOL;
        foreach ($collectionTree as $collection) {
            $html .= '<li' . (isset($collection['current']) ? ' class="active"' : '') . '>' . PHP_EOL;
            // No link to current collection, unless specified.
            if ($linkToCollectionShow && ($linkToCurrentCollection || !isset($collection['current'])) && isset($collection['id'])) {
                $html .= link_to_collection(null, array('class' => 'collection'), 'show', $collectionTable->find($collection['id']));
            }
            // No link to private parent collection.
            elseif (!isset($collection['id'])) {
                $html .= __('[Unavailable]');
            }
            // Display name of current collection.
            else {
                $html .= empty($collection['name']) ? __('[Untitled]') : $collection['name'];
            }
            $plus = '';
            $items = '';
            $sub = $this->collectionTreeList($collection['children'], $linkToCollectionShow, $linkToCurrentCollection);
            if ($sub) {
                $plus = ' <span class="montrer">+</span>';
            }
            if ($this->includeItems && isset($collection['id'])) {
                $plus = ' <span class="montrer">+</span>';
                $items = $this->fetch_items($collection['id']);
            }
            $html .= $plus;
            $html .= $sub;
            $html .= $items;
            $html .= '</li>' . PHP_EOL;
        }
        $html .= '</ul>' . PHP_EOL;
        return $html;
    }

    private function fetch_items($cid)
    {
        $db = get_db();
        $items = $db->query("SELECT id FROM omeka_items WHERE collection_id = " . $cid)->fetchAll();
        if (!$items) {
            return false;
        }

        // Sort items by item order module.
        $ordre = $db->query("SELECT item_id, omeka_item_order_item_orders.order ordre FROM omeka_item_order_item_orders")->fetchAll();
        $order = array();
        foreach ($ordre as $vals) {
            $order[$vals['item_id']] = $vals['ordre'];
        }
        foreach ($items as $id => $item) {
            if (isset($item['id']) && isset($order[$item['id']])) {
                $items[$id]['ordre'] = $order[$item['id']];
            }
        }
        usort($items, function ($a, $b) {
            if (!isset($a['ordre']) || !isset($b['ordre'])) {
                return 1;
            };
            if ($a['ordre'] == $b['ordre']) {
                return 0;
            }
            return ($a['ordre'] < $b['ordre']) ? -1 : 1;
        });

        // Prepare html.
        $notices = '<ul class="notices">' . PHP_EOL;
        foreach ($items as $id => $item) {
            $item = get_record_by_id('item', $item['id']);
            if ($item) {
                $notices .= '<li>' . link_to_item(null, array(), 'show', $item) . '</li>' . PHP_EOL;
            }
        }
        $notices .= '</ul>' . PHP_EOL;
        return $notices;
    }
}
