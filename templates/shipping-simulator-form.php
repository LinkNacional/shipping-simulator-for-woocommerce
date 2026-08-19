<section id="wc-shipping-sim" class="<?php echo esc_attr( $css_class ) ?>">
	<?php do_action( 'wc_shipping_simulator_wrapper_start' ) ?>

	<form method="POST" enctype="application/x-www-form-urlencoded" id="wc-shipping-sim-form" data-params="<?php echo esc_attr( wp_json_encode( $params ) ) ?>">
		<input type="hidden" name="wc_shipping_simulator_submit" value="1">

		<?php do_action( 'wc_shipping_simulator_form_start' ) ?>

		<div id="wc-shipping-sim-form-fields">
			<?php do_action( 'wc_shipping_simulator_form_fields_start' ) ?>

			<input name="postcode" type="<?php echo esc_attr( $input_type ); ?>" value="<?php echo esc_attr( $input_value ) ?>" placeholder="<?php echo esc_attr( $input_placeholder ); ?>" aria-label="<?php echo esc_attr( $input_placeholder ); ?>" class="input-text input-postcode" required>

			<?php do_action( 'wc_shipping_simulator_form_before_button' ) ?>

			<button type="submit" class="button submit" aria-label="<?php esc_attr_e( 'Calculate shipping', 'shipping-simulator-for-woocommerce' ); ?>"><?php echo esc_html( $submit_label ); ?></button>

			<?php do_action( 'wc_shipping_simulator_form_fields_end' ) ?>
		</div>

		<?php do_action( 'wc_shipping_simulator_form_end' ) ?>
	</form>

	<?php do_action( 'wc_shipping_simulator_form_after' ) ?>

	<?php do_action( 'wc_shipping_simulator_wrapper_end' ) ?>
</section>
