#!/usr/bin/env bash
#
# Gera o corpo (body) das GitHub Releases que publicam o .zip do plugin
# "Shipping Simulator for WooCommerce".
#
# Lê os metadados de readme.txt e o resumo da versão em CHANGELOG.md do ref
# atualmente no working directory (mesma lógica dinâmica da skill open-pr),
# e imprime o corpo final no stdout.
#
# Observação: este plugin usa o arquivo `readme.txt` (minúsculo) e o
# `CHANGELOG.md` no formato `## VERSION - YYYY-MM-DD`.
#
# Uso (na raiz do repositório):
#   bash .github/scripts/generate-release-body.sh > dist/release-body.md
#
set -euo pipefail

TEMPLATE=".github/release-body-template.md"
README="readme.txt"

# Lê um campo do cabeçalho do readme.txt (ex.: "Tested up to", "Requires PHP", "Stable tag").
read_meta() {
  grep -m1 -i "^$1:" "$README" \
    | sed -E 's/^[^:]+:[[:space:]]*//; s/[[:space:]]+$//' \
    | tr -d '\r'
}

TESTED_UP="$(read_meta 'Tested up to')"
REQUIRES_PHP="$(read_meta 'Requires PHP')"
STABLE_TAG="$(read_meta 'Stable tag')"

# Bullets da entrada do CHANGELOG.md correspondente à versão estável
# (do cabeçalho "## VERSION - data" até o próximo cabeçalho "## ").
VERSION_SUMMARY="$(awk -v ver="$STABLE_TAG" '
  /^## / { if ($2 == ver) { found = 1; next } else if (found) { exit } }
  found { print }
' CHANGELOG.md)"

# Fallback: se a versão estável não tiver entrada, usa a mais recente do changelog.
if [ -z "$VERSION_SUMMARY" ]; then
  VERSION_SUMMARY="$(awk '/^## / { if (found) exit; found = 1; next } found { print }' CHANGELOG.md)"
fi

# Remove linhas em branco (ou só com espaços) à frente/atrás do resumo.
VERSION_SUMMARY="$(printf '%s\n' "$VERSION_SUMMARY" | awk '
  { line[NR] = $0 }
  NF { if (first == 0) first = NR; last = NR }
  END { if (first > 0) for (i = first; i <= last; i++) print line[i] }
')"

# Substitui os placeholders ${...} do template (substituição literal, sem envsubst).
while IFS= read -r line || [ -n "$line" ]; do
  line="${line//\$\{TESTED_UP\}/$TESTED_UP}"
  line="${line//\$\{REQUIRES_PHP\}/$REQUIRES_PHP}"
  line="${line//\$\{STABLE_TAG\}/$STABLE_TAG}"
  line="${line//\$\{VERSION_SUMMARY\}/$VERSION_SUMMARY}"
  printf '%s\n' "$line"
done < "$TEMPLATE"
