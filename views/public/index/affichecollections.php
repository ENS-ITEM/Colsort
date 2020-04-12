<?php
echo head(array('title' => 'Arborescence du corpus', 'bodyclass' => 'collections browse')); ?>

<style>
div.notices, div.collections {
  display:none;
}
.montrer {
  cursor:pointer;
  color:#aaa;
}
.collection {
  font-weight : bold;
}
#titre-arbo {
  font-weight: bold;
}
.tout {
  cursor:pointer;
}
</style>
<script>
$ = jQuery;
$(document).ready(function() {
    $('.montrer').click(function() {
        $(this).parent().nextAll('div.notices, div.collections').toggle();
    });
    $('.tout').click(function() {
        $('div.notices, div.collections').toggle();
        if ($(this).html() == 'Tout replier') {
            $(this).html('Tout d&eacute;plier');
        } else {
            $(this).html('Tout replier');
        }
    });
});
</script>

<div id='collection-tree'>
<h3 id="titre-arbo">Arborescence du corpus</h3>
<span style="float:right;clear:both;" class='tout'>Tout d&eacute;plier</span><br />
<?php echo $tree; ?>
</div>
<?php echo foot(); ?>
