<?php
    // Form headline and deck with a horizontal divider above and an extra
    // space below.
    // XXX: Would be nice to handle the decoration with a CSS class
    ?>
   
	<div class="panel-heading" style="margin-bottom:0.5em">
		<div class="panel-title table-caption"><?php echo Format::htmlchars($form->getTitle()); ?>
		</div>
	</div>
	<div class="col-sm-12">
		<em style="color:gray;display:inline-block">
			<?php echo Format::htmldecode($form->getInstructions()); ?>
		</em>
	</div>
    <div style="padding:20px 20px 0px">
    <?php
    // Form fields, each with corresponding errors follows. Fields marked
    // 'private' are not included in the output for clients
    global $thisclient;
    foreach ($form->getFields() as $field) {
        if (isset($options['mode']) && $options['mode'] == 'create') {
            if (!$field->isVisibleToUsers() && !$field->isRequiredForUsers())
                continue;
        }
        elseif (!$field->isVisibleToUsers() && !$field->isEditableToUsers()) {
            continue;
        }
        ?>
		<div class="row">
		<div class="col-sm-12">
		<div class="form-group form-group-lg">
			<?php if (!$field->isBlockLevel()) { ?>
			<label for="_<?php echo $field->getFormName(); ?>" class="<?php
				if ($field->isRequiredForUsers()) echo 'required'; ?>">
				<span>
					<?php echo Format::htmlchars($field->getLocal('label')); ?>:
				</span>
			</label>
			<?php
			if ($field->get('hint')) { ?>
				&nbsp;<em style="color:gray;display:inline-block"><?php
					echo Format::viewableImages($field->getLocal('hint')); ?></em>
				<?php
			} ?>
            <?php
            }
			
            $field->render(array('client'=>true));
            
            foreach ($field->errors() as $e) { ?>
                <div class="alert-danger"><?php echo $e; ?></div>
            <?php }
            $field->renderExtras(array('client'=>true));
            ?>
        </div>
        </div>
        </div>
        <?php
    }
?>
</div>
