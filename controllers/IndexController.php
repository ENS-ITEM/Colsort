<?php
require_once 'TraitOrderCollections.php';

class Colsort_IndexController extends Omeka_Controller_AbstractActionController
{
    use TraitOrderCollections;

    protected $tree = '';

    private $hasUser = false;

    public function arbreCollectionsAction()
    {
        $this->hasUser = (bool) current_user();
        $andPublicOnly = $this->hasUser ? '' : 'AND c.public = 1';
        $query = <<<SQL
SELECT collection_id id, name, c.public public
FROM omeka_collection_trees t
LEFT JOIN omeka_collections c ON t.collection_id = c.id
WHERE t.parent_collection_id = 0
$andPublicOnly;
SQL;
        $db = get_db();
        $cols = $db->query($query)->fetchAll();
        $cols = $this->orderCollections($cols);
        $includeItems = (bool) get_option('colsort_append_items');

        $this->tree .= '<ul>' . PHP_EOL;
        foreach ($cols as $col) {
            $collection = get_record_by_id('collection', $col['id']);
            if (!$collection) {
                continue;
            }
            $plus = '';
            $items = '';
            if ($this->fetch_child_collections($col['id'])) {
                $plus = ' <span class="montrer">+</span>';
            }
            if ($includeItems && $items = $this->fetch_items($col['id'])) {
                $plus = ' <span class="montrer">+</span>';
            }
            $this->tree .= '<li>' . link_to_collection(null, array('class' => 'collection'), 'show', $collection) . $plus . '</li>' . PHP_EOL;
            $this->tree .= $items;
        }
        $this->tree .= '</ul>' . PHP_EOL;
        $this->view->tree = $this->tree;
        return true;
    }

    private function fetch_child_collections($collection_id)
    {
        $andPublicOnly = $this->hasUser ? '' : 'AND c.public = 1';
        $query = <<<SQL
SELECT t.collection_id id, name, c.public public
FROM omeka_collection_trees t
INNER JOIN omeka_collections c ON t.collection_id = c.id
WHERE parent_collection_id = $collection_id
$andPublicOnly;
SQL;
        $db = get_db();
        $child_collections = $db->query($query)->fetchAll();
        if (!$child_collections) {
            return false;
        }

        $includeItems = (bool) get_option('colsort_append_items');

        $child_collections = $this->orderCollections($child_collections);

        $this->tree .= '<div class="collections"><ul>' . PHP_EOL;
        foreach ($child_collections as $col) {
            $collection = get_record_by_id('collection', $col['id']);
            if (!$collection) {
                continue;
            }
            $plus = '';
            $items = '';
            if ($includeItems && $items = $this->fetch_items($col['id'])) {
                $plus = ' <span class="montrer">+</span>';
            }
            $this->tree .= '<li>' . link_to_collection(null, array('class' => 'collection'), 'show', $collection) . $plus . '</li>' . PHP_EOL;
            $this->tree .= $items;
        }
        $this->tree .= '</ul></div>' . PHP_EOL;
        return true;
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
        $notices = '<div class="notices"><ul>' . PHP_EOL;
        foreach ($items as $id => $item) {
            $item = get_record_by_id('item', $item['id']);
            if ($item) {
                $notices .= '<li>' . link_to_item(null, array(), 'show', $item) . '</li>' . PHP_EOL;
            }
        }
        $notices .= '</ul></div>' . PHP_EOL;
        return $notices;
    }
}
