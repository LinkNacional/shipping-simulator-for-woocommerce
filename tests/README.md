# Testes — Shipping Simulator for WooCommerce

Testes de **integração** (wp-phpunit) rodando contra o WordPress + WooCommerce
reais, com banco dedicado. Cobrem o fluxo da **calculadora legada** corrigido:

- `Integration\Brazil::update_package()` — monta o destino completo do pacote a
  partir do CEP (BrasilAPI com fallback ViaCEP).
- `Integration\Brazil::sync_customer_shipping_address()` — sincroniza
  `WC()->customer` antes do cálculo do frete.
- `Integration\Autofill_Brazilian_Addresses::fill_package_destination()` —
  preenchimento do endereço do pacote.
- `Calculadora_Api::lookup_cep()` — consulta normalizada + cache por requisição.

Todas as chamadas HTTP são mockadas via filtro `pre_http_request` (ver
`tests/TestCase.php`), então os testes não dependem de rede.

## Pré-requisitos

- Banco de teste (ex.: `local_tests`) — **nunca** o banco de desenvolvimento: o
  runner recria as tabelas `wptests_*`.
- WooCommerce como plugin irmão em `wp-content/plugins/woocommerce`.

## Como rodar

```bash
composer install            # instala phpunit + wp-phpunit + polyfills
composer test               # ou: vendor/bin/phpunit
```

### Ambiente Local WP (Linux)

O `wp-tests-config.php` detecta o WordPress do Local WP automaticamente
(`app/public`). Como o instalador do wp-phpunit roda em um processo `php`
separado, garanta que:

1. o `php` do PATH seja o do Local WP (tem `mysqli`);
2. o `LD_LIBRARY_PATH` aponte para `shared-libs` do Local WP (dependências
   compartilhadas);
3. o socket do MySQL seja informado via `WP_TESTS_DB_SOCKET`.

Exemplo:

```bash
export PATH="/home/<user>/.config/Local/lightning-services/php-8.2.29+0/bin/linux/bin:$PATH"
export LD_LIBRARY_PATH="/home/<user>/.config/Local/lightning-services/php-8.2.29+0/bin/linux/shared-libs"
export WP_TESTS_DB_SOCKET="/home/<user>/.config/Local/run/<hash>/mysql/mysqld.sock"

vendor/bin/phpunit
```

Variáveis de ambiente aceitas por `tests/wp-tests-config.php`:

| Variável                | Padrão          | Descrição                                  |
| ----------------------- | --------------- | ------------------------------------------ |
| `WP_TESTS_DB_NAME`      | `local_tests`   | Nome do banco de teste                     |
| `WP_TESTS_DB_USER`      | `root`          | Usuário do banco                           |
| `WP_TESTS_DB_PASSWORD`  | `root`          | Senha do banco                             |
| `WP_TESTS_DB_SOCKET`    | _(vazio)_       | Socket do MySQL                            |
| `WP_TESTS_DB_HOST`      | `localhost`     | Host do banco (quando sem socket)          |
| `LOCAL_WP_PATH`         | _(autodetecção)_| Raiz do WordPress do Local WP              |
