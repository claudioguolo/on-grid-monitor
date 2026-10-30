# PY9MT Energia

**Hostinger com Git restrito a public_html:** siga [docs/HOSTINGER.md](docs/HOSTINGER.md). Esse procedimento substitui os caminhos antigos de publicacao descritos abaixo. A branch `hostinger` agora instala tudo em `public_html/energia`, com arquivos internos bloqueados em `private/`.

Sistema de coleta de dados de consumo e geração de energia. Monitoramento de consumo e injeção com PHP / CodeIgniter / MySQL.

Repositório: https://github.com/claudioguolo/on-grid-monitor

Versão `1.0.0-php`. Migração do coletor Python: CodeIgniter **4.7.5**, PHP **8.3** no Docker (mínimo **8.2**), MySQL **8.0+** (8.4 no Docker). Compatível com hospedagem PHP compartilhada que ofereça `intl`, `mbstring`, `mysqli`, HTTPS e rewrite para `public/index.php`.

## Ambiente local

```sh
cp .env.example .env  # somente se .env ainda não existe; defina senhas próprias
# Configure MYSQL_PASSWORD e MYSQL_ROOT_PASSWORD no .env.
docker compose up -d --build
docker compose exec -T app php spark migrate --all
```

Painel: http://localhost:8080. A versão PHP agora ocupa a porta 8080 e recebe diretamente os envios do medidor. O contêiner Python foi baixado e seu volume SQLite permanece preservado para recuperação. Não inicie os dois projetos na mesma porta.

Recepção compatível: `POST` ou `GET /api/energia/medidor`, JSON, formulário ou texto. Os nomes dos campos, sinais, valores e APIs de gráficos permanecem compatíveis. MySQL guarda corpo original binário, JSON interpretado, origem, método e horário UTC. Os dados legados conservam seu horário original. O painel exibe tensão, corrente, potência ativa, reativa e aparente, fator de potência, frequência, energia por período e gráficos técnicos das fases A e B. Fase C desconectada.

`config/software.json` preserva os parâmetros da versão Python. Credenciais ficam em `.env` e não entram no Git. Alterações no JSON montado são lidas nas próximas requisições. Não altere o fuso após importar o histórico sem revisar as datas.

## Publicar na Hostinger Single

1. No hPanel, configure PHP **8.3** ou **8.2** e habilite `intl`, `mbstring`, `mysqli`. Crie um banco MySQL e usuário. Confirme os recursos disponíveis no plano contratado.
2. Envie os arquivos com `vendor/` instalado. O pacote em `dist/coletor_energia_php-hostinger.zip` inclui dependências de produção. Não é necessário Docker ou processo Python na hospedagem. Para gerar novamente: `python3 tools/package_hostinger.py`.
3. O document root deve apontar **somente para `public/`**. Mantenha `app`, `vendor`, `config`, `database`, `writable`, `VERSION` e `.env` fora da área pública. Nunca exponha o diretório inteiro do projeto.
4. Se o hPanel não permitir alterar o document root, coloque o projeto em `domains/SEUDOMINIO/energia_app/`. Copie apenas o conteúdo de `public/`, incluindo `.htaccess`, para `domains/SEUDOMINIO/public_html/`. Nesse `public_html/index.php`, altere `require FCPATH . '../app/Config/Paths.php';` para `require FCPATH . '../energia_app/app/Config/Paths.php';`. Mantenha uma cópia de `public/` dentro de `energia_app` para o restante do projeto.
5. No diretório privado do projeto, crie `.env` a partir de `env.hostinger.example`. Preencha a URL HTTPS, as credenciais MySQL, usuário/senha do painel e token do medidor. O usuário do painel é solicitado pelo navegador via HTTP Basic. Em produção, APIs de consulta e painel ficam bloqueados se as credenciais não estiverem configuradas. A recepção exige o token.
6. Garanta que `writable/cache`, `writable/logs`, `writable/session` e `writable/uploads` existam e sejam graváveis pelo PHP. Comece com permissões 755, ajustando conforme o usuário da hospedagem; não use 777 indiscriminadamente.
7. As tabelas sao criadas automaticamente na primeira requisicao, inclusive ao abrir o painel, desde que as credenciais e permissoes MySQL estejam corretas. Opcionalmente, importe `database/schema.sql` pelo phpMyAdmin. Para levar o histórico já migrado, importe **em vez disso** o dump `backups/mysql-history.sql` em um banco vazio. O dump tem dados privados: transfira pelo phpMyAdmin e não o coloque na pasta pública. Alternativamente, com SSH, execute `php spark migrate --all` e o importador NDJSON.
8. Abra `/health`, o painel e a aba Técnica. Confira os totais antes de configurar o aparelho. O login deve ser exigido no painel e `/api/readings`.
9. No medidor, use o domínio do servidor, o caminho `/api/energia/medidor?token=SEU_TOKEN`, a porta/protocolo suportados pelo firmware e o envio HTTP POST. O suporte a HTTPS do aparelho precisa ser confirmado: a tela atual só demonstra HTTP. Não exponha usuário/senha do painel no endereço do medidor. Se o aparelho não suportar HTTPS, use uma ponte local que encaminhe os envios por HTTPS, ou valide uma solução de transporte apropriada antes de publicar a recepção; o coletor atual não implementa essa ponte.

