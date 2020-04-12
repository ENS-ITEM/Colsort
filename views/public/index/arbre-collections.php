<?php
queue_css_file('colsort');
queue_js_file('colsort');

echo head(array('title' => 'Arborescence du corpus', 'bodyclass' => 'collections browse'));
?>

<div id='collection-tree'>
<h3 id="titre-arbo">Arborescence du corpus</h3>
<span style="float:right;clear:both;" class='tout'>Tout d&eacute;plier</span><br />
<?php echo $tree; ?>
</div>
<?php echo foot(); ?>
