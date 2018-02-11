<?php $this->load->view('header');?>
<h2>Change Password</h2>

<div id="infoMessage"><?php echo $message;?></div>

<?php echo form_open("auth/change_password");?>

<div class="form-group">
      <label for="">Old Password:</label>
      <?php 
            $old_password['class'] = 'form-control'; 
            echo form_input($old_password);
      ?>
</div>
      
<div class="form-group">      
      <label for="">New Password (at least <?php echo $min_password_length;?> characters long):</label>
      <?php 
            $new_password['class'] = 'form-control'; 
            echo form_input($new_password);
      ?>            
</div>
      
<div class="form-group">      
      <label for="">Confirm New Password:</label>
      <?php 
            $new_password_confirm['class'] = 'form-control'; 
            echo form_input($new_password_confirm);
      ?>            
</div>
      
<div class="form-group">      
      <?php 
            $user_id['class'] = 'form-control'; 
            echo form_input($user_id);
      ?>      

      <?php echo form_submit('submit', 'Change');?>
</div>
      
<?php echo form_close();?>
<?php $this->load->view('footer');?>