Não há acesso à sua conta Hostinger nesta sessão; a instalação no provedor e o teste real do aparelho pela internet ainda precisam ser feitos.

## Migrar o banco Python sem perdas

Use uma cópia consistente criada com `sqlite3.Connection.backup`, nunca copie apenas o arquivo SQLite de um banco ativo em WAL.

```sh
python3 tools/export_sqlite.py backups/sqlite-before-php.sqlite3 backups/python-history.ndjson
docker compose cp backups/python-history.ndjson app:/tmp/python-history.ndjson
docker compose exec -T app php spark energia:import-python /tmp/python-history.ndjson
```

O importador é transacional e idempotente: uma segunda execução ignora os mesmos registros. Os IDs internos MySQL podem diferir dos IDs SQLite; os dados das medições são preservados e há uma chave de proveniência para deduplicação. A transferência final preservou 4.160 registros do Python, com 171 novos registros acrescentados à cópia inicial. A coleta Python está encerrada; os novos envios são armazenados diretamente no MySQL. Backups da troca ficam em `backups/cutover/`. Para voltar ao Python, primeiro pare o PHP e só então inicie o projeto Python; medições recebidas exclusivamente pelo PHP precisariam de transferência própria.

## Importar TXT

O link **Importar histórico TXT** permite enviar arquivos `DDMMAAAA.txt` sem SSH (formato de 21 ou 29 campos). Upload protegido por autenticação e CSRF. Divide valores por 100 e usa o fuso configurado. Ficam nulos os campos ausentes. Arquivos de até 4 MiB; configure também `upload_max_filesize` e `post_max_size` no provedor. Faça backup pelo phpMyAdmin antes de importar. Não substitui registros existentes. Pela CLI:

```sh
docker compose exec -T app php spark energia:import-txt /tmp/28102026.txt
```

## Testes e operação

```sh
docker compose run --rm --no-deps -v "$PWD/tests:/var/www/html/tests:ro" app php tests/energy_check.php
```

Os testes HTTP de `tests/http_check.php` destinam-se exclusivamente a um banco isolado e credenciais fictícias de teste, pois inserem amostras. Não os execute contra produção. O volume `mysql_data` é persistente; `docker compose down -v` o apaga. Para backup, use phpMyAdmin ou `mysqldump --single-transaction --no-tablespaces`. Backups, `.env`, dados pessoais e dependências não são versionados.

Consumo/injeção instantâneos usam o saldo A+B. Energia acumulada usa diferenças dos contadores por fase e pode diferir do critério de compensação da concessionária. A cobertura incompleta e quedas de contadores são sinalizadas. A média técnica é amostral, não ponderada pelo tempo.

## Criar tabelas com migration

Com o banco vazio já criado no hPanel e as credenciais configuradas no `.env`, execute no diretório privado do projeto (onde está `spark`):

