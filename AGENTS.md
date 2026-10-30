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
  - `is_pro_unlocked()` → **`is_pro_active()` E licença em vigor**. É o que a
    tela usa para decidir pílula/banner/salvamento (filtrável por
    `wc_shipping_simulator_is_pro_unlocked`);
  - `guard_settings($settings)` roda em `Calculadora_Settings::save()`: sem
    `is_pro_unlocked()` os campos `pro` saem da lista do `save_fields()` e a
    option volta ao `default` declarado;
  - `upgrade_url()` → constante `UPGRADE_URL` (página comercial do PRO) ou
    `WC_SHIPPING_SIMULATOR_PRO_UPGRADE_URL` (filtrável);
  - `is_pro_installed()` → plugin **no disco** (ativo ou não): `is_pro_active()`
    || `file_exists(WP_PLUGIN_DIR . PRO_BASENAME)`, com varredura por
    `get_plugins()` em admin para pasta renomeada por reinstalação
    (`...-pro-1`). Filtrável por `wc_shipping_simulator_is_pro_installed`;
  - `should_offer_purchase()` → `! is_pro_installed()` (filtrável por
    `wc_shipping_simulator_should_offer_purchase`). Ver 5-C.
- `Calculadora_Settings::get_fields()` NÃO resolve o default dos campos `pro`
  (senão o valor do banco viraria "default").
- Lógica do recurso: só no plugin PRO.

### Portão: instalar NÃO é licenciar

`is_pro_active()` ("o plugin PRO está carregado") **não** basta para liberar a
UI. O PRO sem licença não registra nenhum hook de recurso, então liberar a tela
nesse estado é prometer o que o código não faz — era o bug de quem baixava o
`.zip`, ativava o plugin e via tudo liberado, com as pílulas e o banner de aviso
sumindo e os campos PRO sendo **gravados** sem efeito nenhum.

A decisão única é `is_pro_unlocked()` = `is_pro_active()` + a resposta do
`Manager` do PRO (`PRO_LICENSE_MANAGER`, consultado via `instance()` — o MESMO
objeto que `activate_key()`/`deactivate_site()` invalidam, para não ler estado
velho de meio de requisição). Se a classe não carregar, o portão **fecha**.

O banner do bloco bloqueado tem dois textos: PRO ativo sem licença fala da
**licença** ("enquanto a licença do PRO não estiver ativa"), PRO ausente fala do
**plugin**. O card do sidebar continua decidido por `is_pro_installed()` (5-C):
licença ausente não ressuscita o card branco de compra.

---

## 5-C. Sidebar das configurações: UM cartão do PRO por vez

A coluna lateral da tela de configurações termina com **um** cartão do PRO. São
dois possíveis, mutuamente exclusivos (`if/else` no template):

| Card | Classe | Quando aparece |
| --- | --- | --- |
| Branco (compra) | `block-pro-offer` | PRO **fora** do site (`should_offer_purchase()`) |
| Roxo (conheça mais) | `block-promotional-card` | PRO **instalado**, ativo ou não |

- O branco traz a lista de recursos, o piso do preço (`license_price()`), a nota
  de licença anual/volume e o CTA `Adquirir a licença`
  (`data-lkn-billing-open` → pop-up de compra). O pop-up só é impresso junto com
  ele: **este CTA é o único gatilho** do pop-up na tela.
- O roxo é o link para `Pro_Gate::upgrade_url()` (botão "Conheça mais"), com
  gradiente `#667eea → #764ba2`. Não é CTA de compra: é "conhecer o produto".
- Quem decide é a **presença do plugin** (`is_pro_installed()`), não a licença e
  nem o "está ativo": desativar o PRO não apaga os arquivos. Duas regressões já
  vieram daqui — os DOIS cartões juntos (com `is_pro_active()`, o plugin
  desativado ressuscitava o de compra) e a tela sem cartão nenhum. Instalar o
  PRO sem comprar deixa o bloco "Licença" (injetado pelo PRO nesta mesma tela)
  como caminho.
