<?php

use Shipping_Simulator\Shortcode;
use Shipping_Simulator\Admin\Settings;
use Shipping_Simulator\Admin\Calculadora_Settings;

$wc_shipping_simulator_prefix = Settings::get_prefix();
$wc_shipping_simulator_shortcode = Shortcode::get_tag();

return [
	[
		'id' => $wc_shipping_simulator_prefix . 'settings',
		'type' => 'title',
		'name' => esc_html__( 'Geral', 'shipping-simulator-for-woocommerce' ),
		'desc' => esc_html__( 'The following options are used to configure the Shipping Simulator.', 'shipping-simulator-for-woocommerce' ),
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'auto_insert',
		'type'     => 'checkbox',
		'name'     => esc_html__( 'Enable auto-insert', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Enable', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => sprintf(
			// translators: %s is a shortcode tag
			esc_html__( 'Display automatically the shipping simulator in product pages. Alternatively you can manually insert the shipping simulator using the %s shortcode.', 'shipping-simulator-for-woocommerce' ),
			"<code>[$wc_shipping_simulator_shortcode]</code>"
		),
		'custom_attributes' => [
			'data-subtitle'          => esc_html__( 'Inserção automática', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip'          => esc_html__( 'Exibe o simulador automaticamente na página do produto.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => esc_html__( 'Controla a inserção automática do shortcode legado.', 'shipping-simulator-for-woocommerce' ),
			'data-description'       => esc_html__( 'Quando ativo, o simulador é inserido automaticamente na página do produto. Como alternativa, use o shortcode [wc_shipping_simulator] manualmente.', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => Calculadora_Settings::auto_insert_default()
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'requires_variation',
		'type'     => 'checkbox',
		'name'     => esc_html__( 'Product variation is required', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Enable', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => esc_html__( 'Disable this option to allow customers simulate shipping rates even when a variation is not selected on variable products. However, always make sure that the variable product has a defined weight.', 'shipping-simulator-for-woocommerce' ),
		'custom_attributes' => [
			'data-subtitle'          => esc_html__( 'Exigir variação selecionada', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip'          => esc_html__( 'Controla o cálculo de frete em produtos variáveis.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => esc_html__( 'Exige a seleção de uma variação antes de simular o frete.', 'shipping-simulator-for-woocommerce' ),
			'data-description'       => esc_html__( 'Desative para permitir simular o frete mesmo sem uma variação selecionada. Garanta que o produto variável tenha um peso definido.', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => 'yes'
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'autofill_addresses',
		'type'     => 'checkbox',
		'name'     => esc_html__( 'Display full address', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Enable', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => esc_html__( 'When this option is activated, the street, neighborhood and city will be displayed in the shipping simulator.', 'shipping-simulator-for-woocommerce' ),
		'custom_attributes' => [
			'data-subtitle'          => esc_html__( 'Exibir endereço completo', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip'          => esc_html__( 'Mostra rua, bairro e cidade no resultado do simulador.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => esc_html__( 'Exibe o endereço completo no simulador de frete.', 'shipping-simulator-for-woocommerce' ),
			'data-description'       => esc_html__( 'Quando ativo, o simulador exibe rua, bairro e cidade após a consulta.', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => 'yes'
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'update_address',
		'type'     => 'radio',
		'name'     => esc_html__( 'Update customer address', 'shipping-simulator-for-woocommerce' ),
		'options'  => [
			'0' => esc_html__( "Don't update", 'shipping-simulator-for-woocommerce' ),
			'1' => esc_html__( "Update only shipping address", 'shipping-simulator-for-woocommerce' ),
			'2' => esc_html__( "Update billing and shipping address", 'shipping-simulator-for-woocommerce' ),
		],
		'desc_tip' => esc_html__( 'The customer address can be updated when a shipping simulation returns shipping options.', 'shipping-simulator-for-woocommerce' ),
		'custom_attributes' => [
			'data-subtitle'          => esc_html__( 'Atualizar endereço do cliente', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip'          => esc_html__( 'Define se o endereço do cliente é atualizado após a simulação.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => esc_html__( 'Atualiza o endereço de envio/cobrança conforme o resultado.', 'shipping-simulator-for-woocommerce' ),
			'data-description'       => esc_html__( 'O endereço do cliente pode ser atualizado quando a simulação retornar opções de frete.', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => '0'
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'form_title',
		'type'     => 'text',
		'name'     => esc_html__( 'Title', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Text that appears before the simulator fields.', 'shipping-simulator-for-woocommerce' ),
		'custom_attributes' => [
			'data-subtitle'          => esc_html__( 'Título do formulário', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip'          => esc_html__( 'Texto exibido antes dos campos do simulador.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => esc_html__( 'Define o título exibido acima do simulador.', 'shipping-simulator-for-woocommerce' ),
			'data-description'       => esc_html__( 'Texto que aparece antes dos campos do simulador de frete.', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => __( 'Check shipping cost and delivery time:', 'shipping-simulator-for-woocommerce' ),
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'input_placeholder',
		'type'     => 'text',
		'name'     => esc_html__( 'Input placeholder', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Text that appears when the postcode field is empty.', 'shipping-simulator-for-woocommerce' ),
		'custom_attributes' => [
			'data-subtitle'          => esc_html__( 'Placeholder do campo', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip'          => esc_html__( 'Texto exibido quando o campo de CEP está vazio.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => esc_html__( 'Define o placeholder do campo de CEP.', 'shipping-simulator-for-woocommerce' ),
			'data-description'       => esc_html__( 'Texto que aparece quando o campo de código postal está vazio.', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => __( 'Type your postcode', 'shipping-simulator-for-woocommerce' ),
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'submit_label',
		'type'     => 'text',
		'name'     => esc_html__( 'Button Text', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Text that appears on the shipping simulator button.', 'shipping-simulator-for-woocommerce' ),
		'custom_attributes' => [
			'data-subtitle'          => esc_html__( 'Texto do botão', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip'          => esc_html__( 'Texto exibido no botão do simulador.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => esc_html__( 'Define o rótulo do botão do simulador.', 'shipping-simulator-for-woocommerce' ),
			'data-description'       => esc_html__( 'Texto que aparece no botão do simulador de frete.', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => __( 'Apply', 'shipping-simulator-for-woocommerce' ),
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'after_results',
		'type'     => 'textarea',
		'name'     => esc_html__( 'Text after results.', 'shipping-simulator-for-woocommerce' ),
		'custom_attributes' => [
			'data-subtitle'          => esc_html__( 'Texto após os resultados', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip'          => esc_html__( 'Mensagem exibida após as opções de frete.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => esc_html__( 'Define o texto exibido após os resultados.', 'shipping-simulator-for-woocommerce' ),
			'data-description'       => esc_html__( 'Os prazos de entrega começam a contar a partir da confirmação do pagamento.', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => __( 'Delivery times start from the confirmation of payment.', 'shipping-simulator-for-woocommerce' ),
		'css' => 'height: 6rem',
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'no_results',
		'type'     => 'textarea',
		'name'     => esc_html__( 'Text when there are no results.', 'shipping-simulator-for-woocommerce' ),
		'custom_attributes' => [
			'data-subtitle'          => esc_html__( 'Texto sem resultados', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip'          => esc_html__( 'Mensagem quando não há opções de frete disponíveis.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => esc_html__( 'Define o texto quando não há resultados.', 'shipping-simulator-for-woocommerce' ),
			'data-description'       => esc_html__( 'Mensagem exibida quando o produto não pode ser entregue na região informada.', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => __( 'Unfortunately at this moment this product cannot be delivered to the specified region.', 'shipping-simulator-for-woocommerce' ),
		'css' => 'height: 6rem;',
	],
	[
		'id' => $wc_shipping_simulator_prefix . 'settings',
		'type' => 'sectionend',
	],
	[
		'id' => $wc_shipping_simulator_prefix . 'settings_debug',
		'type' => 'title',
		'name' => esc_html__( 'Debug', 'shipping-simulator-for-woocommerce' ),
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'debug_mode',
		'type'     => 'checkbox',
		'name'     => esc_html__( 'Debug mode', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Enable', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => __( 'Enable debug mode to log your shipping simulations and display helpful informations in product page.', 'shipping-simulator-for-woocommerce' ),
		'custom_attributes' => [
			'data-subtitle'          => esc_html__( 'Modo de depuração', 'shipping-simulator-for-woocommerce' ),
			'data-desc-tip'          => esc_html__( 'Registra as simulações e exibe informações úteis.', 'shipping-simulator-for-woocommerce' ),
			'data-title-description' => esc_html__( 'Ativa logs de depuração das simulações.', 'shipping-simulator-for-woocommerce' ),
			'data-description'       => esc_html__( 'Habilite para registrar as simulações de frete e exibir informações na página do produto.', 'shipping-simulator-for-woocommerce' ),
		],
		'default'  => 'no'
	],
	[
		'id' => $wc_shipping_simulator_prefix . 'settings',
		'type' => 'sectionend',
	],
];
