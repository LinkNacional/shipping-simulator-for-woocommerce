# Changelog

All notable changes to this project will be documented in this file.

## 2.5.0 - 2026-08-18

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.4.4...2.5.0)

-   Removido o aviso de doação do painel administrativo.
-   Corrigidos os text-domains para melhor suporte a traduções.
-   Requer WordPress 6.0+ e PHP 8.2+.

## 2.4.3 - 2025-09-29

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.4.2...2.4.3)

-   Minor fix.
-   Tested up to WooCommerce 10.2

## 2.4.2 - 2025-04-10

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.4.1...2.4.2)

-   Fix: wierd bug since WooCommerce 9.8

## 2.4.1 - 2025-04-09

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.4.0...2.4.1)

-   Misc: Fix w.org plugin tags

## 2.4.0 - 2025-04-08

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.3.5...2.4.0)

-   Tested up to WordPress 6.8
-   Improve shortcode

## 2.3.5 - 2024-12-05

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.3.4...2.3.5)

-   Tested up to WordPress 6.7
-   Minor fixes

## 2.3.4 - 2024-08-26

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.3.3...2.3.4)

-   Tested up to WordPress 6.6
-   Autofill don't includes complement (address_2) anymore.

## 2.3.3 - 2023-11-19

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.3.2...2.3.3)

-   Tested up to WordPress 6.4

## 2.3.2 - 2023-09-22

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.3.1...2.3.2)

-   Fix: missing translation for some strings.

## 2.3.1 - 2023-09-20

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.3.0...2.3.1)

-   Fix: Street name duplicated in customer address.

## 2.3.0 - 2023-09-19

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.2.2...2.3.0)

-   Tweak: Now uses the [OpenCEP API](https://opencep.com/) to search addresses.
-   Tweak: Renamed filter hook from `woocommerce_correios_get_estimating_delivery` to `wc_shipping_simulator_get_estimating_delivery`.
-   Tweak: Delivery estimating will be shown for other delivery methods.
-   Fix: street name was not updating.

## 2.2.2 - 2023-08-14

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.2.1...2.2.2)

-   Tweak: Remove an uncessary internal check for variations.

## 2.2.1 - 2023-07-20

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.2.0...2.2.1)

-   Add `data-cfasync="false"` attribute on simulator's script tag. This makes the Cloudflare Rocket Loader ignore this tag.
-   Add `defer` attribute on simulator's script tag.
-   New filter hook: `wc_shipping_simulator_script_use_defer`.
-   New filter hook: `wc_shipping_simulator_script_disable_cfrocket`.

## 2.2.0 - 2023-06-20

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.1.0...2.2.0)

-   New filter hook: `woocommerce_correios_get_estimating_delivery`.
-   New filter hook: `wc_shipping_simulator_integration_estimating_delivery_enabled`.
-   New filter hook: `wc_shipping_simulator_integration_estimating_delivery_check_rate`.
-   New filter hook: `wc_shipping_simulator_integration_estimating_delivery_metadata`.
-   New filter hook: `wc_shipping_simulator_integration_estimating_delivery_days`.

## 2.1.0 - 2023-05-28

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/2.0.0...2.1.0)

-   New filter hook: `wc_shipping_simulator_wrapper_css_class`.
-   Tweak: improve inline inputs form layout.
-   Tweak: improve general CSS.
-   Fix: hide free shipping if requires coupon.

## 2.0.0 - 2023-05-15

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.9.0...2.0.0)

-   Tweak: Removed nonce validation from simulator form.
-   Tweak: Added CSS to make the simulator input and button appears side by side.

## 1.9.0 - 2023-03-08

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.8.0...1.9.0)

-   Tweak: Change input mask for Brazilian postcodes from "99 999-999" to "99999-999" (removed a whitespace).
-   Tweak: Improve the shipping calculation for better compatibility with other plugins.

## 1.8.0 - 2023-03-02

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.7.0...1.8.0)

-   The simulator now notifies when an error is preventing it from working correctly. An empty screen is no longer shown.

## 1.7.0 - 2023-01-16

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.6.0...1.7.0)

-   Feature: Display the simulator in backorder products.

## 1.6.0 - 2022-10-28

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.5.0...1.6.0)

-   Feature: Improve simulator/frontend accessibility. #36
-   New action hook: `wc_shipping_simulator_form_before_button`. #42

## 1.5.0 - 2022-10-25

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.4.0...1.5.0)

-   Fix: Shipping simulator appearing for external and grouped products. #39

## 1.4.0 - 2022-09-28

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.3.3...1.4.0)

-   Feature: automatically disables built-in shipping simulator of "Melhor Envio" plugin.

## 1.3.3 - 2022-08-02

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.3.2...1.3.3)

-   Fix: delete plugin database options on uninstallation.
-   New filter hook: `wc_shipping_simulator_get_template_full_path` can be used to override the path of a template file.

## 1.3.2 - 2022-08-01

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.3.1...1.3.2)

-   Fix: conflict with jquery-mask library (present on many sites).

## 1.3.1 - 2022-07-26

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.3.0...1.3.1)

-   Feature: Correios integration now fill the package address with city, street and neighborhood. Useful to some shipping methods like Lalamove.
-   Fix: better message when missing a variation.
-   Fix: free shipping was unavailable for non logged in users.
-   Fix: PHP v8+ deprecation notice.
-   i18n: Frontend unexpected error and timeout is now translatable.

## 1.3.0 - 2022-07-25

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.2.0...1.3.0)

-   New settings option: Update customer address (disabled by default). Now, the customer address can be updated when a shipping simulation returns shipping options.
-   New filter hook: `wc_shipping_simulator_settings_field`
-   Fix: don't display the simulator in products out of stock.

## 1.2.0 - 2022-07-24

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.1.1...1.2.0)

-   Feature: automatically simulates shipping when the customer finishes typing the postcode.
-   Fix: simulator no longer crashes on timeout error.

## 1.1.1 - 2022-07-20

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.1.0...1.1.1)

-   Remove `Update URI: false` from header.

## 1.1.0 - 2022-07-20

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.0.2...1.1.0)

-   Fix correios integration
-   Fix shipping simulations with product variations
-   New settings option: Debug mode (disabled by default)
-   New settings option: Product variation is required (enabled by default). Disable this option to allow customers simulate shipping rates even when a variation is not selected on variable products.
-   Now is possible log the shipping simulations with debug mode enabled
-   Now a box with helpful informations appears on product page with debug mode enabled
-   New filter hook: `wc_shipping_simulator_package_validate_virtual_product`
-   Renamed filter hook: from `wc_shipping_simulator_shipping_package_item` to `wc_shipping_simulator_package_item`
-   Renamed filter hook: from `wc_shipping_simulator_shipping_package_rates` to `wc_shipping_simulator_package_rates`
-   Renamed filter hook: from `wc_shipping_simulator_shipping_package_data` to `wc_shipping_simulator_package_data`

## 1.0.2 - 2022-07-19

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.0.1...1.0.2)

-   Improve frontend javascript

## 1.0.1 - 2022-07-19

[Source code changes](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/compare/1.0.0...1.0.1)

-   Move support and donation links from plugin actions to plugin meta

## 1.0.0 - 2022-07-18

-   Initial release