- `proInstalled` (config do pop-up) usa a mesma detecção: o pop-up nunca tenta
  instalar por cima de uma pasta que já existe. Na prática ele sai sempre
  `false`, porque o pop-up só é impresso junto do card branco (PRO ausente) —
  o parâmetro fica para o caso de o plugin aparecer no disco entre o render e o
  clique.
- CSS em `Admin/css/WcShippingSimulatorAdminLayoutSettings.css`
  (`/* Card promocional roxo */`), compilado por `npm run build:css` em
  `Admin/cssCompiled/*.COMPILED.css` (gitignored: gerado no build, não vai no
  pacote por commit).
- Regressão: `tests/ProPurchaseCardTest.php`.

---

## 5-D. Configurações do admin: "desabilitado" é só VISUAL

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

## 5-E. Layout das configurações: nunca alargar a página

A tela roda dentro do `#wpbody-content`, que no wp-admin é
`overflow: visible !important`: **qualquer** coisa que passe da borda direita do
container vira barra de rolagem horizontal no documento inteiro. Duas regras
seguram isso, e as duas têm teste (`tests/AdminLayoutOverflowTest.php`):

1. `.admin-layout-content` tem `min-width: 0`. Ele é item de flex de
   `.admin-layout-page-wrapper` e o mínimo automático de item de flex é
   `min-content`; como o nav de abas é `flex-wrap: nowrap` com uma aba por
   bloco, esse mínimo fica maior que a tela. O `overflow: hidden` do
   `.admin-layout-top-menu-clip` **esconde** as abas que sobram, mas não encolhe
   o mínimo — era esse o "o nav estica a página mesmo cortado".
2. As setas do carrossel ficam **dentro** da caixa do nav. Elas eram
   `left/right: -30px`; offset negativo de elemento posicionado conta para o
   overflow do ancestral. Com `.has-overflow` a caixa ganha `padding: 0 30px` e
   as setas vão para `left/right: 0` (o recuo só entra quando há o que rolar).

Ao mexer no layout, confira no navegador: `document.documentElement.scrollWidth`
tem de ser igual a `clientWidth` em 1920/1440/1024/782/500 px, com e sem o
carrossel ativo (`.has-overflow`). Edite o CSS **fonte** em `Admin/css/` e rode
`npm run build:css` — o plugin carrega o `.COMPILED.css`.

---

## 5-F. Link final do fluxo de compra: abre a aba do recurso PRO

O pop-up de compra termina com "Ir para configurações". Esse link não vai para a
raiz da tela — vai para o bloco do recurso que o cliente acabou de comprar:

| Plugin | Bloco | Constante |
| --- | --- | --- |
| woo-better | `pro_prazo_entrega` (Prazo de Entrega) | `Billing::LANDING_BLOCK` |
| shipping | `pro_popup_cep` (Pop-up de CEP) | `Billing::LANDING_BLOCK` |

`Billing::settings_url()` acrescenta `?lkn_open=<bloco>` (filtrável por
`wc_better_shipping_calculator_landing_block` / `wc_shipping_simulator_landing_block`;
string vazia desliga).

**Quem interpreta é o template do layout, no SERVIDOR**: ele marca o bloco pedido
como `active` no markup. O JS das abas parte do link que já tem `.active` e faz o
resto (inclusive rolar o carrossel). Não use JS/`load` para isso — depender da
hora em que o handler das abas fica pronto fazia a primeira aba piscar antes de
trocar.

Regras: o id vem de `sanitize_key()` (**não** `sanitize_title()`, que troca `_`
por `-` e quebra `pro_prazo_entrega`); o bloco precisa **existir** na tela; e
bloco `pro` só é destino com `is_pro_unlocked()` — cair numa aba bloqueada
(banner de venda) é pior que cair na primeira. O nav tem abas, não âncoras:
`#bloco` na URL não faz nada.

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
