=== Shipping Simulator for WooCommerce ===
Contributors: linknacional, luizbills
Donate link: https://linknacional.com.br/
Tags: woocommerce, shipping simulator, simulador de frete, calculadora de frete, product page
Stable tag: 3.0.6
Requires at least: 6.0
Requires PHP: 8.2
Tested up to: 7.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Calculate shipping on product and cart pages, with free shipping rules, progress bar and address autofill for WooCommerce.

*This plugin was specially designed for Brazilian stores, but it can be easily adapted (with hooks) to meet other needs.*

== Description ==

Shipping Simulator for [WooCommerce](https://www.linknacional.com.br/wordpress/woocommerce/) brings the shipping calculator to your customers while they are still browsing: directly on the product page and on the cart page. Instead of waiting until checkout, customers enter their postcode (CEP) and instantly see the available shipping methods, price and estimated delivery time.

The plugin also helps increase your average order value with free shipping rules — by minimum cart amount or per product — and a configurable free-shipping progress bar that shows the customer how much is left to unlock free shipping. Results are cached and the last postcode is remembered, so repeated lookups are fast.

= Calculator features =

* Calculate shipping directly from the product page
* Calculate shipping from the cart page
* Free shipping by minimum amount and by product
* Visual customization: colors, borders, icons and position of the fields

= Legacy simulator features =

* Calculate shipping without having to choose variations (Check it on the plugin settings page)
* Automatically fills in and updates the customer's address (Check it on the plugin settings page)
* Uses the same postcode field, button and results list described below
* If you are using some page builder, use the shortcode `[wc_shipping_simulator]`

= PRO version =

Upgrade to Shipping Simulator for [WooCommerce](https://www.linknacional.com.br/wordpress/woocommerce/) PRO for advanced features:

* CEP validation popup that asks visitors to confirm delivery availability, with WhatsApp contact when delivery isn't available
* Hide the address fields automatically when the customer selects local pickup
* Delivery time system: business days and hours, delivery time slots and order deadline tracking

= Calculator fields =

The calculator is deliberately short: the customer only types the postcode and gets the shipping options right away.

* **Postcode / CEP** (`postcode`): the only address field on the calculator. It is required, can be typed as text or number, and its placeholder is configurable. Brazilian stores can also look the address up automatically from it.
* **Calculate shipping** (submit button): triggers the lookup. Its label is configurable, and the field and the button can be repositioned (inline, stacked or floating over the product image).
* **Shipping results** (results list): one line per shipping method, with the method name, the price and the estimated delivery time. It is filled by AJAX, without reloading the page.
* **Free shipping progress bar**: shows how much is left to unlock free shipping, on the cart, and the message shown when the customer gets there. Its texts, colors and values are configurable.
* **Country, State and City** (native fields, not shown for Brazil): Brazilian stores see only the postcode field and get the address filled in automatically. Stores in other countries keep WooCommerce's default calculator with all address fields.

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

= 3.0.6 =

-   On the classic cart (shortcode), the postcode now always fills the shipping state, even when the postcode lookup service is unavailable. Before, the calculation could run without the state and the cart showed "No shipping options were found for XXXXX-XXX" for any postcode.
-   Postcode lookups are now cached, so repeated shipping calculations no longer depend on the external service on every request.
-   An unknown postcode now shows a clear message ("check the number and try again") instead of only an empty shipping result.

= 3.0.5 =

-   Options that are locked by a dependency (e.g. "Free shipping only" with the minimum-amount free shipping turned off) no longer keep the old value stored — the lock is now visual only, so what the screen shows is what gets saved.

= 3.0.4 =

-   While loading, the text below the progress bar no longer repeats the message already shown inside the bar and keeps its height reserved, so the component no longer jumps when the cart updates.
-   The reserved height is measured again when the phone is rotated or the window resized, keeping the layout stable at any screen width.

= 3.0.3 =

-   For Brazilian stores, the native shipping calculator on the cart (shortcode) now shows only the postcode field; the country, state, and city fields are hidden (other stores keep the default calculator).
-   Entering the postcode auto-fills the address for both billing and shipping (city, state, and street) and stores the neighborhood in the session/profile.
-   The postcode is now saved in the Brazilian format (00000-000).

= 3.0.2 =

-   The outdated woo-better notice now reappears after the beta-test notice is dismissed while woo-better remains below 5.0.0, so the update button is always offered when needed.
-   Removed the "Gutenberg number field" note (a woo-better fix) from the settings screen.

= 3.0.1 =

-   The woo-better update notice now installs version 5.0.0 directly from GitHub and only appears when the installed version is below 4.17.2.
-   Removed the outdated alphanumeric-CNPJ feature notice from the settings page.

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
-   Requires [WordPress](https://www.linknacional.com.br/wordpress/) 6.0+ and PHP 8.2+.
-   Allow multiple shipping simulators on the same page (fixes Elementor compatibility).

= 2.4.4 =

-   Tested up to [WordPress](https://www.linknacional.com.br/wordpress/) 6.9 and [WooCommerce](https://www.linknacional.com.br/wordpress/woocommerce/) 10.6

= 2.4.3 =

-   Minor fix.
-   Tested up to [WooCommerce](https://www.linknacional.com.br/wordpress/woocommerce/) 10.2

= 2.4.2 =

-   Fix: wierd bug since [WooCommerce](https://www.linknacional.com.br/wordpress/woocommerce/) 9.8

= 2.4.1 =

-   Misc: Fix w.org plugin tags

= 2.4.0 =

-   Tested up to [WordPress](https://www.linknacional.com.br/wordpress/) 6.8
-   Improve shortcode

= v2.3.5 =

-   Tested up to [WordPress](https://www.linknacional.com.br/wordpress/) 6.7
-   Minor fixes.

= v2.3.4 =

-   Tested up to [WordPress](https://www.linknacional.com.br/wordpress/) 6.6
-   Autofill don't includes complements (address_2) anymore.

= v2.3.3 =

-   Tested up to [WordPress](https://www.linknacional.com.br/wordpress/) 6.4

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
