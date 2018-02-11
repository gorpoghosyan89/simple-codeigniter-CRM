<?php $this->load->view('header'); ?>
<div class='mainInfo'>

    <h2><?php echo $title ?></h2>

    <div id="infoMessage"><?php if (!empty($message)) echo $message; ?></div>

    <?php echo form_open('auth/manage_user'); ?>

    <div class="form-group">
        <label>First Name:</label>
        <?php echo form_input($first_name, $first_name, ' class="form-control"'); ?>
    </div>

    <div class="form-group">
        <label>Last Name:</label>
        <?php echo form_input($last_name, $last_name, ' class="form-control"'); ?>
    </div>

    <div class="form-group">
        <label>Company Name:</label>
        <?php echo form_input($company, $company, ' class="form-control"'); ?>
    </div>

    <div class="form-group">
        <!--            <label>Email:</label>-->
        <!--            --><?php //echo form_input($email, $email, ' class="form-control"');?>
        <strong>Email:</strong> <em><?php echo $email['value'] ?></em>
    </div>

    <?php if (false): //if ($action == 'create'): ?>
        <!--            <div class="form-group">-->
        <!--                  <label>Password:</label>-->
        <!--                  --><?php //echo form_input($password, $password, ' class="form-control"');?>
        <!--            </div>-->
        <!---->
        <!--            <div class="form-group">-->
        <!--                  <label>Confirm Password:</label>-->
        <!--                  --><?php //echo form_input($password_confirm, $password_confirm, ' class="form-control"');?>
        <!--            </div>-->
    <?php endif; ?>

    <?php if ($action == 'edit' && !empty($all_groups)): ?>
        <div class="form-group">
            <h4>Groups</h4>
            <?php foreach ($all_groups as $group): ?>

                <?php
                $checked = '';
                foreach ($user_groups as $user_group) {
                    if ($user_group->id == $group->id) {
                        $checked = ' checked';
                    }
                }
                reset($user_groups);
                ?>

                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="groups[<?php echo $group->id ?>]" <?php echo $checked ?>>
                        <?php echo $group->description ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="user_id" value="<?php echo $id['value'] ?>">
        <input type="hidden" name="action" value="update">
    <?php endif; ?>

    <div class="form-group">
        <?php echo form_submit('submit', $title); ?>
    </div>


    <?php echo form_close(); ?>

</div>
<?php $this->load->view('footer'); ?>
