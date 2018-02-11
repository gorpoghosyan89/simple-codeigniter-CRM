<?php $this->load->view('header');?>
<div class='mainInfo'>

    <h2>Spam Management</h2>

    <?php if(!empty($notice)): ?>
        <div class="alert alert-success">
            <?php echo $notice; ?>
        </div>
    <?php endif; ?>    

    <form action="" method="post"> 
        <input type="hidden" name="bulk_spam_filter" value="mark_all_new_as_spam">
        <button class="btn btn-default">Mark all new messages as spam</button>
    </form>

</div>

<?php $this->load->view('footer');?>