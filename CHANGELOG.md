# Historico de versoes

## Alteracoes posteriores a 1.0.0-php

- Comparativo diario dos ultimos sete dias com grafico de linhas, consumo, energia injetada e saldo, respeitando fuso e cobertura do historico.

- Graficos do painel e da aba Tecnica exibidos somente com linhas, sem marcadores circulares.

- Area administrativa com usuarios, perfis Administrador/Consulta, senhas em hash, ativacao e redefinicao de senha.
- Migracao do acesso atual para primeiro administrador, invalidacao de sessoes apos alteracoes e protecao do proprio acesso.
- Configuracoes persistentes no MySQL e auditoria das alteracoes administrativas.

- Tela de login e sessao de duas horas, logout por POST, CSRF e limite de tentativas, preservando as credenciais existentes.
- Cookies protegidos em HTTPS; APIs de consulta exigem sessao e nao apresentam HTTP Basic; endpoints do medidor continuam com token.

- Exportacao consistente do MySQL local e importacao HTTPS em lotes, preservando dados brutos, horarios e verificacao SHA-256 por registro.
- Reimportacoes idempotentes e conflitos rejeitados sem substituir medicoes existentes.

- Ponte HTTP/HTTPS para Orange Pi, com fila SQLite persistente, reenvio e status local.
- Receptor HTTPS idempotente com preservacao de horario e dados originais.

- Pipeline aguarda o Apache e o health antes de validar, evitando falhas intermitentes durante a inicializacao do conteiner.

- Branch Hostinger instala entrada publica e pasta private bloqueada, compativel com destino public_html/energia; runtime permanece fora da pasta publica.

- Nome oficial PY9MT Energia aplicado ao painel e documentacao; repositorio on-grid-monitor.

- Versao automatica por commit, identificacao discreta no painel e pipeline GitHub para branch Hostinger com dependencias prontas.
- Configuracoes e writable de producao podem ficar em energia_runtime, fora da pasta de deploy.

- Instalacao inicial automatica das tabelas no primeiro acesso, com lock MySQL, configuracao DB_AUTO_MIGRATE e preservacao de bancos existentes.

- Migration inicial autocontida com Database Forge, indices e preservacao de tabelas ja importadas; instrucoes de instalacao por Spark.

- APIs, links e formulario usam o mesmo endereco acessado no navegador, funcionando por IP e localhost e preservando instalacoes em subpastas.

- Quadro unico Geracao/Consumo com colunas Consumida, Gerada (injetada na rede) e Total, mantendo cores e cobertura por periodo.

- Quadro central de saldo de energia (injecao menos consumo) por dia, semana e mes, com indicacao de cobertura parcial.

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
