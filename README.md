# Sorteador OBS

Sorteador para transmissao ao vivo, com **cadastro publico**, **painel administrativo** e **overlay verde para chroma key no OBS**.

## Tecnologias
- HTML5, CSS3 e JavaScript no navegador.
- PHP 8.2+ no Apache/Nginx (API, autenticacao, sorteio e acesso ao banco).
- MySQL 8+ ou MariaDB 10.6+ (InnoDB).
- **Sem Node.js, npm, WebSocket externo ou servicos pagos.**

> Um site feito apenas com HTML/JavaScript nao pode acessar MySQL com seguranca. O PHP atua somente como backend leve; nao altera o frontend solicitado.

## Telas
- `/index.php`: formulario publico (nome, telefone com DDD e CPF).
- `/admin.php`: login, estatisticas, sorteio, consulta por numero/nome/CPF/telefone, historico e reset.
- `/overlay.php`: fundo verde puro (#00FF00), lista rolante dos inscritos, animacao de 4 s e botao de sorteio **somente quando aberto com sessao de admin**.
- `/overlay.php?obs=1`: versao limpa para fonte de navegador do OBS, **sem botoes administrativos**.

O sorteio acontece no servidor com `random_int()` e transacao no MySQL. Cada CPF so pode se cadastrar uma vez e o ganhador fica inelegivel para as proximas rodadas. A tela OBS busca atualizacoes automaticamente, a cada aproximadamente 1,2 segundo, sem precisar recarregar.

## Instalacao na VPS
1. Tenha Apache ou Nginx, PHP 8.2+ com **pdo_mysql**, openssl e MySQL/MariaDB.
2. Clone o repositorio (na pasta do seu projeto):

   ```bash
   git clone https://github.com/vitoryvensreis/sorteadorobs.git
   cd sorteadorobs
   ```

3. Crie o banco e importe a estrutura como usuario com permissao de criar banco:

   ```bash
   mysql -u root -p < sql/schema.sql
   ```

   Caso o painel ja tenha criado o banco, selecione-o e execute as tabelas do arquivo SQL (ignorando `CREATE DATABASE` e `USE`, se necessario). Crie usuario de banco dedicado com permissoes apenas sobre esse banco.

4. Crie a configuracao local:

   ```bash
   cp app/config.example.php app/config.php
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```

   Abra `app/config.php`, coloque os dados reais de MySQL e a chave hexadecimal de 64 caracteres gerada acima. **Nunca publique este arquivo nem mude a chave depois que houver CPFs cadastrados.**

5. Crie o administrador via terminal (senha com pelo menos 12 caracteres):

   ```bash
   read -s -p "Senha do admin: " ADMIN_PASSWORD; echo
   export ADMIN_PASSWORD
   php bin/create-admin.php admin
   unset ADMIN_PASSWORD
   ```

6. No vhost do CloudPanel/Apache/Nginx, configure o **document root** para a pasta `sorteadorobs/public` (nao para a raiz do repositorio). Habilite HTTPS e teste `/index.php` e `/admin.php`.

## Adicionar ao OBS
1. No OBS Studio, crie uma fonte **Navegador** com URL `https://SEU-DOMINIO/overlay.php?obs=1`.
2. Use largura **1920** e altura **1080**.
3. Na fonte, clique em **Filtros → Filtros de efeito → Chroma Key** e escolha **verde**.
4. Abra `/admin.php` em um navegador comum para fazer o sorteio. A fonte do OBS acompanha automaticamente.
5. Se quiser sortear na propria tela verde em um navegador autenticado, abra `/overlay.php` sem `?obs=1`.

## Funcoes administrativas
- **Sortear agora**: escolhe um inscrito ainda nao vencedor, grava no historico e inicia a animacao na tela OBS.
- **Reiniciar vencedores**: apaga o historico e libera todos os mesmos inscritos para novos sorteios.
- **Apagar todos os cadastros**: exclui inscritos e resultados permanentemente, com confirmacao `APAGAR TUDO`. Os IDs do banco sao sequenciais e podem nao voltar a 1 apos limpeza.
- **Consultar**: busca por numero de inscricao, nome, telefone ou CPF completo. O CPF e exibido apenas mascarado.

## Privacidade e seguranca
- CPF validado, criptografado com AES-256-GCM e indexado por HMAC-SHA256; nunca enviado para o overlay publico.
- Consentimento obrigatorio para exibir **nome e numero de inscricao** na transmissao. Telefone e CPF nao aparecem no overlay.
- Login com hash de senha, sessao HttpOnly, token CSRF e limites por IP para login e cadastro.
- Sorteio serializado no banco para evitar vencedores repetidos mesmo com cliques simultaneos.
- O OBS faz consulta periodica; a animacao dura 4 segundos desde a deteccao de um novo sorteio no navegador.
- **Obrigatorio HTTPS em producao.** Antes do evento, defina regras do sorteio, politica de privacidade, prazo de retencao e eventual verificacao de requisitos legais. Sorteios promocionais podem exigir autorizacoes especificas no Brasil.

## Estrutura
```text
app/                 # backend PHP, configuracao e servicos (fora da pasta publica)
bin/create-admin.php # criacao de admin via terminal
public/index.php     # cadastro
public/admin.php     # painel
public/overlay.php   # fonte verde OBS
public/api.php       # API PHP
public/assets/       # CSS e JS
sql/schema.sql       # schema e estado do sorteio
```

**Status:** codigo inicial implementado e versionado. Requer configuracao de dominio, MySQL, PHP e administrador na sua VPS antes do primeiro teste em producao.
