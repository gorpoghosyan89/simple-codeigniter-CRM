<?php $this->load->view('header');?>
<div class='mainInfo'>
<h2>Login</h2>
<div class="pageTitleBorder"></div>
<p>Please login with your email address and password below.</p>

<div id="infoMessage"><?php echo $message;?></div>
<div class="login-form">
	<?php echo form_open("auth/login");?>
		<div class="login-identity">
			<label for="identity">Email address:</label>
			<?php echo form_input($identity);?>
		</div>
		<div class="login-password">
			<label for="password">Password:</label>
			<?php echo form_input($password);?>
		</div>
		<div class="login-remember">
			<label for="remember">Remember Me:</label>
			<?php echo form_checkbox('remember', '1', FALSE, 'id="remember"');?>
		</div>
		<div class="login-submit">
			<?php echo form_submit('submit', 'Login');?>
		</div>
	<?php echo form_close();?>
</div>
<p><a href="forgot_password">Forgot your password?</a></p>

</div>
<?php $this->load->view('footer');?>