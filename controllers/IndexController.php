<?php

class Colsort_IndexController extends Omeka_Controller_AbstractActionController
{
    protected $tree = '';

    public function affichecollectionsAction()
    {
        $query = "SELECT collection_id id, name, c.public public FROM omeka_collection_trees t LEFT JOIN omeka_collections c ON t.collection_id = c.id WHERE t.parent_collection_id = 0";
        $db = get_db();
        $cols = $db->query($query)->fetchAll();

        $this->tree .= '<ul>';

        $cols = $this->orderCollections($cols);

        foreach ($cols as $col) {
            if ($col['public'] <> 1 && !current_user()) {
                continue;
            }
            $collection = get_record_by_id('collection', $col['id']);
            if (!$collection) {
                continue;
            }
            $plus = '';
            if ($this->fetch_child_collections($col['id'])) {
                $plus = '<span class="montrer">+</span>';
            }
            if ($items = $this->fetch_items($col['id'])) {
                $plus = '<span class="montrer">+</span>';
            }
            $this->tree .= '<li>' . link_to_collection(null, array('class' => 'collection'), 'show', $collection) . $plus . '</li>';
            $this->tree .= $items;
        }
        $this->tree .= '</ul>';
        $this->view->tree = $this->tree;
        return true;
    }

    private function fetch_child_collections($collection_id)
    {
        $query = "SELECT t.collection_id id, name, c.public public FROM omeka_collection_trees t INNER JOIN omeka_collections c ON t.collection_id = c.id WHERE parent_collection_id = " . $collection_id;
        $db = get_db();
        $child_collections = $db->query($query)->fetchAll();
        if (!$child_collections) {
            return false;
        }
        $this->tree .= '<div class="collections"><ul>';
        $child_collections = $this->orderCollections($child_collections);
        $plus = '';
        foreach ($child_collections as $col) {
            if ($col['public'] <> 1 && !current_user()) {
                continue;
            }
            $collection = get_record_by_id('collection', $col['id']);
            if (!$collection) {
                continue;
            }
            $plus = '';
            if ($items = $this->fetch_items($col['id'])) {
                $plus = '<span class="montrer">+</span>';
            }
            $this->tree .= '<li>' . link_to_collection(null, array('class' => 'collection'), 'show', $collection) . $plus . '</li>';
            $this->tree .= $items;
        }
        $this->tree .= '</ul></div>';
        return true;
    }

    private function fetch_items($cid)
    {
        $notices = '';
        $db = get_db();
        $items = $db->query("SELECT id FROM omeka_items WHERE collection_id = " . $cid)->fetchAll();
        if (!$items) {
            return false;
        }
        // Sort items by item order module
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
        $notices .= '<div class="notices"><ul>';
        foreach ($items as $id => $item) {
            $item = get_record_by_id('item', $item['id']);
            if ($item) {
                $notices .= '<li>' . link_to_item(null, array(), 'show', $item) . '</li>';
            }
        }
        $notices .= '</ul></div>';
        return $notices;
    }

    public function orderCollections($cols)
    {
        $order = unserialize(get_option('colsort_collections_order')) ?: array();
        foreach ($cols as $id => $col) {
            if (isset($order[$col['id']])) {
                $cols[$id]['ordre'] = $order[$col['id']];
            } else {
                $cols[$id]['ordre'] = 0;
            }
        }
        usort($cols, function ($a, $b) {
            if ($a['ordre'] == $b['ordre']) {
                return 0;
            }
            return ($a['ordre'] < $b['ordre']) ? -1 : 1;
        });
        return $cols;
    }
}
