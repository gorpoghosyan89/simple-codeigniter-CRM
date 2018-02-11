<?php if ($subject == 'Report'): ?>

<script type="text/javascript">
$(function () {
	//CHECK ALL BOXES
	$('.checkall').click(function () {
		$(this).parents('table:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	//ADD DELETE BUTTON
	if($('#ajax_list .delete_all_button').length == 0) { //check if element already exists (for ajax refresh purposes)
		$('#ajax_list').append('<input type="button" value="Delete Selected" class="delete_all_button" onclick="delete_selected();">');
	}
});

function delete_selected()
{
	var list = "";
	$('input[type=checkbox]').each(function() {
		if (this.checked) {
			//remove selection rows
			$('#custom_tr_'+this.value).remove();
			//create list of values that will be parsed to controller
			list += this.value + '|';
		}
	});
	//send data to delete
	$.post(base_url + '/admin/delete_selection', { selection: list }, function(data) {
		var count = list.split("|").length - 1;
		alert('Deleted ' + count + ' records');
	});
}
</script>

<?php endif; ?>

<?php
if(!empty($list)){ ?>
<div class="span12" >

	<table class="table table-bordered tablesorter table-striped">
		<thead>
			<tr>
				<?php if ($subject == 'Report'): ?><th><input type="checkbox" class="checkall" /></th><?php endif; ?>
				<?php foreach($columns as $column){?>
				<th>
					<div class="text-left field-sorting <?php if(isset($order_by[0]) &&  $column->field_name == $order_by[0]){?><?php echo $order_by[1]?><?php }?>"
						rel="<?php echo $column->field_name?>">
						<?php echo $column->display_as; ?>
					</div>
				</th>
				<?php }?>
				<?php if(!$unset_delete || !$unset_edit || !empty($actions)){?>
				<th class="no-sorter">
						<?php echo $this->l('list_actions'); ?>
				</th>
				<?php }?>
			</tr>
		</thead>
		<tbody>
			<?php foreach($list as $num_row => $row){ ?>

			<?php
			if ($subject == 'Report') {
				$temp_string = $row->delete_url;
				$temp_string = explode("/", $temp_string);
				$row_num     = sizeof($temp_string)-1;
				$rowID       = $temp_string[$row_num];
			} else {
			    $rowID = $row_num = 0;
                $temp_string = '';
            }
			?>

			<tr class="<?php echo ($num_row % 2 == 1) ? 'erow' : ''; ?>" id="custom_tr_<?php echo $rowID ?>">
				<?php if ($subject == 'Report'): ?><td><input type="checkbox" name="custom_delete" value="<?php echo $rowID ?>" /></td><?php endif; ?>
				<?php foreach($columns as $column){?>
					<td class="<?php if(isset($order_by[0]) &&  $column->field_name == $order_by[0]){?>sorted<?php }?>">
						<div class="text-left"><?php echo ($row->{$column->field_name} != '') ? $row->{$column->field_name} : '&nbsp;' ; ?></div>
					</td>
				<?php }?>
				<?php if(!$unset_delete || !$unset_edit || !empty($actions)){?>
				<td align="left">
					<div class="tools">
						<div class="btn-group">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							  <?php echo $this->l('list_actions'); ?> <span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								<?php
								if(!$unset_edit){?>
									<li>
										<a href="<?php echo $row->edit_url?>" title="<?php echo $this->l('list_edit')?> <?php echo $subject?>">
											<i class="icon-pencil"></i>
											<?php echo $this->l('list_edit') . ' ' . $subject; ?>
										</a>
									</li>
								<?php
								}
								if(!$unset_delete){?>
									<li>
										<a href="javascript:void(0);" data-target-url="<?php echo $row->delete_url?>" title="<?php echo $this->l('list_delete')?> <?php echo $subject?>" class="delete-row" >
											<i class="icon-trash"></i>
											<?php echo $this->l('list_delete') . ' ' . $subject; ?>
										</a>
									</li>
								<?php
								}
								if(!empty($row->action_urls)){
									foreach($row->action_urls as $action_unique_id => $action_url){
										$action = $actions[$action_unique_id];
										?>
										<li>
											<a href="<?php echo $action_url; ?>" class="<?php echo $action->css_class; ?> crud-action" title="<?php echo $action->label?>"><?php
											if(!empty($action->image_url)){ ?>
												<img src="<?php echo $action->image_url; ?>" alt="" />
											<?php
											}
											echo ' '.$action->label;
											?>
											</a>
										</li>
									<?php
									}
								}
								?>
								</ul>
							</div>
							<div class="clear"></div>
						</div>
					</td>
					<?php }?>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
<?php }else{ ?>
	<br/><?php echo $this->l('list_no_items'); ?><br/><br/>
<?php }?>
