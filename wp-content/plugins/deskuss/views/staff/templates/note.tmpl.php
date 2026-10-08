<div class="quicknote panel" data-id="<?php echo $note->id; ?>">
<div class="panel-heading">
    <div class="header">
        <div class="pull-left">
            <i class="note-type fa <?php echo $note->getExtIconClass(); ?>"
                title="<?php echo $note->getIconTitle(); ?>"></i>&nbsp;
            <?php echo $note->getFormattedTime(); ?>
        </div>
        <div class="panel-heading-controls">
<?php
            echo '<div class="pull-left">'.$note->getStaff()->getName().'</div>';
if (isset($show_options) && $show_options) { ?>
            <div class="options no-pjax" style="display:inline;">
                <a href="#" class="action edit-note btn btn-xs btn-info btn-outline" title="edit"><i class="fa fa-pencil-square"></i></a>
                <a href="#" class="action save-note btn btn-xs btn-success btn-outline" style="display:none" title="save"><i class="fa fa-floppy-o"></i></a>
                <a href="#" class="action cancel-edit btn btn-xs btn-warning btn-outline" style="display:none" title="undo"><i class="fa fa-undo"></i></a>
                <a href="#" class="action delete btn btn-xs btn-danger btn-outline" title="delete"><i class="fa fa-trash"></i></a>
            </div>
<?php } ?>
        </div>
    </div>
</div>	
    <div class="body editable panel-body">
        <?php echo $note->display(); ?>
    </div>
</div>
