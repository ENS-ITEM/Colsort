<?php
class Colsort_PageController extends Omeka_Controller_AbstractActionController
{
    public function orderCollectionsAction()
    {
        $this->orderedCollections = json_decode(get_option('colsort_collections_order'), true) ?: array();
        $this->includeItems = (bool) get_option('colsort_append_items');

        $form = new Zend_Form();
        $configUrl = url('plugins/config?name=Colsort');
        $this->view->content = <<<HTML
<p>Vous pouvez classer les collections en indiquant un chiffre pour chaque collection.</p>
<p>Les collections racines sont indiquées en gras.</p>
<p>Il est conseillé de numéroter les collections de 10 en 10, afin de pouvoir insérer une nouvelle collection sans avoir à reprendre toute la numérotation.</p>
<p>Pour la visualisation, le comportement est différent entre l'affichage des collections mises en avant sur la page d'accueil et l'affichage sur la page 'arbre-collections' qui présente l'arborescence des collections et des notices.</p>
<p>1/ page d'accueil : on affiche dans l'ordre croissant les collections mises en avant, peu importe qu'elles soient parentes ou enfantes.</p>
<p>2/ arbre-collections :
L'ordre hiérarchique parent/enfant prévaut toujours, donc les chiffres n'affecteront que l'ordre des collections de même niveau.
On affiche les collections d'abord dans l'ordre croissant des collections parentes puis pour chaque collection parente, dans l'ordre croissant des collections enfantes.</p>
<p>Si vous mettez à une collection enfante un chiffre inférieur à celui de sa collection parente, cela ne changera pas son affichage qui sera toujours situé après la collection parente.</p>
<p>3/ fiche de la collection l'affichage des sous-collections respectera l'ordre donnée ici à ces sous-collections.</p>
<p>Une option dans la page de <a href="$configUrl">configuration</a> permet d'inclure ou non les items.</p>
<br />
HTML;
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                unset($formData['save']);
                // Convert string keys and values to integer.
                $this->orderedCollections = array_combine(array_map('intval', array_keys($formData)), array_map('intval', array_values($formData)));
                // Keep only interesting data.
                $this->orderedCollections = array_filter($this->orderedCollections);
                // Order by priority.
                asort($this->orderedCollections);
                set_option('colsort_collections_order', json_encode($this->orderedCollections));
                $this->_helper->flashMessenger('Ordre des collections sauvegardé.', 'success');
            }
        }

        $form = $this->getCollectionsForm();
        $this->view->content .= $form;
    }

    protected function orderCollections($cols)
    {
        $result = array();
        $this->orderedCollections = array_filter($this->orderedCollections);
        // Order all collections.
        $cols = array_replace(
            array_intersect_key($this->orderedCollections, $cols),
            $cols
        );
        // Order all children collections.
        foreach ($cols as &$col) {
            $col['children'] = array_keys(array_replace(
                array_intersect_key($this->orderedCollections, array_flip($col['children'])),
                array_flip($col['children'])
            ));
        }
        unset($col);
        // Append each child collection.
        foreach ($cols as $collectionId => $col) {
            if ($col['depth'] === 0) {
                $result[$collectionId] = $col;
                $result = $this->recursiveAppendChildrenCollections($result, $cols, $collectionId);
            }
        }
        return $result;
    }

    protected function recursiveAppendChildrenCollections($collections, $cols, $collectionId)
    {
        foreach ($cols[$collectionId]['children'] as $childCollectionId) {
            $collections[$childCollectionId] = $cols[$childCollectionId];
            $collections = $this->recursiveAppendChildrenCollections($collections, $cols, $childCollectionId);
        }
        return $collections;
    }

    private function getCollectionsForm()
    {
        $form = new Zend_Form();
        $form->setName('SortCollections');

        // The order is alphabetic or by id by default.
        $collectionList = get_db()->getTable('CollectionTree')->getCollectionList();
        $collectionList = $this->orderCollections($collectionList);

        foreach ($collectionList as $cid => $collection) {
            $collectionObj = get_record_by_id('Collection', $cid);
            if (!$collectionObj) {
                continue;
            }

            $nom = link_to_collection(null, array(), 'show', $collectionObj);
            release_object($collectionObj);

            if ($collection['parent'] && isset($collectionList[$collection['parent']]['name'])) {
                $nom .= ' (enfant de <em>' . $collectionList[$collection['parent']]['name'] . '</em>)';
            } else {
                $nom = "<b>$nom</b>";
            }

            $num = empty($this->orderedCollections[$cid]) ? '' : (string) $this->orderedCollections[$cid];
            $fieldCol = new Zend_Form_Element_Text('col_' . $cid);
            $fieldCol
                ->setName($cid)
                ->setLabel($nom)
                ->setAttrib('size', 3)
                ->setAttrib('type', 'number')
                ->setValue($num);
            $form->addElement($fieldCol);
        }

        $form->addElement(new Zend_Form_Element_Submit(
            'save',
            array(
                'label' => 'Soumettre',
            )
        ));

        return $this->prettifyForm($form);
    }

    private function prettifyForm($form)
    {
        // Prettify form
        $form->setDecorators(array(
                'FormElements',
                 array('HtmlTag', array('tag' => 'table')),
                'Form',
        ));
        $form->setElementDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'style' => 'text-align:right;float:right;', 'escape' => false)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
        ));
        return $form;
    }
}
