<?php $this->load->view('header');?>
<?php echo $output; ?>
<?php $this->load->view('footer');?>
<script type="text/javascript">
if("<?php echo $currmethod; ?>"=="index"){
    $("#agencieslist").html('<select id="agencieslistselect" class="form-control"><option value="ALL">All</option><?php foreach($agencieslist->result() as $al){?><option value="<?php echo $al->url_slug ;?>"><?php echo $al->name ;?></option>    <?php }?></select>');
    $("#categorylist").html('<select id="categorylistselect" class="form-control"><option value="ALL">All</option><?php foreach($categorieslist->result() as $cl){?><option value="<?php echo $cl->category_id; ?>"><?php echo $cl->category_name; ?></option><?php }?></select>');
    $("#sortbylist").html('<select id="sortbylistselect" class="form-control"><?php foreach($columnslist as $cll){?><option value="<?php echo $cll; ?>"><?php echo $cll; ?></option><?php }?></select>');
    $(".advexport-anchor").show();
};
</script>
