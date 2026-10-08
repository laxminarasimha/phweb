<div class="widget-timeline-item <?php if ($event->uid) echo 'action'; ?>">
	<div class="widget-timeline-info">
		<div class="widget-timeline-bullet"></div>
        <div class="widget-timeline-icon">
          <i class="<?php echo $event->getIcon(); ?>"></i>
        </div>
	</div>
	<div class="panel panel-warning">
		<div class="panel-body">
			<?php echo $event->getDescription(ThreadEvent::MODE_STAFF); ?>
		</div>	
	</div>	
</div>

