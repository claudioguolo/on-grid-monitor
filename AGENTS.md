# Manutencao do coletor PHP

Toda alteracao concluida deve ser registrada em commit, conforme solicitado pelo usuario. Configure `git config core.hooksPath .githooks` em cada clone. O hook atualiza VERSION automaticamente; nao edite VERSION manualmente. Execute as verificacoes pertinentes antes do commit. Depois do commit, regenere o pacote se precisar entregar o ZIP e reconstrua o conteiner quando a imagem precisar refletir o codigo ou a versao. Nunca versione .env, backups ou medicoes. Publicacao remota exige conexao GitHub configurada; nao afirme que houve push sem confirma-lo.
