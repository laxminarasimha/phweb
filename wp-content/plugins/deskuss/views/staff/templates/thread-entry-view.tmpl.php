<div class="panel panel-primary panel-dark m-b-0">
<div class="panel-heading">
<div class="panel-title drag-handle table-caption"><?php echo __('Original Thread Entry'); ?>
<a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a></div></div>

<div class="panel-body">
<div id="history" class="accordian">

<?php
$E = $entry;
$i = 0;
$omniscient = $thisstaff->hasPerm(ThreadEntry::PERM_EDIT);
do {
    $i++;
    if (!$omniscient
        // The current version is always visible
        && $i > 1
        // If you originally posted it, you can see all the edits
        && $E->staff_id != $thisstaff->getId()
        // You can see your own edits
        && ($E->editor != $thisstaff->getId() || $E->editor_type != 'S')
    ) {
        // Skip edits made by other agents
        continue;
    } ?>
<div class="panel">	
<dt class="panel-heading" style="background-color:white;">
    <a href="#"><i class="fa fa-files-o"></i>
    <label class="control-label"><?php if ($E->title)
        echo Format::htmlchars($E->title).' — '; ?></label>
    <em><?php if (strpos($E->updated, '0000-') === false)
        echo sprintf(__('Edited on %s by %s'), Format::datetime($E->updated),
            ($editor = $E->getEditor()) ? $editor->getName() : '');
    else
        echo __('Original'); ?></em>
    </a>
</dt>
<dd class="hidden panel-body" style="background-color:transparent">
    <div class="thread-body" style="background-color:transparent">
        <?php echo $E->getBody()->toHtml(); ?>
    </div>
</dd>
</div>
<?php
}
while (($E = $E->getParent()) && $E->type == $entry->type);
?>

</div>

<div class="form-group">
        <input type="button" name="cancel" class="close_me btn btn-outline" value="<?php echo __('Close'); ?>">
</div>

</form>
</div>
</div>

<script type="text/javascript">
$(function() {
	
  var I = setInterval(function() {
    var A = $('#history.accordian');
    if (!A.length) return;
    clearInterval(I);

    var allPanels = $('dd', A).hide().removeClass('hidden');
    $('dt > a', A).click(function() {
      if (!$(this).parent().is('.active')) {
        $('dt', A).removeClass('active');
        allPanels.slideUp();
        $(this).parent().addClass('active').next().slideDown();
      }
      return false;
    });
    allPanels.last().show().prev().addClass('active');
  }, 100);
});
