<?php
$desc = $event->getDescription(ThreadEvent::MODE_CLIENT);
if (!$desc)
    return;
?>
<div class="widget-timeline-item <?php if ($event->uid) echo 'action'; ?>">
	<div class="widget-timeline-info">
		<div class="widget-timeline-bullet"></div>
        <div class="widget-timeline-icon">
          <i class="faded fa fa-<?php echo $event->getIcon(); ?>"></i>
        </div>
	</div>
	<div class="panel panel-warning">
		<div class="panel-body">
			<span class="text-faded description"><?php echo $desc; ?></span>
		</div>	
	</div>	
             
</div>