```sh
php spark migrate --all
php spark migrate:status
```

No Docker local, use `docker compose exec -T app php spark migrate --all`.

A migration `2026-10-30-000001_CreateReadings` cria `readings`, chave primária, índice cronológico e chave única de importação diretamente pelo Database Forge do CodeIgniter, sem depender do arquivo SQL. O framework cria a tabela `migrations` para controlar o que já foi executado. Uma segunda execução não recria nem apaga tabelas. Uma tabela `readings` existente (por importação SQL) é preservada; a migration não corrige automaticamente uma estrutura externa incompatível.

A migration cria as tabelas **dentro do banco existente**; não cria o banco nem o usuário MySQL. O primeiro acesso executa as migrations de instalacao se `readings` ainda nao existir. Se o plano não tiver SSH/terminal, use a importação de `database/schema.sql` pelo phpMyAdmin. Não há endpoint web para alterar o esquema.

O comando de rollback é destrutivo: o método `down()` remove `readings` e suas medições. Faça backup antes de qualquer rollback; ele não é necessário na instalação normal.

## Inicialização automática sem SSH

Depois de criar o banco vazio no hPanel e preencher `.env`, basta acessar o painel: o filtro `InitializeDatabase` cria as tabelas com a migration inicial. Isso também acontece se o primeiro acesso for um envio do medidor. O usuário MySQL precisa ter permissões de criação de tabelas e índices.

O padrão é `DB_AUTO_MIGRATE=true`; use `DB_AUTO_MIGRATE=false` no `.env` para optar exclusivamente pela instalação manual. Quando `readings` já existe, o filtro não executa migrations nem altera os registros existentes. Atualizações futuras de esquema continuam usando `php spark migrate --all`; esta automação é para a instalação inicial. O banco e usuário MySQL continuam sendo criados pelo hPanel.

A inicialização usa um lock MySQL para serializar primeiros acessos simultâneos. Falhas de credenciais ou permissões retornam HTTP 503 com orientação genérica e detalhes nos logs privados; um acesso posterior tenta novamente. Não há rota pública para rollback ou atualização manual do esquema.

Para validar a inicialização automática: `python3 tests/bootstrap_check.py` com o Compose ativo. O teste usa banco e contêineres temporários isolados e os remove ao final.

## GitHub → Hostinger e versões automáticas

Cada commit recebe `MAJOR.MINOR.N`, onde `N` é a quantidade de commits no histórico. A série fica em `VERSION_SERIES`. Em cada clone, execute `git config core.hooksPath .githooks`: o hook pre-commit atualiza e inclui `VERSION` no mesmo commit. Arquivos ainda não commitados não constituem uma versão. Não reescreva o histórico de `main`; use commits normais ou squash merge. O GitHub recalcula a versão no histórico completo e gera uma tag por versão validada. A identificação aparece no canto superior direito do painel, inclusive antes de as APIs carregarem.

Ao enviar alterações para `main`, o workflow valida o projeto, gera o pacote com dependências de produção e atualiza a branch **hostinger** sem force push. A branch contém artefatos prontos, `VERSION` e `BUILD.json` com o commit de origem. Configure a integração do hPanel para seguir **hostinger**, nunca `main` (que não contém vendor). Não edite essa branch manualmente. A publicação só ocorre após os testes passarem; falhas ficam registradas em Actions. Permita escrita de Contents ao GitHub Actions e a criação da branch/tag conforme as regras do repositório.

Configuração para `https://www.py9mt.qsl.br/energia/`: publique a branch `hostinger` em `public_html/energia` e mantenha `.env` e writable em `energia_runtime`, no mesmo nível de public_html. A entrada index.php e os bloqueios HTTP já são gerados pelo pipeline. Siga [docs/HOSTINGER.md](docs/HOSTINGER.md) para configurar e validar a instalação.

O pacote manual continua disponível em `dist/`. Ele pode ser instalado sem GitHub. Nenhuma senha deve ser adicionada ao repositório nem à branch de produção. A integração remota ainda precisa ser conectada nas contas; os workflows locais, isoladamente, não publicam o projeto.
