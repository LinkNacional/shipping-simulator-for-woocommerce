<?php
if (!defined('ABSPATH')) {
    exit();
}
?>
<div id="WcShippingSimulatorLinkSettingsCard" style="background-image: url('<?php echo esc_url($backgrounds['right']); ?>'), url('<?php echo esc_url($backgrounds['left']); ?>'); display:none;">
    <div id="WcShippingSimulatorDivLogo">
        <div>
            <?php //phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
            <img src="<?php echo esc_url($logo); ?>" alt="Logo">
            <?php //phpcs:enable ?>
        </div>
        <p><?php echo esc_html($versions); ?></p>
    </div>
    <div id="WcShippingSimulatorDivContent">
        <div id="WcShippingSimulatorDivLinks">
            <div>
                <a target="_blank" href="<?php echo esc_url('https://wordpress.org/plugins/shipping-simulator-for-woocommerce/'); ?>">
                    <b>•</b><?php esc_html_e('Documentação', 'shipping-simulator-for-woocommerce'); ?>
                </a>
                <a target="_blank" href="<?php echo esc_url('https://www.linknacional.com.br/wordpress/'); ?>">
                    <b>•</b><?php esc_html_e('Hosting', 'shipping-simulator-for-woocommerce'); ?>
                </a>
            </div>
            <div>
                <a target="_blank" href="<?php echo esc_url('https://www.linknacional.com.br/wordpress/plugins/'); ?>">
                    <b>•</b><?php esc_html_e('WP Plugin', 'shipping-simulator-for-woocommerce'); ?>
                </a>
                <a target="_blank" href="<?php echo esc_url('https://www.linknacional.com.br/wordpress/suporte/'); ?>">
                    <b>•</b><?php esc_html_e('Suporte WP', 'shipping-simulator-for-woocommerce'); ?>
                </a>
            </div>
        </div>
        <div class="WcShippingSimulatorSupportLinks">
            <div id="WcShippingSimulatorStarsDiv">
                <a target="_blank" href="<?php echo esc_url('https://br.wordpress.org/plugins/shipping-simulator-for-woocommerce/#reviews'); ?>">
                    <p><?php esc_html_e('Avaliar o plugin', 'shipping-simulator-for-woocommerce'); ?></p>
                    <div class="WcShippingSimulatorStars">
                        <span class="dashicons dashicons-star-filled lkn-stars"></span>
                        <span class="dashicons dashicons-star-filled lkn-stars"></span>
                        <span class="dashicons dashicons-star-filled lkn-stars"></span>
                        <span class="dashicons dashicons-star-filled lkn-stars"></span>
                        <span class="dashicons dashicons-star-filled lkn-stars"></span>
                    </div>
                </a>
            </div>
            <div class="WcShippingSimulatorContactLinks">
                <a href="<?php echo esc_url('https://chat.whatsapp.com/IjzHhDXwmzGLDnBfOibJKO'); ?>" target="_blank" rel="noopener">
                    <?php if ( $whatsapp ) : ?>
                        <?php //phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
                        <img src="<?php echo esc_url($whatsapp); ?>" alt="Whatsapp Icon" class="WcShippingSimulatorContactIcon">
                        <?php //phpcs:enable ?>
                    <?php else : ?>
                        WhatsApp
                    <?php endif; ?>
                </a>
                <a href="<?php echo esc_url('https://t.me/wpprobr'); ?>" target="_blank" rel="noopener">
                    <?php if ( $telegram ) : ?>
                        <?php //phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
                        <img src="<?php echo esc_url($telegram); ?>" alt="Telegram Icon" class="WcShippingSimulatorContactIcon">
                        <?php //phpcs:enable ?>
                    <?php else : ?>
                        Telegram
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</div>
