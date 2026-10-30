# Historico de versoes

## Alteracoes posteriores a 1.0.0-php

- Quadro central de saldo de energia (consumo menos injecao) por dia, semana e mes, com indicacao de cobertura parcial.

- PHP assume a porta 8080 e recebe os envios reais do medidor.
- Transferencia final do SQLite para MySQL com verificacao integral; coletor Python baixado e volume preservado.

- Barra lateral com largura reservada no desktop e faixa superior em telas menores.
- Ajustes de grades, tabelas e cartoes para evitar sobreposicao e rolagem horizontal.

## 1.0.0-php

- Migracao para CodeIgniter 4.7.5, PHP 8.2+ e MySQL.
- Painel e APIs compativeis com o coletor Python bifasico.
- Importacao transacional e idempotente do SQLite via NDJSON.
- Importacao TXT pela interface e CLI, com escala e fuso preservados.
- Protecao de producao com autenticacao do painel e token do medidor.
- Ambiente local Docker e pacote para hospedagem PHP compartilhada.
