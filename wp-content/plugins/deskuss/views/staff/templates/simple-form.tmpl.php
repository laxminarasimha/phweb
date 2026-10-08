<?php if ($form->getTitle()) { ?>
	<div class="panel-title table-caption"><strong><?php echo Format::htmlchars($form->getTitle()); ?></strong>:
		<small><?php echo Format::htmlchars($form->getInstructions()); ?></small>
	</div>
<?php
}

foreach ($form->getFields() as $field) { ?>
	<div class="form-group">
	
	<?php
	if (!$field->isBlockLevel()) { ?>
		<div class="col-sm-5">
			<label class="<?php if ($field->isRequired()) echo 'required'; ?>">
				<?php echo Format::htmlchars($field->getLocal('label')); ?>:
			</label>
			<?php
			if ($field->get('hint')) { ?>
				<br />
				<small class="text-muted">
					<?php echo Format::viewableImages($field->getLocal('hint')); ?>
				</small>
			<?php } ?>
		</div>
		<div class="col-sm-7"><?php
	}
	
	$field->render($options);

	foreach ($field->errors() as $e) { ?>
	<?php
	if(!empty($e))
		echo '<div class="alert-danger"><?php echo Format::htmlchars($e); ?></div>';
	?>
	<?php }
	if (!$field->isBlockLevel()) { ?>
		</div>
	<?php } ?>

	</div>
<?php } ?>
