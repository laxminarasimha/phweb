<?php
/*
* TODO: reveiw 
* Deskuss
* https://deskuss.com
* (c) Softaculous Team
*/

$is_admin = current_user_can('administrator');

if (isset($data)) {
	$meta_data = deskuss_get_task_details($data->ID);
	$modal_board_id = isset($board_id) ? $board_id : wp_get_post_parent_id($data->ID);
	render_modal($data, $meta_data, $modal_board_id);
}

function render_modal($data, $meta_data, $board_id = 0) {
	$priority = array(
		'urgent' => array(
			'prio' => 'Urgent',
			'color'  => 'dc3545'
		),
		'high' => array(
			'prio' => 'High',
			'color'  => 'ffc107'
		),
		'medium' => array(
			'prio' => 'Medium',
			'color'  => '007bff'
		),
		'low' => array(
			'prio' => 'low',
			'color'  => '6c757d'
		),
	);
	$description = !empty($meta_data['content']) ? wp_kses_post($meta_data['content']) : 'No description available';
	$priority_level = !empty($meta_data['priority_level']) ? esc_html($meta_data['priority_level']) : '';
	$due_date = !empty($meta_data['due_date']) ? esc_html($meta_data['due_date']) : 'Add due date';
	$comments = isset($meta_data['comments'][0]) ?$meta_data['comments'] : [];
	$attachments = !empty($meta_data['attachments']) ? $meta_data['attachments'] : array();
	$assigned_value = !empty($meta_data['assigned']) ? $meta_data['assigned'] :'No members has assigned';
	$task_activity = deskuss_get_task_activity($data->ID);
	?>
	<div class="deskuss_modal-overlay">
		<!-- <div class='deskuss_loader' style='display:none'></div> -->
		<div class="deskuss_task-modal" data-modalId="<?php echo esc_attr($data->ID); ?>" data-board-id="<?php echo esc_attr($board_id); ?>">
			<div class="deskuss_task-header">
				<div class="deskuss_task-header-details">
					<h2><?php echo esc_html($data->post_title); ?></h2>
				</div>
				<span class="fa fa-remove deskuss_cancel deskuss_close_task_modal"
					style="cursor:pointer"></span>
			</div>

			<div class="deskuss_task-body">

				<div class="deskuss_task-body-left">
					<h3><?php _e('Task Actions', 'deskuss');?></h3>
					<div class="deskuss_task-body-actions">
						<div class="deskuss_action_member" style="position:relative">
							<button class="deskuss_task_members">
								<span class="fa fa-users" style="vertical-align:middle; height:15px; width:15px"></span>
								<span class="deskuss_text"><?php _e('Assign members','deskuss');?></span>
							</button>
							<div class="deskuss_members_list" style="display:none;">
								<div class='deskuss_staff'>
									<div class='deskuss_staff-container'>
										<div class='deskuss_assigned-members'>
											<h3 class='staff_heading'><?php _e('Assigned Staff','deskuss');?></h3>
											<div class="deskuss_members">
												<?php if (!empty($meta_data['assigned'])) : ?>
													<?php foreach ($meta_data['assigned'] as $data) : ?>
														<span data-assigned="<?php echo esc_attr($data); ?>"><?php echo esc_html($data); ?></span>
													<?php endforeach; ?>
												<?php else: ?>
													<span class='not_found'><?php _e('No staff assigned.','deskuss');?></span>
												<?php endif; ?>
											</div>
										</div>
										<div class='deskuss_loader' style='display:none'></div> 
									</div>
								</div>
							</div>
						</div>
						<div class="deskuss_move_task" style="position:relative">
							<button class="deskuss_move_task_btn">
								<span class="fa fa-undo"
									style="vertical-align:middle; height:15px; width:15px"></span>
								<span class="deskuss_text"><?php _e('Move','deskuss');?></span>
							</button>
							<div class="deskuss_move_list" style="display:none;">

							</div>
						</div>
						<div class="deskuss_task_label_container">
							<button class="deskuss_task_label">
								<span class="fa fa-tag"  style="vertical-align:middle; height:15px; width:15px"></span>
								<span class="deskuss_text"><?php _e('Add label','deskuss');?></span>
							</button>
							<div class="deskuss_label_appender" style="display:none;"></div>
						</div>

						<div style="position:relative">
							<button class="deskuss_task_priority" data-priority="<?php echo esc_html($priority_level); ?>">
								<span class="fa fa-flag level_<?php echo esc_attr($priority_level); ?>" 
									style="vertical-align:middle; height:15px;"></span>
								<span class="deskuss_text priority_text level_<?php echo esc_attr($priority_level); ?>">
									<?php _e('Priority ', 'deskuss');?><?php echo esc_html($priority_level); ?>
								</span>
							</button>

							<div class="deskuss_priority_appender" style="display:none;">
								<div class="deskuss-priority-container">
									<?php foreach($priority as $key => $pri): ?>
										<div class="deskuss_priority deskuss-<?php echo esc_attr( $key ); ?>"
											style="color:#<?php echo esc_attr($pri['color']); ?>"
											data-prislug="<?php echo esc_attr( $key ); ?>">
											<span class="fa fa-flag"
												style="color:#<?php echo esc_attr($pri['color']); ?>"></span>
											<?php echo esc_html($pri['prio']); ?>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
						<div>
							<input type="text" name='deskuss-datepicker' id="datepicker" style="display: none;">
							<button class="deskuss_task_calender">
								<span class="fa fa-calendar"
									style="vertical-align:middle; height:15px; width:15px"></span>
										<span class="deskuss_text"><?php echo esc_html( $due_date ); ?></span>
							</button>
						</div>
						<div>
							<button class="deskuss_task_media">
								<span class="fa fa-image"
									style="vertical-align:middle; height:15px; width:15px"></span>
								<span class="deskuss_text"><?php _e('Attachements','deskuss');?></span>
							</button>
						</div>
					</div>
					<div class="deskuss_task-body-attachements">
						<h2>Attachments</h2>
						<?php 
						$output = '';
						if(!empty ($attachments)){
							foreach ($attachments as $attachment) {
								$image_url = wp_get_attachment_url($attachment);
								$output .= '<div class="deskuss_attachment"><a href="' . esc_url($image_url) . '" data-lightbox="image"><img src="' . esc_url($image_url) . '" alt="" /></a></div>';
							}     
						}else{
							$output .= esc_html('Attachments not found');
						}
						?>
						<div class="deskuss_all_attachement"><?php echo $output; ?></div>
					</div>

					<div class="deskuss_task-body-description">
						<h2><?php _e('Description','deskuss');?></h2>
						<div id="rich_text_editor_<?php echo esc_attr($data->ID); ?>" class="deskuss-rich-text-editore type_desc">
										<?php echo wp_kses_post( $description ); ?>
						</div>
						<div class="text_editor_buttons" style='display:none'>
							<button class="deskuss-text-editore-save"><?php _e('Save','deskuss');?></button>
							<button class="deskuss_cancel text_editore_cancel"><?php _e('Close','deskuss');?></button>
						</div>
					</div>
				</div>
				<div class="deskuss_task-body-right">
					<div class="deskuss-tabs" data-type='task'>
						<span class="deskuss-tab deskuss-tab-active" data-tab="comments">Comment</span>
						<span class="deskuss-tab" data-tab="activity">Activity</span>
					</div>
					<div class="deskuss-tab-content deskuss_task-comments deskuss-tab-active">
						<div class="deskuss_comment">
							<?php if(!empty($comments)):?>
								<?php foreach ($comments as $comment): ?>
									<div class="deskuss_single_comment">
										<div class="deskuss_single_comment_meta">
											<span class="deskuss_commenter"><?php echo esc_html(substr($comment->author_name,0,2)); ?></span>
											<p class="comment-author"><?php echo esc_html($comment->author_name); ?><span class="deskuss-comment-date"> <?php echo esc_html( human_time_diff( strtotime($comment->created_at), current_time('timestamp'))) . ' ago';?></span></p>
										</div>
										<p class="comment-text"><?php echo wp_kses_post($comment->description); ?></p>
									</div>
								<?php endforeach; ?>
							<?php endif;?>
						</div>
						<div class="text_editor_buttons type_comment">
							<input type="text" class="deskuss_comments" placeholder="<?php _e('Add comments', 'deskuss');?>" />
							<button class="deskuss_text_editore_comment_save"><?php _e('Send','deskuss');?></button>
						</div>
					</div>
					<div class="deskuss-tab-content deskuss_task-activity">
						<div class="deskuss-activity">
							<ul class="deskuss_activity-container">
								<?php 
								if ( ! empty( $task_activity ) ) {
									foreach ( $task_activity as $item ) {
										$user_initials = substr($item['user'], 0, 2); 
										$time = $item['time'];
								?>
								
									<li>
												<div class="deskuss_user"><?php echo esc_html( $user_initials ); ?></div>
												<div class="deskuss_log">
													<div class="deskuss_activity-content"><?php echo esc_html( $item['log_title'] ); ?></div>
											<div class="deskuss_activity-time"> <?php echo esc_html( human_time_diff( strtotime($time), current_time('timestamp') ) . ' ago' ); ?></div>
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
