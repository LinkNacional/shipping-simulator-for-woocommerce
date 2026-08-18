---
name: prepare-release
description: Prepara release do shipping-simulator-for-woocommerce: atualiza readme.txt, CHANGELOG.md, cabeçalho do main.php e DEPLOY_TAG dos workflows baseado no git log
---

# prepare-release

Atualiza **todos** os arquivos que contêm o número de versão para uma nova release do plugin.

## Parâmetros (via `arguments`)

O usuário pode passar os valores diretamente: `"version=2.5.0 tested_up=6.9 php=7.4 requires_at_least=4.9 highlights=Correção de bug X"`. Se algum valor faltar, pergunte.

- **version** — nova versão (Stable tag)
- **tested_up** — versão do WP testada (Tested up to)
- **php** — versão mínima do PHP (Requires PHP)
- **requires_at_least** — versão mínima do WordPress (Requires at least)
- **highlights** — resumo da versão (opcional, usa git log se vazio)

## Fluxo de execução

### 1. Coletar valores
Se não recebidos via arguments, pergunte ao usuário um por um. Detecte a versão atual via grep no `main.php`:
```
grep -E "Version:|Requires at least:|Requires PHP:" main.php
```

### 2. Analisar as mudanças reais (NÃO copiar os comentários dos commits)
Os bullets do changelog devem descrever o **efeito real** das mudanças, não o texto dos `git log`.
1. Liste os arquivos alterados: `git diff --stat ${LAST_TAG}..HEAD`
2. Leia o diff dos arquivos de código: `git diff ${LAST_TAG}..HEAD -- classes/ core/ templates/ assets/`
3. Escreva cada bullet como "o que mudou para o usuário".

Só se não for possível ler o diff, use os comentários dos commits como pista — nunca como texto final.

### 3. Capturar git log
```bash
LAST_TAG=$(git describe --tags --abbrev=0 2>/dev/null)
if [ -z "$LAST_TAG" ]; then
    git log -n 10 --oneline
else
    git log ${LAST_TAG}..HEAD --oneline
fi
```

### 4. Atualizar TODOS os arquivos com versão

A versão aparece em **6 locais** espalhados por **6 arquivos**. Atualize todos:

#### 4a. `main.php`
- `* Version: NOVA_VERSION` (cabeçalho do plugin — É a fonte da verdade; `Config::init()` lê via `get_file_data`)
- `* Requires at least:` e `* Requires PHP:` — manter em sincronia com o `readme.txt`

#### 4b. `readme.txt`
- `Stable tag:` → nova versão
- `Requires at least:`, `Tested up to:` e `Requires PHP:` se alterados
- Adicionar entrada no topo da seção `== Changelog ==`, **em inglês**, preservando o formato atual (`= VERSION =` + bullets + linha em branco):
  ```
  = 2.5.0 =

  -   Item baseado nas mudanças

  = 2.4.4 =
  ```
- Se `highlights` foi fornecido, avalie adicionar na `== Description ==` (NUNCA apague conteúdo existente)

#### 4c. `CHANGELOG.md`
- Adicionar entrada no topo do arquivo, **em português**, preservando o formato atual (`## VERSION - YYYY-MM-DD` + bullets):
  ```
  ## 2.5.0 - YYYY-MM-DD

  -   Item baseado nas mudanças

  ## 2.4.4 - YYYY-MM-DD
  ```

#### 4d. `.github/workflows/main.yml`
- `DEPLOY_TAG: "NOVA_VERSION"`

#### 4e. `.github/workflows/dev-release.yml`
- `DEPLOY_TAG: "NOVA_VERSION"`

#### 4f. `.github/workflows/wordpressRelease.yml`
- `DEPLOY_TAG: "NOVA_VERSION"`

### 5. Validação final
Rodar grep com a versão **antiga** para confirmar que não restou nenhuma ocorrência fora do esperado:
```
grep -r "VERSAO_ANTIGA" --include="*.php" --include="*.md" --include="*.txt" --include="*.yml" .
```
O esperado: `readme.txt` e `CHANGELOG.md` ainda contêm a versão antiga **apenas** nas entradas antigas do Changelog. Qualquer outro arquivo retornando a versão antiga é **erro**.

Depois, grep com a versão **nova** para confirmar que aparece nos **6 locais**:
```
grep -rn "NOVA_VERSAO" --include="*.php" --include="*.md" --include="*.txt" --include="*.yml" .
```

## Observações específicas deste plugin
- **Fonte da verdade da versão** é o cabeçalho `Version:` do `main.php` (lido por `core/Config.php` via `get_file_data`). Não há constante de versão.
- **`core/VERSION`** é um arquivo legado/obsoleto (conteúdo `2.0.1`) que **não é lido pelo código**. Não usá-lo como fonte de versão; pode ser removido ou ignorado.
- `composer.json` e `package.json` **não** têm campo `version` — não editar.
- `README.md` **não** tem campo de versão explícito (badges dinâmicas) — não editar.
- Não há `release-candidate.yml`; o pré-release usa `dev-release.yml`.
- Assets do WP.org: pasta `.wordpress-org/` (workflow de deploy usa `ASSETS_DIR: .wordpress-org`).
- `readme.txt` usa `= VERSION =`; `CHANGELOG.md` usa `## VERSION - YYYY-MM-DD`. Formatar cada um no seu padrão.
- **Text domain**: `wc-shipping-simulator` (difere do slug `shipping-simulator-for-woocommerce`). NÃO renomear sem tratar o `textdomain_mismatch` e as traduções.
- **Build no release**: os workflows já rodam `composer install --no-dev` + `npm run build` antes de empacotar (necessário porque `vendor/` e os `.min.*` são gitignored). Não remover essas etapas.
