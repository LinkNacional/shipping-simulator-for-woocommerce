<?php use Shipping_Simulator\Helpers as h; ?>

<?php if ( count( $rates ) > 0 ) : ?>
	<?php do_action( 'wc_shipping_simulator_results_before', $data ) ?>

	<table aria-label="<?php esc_attr_e( 'Avaliable shipping options', 'shipping-simulator-for-woocommerce' ); ?>">
		<?php do_action( 'wc_shipping_simulator_results_start', $rates ) ?>

		<?php foreach ( $rates as $rate ) : ?>
			<tr class="shipping-rate-method-<?php echo esc_attr( $rate->get_method_id() ) ?>">
				<th class="col-label" scope="row">
					<span class="shipping-rate-label"><?php echo h::safe_html( $rate->get_label() ); ?></span>
					<?php do_action( 'wc_shipping_simulator_results_col_label', $rate ) ?>
				</th>
				<td class="col-cost">
					<?php echo wc_price( $rate->get_cost() ); ?>
					<?php do_action( 'wc_shipping_simulator_results_col_cost', $rate ) ?>
				</td>
			</tr>
		<?php endforeach; ?>

		<?php do_action( 'wc_shipping_simulator_results_end', $rates ) ?>
	</table>

	<?php do_action( 'wc_shipping_simulator_results_after', $data ) ?>
<?php else : ?>
	<?php do_action( 'wc_shipping_simulator_no_results_before', $data ) ?>

	<?php if ( $notice ) : ?>
		<div class="no-results"><?php echo h::safe_html( $notice ) ?></div>
	<?php endif; ?>

	<?php do_action( 'wc_shipping_simulator_no_results_after', $data ) ?>
<?php endif ?>
