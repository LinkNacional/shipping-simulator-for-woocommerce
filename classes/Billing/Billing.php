<?php
/**
 * Módulo de compra da licença PRO.
 *
 * O plugin gratuito é quem conversa com o cliente: abre o pop-up, manda os
 * dados de pagamento para a API de cobrança, instala o `.zip` e grava a chave
 * no plugin PRO.
 *
 * ── O que NÃO passa por aqui ──────────────────────────────────────────────
 * Nada de dado sensível fica neste site. O número do cartão vai do navegador
 * direto para a API de cobrança (HTTPS) e nunca é gravado, nem no navegador
 * nem no banco do WordPress. O que sobra aqui é a chave de licença — que é
 * pública para o dono da loja, é dele.
 *
 * ── Duas cópias ───────────────────────────────────────────────────────────
 * Este módulo é copiado no `shipping-simulator-for-woocommerce` (mesma
 * estrutura, namespace diferente), como já acontece com `Includes/License/`
 * dos PROs. O que muda entre as cópias está só na constante `CONFIG` de
 * `config()` — slug do produto, option da licença e basename do PRO.
 *
 * @package Shipping_Simulator\Billing
 */

namespace Shipping_Simulator\Billing;

use Shipping_Simulator\Helpers as h;

// Impede acesso direto.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Configuração do fluxo de compra e o boot dos hooks.
 */
final class Billing
{
    /** Versão do módulo (cache-busting dos assets próprios). */
    public const VERSION = '1.0.0';

    /**
     * A versão do asset sai do `filemtime`, não de `VERSION`.
     *
     * `VERSION` é fixa: só muda quando alguém lembra de bumpá-la. Sem isso o
     * navegador continua servindo o JS antigo depois de atualizar o plugin, e um
     * JS antigo fala o contrato antigo da API — o pop-up passa a receber 404
     * (`unknown_product`) porque o campo do produto mudou de nome.
     */
    private static function asset_version( string $file ): string {
        $path = \plugin_dir_path( \Shipping_Simulator\Core\Config::get( 'FILE' ) ) . 'classes/Billing/assets/' . $file;
        $time = is_readable( $path ) ? filemtime( $path ) : false;

        return false === $time ? self::VERSION : self::VERSION . '.' . $time;
    }

    /** Prefixo/namespace das rotas REST. */
    public const REST_NAMESPACE = 'lkn-billing/v1';

    /**
     * Token público embutido no `.zip`.
     *
     * NÃO é senha: qualquer pessoa que baixe o plugin lê este valor. Serve só
     * para a API separar o tráfego do plugin do tráfego aleatório — quem
     * protege de verdade é o rate limit e o antifraude do PSP.
     */
    public const FREE_TOKEN = 'free-sbx-3f9a1c77d0b24e58a6c1';

    /**
     * Configuração desta cópia.
     *
     * @return array<string, mixed>
     */
    public static function config(): array
    {
        /**
         * URL da API de cobrança.
         *
         * Em produção aponta para o serviço da Link Nacional. Em
         * desenvolvimento (`WP_DEBUG`) vai para a instância local, para não
         * precisar de túnel só para testar o pop-up.
         */
        $api_url = defined('LKN_BILLING_API_URL')
            ? (string) constant('LKN_BILLING_API_URL')
            : ((defined('WP_DEBUG') && WP_DEBUG) ? 'http://localhost:8100' : 'https://billing.linknacional.com.br');

        return array(
            /**
             * Slug do produto na API de cobrança e na de licenças.
             *
             * É o nome da PASTA do plugin PRO — o mesmo `PRO_BASENAME` usado
             * para instalar/ativar logo abaixo, só sem o `/arquivo.php`.
             * Derivar daqui, e não de um apelido digitado à mão
             * (`shipping-simulator-pro`), mantém os dois lados falando do
             * MESMO plugin: o `.zip` que o pop-up instala é
             * `shipping-simulator-for-woocommerce-pro.zip` e a licença é
             * emitida para `shipping-simulator-for-woocommerce-pro`.
             */
            'product' => dirname( \Shipping_Simulator\Admin\Pro_Gate::PRO_BASENAME ),
            /** Nome exibido no pop-up. */
            'product_name' => __('Shipping Simulator for WooCommerce PRO', 'shipping-simulator-for-woocommerce'),
            /** Option da chave de licença, lida pelo plugin PRO. */
            'license_option' => 'shipping_simulator_pro_license_key',
            /**
             * Options de estado do PRO, limpas na ativação para forçar
             * revalidação imediata (`state()` vira `unverified` e o
             * `maybe_check()` reconsulta em vez de esperar 24h).
             */
            'license_state_options' => array(
                'shipping_simulator_pro_license_result',
                'shipping_simulator_pro_license_last_error',
            ),
            /** Option de timestamp da última checagem (zerada na ativação). */
            'license_last_check_option' => 'shipping_simulator_pro_license_last_check',
            /** Basename do plugin PRO (arquivo principal). Fonte: Pro_Gate. */
            'pro_basename' => \Shipping_Simulator\Admin\Pro_Gate::PRO_BASENAME,
            /**
             * Manager de licença do PRO. É ele quem sabe ativar (chamada à API,
             * token, cert, estado); este módulo NÃO reimplementa isso.
             */
            'manager_class' => '\\Lkn\\ShippingSimulatorForWooCommercePro\\Includes\\License\\Manager',
            /** API de cobrança, sem barra no final. */
            'api_url' => rtrim($api_url, '/'),
            /** Texto de apoio do rodapé do pop-up. */
            'support_url' => 'https://www.linknacional.com.br/wordpress/woocommerce/checkout-brasileiro/',
        );
    }

