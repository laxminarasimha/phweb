<?php
global $thisstaff, $cfg, $role, $dept;
$role  = $thisstaff->getRole($dept);
$timeFormat = null;
if ($thisstaff && !strcasecmp($thisstaff->datetime_format, 'relative')) {
    $timeFormat = function($datetime) {
        return Format::relativeTime(Misc::db2gmtime($datetime));
    };
}

$entryTypes = array('M'=>'message', 'R'=>'response', 'N'=>'note');
$user = $entry->getUser() ?: $entry->getStaff();
$name = $user ? $user->getName() : $entry->poster;
$avatar = '';
if ($user && $cfg->isAvatarsEnabled())
    $avatar = $user->getAvatar();
?>
<style>
.dialog{
	z-index:9999;
}
.thread-entry > .avatar {
    display: inline-block;
    width: 48px;
    height: auto;
    border-radius: 5px;
}
.avatar > img.avatar {
    width: 100%;
    height: auto;
}
</style>
<div style="clear: both;"></div>
<div class="panel-body thread-entry <?php echo $entryTypes[$entry->type]; ?> <?php if ($avatar) echo 'avatar'; ?>" style="overflow-x: auto;position: relative;">
<?php if ($avatar) { ?>
    <span class="<?php echo ($entry->type == 'M') ? 'pull-left' : 'pull-right'; ?> avatar">
<?php echo $avatar; ?>
    </span>
<?php } ?>
		<div class="popover popover-<?php  echo ($entry->type == 'M') ? 'info' : 'success'; ?>  fade <?php echo ($entry->type == 'M') ? 'right' : 'left'; ?> in"  style="position:relative;display: block;max-width:none;margin-<?php echo ($entry->type == 'M') ? 'left' : 'right'; ?>: 66px" >
		<div class="arrow" style="top: 20px;"></div>
			<div class="popover-title">
				<div class="header">
				<div class="panel-heading-controls">
					<div class="pull-right">
				<?php
				if ($role->hasPerm(TicketModel::PERM_REPLY)) { ?>
				<div id="entry-action-reply-<?php echo $entry->getId(); ?>" class="btn-group anchor-right btn-group-xs">
					<a href="#post-reply" class="btn post-response action-button" title="<?php echo __('Post Reply'); ?>"><i class="fa fa-reply"></i>&nbsp; <?php echo __('Reply'); ?></a>
				</div>
				<?php
				} ?>
			<?php   if ($entry->hasActions()) {
						$actions = $entry->getActions(); ?>
						<div id="entry-action-more-<?php echo $entry->getId(); ?>" class="btn-group action-dropdown anchor-right btn-group-xs">	
							 <span data-toggle="dropdown" class="btn dropdown-toggle" data-dropdown="#entry-action-more-<?php echo $entry->getId(); ?>">
								<i class="fa fa-caret-down"></i><i class="fa fa-cog"></i>
							</span>  
							<ul class="dropdown-menu dropdown-menu-right">
				<?php       foreach ($actions as $group => $list) {
								foreach ($list as $id => $action) { ?>
								<li>
									<a class="no-pjax" href="#" onclick="javascript:
									<?php echo str_replace('"', '\\"', $action->getJsStub()); ?>; return false;">
									<i class="<?php echo $action->getIcon(); ?>"></i> <?php
									echo $action->getName();
								?></a></li>
				<?php           }
							} ?>
							</ul>
						</div>
			<?php   } ?>
					<span class="textra light">
			<?php   if ($entry->flags & ThreadEntry::FLAG_EDITED) { ?>
						<span class="label label-bare" title="<?php
						echo sprintf(__('Edited on %s by %s'), Format::datetime($entry->updated),
							($editor = $entry->getEditor()) ? $editor->getName() : '');
							?>"><?php echo __('Edited'); ?></span>
			<?php   }
					if ($entry->flags & ThreadEntry::FLAG_RESENT) { ?>
						<span class="label label-bare"><?php echo __('Resent'); ?></span>
			<?php   }
					if ($entry->flags & ThreadEntry::FLAG_COLLABORATOR) { ?>
						<span class="label label-bare"><?php echo __('Collaborator'); ?></span>
			<?php   } ?>
					</span>
				</div>
			</div>
			<?php
					echo sprintf(__('<span class="panel-title">%s</span> posted %s'), $name,
						sprintf('<a name="entry-%d" href="#entry-%1$s"><time %s
							datetime="%s" data-toggle="tooltip" title="%s">%s</time></a>',
							$entry->id,
							$timeFormat ? 'class="relative"' : '',
							date(DateTime::W3C, Misc::db2gmtime($entry->created)),
							Format::daydatetime($entry->created),
							$timeFormat ? $timeFormat($entry->created) : Format::datetime($entry->created)
						)
					); ?>
						<span style="max-width:500px" class="faded title truncate"><?php
							echo $entry->title; ?>
						</span>
				</div>
			</div>
			<div class="popover-content thread-body no-pjax" style="background: <?php  echo ($entry->type == 'M') ? '#f3fbfb' : '#fafff7'; ?>">
				<div>
					<div><?php echo $entry->getBody()->toHtml(); ?></div>
				</div>
				<?php
					// The strangeness here is because .has_attachments is an annotation from
					// Thread::getEntries(); however, this template may be used in other
					// places such as from thread entry editing
					$atts = isset($thread_attachments) ? $thread_attachments[$entry->id] : $entry->attachments;
					if (isset($atts) && is_array($atts)) {
				?>
				
				<div class="panel-footer">
					<div class="attachments"><?php
						foreach ($atts as $A) {
							if ($A->inline)
								continue;
							$size = '';
							if ($A->file->size)
								$size = sprintf('<small class="filesize faded">%s</small>', Format::file_size($A->file->size));
						?>
						<span class="attachment-info">
						<i class="fa fa-paperclip"></i>
						<a class="no-pjax truncate filename" href="<?php echo $A->file->getDownloadUrl();
							?>" download="<?php echo Format::htmlchars($A->getFilename()); ?>"
							target="_blank"><?php echo Format::htmlchars($A->getFilename());
						?></a>&nbsp;<?php echo $size;?>
						</span>
						<?php
						}
				echo '</div>
				</div>';
					}
				?>
				
				
			</div>
		</div>	
			<?php
					if (!isset($thread_attachments) && ($urls = $entry->getAttachmentUrls())) { ?>
						<script type="text/javascript">
							$('#thread-entry-<?php echo $entry->getId(); ?>')
								.data('urls', <?php
									echo JsonDataEncoder::encode($urls); ?>)
								.data('id', <?php echo $entry->getId(); ?>);
						</script>
				<?php
					} ?>

</div>
