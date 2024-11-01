<?php $secondary_color = tajer_get_option( 'secondary_color', 'tajer_general_settings', 'green' ); ?>
<h4 class="ui dividing header"><?php esc_html_e( 'Billing Information', 'tajer' ); ?></h4>
<div class="fields">
	<div class="seven wide field">
		<label><?php esc_html_e( 'Card Number', 'tajer' ); ?></label>
		<input type="text" name="card[number]" maxlength="16" placeholder="<?php esc_attr_e( 'Card #', 'tajer' ); ?>">
	</div>
	<div class="three wide field">
		<label><?php esc_html_e( 'CVC', 'tajer' ); ?></label>
		<input type="text" name="card[cvc]" maxlength="3" placeholder="<?php esc_attr_e( 'CVC', 'tajer' ); ?>">
	</div>
	<div class="six wide field">
		<label><?php esc_html_e( 'Expiration', 'tajer' ); ?></label>

		<div class="two fields">
			<div class="field">
				<select class="ui fluid search dropdown" name="card[expire-month]">
					<option value=""><?php esc_html_e( 'Month', 'tajer' ); ?></option>
					<option value="1"><?php esc_html_e( 'January', 'tajer' ); ?></option>
					<option value="2"><?php esc_html_e( 'February', 'tajer' ); ?></option>
					<option value="3"><?php esc_html_e( 'March', 'tajer' ); ?></option>
					<option value="4"><?php esc_html_e( 'April', 'tajer' ); ?></option>
					<option value="5"><?php esc_html_e( 'May', 'tajer' ); ?></option>
					<option value="6"><?php esc_html_e( 'June', 'tajer' ); ?></option>
					<option value="7"><?php esc_html_e( 'July', 'tajer' ); ?></option>
					<option value="8"><?php esc_html_e( 'August', 'tajer' ); ?></option>
					<option value="9"><?php esc_html_e( 'September', 'tajer' ); ?></option>
					<option value="10"><?php esc_html_e( 'October', 'tajer' ); ?></option>
					<option value="11"><?php esc_html_e( 'November', 'tajer' ); ?></option>
					<option value="12"><?php esc_html_e( 'December', 'tajer' ); ?></option>
				</select>
			</div>
			<div class="field">
				<input type="text" name="card[expire-year]" maxlength="4"
				       placeholder="<?php esc_attr_e( 'Year', 'tajer' ); ?>">
			</div>
		</div>
	</div>
</div>

<!--<div class="ui error message">-->
<!--	<i class="close icon"></i>-->
<!---->
<!--	<div class="header">-->
<!--		--><?php //esc_html_e( 'There was some errors with your submission!', 'tajer' ); ?>
<!--	</div>-->
<!--	<ul class="list" id="tajer_purchase_form_errors">-->
<!--	</ul>-->
<!--</div>-->

<div class="ui hidden positive message" id="tajer_purchase_form_success_message">
	<i class="close icon"></i>

	<div class="header">
	</div>
	<p></p>
</div>

<div class="field">
	<div class="two fields">
		<div class="field">
			<button id="tajer_purchase_form_submit_button"
			        class='fluid ui <?php echo $secondary_color; ?> button'><?php esc_html_e( 'Checkout', 'tajer' ); ?></button>
		</div>
		<div class="field">
			<a href="<?php echo tajer_get_option( 'continue_shopping', 'tajer_general_settings', '#' ); ?>"
			   class="fluid ui button tajer-continue-shopping"><?php esc_html_e( 'Continue Shopping?', 'tajer' ); ?></a>
		</div>
	</div>
</div>