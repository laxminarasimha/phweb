
<script>
$(function () {
    $('.sort_tickets').click(function (e) {
		
        e.preventDefault();
		
		var query = addSearchParam({'sort': $(this).children("a").attr("data-mode"), 'dir': $(this).children("a").attr("data-dir")});
		window.location = '?' + query;
    });
});
</script>

<div class="btn-group">
  <button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown" title="<?php echo $sort_options[$sort_cols]; ?>"><i class="fa fa-caret-down pull-right"></i>
	<span><i class="fa fa-sort-amount-asc <?php if ($sort_dir); ?>"></i> <?php echo __('Sort');?></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-right">
    <?php foreach ($queue_sort_options as $mode) {
    $desc = $sort_options[$mode];
    $icon = '';
    $dir = '0';
    $selected = $sort_cols == $mode; ?>
    <li class="sort_tickets" <?php
		if ($selected) {
		echo 'class="active"';
		$dir = ($sort_dir == '1') ? '0' : '1'; // Flip the direction
		$icon = ($sort_dir == '1') ? 'fa fa-hand-o-up' : 'fa fa-hand-o-down';
		}
		?>>
		<a href="#" class="popup-dialog" data-mode="<?php echo $mode; ?>" data-dir="<?php echo $dir; ?>"><i class=" <?php echo $icon; ?>"></i> <?php echo Format::htmlchars($desc); ?></a>
	</li>
    <?php } ?>
 </ul>
</div>


