<section>

	<?php if ($error['warning']) { ?>
	<div class="warning alert alert-error alert-danger"><?php echo $error['warning']; ?></div>
	<?php } ?>

	<?php if ($show_instructions) { ?>
	<div class="alert alert-success"><?php echo $text_instructions; ?>
	<br/><a href="<?php echo $login; ?>"><?php echo $text_login; ?></a>
	</div>
	<?php } ?>

  
    <div class="lockedpanel">
        <div class="loginuser">
            <img src="<?php echo $template_dir; ?>image/login.png" alt="<?php echo $text_login; ?>" />
        </div>
        <div class="logged">
            <h4><?php echo $heading_title; ?></h4>
            <small class="text-muted"><a href="<?php echo $login; ?>"><?php echo $text_login; ?></a></small>
        </div>
        
        <?php if (!$show_instructions) { ?>        
 		<?php echo $form['form_open']; ?>
 		
		<?php foreach ($form['fields'] as $name => $field) { ?>
		<?php if( $field->type == 'input') { ?>
		<div class="form-group <?php if (!empty($error[$name])) { ?>has-error<?php } ?>">
			<?php if (!empty($error[$name])) { ?>
			<div class="help-block with-errors"><?php echo $error[$name]; ?></div>
			<?php } ?>
			<?php echo $field; ?>
		</div>	
		<?php } else if( $field->type == 'captcha')  { ?>
		<div class="form-group <?php if (!empty($error[$name])) { ?>has-error<?php } ?>">
			<?php if (!empty($error[$name])) { ?>
			<div class="help-block with-errors"><?php echo $error[$name]; ?></div>
			<?php } ?>
			<?php echo $field; ?>
		</div>	
		<?php } ?>		
		<?php } //foreach end ?>
		
		<button type="submit" class="btn btn-primary btn-block"><i class="fa fa-envelope-o"></i> <?php echo $form['submit']; ?></button>

		</form>
		<a href="<?php echo $login ?>"><?php echo $text_login ?></a>
		<?php } ?>
		
    </div><!-- lockedpanel -->
  
</section>