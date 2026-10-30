# Coletor de Energia — PHP / CodeIgniter / MySQL

Versão `1.0.0-php`. Migração do coletor Python: CodeIgniter **4.7.5**, PHP **8.3** no Docker (mínimo **8.2**), MySQL **8.0+** (8.4 no Docker). Compatível com hospedagem PHP compartilhada que ofereça `intl`, `mbstring`, `mysqli`, HTTPS e rewrite para `public/index.php`.

## Ambiente local

```sh
cp .env.example .env  # somente se .env ainda não existe; defina senhas próprias
# Configure MYSQL_PASSWORD e MYSQL_ROOT_PASSWORD no .env.
docker compose up -d --build
docker compose exec -T app php spark migrate --all
```

Painel: http://localhost:8081. A versão Python permanece na porta 8080, com seu volume SQLite preservado. O medidor continua apontado para ela até a troca explícita do destino. Não há sincronização contínua automática entre os bancos.

Recepção compatível: `POST` ou `GET /api/energia/medidor`, JSON, formulário ou texto. Os nomes dos campos, sinais, valores e APIs de gráficos permanecem compatíveis. MySQL guarda corpo original binário, JSON interpretado, origem, método e horário UTC. Os dados legados conservam seu horário original. O painel exibe tensão, corrente, potência ativa, reativa e aparente, fator de potência, frequência, energia por período e gráficos técnicos das fases A e B. Fase C desconectada.

`config/software.json` preserva os parâmetros da versão Python. Credenciais ficam em `.env` e não entram no Git. Alterações no JSON montado são lidas nas próximas requisições. Não altere o fuso após importar o histórico sem revisar as datas.

## Publicar na Hostinger Single

1. No hPanel, configure PHP **8.3** ou **8.2** e habilite `intl`, `mbstring`, `mysqli`. Crie um banco MySQL e usuário. Confirme os recursos disponíveis no plano contratado.
2. Envie os arquivos com `vendor/` instalado. O pacote em `dist/coletor_energia_php-hostinger.zip` inclui dependências de produção. Não é necessário Docker ou processo Python na hospedagem. Para gerar novamente: `python3 tools/package_hostinger.py`.
3. O document root deve apontar **somente para `public/`**. Mantenha `app`, `vendor`, `config`, `database`, `writable`, `VERSION` e `.env` fora da área pública. Nunca exponha o diretório inteiro do projeto.
4. Se o hPanel não permitir alterar o document root, coloque o projeto em `domains/SEUDOMINIO/energia_app/`. Copie apenas o conteúdo de `public/`, incluindo `.htaccess`, para `domains/SEUDOMINIO/public_html/`. Nesse `public_html/index.php`, altere `require FCPATH . '../app/Config/Paths.php';` para `require FCPATH . '../energia_app/app/Config/Paths.php';`. Mantenha uma cópia de `public/` dentro de `energia_app` para o restante do projeto.
5. No diretório privado do projeto, crie `.env` a partir de `env.hostinger.example`. Preencha a URL HTTPS, as credenciais MySQL, usuário/senha do painel e token do medidor. O usuário do painel é solicitado pelo navegador via HTTP Basic. Em produção, APIs de consulta e painel ficam bloqueados se as credenciais não estiverem configuradas. A recepção exige o token.
6. Garanta que `writable/cache`, `writable/logs`, `writable/session` e `writable/uploads` existam e sejam graváveis pelo PHP. Comece com permissões 755, ajustando conforme o usuário da hospedagem; não use 777 indiscriminadamente.
7. Pelo phpMyAdmin, importe `database/schema.sql` para criar as tabelas vazias. Para levar o histórico já migrado, importe **em vez disso** o dump `backups/mysql-history.sql` em um banco vazio. O dump tem dados privados: transfira pelo phpMyAdmin e não o coloque na pasta pública. Alternativamente, com SSH, execute `php spark migrate --all` e o importador NDJSON.
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

O importador é transacional e idempotente: uma segunda execução ignora os mesmos registros. Os IDs internos MySQL podem diferir dos IDs SQLite; os dados das medições são preservados e há uma chave de proveniência para deduplicação. Como a coleta Python continua ativa, repita uma exportação consistente e a importação no momento da troca de destino para trazer as novas medições.

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
