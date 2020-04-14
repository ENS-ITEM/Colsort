<?php
/**
 * @var Omeka_View $this
 */
?>

<fieldset id="fieldset-colsort"><legend><?php echo __('Public'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('colsort_append_items', __('Inclure les items dans l’arborescence')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formCheckbox(
                'colsort_append_items',
                true,
                array('checked' => (bool) get_option('colsort_append_items'))
            ); ?>
            <p class="explanation">
                <?php echo __('Si coché, l’arborescence contiendra également les items.'); ?>
            </p>
        </div>
    </div>
</fieldset>
