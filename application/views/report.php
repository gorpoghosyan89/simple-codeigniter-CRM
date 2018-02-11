<div class="single-report">
	<?php if ($image_url) { ?>
		<a href="<?php echo( $image_url ); ?>"><img class="image" src="<?php echo( $image_url ); ?>" alt=""/></a>
	<?php } ?>
	<div class="report-data">



		<a class="btn btn-default" href="<?php echo site_url('/admin/index/edit/' . $report->report_id) ?>">Edit this Report</a>

		<dl style="margin-top : 3em;">
			<dt>Report ID</dt>
			<dd><span class="report-id"><?php echo($report->report_id) ?></span>
			<?php if (!empty($report->source_client)) {
				echo($external_link); 
			} ?>
			
			</dd>
		</dl>
		<dl >
			<dt>Status</dt>
			<dd class="fmse-status-<?php echo($report->is_closed)?>"><?php echo($report->status_name)?></dd>
		</dl>
		<dl >
			<dt>Status Notes</dt>
			<dd><?php echo($report->status_notes)?></dd>
		</dl>
		<dl >
			<dt>Priority</dt>
			<dd class="fmse-prio<?php echo($report->priority)?>"><?php echo($report->prio_name)?></dd>
		</dl>
		<dl >
			<dt>Category</dt>
			<!-- id: <?php echo($report->category_id)?> -->
			<dd><?php echo($report->category_name)?></dd>
		</dl>
		<dl >
			<dt>Description</dt>
			<dd><?php echo($report->description)?></dd>
		</dl>
		<!--
		<dl >
			<dt>Token</dt>
			<dd><?php echo($report->token)?></dd>
		</dl>
		-->
		<div class="time-data">
			<dl >
				<dt>Requested</dt>
				<dd><?php echo($report->requested_datetime)?></dd>
			</dl>
			<dl >
				<dt>Updated</dt>
				<dd><?php echo($report->updated_datetime)?></dd>
			</dl>
			<dl >
				<dt>Expected</dt>
				<dd><?php echo($report->expected_datetime)?></dd>
			</dl>
		</div>
		<dl >
			<dt>External</dt>
			<dd>
				<?php if (!empty($report->source_client)) {
					echo($report->open311_clients_name); 
				}
				if (!empty($report->external_id)) { echo(" ref: " . $report->external_id); } ?>
			</dd>
		</dl>
		<dl >
			<dt>Media URL</dt>
			<dd>
				<?php if ($report->media_url ) { ?>
					<a href="<?php echo("$report->media_url"); ?>"><?php echo("$report->media_url"); ?></a>
				<?php } ?>
			</dd>
		</dl>

		<div class="location-data" style="display:none">
			<dl >
				<dt>Address</dt>
				<dd><?php echo($report->address)?></dd>
			</dl>
			<dl >
				<dt>Post code</dt>
				<dd><?php echo($report->postal_code)?></dd>
			</dl>
			<dl >
				<dt>lat : long</dt>
				<dd><?php echo($report->lat)?> : <?php echo($report->long)?></dd>
			</dl>
		</div>

		<div class="submitter-data">
			<dl >
				<dt>Email</dt>
				<dd><?php echo($report->email)?></dd>
			</dl>
			<dl >
				<dt>Device ID</dt>
				<dd><?php echo($report->device_id)?></dd>
			</dl>
			<dl >
				<dt>Account</dt>
				<dd><?php echo($report->account_id)?></dd>
			</dl>
			<dl >
				<dt>Name</dt>
				<dd>
					<?php echo($report->first_name)?>
					<?php echo($report->last_name)?>
				</dd>
			</dl>
			<dl >
				<dt>Phone</dt>
				<dd><?php echo($report->phone)?></dd>
			</dl>
		</div>
		
		<?php if( !empty($report->attribute) && $attributes = json_decode($report->attribute) ): ?>
		<h3 style="margin-top : 5em">Custom Attributes</h3>
		<div class="custom-attributes">
			<?php foreach ($attributes as $attribute): ?>
			<?php 
				// Set attribute title
				$attribute_title = (!empty($attribute->attribute_description)) ? $attribute->attribute_description : $attribute->attribute_code; 

				// Check if value is a URL, make it a link if it is
				if (!filter_var($attribute->value, FILTER_VALIDATE_URL) === false) {
				    $attribute->value = '<a href="' . $attribute->value . '">' . $attribute->value . '</a>';
				} 
			?>
			<dl >
				<dt><?php echo $attribute_title; ?></dt>
				<dd><?php echo $attribute->value ?></dd>
			</dl>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<h3 style="margin-top : 5em">Agency Delegation</h3>
		<div class="other-data">
			<dl >
				<dt>Responsible</dt>
				<dd><?php echo($report->agency_responsible)?></dd>
			</dl>
			<dl >
				<dt>Service notice</dt>
				<dd><?php echo($report->service_notice)?></dd>
			</dl>
			<dl >
				<dt>Engineer</dt>
				<dd><?php echo($report->engineer)?></dd>
			</dl>
		</div>



	</div>
</div>


