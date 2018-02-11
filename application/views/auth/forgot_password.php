<?php $this->load->view('header');?>
<h2>Forgot Password</h2>
<p>Please enter your email address so we can send you an email to reset your password.</p>

<div id="infoMessage"><?php echo $message;?></div>

<?php echo form_open("auth/forgot_password");?>

      <p>Email Address:<br />
      <?php echo form_input($email);?>
      </p>
      
      <p><?php echo form_submit('submit', 'Submit');?></p>
      
<?php echo form_close();?>
<?php $this->load->view('footer');?>