    /**
     * Registra os hooks do módulo.
     *
     * Chamado uma vez pelo plugin principal.
     *
     * @return void
     */
    public static function boot(): void
    {
        Rest::boot();
    }

    /**
     * Enfileira os assets do pop-up (só na tela onde ele existe).
     *
     * @return void
     */
    public static function enqueue(): void
    {
        // O pop-up não deve reinstalar o que já está no disco: instalado é
        // instalado, com o plugin ativo ou desativado (`class_exists` só vale
        // com o PRO carregado).
        $config = self::config();
        $pro_installed = \Shipping_Simulator\Admin\Pro_Gate::is_pro_installed();

        $base = h::plugin_url( 'classes/Billing/assets/' );

        wp_enqueue_style(
            'wc-shipping-simulator-billing',
            $base . 'billing.css',
            array(),
            self::asset_version( 'billing.css' ),
            'all'
        );

        wp_enqueue_script(
            'wc-shipping-simulator-billing',
            $base . 'billing.js',
            array(),
            self::asset_version( 'billing.js' ),
            true
        );

        $config = self::config();

        // `wp_localize_script` converte escalar para string (`false` vira "" e
        // `true` vira "1"), o que funciona por acaso no JS. `wp_json_encode` preserva
        // o tipo — o `proInstalled` chega booleano de verdade.
        wp_add_inline_script(
            'wc-shipping-simulator-billing',
            'window.lknBilling = ' . wp_json_encode(
                array(
                'apiUrl' => $config['api_url'],
                'product' => $config['product'],
                'proInstalled' => (bool) $pro_installed,
                'productName' => $config['product_name'],
                'freeToken' => self::FREE_TOKEN,
                // SEM barra no fim: o JS concatena `/install` e `/activate`.
                // Com barra aqui a URL virava `.../lkn-billing/v1//install`, e o
                // WordPress respondia `rest_no_route` (404) — instalar e ativar
                // nunca funcionaram.
                'restUrl' => esc_url_raw(rest_url(self::REST_NAMESPACE)),
                'nonce' => wp_create_nonce('wp_rest'),
                'supportUrl' => $config['support_url'],
                'settingsUrl' => self::settings_url(),
                'i18n' => array(
                    'title' => __('Adquirir a licença PRO', 'shipping-simulator-for-woocommerce'),
                    'stepEmail' => __('Para onde enviamos a licença?', 'shipping-simulator-for-woocommerce'),
                    'email' => __('E-mail', 'shipping-simulator-for-woocommerce'),
                    'chooseMethod' => __('Como você quer pagar?', 'shipping-simulator-for-woocommerce'),
                    'pix' => __('Pix', 'shipping-simulator-for-woocommerce'),
                    'pixHint' => __('Aprovação imediata. Aponte a câmera para o QR code.', 'shipping-simulator-for-woocommerce'),
                    'card' => __('Cartão de crédito', 'shipping-simulator-for-woocommerce'),
                    'cardHint' => __('Cobrança na hora, na fatura do cartão.', 'shipping-simulator-for-woocommerce'),
                    'fullName' => __('Nome completo', 'shipping-simulator-for-woocommerce'),
                    'taxId' => __('CPF ou CNPJ', 'shipping-simulator-for-woocommerce'),
                    'cardNumber' => __('Número do cartão', 'shipping-simulator-for-woocommerce'),
                    'cardHolder' => __('Nome impresso no cartão', 'shipping-simulator-for-woocommerce'),
                    'cardExpiry' => __('Validade (MM/AA)', 'shipping-simulator-for-woocommerce'),
                    'cardCvv' => __('Código de segurança', 'shipping-simulator-for-woocommerce'),
                    'installments' => __('Parcelas', 'shipping-simulator-for-woocommerce'),
                    'next' => __('Continuar', 'shipping-simulator-for-woocommerce'),
                    'back' => __('Voltar', 'shipping-simulator-for-woocommerce'),
                    'pay' => __('Pagar', 'shipping-simulator-for-woocommerce'),
                    'copyPix' => __('Copiar código Pix', 'shipping-simulator-for-woocommerce'),
                    'copied' => __('Copiado', 'shipping-simulator-for-woocommerce'),
                    'waitingPayment' => __('Aguardando o pagamento. Esta tela se atualiza sozinha.', 'shipping-simulator-for-woocommerce'),
                    'paid' => __('Pagamento aprovado', 'shipping-simulator-for-woocommerce'),
                    'fulfilling' => __('Pagamento aprovado. Preparando sua licença…', 'shipping-simulator-for-woocommerce'),
                    'installing' => __('Instalando…', 'shipping-simulator-for-woocommerce'),
                    'activate' => __('Ativar licença', 'shipping-simulator-for-woocommerce'),
                    'activating' => __('Ativando…', 'shipping-simulator-for-woocommerce'),
                    'manualDownload' => __('Baixar o .zip e instalar manualmente', 'shipping-simulator-for-woocommerce'),
                    'retry' => __('Tentar de novo', 'shipping-simulator-for-woocommerce'),
                    'close' => __('Fechar', 'shipping-simulator-for-woocommerce'),
                    'genericError' => __('Não foi possível concluir agora. Tente de novo em instantes.', 'shipping-simulator-for-woocommerce'),
                    'rateLimited' => __('Muitas tentativas em pouco tempo. Aguarde um instante e tente de novo.', 'shipping-simulator-for-woocommerce'),
                    'offlineError' => __('Não conseguimos falar com o servidor de pagamento. Verifique sua conexão e recarregue a página.', 'shipping-simulator-for-woocommerce'),
                    'setupError' => __('Esta compra não está disponível agora. Fale com o suporte.', 'shipping-simulator-for-woocommerce'),
                    'cardInvalid' => __('Confira os dados do cartão.', 'shipping-simulator-for-woocommerce'),
                    'paidInFull' => __('à vista', 'shipping-simulator-for-woocommerce'),
                    'installPlugin' => __('Instalar plugin', 'shipping-simulator-for-woocommerce'),
                    'pluginInstalled' => __('Plugin instalado', 'shipping-simulator-for-woocommerce'),
                    'licenseActive' => __('Licença ativa!', 'shipping-simulator-for-woocommerce'),
                    'cvvInvalid' => __('Confira o código de segurança do cartão.', 'shipping-simulator-for-woocommerce'),
                    'holderInvalid' => __('Informe o nome impresso no cartão.', 'shipping-simulator-for-woocommerce'),
                    'expiryInvalid' => __('Confira a validade do cartão (MM/AA).', 'shipping-simulator-for-woocommerce'),
                    'emailInvalid' => __('Informe um e-mail válido.', 'shipping-simulator-for-woocommerce'),
                    'taxIdInvalid' => __('Informe um CPF (11 dígitos) ou CNPJ (14 dígitos).', 'shipping-simulator-for-woocommerce'),
                    'nameInvalid' => __('Informe o nome completo do titular.', 'shipping-simulator-for-woocommerce'),
                ),
                )
            ) . ';',
            'before'
        );
    }

