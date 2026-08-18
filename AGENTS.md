# AGENTS.md — Diretrizes Absolutas

Regras imutáveis. Qualquer desvio deve ser justificado no código via comentário `// REASON:`.

---

## 1. Arquitetura

### Estado atual
- Plugin OO com autoload PSR-4 via Composer.
- `main.php` — bootstrap: inclui `vendor/autoload.php` e chama `Shipping_Simulator\Core\Main::start_plugin()`.
- `core/` — núcleo (Config, Loader, Dependencies, Main, Traits).
- `classes/` — lógica de negócio (Admin, Integration, Shortcode, Request, Helpers, etc.).
- `templates/` — templates HTML.
- `assets/` — CSS/JS/imagens (fontes + `.min.*` gerados via esbuild).
- `config.php` / `dependencies.php` / `loader.php` — configuração e registro de dependências.

### SOLID
- **S — Single Responsibility**: manter a separação atual. Ao crescer, dividir classes com >1 motivo para mudar.
- **O — Open/Closed**: extensão via hooks/filtros, nunca via edição de código existente.
  - Padrão: `apply_filters('wc_shipping_simulator_*', $value, $context)`.
- **D — Dependency Inversion**: config via `Config::get()` / `get_option()`, nunca hardcoded.

### PSR-4 (composer.json)
```
Shipping_Simulator\        → classes/
Shipping_Simulator\Core\   → core/
```
- 1 classe por arquivo. Nome do arquivo = nome da classe.

---

## 2. Segurança

### Superglobais — sanitizar SEMPRE
```php
// Proibido
$id = $_GET['id'];

// Obrigatório
$id = isset($_GET['id']) ? absint($_GET['id']) : 0;
$name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
```

### Nonces — toda requisição state-changing
```php
if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wc_shipping_simulator_action')) {
    wp_die('Security check failed.');
}
```

### Output escaping
```php
echo esc_html($value);       // HTML context
echo esc_attr($value);       // Attribute context
echo esc_url($url);          // URL context
```

### SQL — prepared statements
```php
// Proibido
$wpdb->query("SELECT * FROM $wpdb->postmeta WHERE meta_key = '$key'");

// Obrigatório
$wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key = %s", $key);
```

---

## 3. Padrões WordPress / WooCommerce

### Naming
- Namespace/classes: `Shipping_Simulator\...`
- Funções/hooks: `wc_shipping_simulator_*`
- Options: `wc_shipping_simulator_*`
- Constantes de config: chaves do `config.php` (ex.: `SLUG`, `PREFIX`, `TEMPLATES_DIR`)

### Internacionalização
- Toda string visível ao usuário: `__()`, `_e()`, `_n()`
- Text domain: `shipping-simulator-for-woocommerce`

### Slug (WordPress.org) — NÃO ALTERAR
- `shipping-simulator-for-woocommerce` (grafia correta).
- NÃO renomear pasta nem `main.php`.

### Comportamento
- Shortcode: `[wc_shipping_simulator]`.
- Settings: `WooCommerce > Settings > Shipping > Shipping Simulator`.
- Cálculo de frete na página do produto.

### Assets
- Fontes em `assets/js/form.js` e `assets/css/form.css`.
- `.min.*` são artefatos do esbuild (`npm run build`) e **são gitignored** — NÃO editar manualmente.
- Versionar enqueue com `Config::get( 'VERSION' )`.

---

## 4. Tratamento de Erros
- Nunca expor stack traces para o frontend.
- Fallback quando WooCommerce não está instalado (`Dependencies`).
- `vendor/autoload.php` ausente → notice admin (já implementado em `main.php`).

---

## 5. Build & Qualidade

```bash
composer install          # setup (phpstan no dev)
composer check            # phpstan
npm install && npm run build   # minifica JS/CSS via esbuild
composer make-pot         # gera o .pot (wp i18n make-pot)
```

- `vendor/` é gerado pelo Composer e **gitignored** (autoload).
- `assets/js/form.min.js` / `assets/css/form.min.css` são artefatos — editar os fontes e rodar `npm run build`.

---

## 6. Comunicação (Caveman Mode + RTK)

### Caveman Mode — ATIVO
- Zero saudações. Zero "claro!", "ótimo!", "vamos lá!".
- Zero resumos pós-entrega.
- Frases curtas. Sem períodos compostos.
- Código > prosa. Sempre.

### RTK (Rust Token Killer) — ATIVO
- Logs de terminal são comprimidos pelo RTK antes de chegar ao LLM.
- Nunca solicitar output verboso se snippet RTK estruturado já foi fornecido.
- Confiar no pré-parsing do RTK.

### Formato de resposta esperado
```
Tipo: [fix|feat|refactor|security]
Arquivo: path/to/file.php:123
Problema: descrição ≤1 linha
Solução: descrição ≤1 linha
---
[código/diff]
```
