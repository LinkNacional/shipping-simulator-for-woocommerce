=== Shipping Simulator for WooCommerce ===
Contributors: linknacional
Donate link: https://linknacional.com.br/
Tags: woocommerce, shipping simulator, simulador de frete, calculadora de frete, product page
Stable tag: 3.0.0
Requires at least: 6.0
Requires PHP: 8.2
Tested up to: 7.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Calculate shipping on product and cart pages, with free shipping rules, progress bar and address autofill for WooCommerce.

*This plugin was specially designed for Brazilian stores, but it can be easily adapted (with hooks) to meet other needs.*

== Description ==

Shipping Simulator for WooCommerce brings the shipping calculator to your customers while they are still browsing: directly on the product page and on the cart page. Instead of waiting until checkout, customers enter their postcode (CEP) and instantly see the available shipping methods, price and estimated delivery time.

The plugin also helps increase your average order value with free shipping rules — by minimum cart amount or per product — and a configurable free-shipping progress bar that shows the customer how much is left to unlock free shipping. Results are cached and the last postcode is remembered, so repeated lookups are fast.

= Calculator features =

* Calculate shipping directly from the product page
* Calculate shipping from the cart page
* Free shipping by minimum amount and by product
* Cart free-shipping progress bar with configurable messages
* Automatic postcode lookup with cached results (the last postcode is remembered)
* Visual customization of the fields: colors, borders, icons and position

= Legacy simulator features =

* Calculate shipping without having to choose variations (Check it on the plugin settings page)
* Automatically fills in and updates the customer's address (Check it on the plugin settings page)
* Customizable texts: title, placeholder, button and messages
* If you are using some page builder, use the shortcode `[wc_shipping_simulator]`

= PRO version =

Upgrade to Shipping Simulator for WooCommerce PRO for advanced features:

* CEP validation popup that asks visitors to confirm delivery availability, with WhatsApp contact when delivery isn't available
* Hide the address fields automatically when the customer selects local pickup
* Delivery time system: business days and hours, delivery time slots and order deadline tracking

= Settings =

Access your admin panel and open **WooCommerce > Settings > Calculadora de frete** to configure the new shipping calculator, free shipping and progress bar options. The previous simulator settings still exist and are now available as **WooCommerce > Settings > Shipping > Simulador de Frete (Legado)**.

= Contributions =

For bugs, suggestions or contributions, open an issue in our [GitHub Repository](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/issues) or create a topic in the [WordPress Plugin Forum](https://wordpress.org/support/plugin/shipping-simulator-for-woocommerce/).

= Donations =

Support this plugin on [https://linknacional.com.br/](https://linknacional.com.br/)

== Frequently Asked Questions ==

= Where can I get support or talk to other users? =

You can ask for help in the [Plugin Forum](https://wordpress.org/support/plugin/shipping-simulator-for-woocommerce/)

== Screenshots ==

1. Shipping simulator demo (in portuguese)
2. Shipping simulator without results (in portuguese)
3. Access the Settings to configure the plugin.
4. Shipping calculator settings page (Calculadora de frete)
5. Cart using the WooCommerce shortcode (before)
6. Cart using the WooCommerce shortcode (after)
7. Free shipping progress bar in Gutenberg cart
8. Free shipping progress bar in Gutenberg checkout
9. Free shipping progress bar in legacy cart
10. Free shipping progress bar in legacy checkout
11. Postcode (CEP) calculator component
12. Postcode (CEP) calculator component layout

== Changelog ==

= 3.0.0 =

-   Added a new postcode (CEP) shipping calculator on product and cart pages with automatic address autofill, styled input and button, and a list of shipping methods with price and estimated delivery time.
-   Calculator results are cached and the last postcode is remembered, avoiding unnecessary lookups.
-   Added a cart free-shipping progress bar with configurable minimum amount, remaining-value and success messages.
-   Added a per-product free shipping option and free-shipping-by-minimum-amount rules.
-   Fixed hiding of checkout and cart address fields when shipping is disabled, including block, classic and shortcode checkout.
-   Added a new "Shipping calculator" settings tab (WooCommerce > Settings) with options imported automatically from the woo-better plugin when present.

= 2.5.0 =

-   Removed the admin donation notice.
-   Fixed text domain strings for better translation support.
-   Requires WordPress 6.0+ and PHP 8.2+.
-   Allow multiple shipping simulators on the same page (fixes Elementor compatibility).

= 2.4.4 =

-   Tested up to WordPress 6.9 and WooCommerce 10.6

= 2.4.3 =

-   Minor fix.
-   Tested up to WooCommerce 10.2

= 2.4.2 =

-   Fix: wierd bug since WooCommerce 9.8

= 2.4.1 =

-   Misc: Fix w.org plugin tags

= 2.4.0 =

-   Tested up to WordPress 6.8
-   Improve shortcode

= v2.3.5 =

-   Tested up to WordPress 6.7
-   Minor fixes.

= v2.3.4 =

-   Tested up to WordPress 6.6
-   Autofill don't includes complements (address_2) anymore.

= v2.3.3 =

-   Tested up to WordPress 6.4

= v2.3.2 =

-   Fix: missing translation for some strings.

= v2.3.1 =

-   Fix: Street name duplicated in customer address.

[See changelog for all versions](https://github.com/LinkNacional/shipping-simulator-for-woocommerce/blob/main/CHANGELOG.md)

== Upgrade Notice ==

= 3.0.0 =
* The shipping calculator features moved from the woo-better plugin to here. Existing configurations are imported automatically.

= 2.0.0 =
* Remove simulator nonce validation and improved CSS

= 1.3.2 =
* Important: fixes conflict with jquery-mask library

= 1.0.0 =
* Initial release
