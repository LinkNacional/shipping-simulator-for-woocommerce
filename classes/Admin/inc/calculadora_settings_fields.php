<?php

use Shipping_Simulator\Admin\Calculadora_Settings;

$wc_shipping_simulator_prefix = Calculadora_Settings::get_prefix_migrated();

return [
	// ─────────────────────────────────────────────
	// TAB 1: Geral
	// ─────────────────────────────────────────────
	[
		'title' => __( 'Geral', 'shipping-simulator-for-woocommerce' ),
		'type'  => 'title',
		'id'    => $wc_shipping_simulator_prefix . 'calc_title_geral',
	],
	[
		'title'    => __( 'Opções de Frete e Entrega', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_disabled_shipping',
		'default'  => 'default',
		'desc_tip' => false,
		'type'     => 'select',
		'options'  => [
			'all'     => __( 'Desabilitar Frete e Endereço para Todos', 'shipping-simulator-for-woocommerce' ),
			'digital' => __( 'Desabilitar Frete e Endereço Apenas para Produtos Digitais', 'shipping-simulator-for-woocommerce' ),
			'default' => __( 'Manter Padrão do WooCommerce', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Configure como o endereço de entrega e os métodos de frete serão apresentados no checkout.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Entrega dinâmica será mantida conforme o padrão do Woocommerce.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Gerencie as opções de endereço e cálculo de frete.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Esconder Frete personalizado para Produtos Digitais', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => false,
		'id'       => $wc_shipping_simulator_prefix . 'calc_hide_calculator_digital',
		'default'  => 'no',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Exibir calculador normalmente para todos os tipos de produtos.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Define se o simulador de frete personalizado (página de produto e carrinho) deve ser escondido quando há apenas produtos digitais/virtuais.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Controle a exibição do simulador de frete personalizado para produtos digitais.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Fonte para Busca de CEP', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => false,
		'id'       => $wc_shipping_simulator_prefix . 'calc_font_source',
		'default'  => 'yes',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Fonte Poppins (recomendada)', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Fonte do Site', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-subtitle' => __( 'Fonte Padrão', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Selecione a fonte a ser aplicada no campo de busca do CEP (Código de Endereçamento Postal).', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a fonte que melhor se adapta ao design da sua página.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Configura a fonte para o componente de busca de CEP.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Link Rápido de Configuração', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => false,
		'id'       => $wc_shipping_simulator_prefix . 'calc_enable_settings_link',
		'default'  => 'no',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-subtitle' => __( 'Exibir Link de Configuração', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Exibe um atalho para as configurações do plugin nas páginas de Carrinho e de Produto quando o utilizador for um administrador.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Habilite esta opção para exibir o link de configuração nas páginas frontend (visíveis ao público) para os utilizadores administradores.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Exibir o link de configuração somente para utilizadores administradores.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Exibir Detalhes do Pedido', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => false,
		'id'       => $wc_shipping_simulator_prefix . 'calc_enable_order_details',
		'default'  => 'yes',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Exibe informações detalhadas dos pedidos para melhor acompanhamento e controle.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Habilite para mostrar detalhes adicionais dos pedidos, incluindo informações de entrega e dados complementares.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Ative a exibição de detalhes completos dos pedidos.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'type' => 'sectionend',
		'id'   => $wc_shipping_simulator_prefix . 'calc_geral',
	],

	// ─────────────────────────────────────────────
	// TAB 2: Frete Grátis
	// ─────────────────────────────────────────────
	[
		'title' => __( 'Frete Grátis', 'shipping-simulator-for-woocommerce' ),
		'type'  => 'title',
		'id'    => $wc_shipping_simulator_prefix . 'calc_title_frete',
	],
	[
		'title'    => __( 'Opções de Frete Grátis', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => false,
		'id'       => $wc_shipping_simulator_prefix . 'enable_min_free_shipping',
		'default'  => 'no',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-subtitle' => __( 'Habilitar Mínimo para Frete Grátis', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Permite definir um valor mínimo para ativar o frete grátis.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Habilite esta opção para configurar um valor mínimo para frete grátis.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Configure aqui as regras para o frete grátis.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Valor Mínimo', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'min_free_shipping_value',
		'desc_tip' => false,
		'default'  => '',
		'type'     => 'number',
		'custom_attributes' => [
			'min' => 0,
			'step' => '0.01',
			'data-desc-tip' => __( 'Defina o valor mínimo necessário para ativar o frete grátis.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira o valor mínimo do carrinho para que o frete grátis seja ativado.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Ex: 200,00', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Base de Cálculo', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'free_shipping_calc_base',
		'desc_tip' => false,
		'default'  => 'subtotal',
		'type'     => 'select',
		'options'  => [
			'subtotal' => __( 'Subtotal', 'shipping-simulator-for-woocommerce' ),
			'total'    => __( 'Subtotal + Cupons', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Define qual valor será usado como base para o cálculo do frete grátis.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( '<strong>Subtotal:</strong> considera apenas o valor dos produtos.<br><strong>Subtotal + Cupons:</strong> considera subtotal com cupons de desconto aplicados (não inclui juros/taxas de gateway).', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Escolha entre usar apenas o subtotal ou subtotal com cupons de desconto como base para liberar o frete grátis.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Ocultar outros fretes quando Grátis', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => false,
		'id'       => $wc_shipping_simulator_prefix . 'only_free_shipping',
		'default'  => 'yes',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-subtitle' => __( 'Ocultar as opções de entrega', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Define quais opções aparecem quando o frete é gratuito.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Se habilitado, oculta métodos de envio pagos (como PAC, SEDEX e outros) sempre que o cliente atingir os requisitos para Frete Grátis.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Define se os demais métodos de envio devem ser ocultados quando o Frete Grátis estiver disponível.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Evitar Duplicidade de Frete Grátis', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'avoid_free_shipping_duplication',
		'desc_tip' => false,
		'default'  => 'no',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Evita que múltiplas opções de frete grátis sejam exibidas ao mesmo tempo.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Se habilitado, o sistema mostrará apenas uma opção de frete grátis quando já houver uma disponível, evitando confusão para o cliente.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Exibe apenas uma opção de frete grátis por vez, caso já exista uma disponível.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'       => __( 'Prazo de Entrega (Frete Grátis por Valor Mínimo)', 'shipping-simulator-for-woocommerce' ),
		'id'          => $wc_shipping_simulator_prefix . 'min_free_shipping_delivery_time',
		'type'        => 'text',
		'default'     => '',
		'placeholder' => __( 'Ex: 9 dias úteis, 3 a 5 dias, etc.', 'shipping-simulator-for-woocommerce' ),
		'desc'        => __( 'Adicione um prazo estimado que aparecerá ao lado do frete grátis por valor. Se deixado em branco, será exibido apenas "Frete Grátis".', 'shipping-simulator-for-woocommerce' ),
		'desc_tip'    => false,
		'custom_attributes' => [
			'data-desc-tip' => __( 'Insira o texto do prazo. Ele será exibido entre parênteses.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Exemplo de exibição no carrinho: Frete Grátis (Valor mínimo) (9 dias úteis).', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Formato de exibição do prazo', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Mensagens para o Frete Grátis', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'min_free_shipping_message',
		'desc_tip' => false,
		'default'  => 'Falta(m) apenas mais {value} para obter FRETE GRÁTIS',
		'type'     => 'textarea',
		'custom_attributes' => [
			'data-subtitle' => __( 'Mensagem de Frete Mínimo', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Defina as mensagens de feedback na barra de progresso.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Use {value} como marcador para o valor restante (opcional). Ex: "Falta(m) apenas mais {value} para obter FRETE GRÁTIS" ou apenas "Adicione mais produtos para obter frete grátis"', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Mensagem exibida quando o valor do carrinho ainda não atingiu o mínimo para frete grátis.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Mensagem de Frete Grátis Ativado', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'min_free_shipping_success_message',
		'desc_tip' => false,
		'default'  => 'Parabéns! Você tem frete grátis!',
		'type'     => 'textarea',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Mensagem exibida quando o valor do carrinho atingiu o mínimo para frete grátis.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Mensagem de parabéns exibida quando o cliente se qualifica para frete grátis.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Esta mensagem será exibida quando o frete grátis estiver ativo.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Exibir o valor restante na barra do frete', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'enable_progress_bar_value',
		'desc_tip' => false,
		'default'  => 'no',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Mostra o valor restante para obter frete grátis.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Ao habilitar esta opção, será exibido as informações de valor restante dentro da barra de progresso.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Mostra o valor restante para obter frete grátis.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Frete Grátis por Produto', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'enable_free_shipping_by_product',
		'desc_tip' => false,
		'default'  => 'yes',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Ativa a caixa de seleção de frete grátis na edição do produto.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => sprintf(
				/* translators: %s is the admin URL to the products listing page. */
				__( 'Ao habilitar, uma opção de "Frete Grátis" será exibida na aba "Entrega" dentro de cada produto. <a href="%s" target="_blank" style="font-weight: bold; text-decoration: underline;">Clique aqui para ver todos os produtos</a> e configurar.', 'shipping-simulator-for-woocommerce' ),
				admin_url( 'edit.php?post_type=product' )
			),
			'data-title-description' => __( 'Exibe um checkbox de frete grátis na aba de entrega dos produtos.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'       => __( 'Prazo de Entrega (Frete Grátis por Produto)', 'shipping-simulator-for-woocommerce' ),
		'id'          => $wc_shipping_simulator_prefix . 'free_shipping_by_product_delivery_time',
		'type'        => 'text',
		'default'     => '',
		'placeholder' => __( 'Ex: 9 dias úteis, 2 semanas, etc.', 'shipping-simulator-for-woocommerce' ),
		'desc'        => __( 'Adicione um prazo estimado que aparecerá ao lado do nome do frete. Se deixado em branco, será exibido apenas "Frete Grátis (Produto)".', 'shipping-simulator-for-woocommerce' ),
		'desc_tip'    => false,
		'custom_attributes' => [
			'data-desc-tip' => __( 'Insira o texto do prazo. Ele será exibido entre parênteses.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Exemplo de exibição no carrinho: Frete Grátis (Produto) (9 dias úteis).', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Formato de exibição do prazo', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Detecção Inteligente de Frete Grátis', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'enable_free_shipping_detection',
		'desc_tip' => false,
		'default'  => 'yes',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Preenche automaticamente a barra de frete quando o benefício for alcançado.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Ao habilitar, o plugin detecta se o carrinho do cliente já se qualifica para frete grátis e preenche a barra de progresso automaticamente, informando ao cliente que ele já ganhou o benefício.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Torna a barra de frete dinâmica e integrada com as regras de frete grátis.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Exibir outros fretes com Frete Grátis', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => false,
		'id'       => $wc_shipping_simulator_prefix . 'keep_other_methods_with_free_shipping',
		'default'  => 'yes',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-subtitle' => __( 'Exibir todos os métodos de envio', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Controla se outros métodos de frete são exibidos quando há frete grátis disponível.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Se habilitado, exibe todos os métodos de envio (pagos e gratuitos) mesmo quando o cliente se qualifica para frete grátis. Se desabilitado, exibe apenas as opções de frete grátis, ocultando os demais métodos de envio como PAC, SEDEX e fretes de plugins de terceiros.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Define se os métodos de envio pagos são exibidos quando o frete grátis estiver disponível.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'type' => 'sectionend',
		'id'   => $wc_shipping_simulator_prefix . 'calc_frete',
	],

	// ─────────────────────────────────────────────
	// TAB 3: Shortcodes (informativo)
	// ─────────────────────────────────────────────
	[
		'title' => __( 'Shortcodes', 'shipping-simulator-for-woocommerce' ),
		'desc'  => __( '<p><strong>Carrinho:</strong><br><code class="wc-shipping-simulator-shortcode">[woocommerce_cart]</code></p>
			<p style="padding: 10px 0;"> </p>
			<p><strong>Finalização de compra:</strong><br><code class="wc-shipping-simulator-shortcode">[woocommerce_checkout]</code></p>
			<p style="padding: 10px 0;"> </p>
			<p style="margin-top: 15px; color: #8F8F8F;"><span><strong>Importante:</strong> Caso queira desativar os campos de endereço no carrinho, recomendamos desativar a opção "Ativar a calculadora de entrega na página de carrinho" em <a href="/wp-admin/admin.php?page=wc-settings&tab=shipping&section=options" target="_blank">configurações de entrega do WooCommerce</a>. Disponível apenas para página de carrinho por shortcode.</span></p>', 'shipping-simulator-for-woocommerce' ),
		'type'  => 'title',
		'id'    => $wc_shipping_simulator_prefix . 'calc_title_shortcodes',
	],
	[
		'type' => 'sectionend',
		'id'   => $wc_shipping_simulator_prefix . 'calc_shortcodes',
	],

	// ─────────────────────────────────────────────
	// TAB 4: Produto
	// ─────────────────────────────────────────────
	[
		'title' => __( 'Produto', 'shipping-simulator-for-woocommerce' ),
		'type'  => 'title',
		'id'    => $wc_shipping_simulator_prefix . 'calc_product_page_settings',
	],
	[
		'title'    => __( 'Cálculo de Frete na Página do Produto', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_enable_product_page',
		'default'  => 'yes',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-subtitle' => __( 'Exibir Calculadora de Frete', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Habilite esta opção para exibir o campo da calculadora de frete diretamente na página do produto.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Exibe o campo de cálculo de frete (CEP) na página de produto.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Ativa o campo de cálculo de frete na página individual do produto.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Estilo Atual (Input)', 'shipping-simulator-for-woocommerce' ),
		'type'     => 'text',
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_postcode_current_style',
		'default'  => '',
		'custom_attributes' => [
			'readonly' => 'readonly',
			'data-desc-tip' => __( 'Exibe o estilo atual aplicado ao campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Este campo é apenas informativo e exibe o estilo atual.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Estilo Atual (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Posição do Campo', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_input_position',
		'type'     => 'select',
		'options'  => [
			'top'    => __( 'Topo', 'shipping-simulator-for-woocommerce' ),
			'middle' => __( 'Meio', 'shipping-simulator-for-woocommerce' ),
			'bottom' => __( 'Base', 'shipping-simulator-for-woocommerce' ),
			'custom' => __( 'Personalizado', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => 'top',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a posição do campo na página.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha se o campo será exibido no topo, meio ou na base do componente.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Posição do Campo.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Posição personalizada', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_custom_position',
		'type'     => 'text',
		'default'  => '',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Personalize a posição de exibição do CEP.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira a classe(.class) ou id(#id) do componente para inseri-lo em um local personalizado.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Definia um local personalizado de sua escolha.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Personalizar Campo de Entrada', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_input_background_color_field',
		'type'     => 'text',
		'default'  => '#ffffff',
		'custom_attributes' => [
			'data-subtitle' => __( 'Cor de fundo (Input)', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Adicione sua identidade visual aos campos.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor de fundo para o campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Cor de Fundo (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Cor do texto (Input)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_input_color_field',
		'type'     => 'text',
		'default'  => '#2C3338',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a cor de texto do campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor do texto para o campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'A cor do texto é aplicada apenas no momento em que o input é digitado, onde a cor não se aplica ao placeholder do componente.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Largura da Borda (Input)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_input_border_width',
		'type'     => 'text',
		'default'  => '1px',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a largura da borda do campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira a largura da borda em pixels(recomendado) ou outra unidade.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Largura da Borda (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Estilo da Borda (Input)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_input_border_style',
		'type'     => 'select',
		'default'  => 'solid',
		'options'  => [
			'none'   => __( 'Nenhuma', 'shipping-simulator-for-woocommerce' ),
			'solid'  => __( 'Sólida', 'shipping-simulator-for-woocommerce' ),
			'dashed' => __( 'Tracejada', 'shipping-simulator-for-woocommerce' ),
			'dotted' => __( 'Pontilhada', 'shipping-simulator-for-woocommerce' ),
			'double' => __( 'Dupla', 'shipping-simulator-for-woocommerce' ),
			'groove' => __( 'Sulcada', 'shipping-simulator-for-woocommerce' ),
			'ridge'  => __( 'Crestada', 'shipping-simulator-for-woocommerce' ),
			'inset'  => __( 'Inserida', 'shipping-simulator-for-woocommerce' ),
			'outset' => __( 'Sobressalente', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina o estilo da borda do campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha o estilo da borda (ex: sólida, tracejada, etc.).', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Estilo da Borda (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Cor da Borda (Input)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_input_border_color_field',
		'type'     => 'color',
		'default'  => '#ccc',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a cor da borda do campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor da borda para o campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Cor da Borda (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Raio da Borda (Input)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_input_border_radius',
		'type'     => 'text',
		'default'  => '4px',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina o raio da borda do campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira o raio da borda em pixels(recomendado) ou outra unidade.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Raio da Borda (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Personalizar Botão Consultar', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_button_background_color_field',
		'type'     => 'color',
		'default'  => '#0073aa',
		'custom_attributes' => [
			'data-subtitle' => __( 'Cor de fundo (Botão)', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Adicione sua identidade visual aos campos.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor de fundo para o botão.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Cor de Fundo (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Cor do texto (Botão)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_button_color_field',
		'type'     => 'color',
		'default'  => '#ffffff',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a cor de texto do botão.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor do texto para o botão.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Cor de Texto (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Largura da Borda (Botão)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_button_border_width',
		'type'     => 'text',
		'default'  => '1px',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a largura da borda do botão.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira a largura da borda em pixels(recomendado) ou outra unidade.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Largura da Borda (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Estilo da Borda (Botão)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_button_border_style',
		'type'     => 'select',
		'default'  => 'none',
		'options'  => [
			'none'   => __( 'Nenhuma', 'shipping-simulator-for-woocommerce' ),
			'solid'  => __( 'Sólida', 'shipping-simulator-for-woocommerce' ),
			'dashed' => __( 'Tracejada', 'shipping-simulator-for-woocommerce' ),
			'dotted' => __( 'Pontilhada', 'shipping-simulator-for-woocommerce' ),
			'double' => __( 'Dupla', 'shipping-simulator-for-woocommerce' ),
			'groove' => __( 'Sulcada', 'shipping-simulator-for-woocommerce' ),
			'ridge'  => __( 'Crestada', 'shipping-simulator-for-woocommerce' ),
			'inset'  => __( 'Inserida', 'shipping-simulator-for-woocommerce' ),
			'outset' => __( 'Sobressalente', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina o estilo da borda do botão.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira o estilo da borda (ex: sólido, tracejado, etc.).', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Estilo da Borda (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Cor da Borda (Botão)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_button_border_color_field',
		'type'     => 'color',
		'default'  => '#0073aa',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a cor da borda do botão.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor da borda para o botão.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Cor da Borda (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Raio da Borda (Botão)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_button_border_radius',
		'type'     => 'text',
		'default'  => '4px',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina o raio da borda do botão.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira o raio da borda em pixels(recomendado) ou outra unidade.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Raio da Borda (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Configurações Extras', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_input_placeholder',
		'type'     => 'text',
		'default'  => 'Insira seu CEP',
		'custom_attributes' => [
			'data-subtitle' => __( 'Placeholder', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Adicione sua identidade visual aos campos.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira o texto que será exibido como placeholder.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Placeholder.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Definir Ícone', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_input_icon',
		'type'     => 'radio',
		'options'  => [
			'transit'  => __( 'Ícone de Entrega', 'shipping-simulator-for-woocommerce' ),
			'bill'     => __( 'Ícone de Conta', 'shipping-simulator-for-woocommerce' ),
			'truck'    => __( 'Ícone de Caminhão', 'shipping-simulator-for-woocommerce' ),
			'postcode' => __( 'Ícone de Postcode', 'shipping-simulator-for-woocommerce' ),
			'zipcode'  => __( 'Ícone de Zipcode', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => 'transit',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Escolha um ícone para o campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Selecione um ícone para exibir no campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Ícone do input de CEP.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Cor do Ícone', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_product_input_icon_color',
		'type'     => 'select',
		'options'  => [
			'black-icon' => __( 'Preto', 'shipping-simulator-for-woocommerce' ),
			'gray-icon'  => __( 'Cinza', 'shipping-simulator-for-woocommerce' ),
			'red-icon'   => __( 'Vermelho', 'shipping-simulator-for-woocommerce' ),
			'pink-icon'  => __( 'Rosa', 'shipping-simulator-for-woocommerce' ),
			'green-icon' => __( 'Verde', 'shipping-simulator-for-woocommerce' ),
			'blue-icon'  => __( 'Azul', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => 'blue-icon',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a cor do ícone.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor para o ícone.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Escolha a cor no qual será utilizada para definir a cor do icone do input.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'type' => 'sectionend',
		'id'   => $wc_shipping_simulator_prefix . 'calc_product_page_settings',
	],

	// ─────────────────────────────────────────────
	// TAB 5: Carrinho
	// ─────────────────────────────────────────────
	[
		'title' => __( 'Carrinho', 'shipping-simulator-for-woocommerce' ),
		'type'  => 'title',
		'id'    => $wc_shipping_simulator_prefix . 'calc_cart_page_settings',
	],
	[
		'title'    => __( 'Cálculo de Frete na Página de Carrinho', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_enable_cart_page',
		'default'  => 'yes',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-subtitle' => __( 'Exibir Calculadora de Frete', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Habilite esta opção para exibir o campo da calculadora de frete na página do carrinho.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Exibe o campo de cálculo de frete (CEP) na página do carrinho.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Ativa a exibição da calculadora de frete na página do carrinho.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Estilo Atual (Input)', 'shipping-simulator-for-woocommerce' ),
		'type'     => 'text',
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_postcode_current_style',
		'default'  => '',
		'custom_attributes' => [
			'readonly' => 'readonly',
			'data-desc-tip' => __( 'Exibe o estilo atual aplicado ao campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Este campo é apenas informativo e exibe o estilo atual.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Estilo Atual (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Posição do Campo', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_input_position',
		'type'     => 'select',
		'options'  => [
			'top'    => __( 'Topo', 'shipping-simulator-for-woocommerce' ),
			'middle' => __( 'Meio', 'shipping-simulator-for-woocommerce' ),
			'bottom' => __( 'Base', 'shipping-simulator-for-woocommerce' ),
			'custom' => __( 'Personalizado', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => 'top',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a posição do campo na página.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha se o campo será exibido no topo, meio ou na base do componente.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Posição do Campo.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Posição personalizada', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_custom_position',
		'type'     => 'text',
		'default'  => '',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Personalize a posição de exibição do CEP.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira a classe(.class) ou id(#id) do componente para inseri-lo em um local personalizado.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Definia um local personalizado de sua escolha.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Personalizar Campo de Entrada', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_input_background_color_field',
		'type'     => 'text',
		'default'  => '#ffffff',
		'custom_attributes' => [
			'data-subtitle' => __( 'Cor de fundo (Input)', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Adicione sua identidade visual aos campos.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor de fundo para o campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Cor de Fundo (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Cor do texto (Input)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_input_color_field',
		'type'     => 'text',
		'default'  => '#2C3338',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a cor de texto do campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor do texto para o campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'A cor do texto é aplicada apenas no momento em que o input é digitado, onde a cor não se aplica ao placeholder do componente.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Largura da Borda (Input)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_input_border_width',
		'type'     => 'text',
		'default'  => '1px',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a largura da borda do campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira a largura da borda em pixels(recomendado) ou outra unidade.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Largura da Borda (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Estilo da Borda (Input)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_input_border_style',
		'type'     => 'select',
		'default'  => 'solid',
		'options'  => [
			'none'   => __( 'Nenhuma', 'shipping-simulator-for-woocommerce' ),
			'solid'  => __( 'Sólida', 'shipping-simulator-for-woocommerce' ),
			'dashed' => __( 'Tracejada', 'shipping-simulator-for-woocommerce' ),
			'dotted' => __( 'Pontilhada', 'shipping-simulator-for-woocommerce' ),
			'double' => __( 'Dupla', 'shipping-simulator-for-woocommerce' ),
			'groove' => __( 'Sulcada', 'shipping-simulator-for-woocommerce' ),
			'ridge'  => __( 'Crestada', 'shipping-simulator-for-woocommerce' ),
			'inset'  => __( 'Inserida', 'shipping-simulator-for-woocommerce' ),
			'outset' => __( 'Sobressalente', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina o estilo da borda do campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha o estilo da borda (ex: sólida, tracejada, etc.).', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Estilo da Borda (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Cor da Borda (Input)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_input_border_color_field',
		'type'     => 'text',
		'default'  => '#ccc',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a cor da borda do campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor da borda para o campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Cor da Borda (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Raio da Borda (Input)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_input_border_radius',
		'type'     => 'text',
		'default'  => '4px',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina o raio da borda do campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira o raio da borda em pixels(recomendado) ou outra unidade.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Raio da Borda (Input).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Personalizar Botão Consultar', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_button_background_color_field',
		'type'     => 'text',
		'default'  => '#0073aa',
		'custom_attributes' => [
			'data-subtitle' => __( 'Cor de fundo (Botão)', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Adicione sua identidade visual aos campos.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor de fundo para o botão.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Cor de Fundo (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Cor do texto (Botão)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_button_color_field',
		'type'     => 'text',
		'default'  => '#ffffff',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a cor de texto do botão.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor do texto para o botão.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Cor de Texto (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Largura da Borda (Botão)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_button_border_width',
		'type'     => 'text',
		'default'  => '1px',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a largura da borda do botão.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira a largura da borda em pixels(recomendado) ou outra unidade.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Largura da Borda (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Estilo da Borda (Botão)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_button_border_style',
		'type'     => 'select',
		'default'  => 'none',
		'options'  => [
			'none'   => __( 'Nenhuma', 'shipping-simulator-for-woocommerce' ),
			'solid'  => __( 'Sólida', 'shipping-simulator-for-woocommerce' ),
			'dashed' => __( 'Tracejada', 'shipping-simulator-for-woocommerce' ),
			'dotted' => __( 'Pontilhada', 'shipping-simulator-for-woocommerce' ),
			'double' => __( 'Dupla', 'shipping-simulator-for-woocommerce' ),
			'groove' => __( 'Sulcada', 'shipping-simulator-for-woocommerce' ),
			'ridge'  => __( 'Crestada', 'shipping-simulator-for-woocommerce' ),
			'inset'  => __( 'Inserida', 'shipping-simulator-for-woocommerce' ),
			'outset' => __( 'Sobressalente', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina o estilo da borda do botão.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira o estilo da borda (ex: sólido, tracejado, etc.).', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Estilo da Borda (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Cor da Borda (Botão)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_button_border_color_field',
		'type'     => 'text',
		'default'  => '#0073aa',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a cor da borda do botão.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor da borda para o botão.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Cor da Borda (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Raio da Borda (Botão)', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_button_border_radius',
		'type'     => 'text',
		'default'  => '4px',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina o raio da borda do botão.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira o raio da borda em pixels(recomendado) ou outra unidade.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Raio da Borda (Botão).', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Configurações Extras', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_input_placeholder',
		'type'     => 'text',
		'default'  => 'Insira seu CEP',
		'custom_attributes' => [
			'data-subtitle' => __( 'Placeholder', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip' => __( 'Adicione sua identidade visual aos campos.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Insira o texto que será exibido como placeholder.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Placeholder.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Definir Ícone', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_input_icon',
		'type'     => 'radio',
		'options'  => [
			'transit'  => __( 'Ícone de Entrega', 'shipping-simulator-for-woocommerce' ),
			'bill'     => __( 'Ícone de Conta', 'shipping-simulator-for-woocommerce' ),
			'truck'    => __( 'Ícone de Caminhão', 'shipping-simulator-for-woocommerce' ),
			'postcode' => __( 'Ícone de Postcode', 'shipping-simulator-for-woocommerce' ),
			'zipcode'  => __( 'Ícone de Zipcode', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => 'transit',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Escolha um ícone para o campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Selecione um ícone para exibir no campo de entrada.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Ícone do input de CEP.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Cor do Ícone', 'shipping-simulator-for-woocommerce' ),
		'id'       => $wc_shipping_simulator_prefix . 'calc_cart_input_icon_color',
		'type'     => 'select',
		'options'  => [
			'black-icon' => __( 'Preto', 'shipping-simulator-for-woocommerce' ),
			'gray-icon'  => __( 'Cinza', 'shipping-simulator-for-woocommerce' ),
			'red-icon'   => __( 'Vermelho', 'shipping-simulator-for-woocommerce' ),
			'pink-icon'  => __( 'Rosa', 'shipping-simulator-for-woocommerce' ),
			'green-icon' => __( 'Verde', 'shipping-simulator-for-woocommerce' ),
			'blue-icon'  => __( 'Azul', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => 'blue-icon',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Defina a cor do ícone.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha a cor para o ícone.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Escolha a cor no qual será utilizada para definir a cor do icone do input.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'type' => 'sectionend',
		'id'   => $wc_shipping_simulator_prefix . 'calc_cart_page_settings',
	],

	// ─────────────────────────────────────────────
	// TAB 6: Cache
	// ─────────────────────────────────────────────
	[
		'title' => __( 'Cache', 'shipping-simulator-for-woocommerce' ),
		'type'  => 'title',
		'id'    => $wc_shipping_simulator_prefix . 'calc_title_cache',
	],
	[
		'title'    => __( 'Consulta automática de CEP', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => false,
		'id'       => $wc_shipping_simulator_prefix . 'calc_enable_auto_postcode_search',
		'default'  => 'yes',
		'type'     => 'radio',
		'options'  => [
			'yes' => __( 'Habilitar', 'shipping-simulator-for-woocommerce' ),
			'no'  => __( 'Desabilitar', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Calcula fretes automaticamente sem necessidade de clicar em "Calcular", melhorando a experiência do usuário.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Habilite para realizar consultas de frete automaticamente nas páginas de produto e carrinho, assim que um CEP válido for detectado ou informado.<br><br><strong>Dicas em caso de problema com cache:</strong><ul style="margin:0 0 0 18px;padding:0;list-style:disc;">
				<li><strong>Arquivos JavaScript Excluídos:</strong> Adicione o nome do arquivo do seu script JS (ex: <code>WcBetterShippingCalculatorForBrazilCustomCartPostcode.js</code> ou o handle do script) na seção de exclusão/adiamento de execução de JavaScript do seu plugin de cache.</li>
				<li><strong>Delay JavaScript Execution:</strong> Adicione <code>WooBetterData</code> na lista de exclusões para garantir o funcionamento correto.</li>
				<li><strong>Cache de URLs:</strong> O plugin usa parâmetros na URL (como <code>?postcode=</code>). Certifique-se de que o WP Rocket ou outro plugin de cache <b>não está forçando o cache dessas URLs</b> na API REST.</li>
				</ul>', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Disponível apenas no WooCommerce 10.0 ou superior. Essa funcionalidade requer uma versão compatível do WooCommerce para funcionar corretamente.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Tempo de expiração do cache', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => false,
		'id'       => $wc_shipping_simulator_prefix . 'calc_cache_expiration_time',
		'default'  => '0',
		'type'     => 'select',
		'options'  => [
			'0'       => __( 'Não Expirar', 'shipping-simulator-for-woocommerce' ),
			'10'      => __( '10 minutos', 'shipping-simulator-for-woocommerce' ),
			'30'      => __( '30 minutos', 'shipping-simulator-for-woocommerce' ),
			'60'      => __( '1 hora', 'shipping-simulator-for-woocommerce' ),
			'120'     => __( '2 horas', 'shipping-simulator-for-woocommerce' ),
			'300'     => __( '5 horas', 'shipping-simulator-for-woocommerce' ),
			'720'     => __( '12 horas', 'shipping-simulator-for-woocommerce' ),
			'1440'    => __( '1 dia', 'shipping-simulator-for-woocommerce' ),
			'2880'    => __( '2 dias', 'shipping-simulator-for-woocommerce' ),
			'10080'   => __( '1 semana', 'shipping-simulator-for-woocommerce' ),
			'20160'   => __( '2 semanas', 'shipping-simulator-for-woocommerce' ),
			'43200'   => __( '1 mês', 'shipping-simulator-for-woocommerce' ),
		],
		'custom_attributes' => [
			'data-desc-tip' => __( 'Define o tempo de armazenamento do cache de CEP. Cache mais longo melhora performance, mas pode exibir dados desatualizados.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Escolha o período de validade do cache de consultas de CEP. O padrão é não expirar.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Tempo de expiração do cache de consultas de CEP.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'title'    => __( 'Reset automático de cache', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => false,
		'id'       => $wc_shipping_simulator_prefix . 'calc_enable_auto_cache_reset',
		'default'  => 'WCBCB_9X2K4M7P5R8T3N6Y1Q',
		'type'     => 'text',
		'custom_attributes' => [
			'data-desc-tip' => __( 'Token de segurança para limpeza do cache. Use o botão "Limpar Cache" para remover todas as consultas armazenadas e forçar o recálculo de frete.', 'shipping-simulator-for-woocommerce' ),
			'data-description' => __( 'Ao clicar em "Limpar Cache", todas as consultas armazenadas serão removidas. Como consequência, os visitantes precisarão recalcular o frete ao acessar as páginas de produtos. Recomenda-se usar essa função após ajustes importantes nas configurações de entrega.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => __( 'Ao atualizar as regras ou valores de frete, o cache antigo pode continuar exibindo informações desatualizadas. Limpar o cache garante que todos os visitantes recebam os novos cálculos corretos.', 'shipping-simulator-for-woocommerce' ),
		],
	],
	[
		'type' => 'sectionend',
		'id'   => $wc_shipping_simulator_prefix . 'calc_cache',
	],
];
