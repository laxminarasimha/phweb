<div><?php
foreach ($form->getFields() as $field) { ?>
    <span style="display:inline-block;padding-right:5px;vertical-align:top">
        <label class="<?php if ($field->get('required')) echo 'required'; ?>">
            <?php echo Format::htmlchars($field->get('label')); ?></label>
        <div><?php
        $field->render();
		
        if ($field->get('hint') && !$field->isBlockLevel()) { ?>
            <br/><em style="color:gray;display:inline-block"><?php
                echo Format::htmlchars($field->get('hint')); ?></em>
        <?php
        }
        foreach ($field->errors() as $e) { ?>
            <br />
            <div class="alert-danger"><?php echo Format::htmlchars($e); ?></div>
        <?php } ?>
        </div>
    </span><?php
} ?>
</div>
