<?php

Trait TraitOrderCollections
{
    protected function orderCollections($cols)
    {
        $order = unserialize(get_option('colsort_collections_order')) ?: array();
        foreach ($cols as $id => $col) {
            if (isset($order[$col['id']])) {
                $cols[$id]['ordre'] = $order[$col['id']];
            }
        }
        usort($cols, function ($a, $b) {
            if (!isset($a['ordre'])
                || !isset($b['ordre'])
                || ($a['ordre'] == $b['ordre'])
            ) {
                return 0;
            }
            return ($a['ordre'] < $b['ordre']) ? -1 : 1;
        });
        return $cols;
    }
}
