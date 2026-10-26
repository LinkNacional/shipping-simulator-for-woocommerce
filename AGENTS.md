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

## 5-B. Configurações compartilhadas com a versão PRO (bloco PRO)

As opções da versão PRO vivem na MESMA aba do plugin gratuito
(`wc-shipping-simulator-calculadora`, "Calculadora de frete"). O plugin PRO
**não registra aba/página própria**.

- Declare o bloco/campos em `classes/Admin/inc/calculadora_settings_fields.php`
  com `'pro' => true`. O bloco ganha a pílula vermelha `PRO` no nav, no banner e no
  título de cada card.
- `Shipping_Simulator\Admin\Pro_Gate`:
  - `is_pro_active()` → `defined('SHIPPING_SIMULATOR_FOR_WOOCOMMERCE_PRO_VERSION')`
    (filtrável por `wc_shipping_simulator_is_pro_active`);
  - `guard_settings($settings)` roda em `Calculadora_Settings::save()`: com o PRO
    inativo os campos `pro` saem da lista do `save_fields()` e a option volta ao
    `default` declarado;
  - `upgrade_url()` → constante `UPGRADE_URL` (`'#'` até a página comercial
    existir) ou `WC_SHIPPING_SIMULATOR_PRO_UPGRADE_URL` (filtrável).
- `Calculadora_Settings::get_fields()` NÃO resolve o default dos campos `pro`
  (senão o valor do banco viraria "default").
- Lógica do recurso: só no plugin PRO.

---

## 5-C. Configurações do admin: "desabilitado" é só VISUAL

**NUNCA** use o atributo `disabled` (nem `element.disabled = true`, `$.prop('disabled', true)`,
`disabled(...)` no PHP) para fingir que um campo de configuração está desabilitado por
depender de outro. Use `window.wcShippingSimulatorFakeDisabled(elementos, true|false)`
(`Admin/js/WcShippingSimulatorAdminFakeDisabled.js` + classe
`.wc-shipping-simulator-fake-disabled` do `AdminSettings.css`).

**Por quê (bug grotesco herdado do woo-better, 5.2.0):** input `disabled` **não vai no
POST**. O `WC_Admin_Settings::save_fields()` recebe `null`, cai no
`if ( is_null( $value ) ) continue;` e **mantém o valor antigo da option**. Resultado: a
tela mostrava "Não"/cinza (o JS ainda forçava o valor), o banco continuava `yes` e o
recurso **seguia ativo** — era assim que "Frete grátis por valor mínimo" desligado
continuava valendo, e o mesmo valia para "Somente frete grátis", "Evitar duplicidade de
frete grátis", "Valores na barra de progresso" e o select "Base do frete grátis".

**Regras:**
- O input continua **habilitado** e o valor exibido (inclusive o que o JS força) é o
  valor **enviado**. Tela e banco nunca divergem.
- Bloqueio de interação: clique/`mousedown`/teclado em fase de **captura** (o
  `preventDefault` no `<label>` também impede a ativação), não `pointer-events`.
- O valor coerente com o pai desligado continua sendo forçado no JS — e, como agora ele
  vai no POST, é ele que fica salvo.
- Ao marcar o valor no JS, **dispare `change`** (`radio.dispatchEvent(new Event('change',
  { bubbles: true }))`) em vez de `radio.click()`: o clique é interceptado pelo bloqueio
  visual e outros scripts (ex.: o preview da barra) ficariam dessincronizados.
- Campos de TEXTO/ÁREA travados usam `readOnly` (que **é** enviado no POST), nunca
  `disabled`.
- Exceção: botões e elementos que não são campo de formulário (nav das abas, botão do
  modal de aviso, botão "Limpar o cache") podem usar `disabled` normalmente.
- Guarda de regressão: `tests/AdminFakeDisabledTest.php` falha se voltar `disabled` em
  campo de opção das telas de configuração.

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
