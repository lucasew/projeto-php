# PHPUtils

- Comentários e analytics para site estático de forma barata e self hosted
- Scripts de comentários e analytics se comunicam com a aplicação usando a API
- A API sempre retorna JSON

# Motivação

Começou como um projeto da faculdade, mas que eu acabei me empolgando um pouco hehehe

# Como utilizar

## Como funciona essa tipagem nos valores

- Essas tipagens dos valores que você vai ver são todas no formato `nome:tipo`
- Tipos podem ser compostos, assim como funções.
- Tipos básicos
  - `string`: Texto
  - `int`: Numero inteiro
  - `required(T)`: Implica que o tipo `T` tem que ser obrigatoriamente especificado ou vai dar pau
  - `enum(A,B)`: Implica que o valor pode ser ou `A` ou `B`.
  - `or(T,v)`: Implica que o valor de tipo `T` é opcional de ser especificado e se não for especificado é `v`
- Valores que terminam com `id` como `cid` e `uid` implica tipo `int` 
- Pedaços de rota que começam com :, como `/api/entidade/:isso/list`, implicam strings simplificadas, ou seja, apenas caracteres alfanuméricos, numeros e -. Talvez funciona outros caracteres mas mexer com escape codes é um saco então evite.

## Autenticação

Existem basicamente dois jeitos de autenticar uma requisição

- `JWT`: passando o parâmetro `jwt:string` nas requests. Um token JWT pode ser obtido fazendo login.
- Passando usuário e senha em todas as requisições usando os parâmetros `login_user:string` e `login_password:string`.

## Rotas
- Todas as operações de todas as rotas podem ser acessadas com requisições GET. Melhor para testar no browser hehehe
- As rotas seguem um padrão que não necessariamente adere ao REST, até porque é tudo GET
- Quase todas as rotas seguem o padrão `/api/:entidade/:identificação/:operação`, podendo também não referenciar um valor existente sendo usadas como `/api/:entidade/:operação` diretamente
- As rotas tem 3 níveis de privilégio sendo eles
    - **anonimo**: qualquer um de qualquer lugar pode acessar a rota sem ter que criar conta nem nada
    - **autenticado**: qualquer um que tenha conta pode acessar a rota, limitações de controle de acesso podem ser impostas
    - **administração**: apenas usuários com role de administração podem usar a rota
- Rotas que apagam e alteram coisas no banco fornecem um `modified` que indica se alguma coisa foi alterada. Se você não tem permissão de alterar alguma coisa ou não existe esse modified vai estar como falso. 

## Implementação
- Todas as definições de rotas, seguidas do arquivo que implementa as mesmas estão no final do arquivo `routes.php`. E sim, é uma gambiarra isso ai mas quebrou um galho e permite iterar em rotas novas e existentes MUITO rápido

## Rotas implementadas
- Definidas na mesma ordem que estão definidas no `routes.php`

### Healthcheck
Rotas para verificação da integridade do servidor, se essa rota falhar é porque tem problemas de configuração, ou só o schema do banco não foi aplicado. Para aplicar o schema veja a rota `/api/admin/db_bootstrap`.

- `/healthcheck`: Verifica se as tabelas foram criadas e se o servidor está comunicando com o banco corretamente. Use essa rota para checar se o servidor está de pé.
    - `PUBLIC`

### Demos
Rotas não utilizadas na aplicação que foram criadas para testar as funções de criação de rotas que eu fiz na mão mesmo.

É pra brincar mesmo.

### Admin
Rotas de administração e debug que só são acessíveis por administradores

- `/api/admin/db_bootstap`: Dropa as tabelas da aplicação do banco e recria elas. PELO AMOR DE DEUS, NÃO USA ISSO NO DB DE PRODUÇÂO.
    - `ADMIN`
- `/api/admin/create`: Permite um administrador criar novos usuários
    - `ADMIN`
    - `user:required(string)`: Nome do novo usuário
    - `password:required(string)`: Senha do novo usuário
    - `role:required(or(enum(USER,ADMIN),USER))`: O usuário vai ser normal ou admin?
- `/api/admin/listall/:entity`: Lista todos os valores de uma entidade do banco, um `select * from :entity` mesmo.
    - `ADMIN`
    - `entity:required(string)`: Entidade a ser dumpada

