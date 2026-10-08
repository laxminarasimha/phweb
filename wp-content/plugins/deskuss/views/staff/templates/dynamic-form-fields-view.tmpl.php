<?php

$warnbtn= dsk_optGET('warnbtn');
?>
<tbody data-form-id="<?php echo $form->get('id'); ?>" class="sortable-rows">
    <tr>
        <td class="handle" colspan="7">
            <input type="hidden" name="forms[]" value="<?php echo $form->get('id'); ?>" />
            <div class="pull-right">
            <i class="fa fa-arrows fa-lg"></i>
            <a data-type="warning" data-message='You must click "<?php echo $warnbtn; ?>" to apply the new changes' class="growl_popup" href="#" title="<?php echo __('Delete'); ?>" onclick="javascript:
            if (confirm(__('You sure?'))) {
                var tbody = $(this).closest('tbody');
                $(this).closest('form')
                    .find('[name=form_id] [value=' + tbody.data('formId') + ']')
                    .prop('disabled', false);
                tbody.fadeOut(function(){this.remove()});
            }
            return false;"><i class="fa fa-2x fa-trash"></i></a>
            </div>
            <div><strong><?php echo Format::htmlchars($form->getLocal('title')); ?></strong></div>
            <div><?php echo Format::htmldecode($form->getLocal('instructions')); ?></div>
        </td>
    </tr>
    <tr class="header">
        <th><?php echo __('Enable'); ?></th>
        <th><?php echo __('Label'); ?></th>
        <th><?php echo __('Type'); ?></th>
        <th><?php echo __('Visibility'); ?></th>
        <th><?php echo __('Variable'); ?></th>
    </tr>
<?php
    foreach ($form->getFields() as $f) { ?>
    <tr>
        <td><input type="checkbox" name="fields[]" value="<?php
            echo $f->get('id'); ?>" <?php
            if ($f->isEnabled()) echo 'checked="checked"'; ?>/></td>
        <td><?php echo $f->get('label'); ?></td>
        <td><?php $t=FormField::getFieldType($f->get('type')); echo __($t[0]); ?></td>
        <td><?php echo $f->getVisibilityDescription(); ?></td>
        <td><?php echo $f->get('name'); ?></td>
    </tr>
    <?php } ?>
</tbody>

