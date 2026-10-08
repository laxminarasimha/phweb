<?php

if(!defined('DSKCLIENTINC') || !$thisclient || !$ticket || !$ticket->checkUserAccess($thisclient)) die('Access Denied!');

?>
<div class="panel">
<div class="panel-heading">
<div class="panel-title table-capton">
    <?php echo sprintf(__('Editing Ticket #%s'), $ticket->getNumber()); ?>
</div>
</div>

<div class="panel-body">
<form action="tickets.php" method="post">
    <?php echo csrf_token(); ?>
    <input type="hidden" name="a" value="edit"/>
    <input type="hidden" name="id" value="<?php echo Format::htmlchars($_REQUEST['id']); ?>"/>

    <div id="dynamic-form">
    <?php if ($forms)
        foreach ($forms as $form) {
            $form->render(false);
    } ?>
    </div>


<p style="text-align: center;">
    <input type="submit" class="btn btn-success" value="Update"/>
    <input type="reset" class="btn btn-info" value="Reset"/>
    <input type="button" class="btn" value="Cancel" onclick="javascript:
        window.location.href='tickets.php?id=<?php echo esc_attr($ticket->getId()); ?>'" />
</p>

</form>
</div>
</div>