<?php
/**
 * Card lateral de marca/suporte das configurações.
 *
 * Portado do card da versão PRO.
 *
 * @package Shipping_Simulator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ss_card_backgrounds = isset( $backgrounds ) && is_array( $backgrounds ) ? $backgrounds : array(
	'right' => '',
	'left'  => '',
);
$ss_card_logo     = isset( $logo ) ? $logo : '';
$ss_card_whatsapp = isset( $whatsapp ) ? $whatsapp : '';
$ss_card_telegram = isset( $telegram ) ? $telegram : '';
$ss_card_versions = isset( $versions ) ? $versions : '';
?>
<div class="linkn-link-settings-card" style="background-image: url('<?php echo esc_url( $ss_card_backgrounds['right'] ); ?>'), url('<?php echo esc_url( $ss_card_backgrounds['left'] ); ?>');">
	<div class="linkn-link-logo" id="lkn-shipping-simulator-div-logo">
		<div>
			<?php if ( '' !== $ss_card_logo ) : ?>
				<img src="<?php echo esc_url( $ss_card_logo ); ?>" alt="Logo">
			<?php endif; ?>
		</div>
		<p><?php echo esc_html( $ss_card_versions ); ?></p>
	</div>
	<div class="linkn-link-content">
		<div class="linkn-link-links">
			<div>
				<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://www.linknacional.com.br/wordpress/plugins/' ); ?>">
					<b>&bull;</b><?php esc_html_e( 'WP Plugins', 'shipping-simulator-for-woocommerce' ); ?>
				</a>
				<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://www.linknacional.com.br/wordpress/hospedagem/' ); ?>">
					<b>&bull;</b><?php esc_html_e( 'Hospedagem', 'shipping-simulator-for-woocommerce' ); ?>
				</a>
			</div>
			<div>
				<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://www.linknacional.com.br/wordpress/suporte/' ); ?>">
					<b>&bull;</b><?php esc_html_e( 'Suporte WP', 'shipping-simulator-for-woocommerce' ); ?>
				</a>
				<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://www.linknacional.com.br/wordpress/' ); ?>">
					<b>&bull;</b><?php esc_html_e( 'Soluções WP', 'shipping-simulator-for-woocommerce' ); ?>
				</a>
			</div>
		</div>
		<div class="linkn-support-links">
			<div class="linkn-stars-div">
				<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://wordpress.org/plugins/shipping-simulator-for-woocommerce/#reviews' ); ?>">
					<p><?php esc_html_e( 'Avaliar o plugin', 'shipping-simulator-for-woocommerce' ); ?></p>
					<div class="linkn-stars">
						<span class="dashicons dashicons-star-filled linkn-stars-icon"></span>
						<span class="dashicons dashicons-star-filled linkn-stars-icon"></span>
						<span class="dashicons dashicons-star-filled linkn-stars-icon"></span>
						<span class="dashicons dashicons-star-filled linkn-stars-icon"></span>
						<span class="dashicons dashicons-star-filled linkn-stars-icon"></span>
					</div>
				</a>
			</div>
			<div class="linkn-contact-links">
				<?php if ( '' !== $ss_card_whatsapp ) : ?>
					<a href="<?php echo esc_url( 'https://www.linknacional.com.br/assets/whatsapp-grupo-wordpress' ); ?>" target="_blank" rel="noopener noreferrer">
						<img src="<?php echo esc_url( $ss_card_whatsapp ); ?>" alt="Whatsapp Icon" class="linkn-contact-icon">
					</a>
				<?php endif; ?>
				<?php if ( '' !== $ss_card_telegram ) : ?>
					<a href="<?php echo esc_url( 'https://t.me/wpprobr' ); ?>" target="_blank" rel="noopener noreferrer">
						<img src="<?php echo esc_url( $ss_card_telegram ); ?>" alt="Telegram Icon" class="linkn-contact-icon">
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
