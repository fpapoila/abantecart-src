<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<?php echo $setting_tabs ?>

<div id="content" class="panel panel-default">
	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">
			<?php if($store_id > 0){ ?>
				<a class="actionitem  tooltips" title="<?php echo $edit_store_button->title; ?>" href="<?php echo $edit_store_button->href; ?>"><i class="fa fa-edit fa-2x"></i></a>
			<?php } ?>
			<?php if($delete_store_button){ ?>
				<a class="actionitem  tooltips"
				   title="<?php echo $delete_store_button->title; ?>"
				   href="<?php echo $delete_store_button->href; ?>"
				   data-confirmation="delete"
					><i class="fa fa-trash-o fa-2x"></i></a>
			<?php } ?>
			<a class="actionitem tooltips" title="<?php echo $new_store_button->title; ?>" href="<?php echo $new_store_button->href; ?>"><i class="fa fa-plus-circle fa-lg fa-2x"></i></a>
		</div>			
		<?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
	</div>

	<?php echo $form['form_open']; ?>
	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<?php foreach ($form['fields'] as $section => $fields) { ?>
		<label class="h4 heading"><?php echo $form_title; ?></label>
			<?php foreach ($fields as $name => $field) { ?>
			<?php
				//Logic to calculate fields width
				$widthcasses = "col-sm-7";
				if ( is_int(stripos($field->style, 'large-field')) ) {
					$widthcasses = "col-sm-7";
				} else if ( is_int(stripos($field->style, 'medium-field')) || is_int(stripos($field->style, 'date')) ) {
					$widthcasses = "col-sm-5";
				} else if ( is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch')) ) {
					$widthcasses = "col-sm-3";
				} else if ( is_int(stripos($field->style, 'tiny-field')) ) {
					$widthcasses = "col-sm-2";
				}
				$widthcasses .= " col-xs-12";
			?>
		<div class="form-group <?php if (!empty($error[$name])) { echo "has-error"; } ?>">
			<label class="control-label col-sm-4 col-xs-12" for="<?php echo $field->element_id; ?>"><?php echo ${'entry_' . $name}; ?></label>
			<div class="input-group afield <?php echo $widthcasses; ?> <?php echo ($name == 'description' ? 'ml_ckeditor' : '')?>">
				<?php echo $field;	?>
			</div>
		    <?php if (!empty($error[$name])) { ?>
		    <span class="help-block field_err"><?php echo $error[$name]; ?></span>
		    <?php } ?>
		</div>
			<?php }
			}  ?>

		<div id="image">
	    <?php if ( !empty($update) ) { echo $resources_html; } ?>
		</div>

	</div>

	<div class="panel-footer col-xs-12">
		<div class="text-center">
			<button class="btn btn-primary">
			<i class="fa fa-save fa-fw"></i> <?php echo $form['submit']->text; ?>
			</button>
			<a class="btn btn-default" href="<?php echo $cancel; ?>">
			<i class="fa fa-arrow-left fa-fw"></i> <?php echo $form['cancel']->text; ?>
			</a>
		</div>
	</div>

	</form>

</div><!-- <div class="tab-content"> -->

<script type="text/javascript"><!--
CKEDITOR.replace('store_description[<?php echo $content_language_id; ?>][description]', {
	filebrowserBrowseUrl : false,
    filebrowserImageBrowseUrl : '<?php echo $rl; ?>',
    filebrowserWindowWidth : '920',
    filebrowserWindowHeight : '520',
	language: '<?php echo $language_code; ?>'
});
//--></script>
