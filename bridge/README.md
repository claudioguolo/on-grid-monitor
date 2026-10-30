# PY9MT Energia — ponte do Orange Pi

Fluxo: medidor → HTTP local → SQLite persistente → HTTPS autenticado → MySQL online.

Instalacao destinada a `/dados/sistemas/py9mt-energia-bridge`, independente de `seriema` na porta 80. Recebe em `http://192.168.0.86:8080/api/energia/medidor`. POST JSON e formulario, GET com query e texto bruto sao preservados. O computador antigo deixa de ser necessario para essa coleta.

Copie `.env.example` para `.env`, preencha ONLINE_TOKEN com o mesmo meter.token do servidor e use ONLINE_URL=`https://energia.py9mt.qsl.br/api/energia/bridge`. O BRIDGE_ID precisa ser estavel e exclusivo desse equipamento. Nunca envie o .env ao Git. Suba com `docker compose up -d --build`. A politica restart always inicia o conteiner com o Docker; o Docker precisa estar habilitado no sistema operacional.

O medidor so recebe HTTP 200 depois do commit SQLite. Sem espaco ou com falha de armazenamento recebe 503; o aparelho precisa reenviar nesse caso. Payload maximo 128 KiB. O coletor mantem um UUID por evento salvo e confirma a entrega somente quando o servidor HTTPS responde ok e o mesmo event_id. Se a resposta se perder, o evento e reenviado e o servidor reconhece a duplicata pela chave unica existente. A chave repetida com conteudo diferente e rejeitada. Isso evita duplicatas dos reenvios da ponte; duas requisicoes distintas reenviadas pelo proprio medidor sem identidade de amostra ainda sao dois eventos.

Falhas de rede, HTTP ou autenticacao permanecem pendentes; novas tentativas usam atraso crescente limitado a 5 minutos. A fila sobrevive a reinicios, nao exclui eventos automaticamente e conserva tambem os ja entregues. Confirme o espaco disponivel periodicamente. O volume `cache` contem `/data/queue.sqlite3`. Nunca execute down -v se quiser preservar o historico. O banco usa WAL e synchronous FULL; para backup consistente, use sqlite3.Connection.backup em vez de copiar apenas o arquivo com o processo ativo.

Status: `http://192.168.0.86:8080/health` mostra cached, pending, delivered, last_delivery e last_error, sem credenciais. `docker compose ps` e `docker compose logs --tail=50` verificam o servico. O health confirma o armazenamento local, nao a disponibilidade da internet. Pendencias e last_error indicam problemas de encaminhamento.

O transporte externo exige certificado HTTPS valido e nao segue redirecionamentos. O token vai no cabecalho X-Meter-Token, nao na URL. O servidor conserva os bytes originais e o horario UTC em que o Orange Pi recebeu a amostra; arrived_at registra a chegada posterior na nuvem. Mantenha o relogio do Orange Pi sincronizado. O circuito continua bifasico: as regras A/B do painel online sao preservadas.

A porta HTTP local nao tem login e deve ficar acessivel apenas na rede confiavel do medidor; nao abra essa porta no roteador para a internet. Este componente nao inclui interface de administracao remota.

Testes: `python3 -m unittest discover -s bridge -p 'test_*.py' -v`. O teste integrado do receptor online usa banco isolado: `python3 tests/bridge_http_check.py` com o Compose principal ativo.
