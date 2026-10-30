<?php
/*
Always check for the constant WP_UNINSTALL_PLUGIN in uninstall.php before doing anything. This protects against direct access.
The constant will be defined by WordPress during the uninstall.php invocation.
The constant is NOT defined when uninstall is performed by `register_uninstall_hook`.
Reference: https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/#method-2-uninstall-php
*/
defined( 'WP_UNINSTALL_PLUGIN' ) || exit( 1 );

global $wpdb;

$wc_shipping_simulator_prefix = 'wc_shipping_simulator_';
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE %s",
        $wc_shipping_simulator_prefix . '%'
    )
);

/*
 * Licença do PRO e o boolean da página de boas-vindas.
 *
 * Estas options são escritas pelo PRO (e pela rota de ativação deste plugin),
 * mas moram no banco DESTE site. Deixá-las para trás faz a próxima instalação
 * nascer "suja": com a chave de uma compra anterior e com `onboarding_done`
 * já gravado, a página de boas-vindas do PRO nunca mais aparece (ela só
 * aparece quando esses booleanos estão vazios).
 *
 * A lista está duplicada em `tests/ProPurchaseCardTest.php` (o teste não pode
 * depender deste arquivo para saber o que esperar, e este arquivo não pode
 * depender das classes do plugin). `test_uninstall_lista_de_licenca_esta_completa`
 * compara as duas: tirar uma option daqui quebra o teste.
 */
$lkn_billing_license_options = array(
    'shipping_simulator_pro_license_key',
    'shipping_simulator_pro_license_token',
    'shipping_simulator_pro_license_license_id',
    'shipping_simulator_pro_license_domain',
    'shipping_simulator_pro_license_cert',
    'shipping_simulator_pro_license_policy',
    'shipping_simulator_pro_license_result',
    'shipping_simulator_pro_license_last_check',
    'shipping_simulator_pro_license_last_error',
    'shipping_simulator_pro_license_expires_at',
    'shipping_simulator_pro_license_installed',
    // Os três booleanos da boas-vindas: `done`/`shown` gravados, `pending` é o
    // armamento. Apagados, a primeira instalação volta a mostrar a página.
    'shipping_simulator_pro_license_onboarding_pending',
    'shipping_simulator_pro_license_onboarding_done',
    'shipping_simulator_pro_license_onboarding_shown',
);

foreach ($lkn_billing_license_options as $lkn_billing_option) {
    delete_option($lkn_billing_option);
}

// Transientes de guarda da boas-vindas (`lkn_pro_ob_{user_id}_{slug}`): sem
// isso, quem reinstalar o PRO fica até o TTL sem ver a página.
global $wpdb;
// O `LIKE` cobre os dois registros de cada transiente (`_transient_x` e
// `_transient_timeout_x`): sobrando o timeout, o nome fica no banco.
//
// Dois sufixos porque o slug do produto mudou: o PRO passou a usar o nome da
// PASTA do plugin (`shipping-simulator-for-woocommerce-pro`) e o apelido curto
// (`shipping-simulator-pro`) só sobrevive em guardas criadas por versões
// anteriores. Limpar os dois é o que faz a próxima instalação nascer limpa em
// qualquer um dos casos.
$lkn_billing_guard_slugs = array(
    '_shipping-simulator-for-woocommerce-pro',
    '_shipping-simulator-pro',
);

foreach ($lkn_billing_guard_slugs as $lkn_billing_guard_slug) {
    $lkn_billing_guard = $wpdb->esc_like('_transient_') . '%' . $wpdb->esc_like('lkn_pro_ob_') . '%'
        . $wpdb->esc_like($lkn_billing_guard_slug);

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $lkn_billing_guard
        )
    );
}
