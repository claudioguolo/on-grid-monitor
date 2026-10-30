# PY9MT Energia — instalacao pelo Git da Hostinger

Este procedimento substitui as instrucoes anteriores que pediam para publicar o Git fora de public_html.

## 1. Backup e publicacao

Faca backup dos arquivos existentes de `public_html/energia` e do banco antes de alterar a integracao. Preserve qualquer `.env` existente em local privado. No hPanel > Avancado > Git:

- Repositorio: `claudioguolo/on-grid-monitor`.
- Branch: **hostinger**, produzida pelo GitHub Actions apos os testes.
- Destino: **public_html/energia**, nunca public_html inteiro.
- Habilite a publicacao automatica depois da primeira validacao.

Aguarde o workflow do GitHub terminar e faca um Redeploy. A branch ja inclui dependencias e a entrada publica ajustada; nao copie mais public/ nem edite index.php manualmente.

Estrutura resultante:

```
Home/
  energia_runtime/
    .env
    writable/
      cache/
      logs/
      session/
      uploads/
  public_html/
    energia/
      index.php
      .htaccess
      private/
        .htaccess
        app/
        vendor/
        config/
        database/
        VERSION
        BUILD.json
```

`private/.htaccess` bloqueia toda requisicao HTTP; o .htaccess da entrada tambem bloqueia essa pasta antes do roteamento. O PHP inclui esses arquivos internamente. Isso depende do servidor respeitar as regras: valide os bloqueios abaixo antes de deixar a instalacao ativa. A pasta energia_runtime deve ficar no mesmo nivel de public_html, nao dentro dela.

## 2. Configurar uma unica vez

No gerenciador de arquivos, crie `energia_runtime` e as subpastas mostradas acima. Copie o conteudo de `public_html/energia/private/env.hostinger.example` para `energia_runtime/.env` e preencha:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://www.py9mt.qsl.br/energia/'
database.default.hostname = localhost
database.default.database = 'NOME_COMPLETO_DO_BANCO'
database.default.username = 'USUARIO_COMPLETO_DO_BANCO'
database.default.password = 'SENHA_MYSQL'
database.default.DBDriver = MySQLi
dashboard.username = admin
dashboard.password = 'SENHA_FORTE_DO_PAINEL'
meter.token = 'TOKEN_ALEATORIO_DO_MEDIDOR'
DB_AUTO_MIGRATE = true
```

Habilite PHP 8.2 ou 8.3 e as extensoes intl, mbstring e mysqli. Os diretorios writable precisam ser gravaveis pelo usuario PHP (use as permissoes recomendadas pelo provedor). Nao coloque senhas no GitHub, no campo de configuracao do Git ou em public_html. O banco vazio e o usuario continuam sendo criados pelo hPanel; as tabelas sao criadas no primeiro acesso.

## 3. Validar

- `https://www.py9mt.qsl.br/energia/`: solicita login e mostra o painel com a versao atual.
- `https://www.py9mt.qsl.br/energia/health`: retorna `{"ok":true}`.
- `https://www.py9mt.qsl.br/energia/private/VERSION`: deve retornar **403**.
- `https://www.py9mt.qsl.br/energia/private/config/software.json`: deve retornar **403**.
- `https://www.py9mt.qsl.br/energia/private/vendor/autoload.php`: deve retornar **403**.

Se qualquer arquivo privado ficar acessivel, interrompa o uso e revise as regras com a Hostinger. Nao configure ainda o medidor ate validar o HTTPS e o endpoint de recepcao; o suporte HTTPS do firmware precisa ser testado separadamente.

## Atualizacoes

As mudancas commitadas em main passam pelos testes e atualizam hostinger. O hPanel publica o novo conteudo, inclusive index.php e .htaccess, sem edicao manual. energia_runtime permanece fora do destino do Git. A inicializacao automatica atende bancos vazios; novas migrations futuras exigem planejamento de atualizacao e backup.

Instalacao manual alternativa: extraia `dist/py9mt-energia-public_html.zip` diretamente em public_html/energia, mantendo a mesma configuracao de energia_runtime. O ZIP `coletor_energia_php-hostinger.zip` e um pacote-fonte usado internamente pelo pipeline, nao deve ser extraido inteiro na pasta publica.
