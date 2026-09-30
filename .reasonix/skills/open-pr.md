---
name: open-pr
description: Abre PR de dev → main no shipping-simulator-for-woocommerce no padrão Link Nacional (título VERSION - repo(resumo); corpo com metadados e CHANGELOG)
---

# open-pr (shipping-simulator-for-woocommerce)

Abre um Pull Request de `dev` → `main` via `gh pr create`, no padrão Link Nacional, para o repositório `shipping-simulator-for-woocommerce`.

## Parâmetros (via `arguments`)

O usuário pode passar: `version=3.0.3 tested_up=7.1 summary=Ajuste no aviso de versão do woo-better`. Qualquer valor ausente é extraído do código.

- **version** — versão da release (Stable tag / cabeçalho do `main.php`)
- **tested_up** — WP testado até
- **summary** — resumo CURTO usado no TÍTULO. Se ausente, derive da entrada mais recente do `CHANGELOG.md` (NÃO do `git log`).

## Fluxo de execução

### 1. Extrair metadados (se não vierem nos arguments)

```bash
# Cabeçalho PHP (fonte da verdade da versão) — main.php, lido por core/Config.php
grep -m1 -E "^\s*\*\s*Version:" main.php
grep -m1 -E "^\s*\*\s*Requires PHP:" main.php

# readme.txt (Tested up to / Stable tag)
grep -m1 -i "^Tested up to:" readme.txt
grep -m1 -i "^Stable tag:" readme.txt

# Nome do repositório no GitHub (NÃO usar basename $PWD)
REPO_NAME=$(basename -s .git "$(git config --get remote.origin.url)")
# → shipping-simulator-for-woocommerce
```

### 2. Ler o changelog da versão atual (fonte do resumo e dos bullets)

⚠️ **Regra anti-redundância.** NÃO use `git log` para gerar o resumo — o range de commits está dessincronizado e traz itens de versões já publicadas. Leia a entrada mais recente do changelog:

```bash
# Preferir CHANGELOG.md (português). Fallback: readme.txt, seção == Changelog ==.
head -n 20 CHANGELOG.md
```

A entrada mais recente tem o formato `## VERSION - YYYY-MM-DD` seguido de bullets `- ...` / `* ...`.

- **TÍTULO**: resuma esses bullets em uma frase curta (≤ ~12 palavras).
- **CORPO (seção CHANGELOG)**: copie os bullets do `CHANGELOG.md` (português), sem hash e sem reescrever.

> Dica: o corpo do PR é o **mesmo** conteúdo do release body gerado pelo workflow. Para reproduzi-lo localmente:
> `bash .github/scripts/generate-release-body.sh`

### 3. Montar TÍTULO

Formato exato (obrigatório) — **sem espaço antes do parêntese**:

```
VERSION - REPO_NAME(RESUMO_CURTO)
```

Exemplo:

```
3.0.3 - shipping-simulator-for-woocommerce(Ajuste no aviso de versão do woo-better)
```

### 4. Montar CORPO

Use exatamente este gabarito. Os campos de cabeçalho saem do `readme.txt`/cabeçalho do `main.php`; a Descrição e a Instalação são boilerplate fixo; só o CHANGELOG e a versão variam:

```markdown
# Shipping Simulator for WooCommerce
Contribuidores: linknacional, luizbills
Link: https://linknacional.com.br/
Tags: woocommerce, shipping simulator, simulador de frete, calculadora de frete, product page
Testado até: {TESTED_UP}
Versão estável: {VERSION}
Licença: GPLv3
URI da Licença: https://www.gnu.org/licenses/gpl-3.0.html
Traduções: Português(Brasil) / Inglês

Calcule o frete nas páginas de produto e carrinho, com regras de frete grátis, barra de progresso e autopreenchimento de endereço para WooCommerce.

## Descrição

O Shipping Simulator for WooCommerce traz a calculadora de frete para o cliente enquanto ele ainda navega: direto na página do produto e no carrinho. Em vez de esperar o checkout, o cliente informa o CEP e vê na hora os métodos de entrega disponíveis, o preço e o prazo estimado.

O plugin também ajuda a aumentar o ticket médio com regras de frete grátis — por valor mínimo no carrinho ou por produto — e uma barra de progresso configurável que mostra quanto falta para desbloquear o frete grátis. Os resultados são cacheados e o último CEP é lembrado, então consultas repetidas são rápidas.

= Recursos da calculadora =

* Calcular o frete direto na página do produto
* Calcular o frete na página do carrinho
* Frete grátis por valor mínimo e por produto
* Barra de progresso de frete grátis no carrinho com mensagens configuráveis
* Busca automática de CEP com resultados em cache (o último CEP é lembrado)
* Personalização visual dos campos: cores, bordas, ícones e posição

= Recursos do simulador legado =

* Calcular o frete sem precisar escolher variações (confira nas configurações do plugin)
* Preenche e atualiza automaticamente o endereço do cliente (confira nas configurações do plugin)
* Textos personalizáveis: título, placeholder, botão e mensagens
* Se estiver usando algum page builder, use o shortcode `[wc_shipping_simulator]`

**Dependências**

Este plugin depende do WooCommerce.

**Instruções de uso**

Acesse o painel e abra **WooCommerce > Configurações > Calculadora de frete** para configurar a nova calculadora, o frete grátis e as opções da barra de progresso.

## Instalação

1. Baixe o plugin.
2. No painel administrativo do WordPress, vá para Plugins > Adicionar Novo.
3. Clique em "Enviar Plugin" e selecione o arquivo ZIP do plugin que você baixou.
4. Clique em "Instalar Agora" e, em seguida, em "Ativar Plugin".
5. Certifique-se de que o WooCommerce também está ativado.

## CHANGELOG:

{BULLETS copiados da entrada mais recente do CHANGELOG.md, no formato "* Item". NÃO invente a partir do git log.}
```

### 5. Abrir o PR

Sempre `dev` → `main`:

```bash
gh pr create \
  --base main \
  --head dev \
  --title "VERSION - REPO_NAME(RESUMO_CURTO)" \
  --body "$(cat <<'EOF'
...corpo...
EOF
)"
```

### 6. Confirmar

Mostre a URL retornada pelo `gh` e o comando usado. Se o PR já existir para `dev` → `main`, o `gh` vai avisar — não force `--force` sem pedir.

## Regras

- **Nunca** edite arquivos do repo para abrir o PR (é só `gh pr create`).
- Título SEMPRE no formato `VERSION - shipping-simulator-for-woocommerce(resumo)`, **sem espaço antes do parêntese**.
- `REPO_NAME` é o nome do repositório no GitHub (`shipping-simulator-for-woocommerce`) — derive via `git config --get remote.origin.url`, não de `basename $PWD`.
- Corpo SEMPRE com o cabeçalho de metadados, Descrição, Instalação e a seção `## CHANGELOG:` com os bullets da versão.
- Se `version` / `tested_up` divergirem entre o cabeçalho do `main.php` e o `readme.txt`, use o **cabeçalho do `main.php`** e avise.
- Nunca inclua hashes de commit no corpo.
- Bullets do corpo SEMPRE vindos da entrada mais recente do `CHANGELOG.md` — nunca do `git log`.