    /**
     * Bloco do nav que a tela de configuração deve abrir depois do fluxo.
     *
     * O fluxo termina com a licença ativa e o PRO instalado; o próximo passo
     * natural é configurar o recurso que acabou de ser comprado, não a primeira
     * aba da tela. "Pop-up de CEP" é o recurso PRO deste plugin (é o que o card
     * de compra anuncia), então o link final cai direto nele.
     *
     * Filtrável porque o dia em que este plugin tiver outro recurso PRO como
     * carro-chefe basta apontar para outro bloco, sem tocar no resto.
     */
    public const LANDING_BLOCK = 'pro_popup_cep';

    /**
     * URL da tela de configuração do plugin (destino do link final).
     *
     * Leva `lkn_open`, que o template do layout interpreta para abrir o bloco
     * direto: o nav é controlado por JS (as abas não têm `href` de âncora), então
     * uma URL com `#bloco` não levaria a lugar nenhum.
     *
     * @return string
     */
    public static function settings_url(): string
    {
        $url = admin_url( 'admin.php?page=wc-settings&tab=' . \Shipping_Simulator\Admin\Calculadora_Settings::TAB_ID );

        $block = (string) apply_filters( 'wc_shipping_simulator_landing_block', self::LANDING_BLOCK );

        if ( '' === $block ) {
            return $url;
        }

        return add_query_arg( 'lkn_open', rawurlencode( $block ), $url );
    }
}
