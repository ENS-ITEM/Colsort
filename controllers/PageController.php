<?php

class Colsort_PageController extends Omeka_Controller_AbstractActionController
{
    public function ordercollectionsAction()
    {
        $form = new Zend_Form();
        // $this->view->content = "Vous pouvez classer les collections en indiquant un chiffre pour chaque collection.<br /><br />Vous pouvez classer les collections de même niveau entre elles, en indiquant un chiffre pour chaque collection.<br /><br />L'ordre hiérarchique prévaut toujours, donc les chiffres n'affecteront que l'ordre des collections de même niveau.<br /><br > Les collections racines sont entourées de ***.<br /><br />Il est conseillé de numéroter les collections de 10 en 10, afin de pouvoir insérer une nouvelle collection sans avoir à reprendre toute la numérotation.";
        $this->view->content = "Vous pouvez classer les collections en indiquant un chiffre pour chaque collection.<br /><br />Les collections racines sont indiquées en gras.<br /><br />Il est conseillé de numéroter les collections de 10 en 10, afin de pouvoir insérer une nouvelle collection sans avoir à reprendre toute la numérotation.<br /><br />Pour la visualisation, le comportement est différent entre l'affichage des collections mises en avant sur la page d'accueil et l'affichage sur la page 'arbre-collections' qui présente l'arborescence des collections et des notices.<br /><br />1/ page d'accueil : on affiche dans l'ordre croissant les collections mises en avant, peu importe qu'elles soient parentes ou enfantes.<br /><br />2/ arbre-collections :
L'ordre hiérarchique parent/enfant prévaut toujours, donc les chiffres n'affecteront que l'ordre des collections de même niveau.
On affiche les collections d'abord dans l'ordre croissant des collections parentes puis pour chaque collection parente, dans l'ordre croissant des collections enfantes.<br /><br />Si vous mettez à une collection enfante un chiffre inférieur à celui de sa collection parente, cela ne changera pas son affichage qui sera toujours situé après la collection parente.<br /><br />3/ fiche de la collection l'affichage des sous-collections respectera l'ordre donnée ici à ces sous-collections.<br /><br />";
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                unset($formData['save']);
                $order = serialize($formData);
                set_option('sortcol_preferences', $order);
                $this->_helper->flashMessenger('Ordre des collections sauvegardé.');
            }
        }

        $form = $this->getCollectionsForm();
        $this->view->content .= $form;
    }

    private function getCollectionsForm()
    {
        $order = unserialize(get_option('sortcol_preferences'));
        $form = new Zend_Form();
        $form->setName('SortCollections');

        // Tri selon numéro d'ordre
        asort($order);

        $collections = get_recent_collections(1000);
        $collections = $this->orderCollections($collections);
        foreach ($collections as $i => $col) {
            $cid = metadata($col, 'id');
            $nom = metadata($col, array('Dublin Core', 'Title'));
            $nom = "<a href='" . WEB_ROOT . "/collections/show/$cid'>$nom</a>";
            $query = "SELECT parent_collection_id FROM omeka_collection_trees WHERE collection_id = $cid";
            $db = get_db();
            $parentId = $db->query($query)->fetchAll();
            $parentId = $parentId[0]['parent_collection_id'];
            $parentName = $db->query("SELECT name FROM omeka_collection_trees WHERE collection_id = $parentId")->fetchAll();
            if ($parentName) {
                $parentName = $parentName[0]['name'];
            } else {
                $parentName = '';
            }

            if ($parentId <> 0) {
                $nom .= " (enfant de <em>$parentName</em>)";
            } else {
                $nom = "<b>$nom</b>";
            }
            $fieldCol = new Zend_Form_Element_Text('col_' . $cid);
            $fieldCol->setName($cid);
            $fieldCol->setAttrib('size', 3);
            $fieldCol->setLabel($nom);
            isset($order[$cid]) ? $num = $order[$cid] : $num = 0;
            if ($num) {
                $fieldCol->setValue($num);
            }
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

    public function orderCollections($cols)
    {
        $order = unserialize(get_option('sortcol_preferences'));
        foreach ($cols as $id => $col) {
            $cols[$id]['ordre'] = $order[$col['id']];
        }
        usort($cols, function ($a, $b) {
            return ($a['ordre'] < $b['ordre']) ? -1 : 1;
        });
        return $cols;
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
