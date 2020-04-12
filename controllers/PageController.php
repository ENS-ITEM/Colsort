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
                // Sort by the specified order.
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
        foreach ($cols as $id => $col) {
            if (isset($this->orderedCollections[$col['id']])) {
                $cols[$id]['ordre'] = $this->orderedCollections[$col['id']];
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

    private function getCollectionsForm()
    {
        $form = new Zend_Form();
        $form->setName('SortCollections');

        $db = get_db();

        $collections = get_recent_collections(1000);
        $collections = $this->orderCollections($collections);
        foreach ($collections as $col) {
            $cid = $col['id'];
            $query = "SELECT parent_collection_id FROM omeka_collection_trees WHERE collection_id = $cid";
            $parentId = $db->query($query)->fetchAll();
            $parentId = $parentId[0]['parent_collection_id'];
            $parentName = $db->query("SELECT name FROM omeka_collection_trees WHERE collection_id = $parentId")->fetchAll();
            if ($parentName) {
                $parentName = $parentName[0]['name'];
            } else {
                $parentName = '';
            }

            $nom = link_to_collection(null, array(), 'show', $col);
            if ($parentId <> 0) {
                $nom .= " (enfant de <em>$parentName</em>)";
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