- `/api/admin/env/:variable`: Uma rota para acessar o objeto `$_ENV` do PHP
  - `ADMIN`
  - `variable:required(string)`: Variável a ser descarregada

- `/api/admin/gc`: Coleta lixo de entidades apagadas. Usuários, sites e slugs apagados geram lixo, que são comentários, slugs, sites e pontos de analytics que eram desses elementos apagados. Esta rota apaga tudo de uma vez.
  - `ADMIN`

- `/api/admin/gc_dryrun`: Mostra de forma aproximada quanto lixo tem para ser coletado pela rota acima. Note que se um usuário é apagado, os sites dele viram lixo, de apagar os sites que viraram lixo os pontos de analytics e slugs viram lixo também e slugs apagados fazem comentários virarem lixo a ser apagado então os valores desta rota vão ser menos do que realmente seria apagado pelo GC porque não leva em consideração essa cascata de fatores.
  - `ADMIN`

### Usuário

Rotas para administração não privilegiada de usuários, login, cadastro e exclusão de contas

- `/api/user/signup`: Cadastra um novo usuário
  - `ANONYMOUS`
  - `user:required(string)`: Nome de usuário do novo usuário, não pode já existir na base
  - `password:required(string)`: Senha deste usuário, ela é criptografada no banco
- `/api/user/login`: Gera um token [JWT](https://jwt.io) que pode ser passado em requests futuras
  - `AUTHENTICATED`
  - **NOTA** Para essa rota funcionar é necessário autenticar nela usando o meio do usuário e senha.
  - `login_user:required(string)`: Nome de usuário de uma conta existente
  - `login_password:required(string)`: Senha da conta existente
- `/api/user/whoami`: Uma rota para testar a autenticação, obtém o usuário atual, o id de usuário dele e se ele é admin. É a mesma informação que está no payload do JWT.
  - `AUTHENTICATED`
- `/api/user/delete`: Apaga o usuário logado. Não pode ser desfeito. Não precisa de parâmetros.
  - `AUTHENTICATED`

### Site

Rotas para fazer CRUD e administração de sites que são usados pelo widget de comentários e pelo script de analytics. Um site precisa existir na base para poder ser integrado e um site sempre vai estar associado a um usuário.

- `/api/site/create`: Cria um site
  - `AUTHENTICATED`
  - `domain:required(string)`: Domínio do site
- `/api/site/list`:  Lista os sites pertencentes ao usuário atual
  - `AUTHENTICATED`
- `/api/site/:domain/get`: Checa se um site foi cadastrado
  - `ANONYMOUS`
- `/api/site/:domain/delete`: Deleta um site que o usuário tem
  - `AUTHENTICATED`

### Comentário

Rotas para criação e acesso de comentários

- `/api/comment/:domain/:slug/create`: Posta um comentário no site `:domain` post `:slug.` Se o slug não existir ele será criado na hora, se o site não existir vai dar pau.
  - `AUTHENTICATED`

- `/api/comment/:domain/:slug/list`: Lista os comentários no site `:domain` post `:slug`
  - `ANONYMOUS`
- `/api/comment/:slug_id/list`: Lista os comentários usando o id do slug, mais eficiente depois que se conhece o mesmo. Bom para refetchs.
  - `ANONYMOUS`
- `/api/comment/:cid/delete`: Deleta um comentário
  - **NOTA**: Admins podem apagar qualquer comentário, usuários normais podem apagar apenas os próprios comentários.
- `/api/comment/:cid/update`: Atualiza um comentário
  - `AUTHENTICATED`
  - Apenas o autor pode editar o comentário

### Slug

Slug é um post, página ou qualquer coisa do tipo. Um site pode ter n slugs mas um slug pode apenas ser de um site. Diferentes sites podem ter slugs com o mesmo nome sem problemas.

- `/api/slug/:slid/delete`:  Apaga um slug pelo id
  - `AUTHENTICATED`
  - Apenas o dono do site que o slug pertence pode apagar slugs
- `/api/slug/list`:  Lista os slugs dos sites do usuário atual agrupado pelo domínio do site
  - `AUTHENTICATED`
- `/api/slug/:domain/list`:  Lista os slugs de um domínio pertencente ao usuário atual
  - `AUTHENTICATED`

### Analytics

Métricas enviadas por um script cliente utilizadas para análise futura

- `/api/analytics/ping/:domain/:tag`: Envia um ponto de dados de tag `:tag` relacionado ao domínio `:domain`
  - `ANONYMOUS`
  - Todas as informações são captadas automaticamente, nenhum campo é obrigatório. Informações não planejadas podem ser passadas no body. Se o body passar de 100KB o restante é descartado. 
  - São salvos
    - IP
    - Site especificado
    - Usuário especificado se logado
    - No máximo 100KB do body salvo como texto no banco, geralmente é JSON
    - Se o User-Agent do cliente é considerado dispositivo móvel
    - A tag especificada no route param. É útil para separar dados de teste AB de dados de desempenho por exemplo.

- `/api/analytics`
  - `ADMIN`
  - Mostra os pontos de dados de todos os sites
- `/api/analytics/:domain`: Mostra os pontos de dados de um domínio do usuário atual
  - `AUTHENTICATED`

### Scripts utilitários

- `/commentsection.js`: Caixa de comentários que pode ser adicionada no seu site. 
  - Ele renderiza a caixa de comentários abaixo da tag script.
  - A estilização é por sua conta. 
    - Os elementos tem classes que podem ser estilizadas usando o CSS da sua página. 
  - Interação não trivial é feita usando `alert`s, `prompt`s e `confirm`s porque é mais simples e universal. Bem mais dificil de errar.
  - O script possui algumas opções passadas usando query params na tag script.
    - `slug:required(string)`: Slug do site onde será postado os comentários. 
      - Se você usa um Hugo da vida e consegue se desenrolar com o template tu tira isso de letra.
      - No caso do Hugo para normalizar o nome do caminho eu recomendo o uso do [urlize](https://gohugo.io/functions/urlize/).
    - `host:string`: Por padrão é o `window.location.host`. Você pode deixar sem especificar mas se você usa vercel e gosta de testar nos URLs de preview eu recomendo usar para não ter dor de cabeça a toa.
- `/utils.js`: Coisas comuns usadas em outros scripts e no dashboard simplificado que foi feito para coisas que é um tanto tenso de fazer numa caixa de comentários, como criar e apagar sites, apagar a conta, cadastrar e ter a opção de apagar slugs.
  - Não é uma boa usar externamente mas eu não to impedindo ninguém.
- `/analytics.js`: Script que fornece as primitivas necessárias para analytics usando essa aplicação. Puxando da aplicação PHP ele já desenrola de achar o servidor da API.
  - Este script define uma função chamada emitAnalyticsEvent que envia um ponto de dados usando a API. Por padrão esse script não manda nada nunca. É o seu código que aciona a função que faz o script fazer alguma coisa.
  - O script possui algumas opções passadas usando query params na tag script.
    - `defaultTag:or(string,"generic")`: Tag padrão para envio de métricas se a tag do ponto de dados não for definida. O padrão é `generic`
    - `host:or(string,window.location.host)`: Por padrão é o `window.location.host`. Você pode deixar sem especificar mas se você usa vercel e gosta de testar nos URLs de preview eu recomendo usar para não ter dor de cabeça a toa.
    - `enablePerformancePacket:boolean`: Só de definir qualquer coisa que não seja vazio já faz esta propriedade ser interpretada como verdadeiro. Essa propriedade ativa, ao lançar o evento `load` na página o envio de um pacote de tag `performance` enviando o conteúdo de `window.performance`. Ele avisa no console quando enviar. A precisão dos dados é na casa dos segundos.

# O quanto isso tudo foi testado

Não muito. As operações com o banco estão funcionais, talvez tenha algum bug por quebra de API aqui e alí, um erro silencioso acolá no frontend. 

O grosso do serviço deste projeto foi feito em dois dias, talvez coisa de umas 20 horas de serviço. Não costumo contar o tempo das coisas, isso é quase um chute.

PHP é bem divertido de trabalhar, uma das linguagens mais direto ao ponto que existe e que permite umas gambetas muito bem boladas.

Eu sou time PHP estruturado. Só tem chamada de classe nessa aplicação porque ou não tem outro jeito ou eu descobri o jeito OO primeiro e fiz um wrapper estruturado e to com preguiça de pesquisar a versão estruturada das funções.

# Trabalhos futuros

- Um framework nos mesmos moldes talvez?

- Um backend GraphQL quase automático?
- Quem sabe hihihi
