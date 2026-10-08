<?php
// TODO: reveiw 
if (!defined('DESKUSS_VERSION')) {
	die('Hacking Attempt!');
}

$ticket_activity = deskuss_get_ticket_activity($data->ticket_id);

//add features in the future
if (isset($data)) {
	render_ticket_modal($data, $ticket_activity);
}


function render_ticket_modal($data, $ticket_activity){
	?>
	<div class='deskuss_ticket-overlay'>
		<div class='deskuss_ticket-modal' data-modalid="<?php echo esc_attr($data->ticket_id);?>">
			<div class="deskuss_ticket-header">
				<div class="deskuss_ticket-header-details">
					<h2>
						<a href="<?php echo esc_url(get_site_url() . '/deskuss/admin/tickets.php?id=' . esc_attr($data->ticket_id)); ?>" >
							<?php echo esc_html($data->number); ?> 
						</a>
					</h2>
				</div>
				<span class="fa fa-times deskuss_cancel deskuss_close_task_modal" style="cursor:pointer"></span>
			</div>
			<div class='deskuss_ticket_body'>
				<div class="deskuss_ticket-body-left">
					<div class='deskuss_ticket-subject'>
						<h2><?php _e('Subject','deskuss'); ?></h2>
						<p><?php echo esc_html($data->subject);?></p>
					</div>
					<div class='deskuss_ticket-description'>
						<h2><?php _e('Description','deskuss'); ?></h2>
						<span><?php echo wp_kses_post($data->body);?></span>
					</div>
				</div>
				<div class="deskuss_ticket-body-right">
					<div class="deskuss-tabs" data-type="ticket">
						<span class="deskuss-tab deskuss-tab-active" data-tab="info">Ticket Info</span>
						<span class="deskuss-tab" data-tab="activity">Activity</span>
					</div>
					<div class="deskuss-tab-content deskuss_ticket-info deskuss-tab-active">
						<div class='deskuss_ticket-creator'><?php _e('Created by ','deskuss'); ?><?php echo esc_html($data->poster) ?></div>
						
						<div class='deskuss_ticket-assignee'><?php _e('Assigned by ','deskuss');?><?php echo esc_html($data->assigned_by)?></div>
						<div class='deskuss_ticket-priority'><?php _e('Priority ','deskuss');?><?php echo esc_html($data->priority)?></div>
						<?php if(!empty($data->duedate)) :?>
							<div class='deskuss_ticket-due-date'><?php _e('Due On ','deskuss');?><?php echo esc_html($data->duedate)?></div>
						<?php endif; ?>
					</div>

					<div class="deskuss-tab-content deskuss_ticket-activity">
						<div class="deskuss-activity">
							<ul class="deskuss_activity-container">
								<?php 
								if ( ! empty( $ticket_activity ) ) {
									
									foreach ( $ticket_activity as $item ) {
										$user_initials = substr($item->poster, 0, 2);
										$time = $item->entry_created;

										if ( intval($item->staff_id) === 0 ) {
											$log_title = esc_html( $item->poster ) . ' has created the ticket';
										} else {
											$staff = deskuss_get_user_info( $item->staff_id );
											$staff_name = '';

											if ( $staff ) {
												$staff_name = esc_html( $staff->member_firstname . ' ' . $staff->member_lastname );
											}

											$log_title = $staff_name . ' has replied to the ticket';
										}
								?>
										<li>
											<div class="deskuss_user"><?php echo esc_html($user_initials); ?></div>

											<div class="deskuss_log">
											<div class="deskuss_activity-content">
												<?php echo esc_html( $log_title ); ?>
											</div>

												<div class="deskuss_activity-time">
													<?php 
														echo esc_html(human_time_diff( strtotime($time), current_time('timestamp')) . ' ago');
													?>
												</div>
											</div>
										</li>
								<?php 
									}
								}
								?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php 
}
?>