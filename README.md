📋 Descrição do Projeto

Aplicação web em PHP e MySQL para gerenciamento de atividades de ACC para  a UFOPA, no sistema os usuários são autenticados e há um controle de acesso por perfis (Aluno, Coordenador e Orientador). Inclui cadastro com confirmação por e‑mail, login seguro com hashing de senhas, redirecionamento automático baseado no tipo de usuário e áreas dedicadas para cada perfil.

## Instalação e Setup

Siga os passos abaixo para colocar o projeto em funcionamento na sua máquina local:

1. **Baixar o repositório**  
   ```bash
   Baixe o repositório

    Iniciar o XAMPP

        Abra o painel de controle do XAMPP

        Inicie os serviços Apache e MySQL

    Criar o banco de dados

        Acesse o phpMyAdmin em:

    http://localhost/phpmyadmin/

    No menu lateral, clique em Novo para criar um novo banco de dados.

    Copie e cole o script SQL disponível em banco de dados.sql e execute-o para criar as tabelas e inserir dados iniciais.

Instalar os arquivos do projeto

    Copie a pasta páginas/ para dentro da pasta htdocs do XAMPP:

    cp -r páginas/ /caminho/para/xampp/htdocs/

Acessar a aplicação

    No navegador, abra:
    http://localhost/páginas/

🔐 Mecanismos de Segurança Implementados

1. Gerenciamento de Sessão

    Todas as páginas iniciam a sessão via session_start() e conferem se o usuário está autenticado antes de exibir conteúdo protegido (por exemplo, em home_aluno.php) .

O logout limpa e destrói a sessão completamente, prevenindo reuso de credenciais antigas.

2. Proteção contra Injeção de SQL

    Todas as operações de leitura e escrita no banco usam prepared statements com bind_param(), evitando injeção de SQL (em cadastro.php, login.php e confirmacao.php) .

3. Armazenamento Seguro de Senhas

    As senhas são sempre hasheadas com password_hash(..., PASSWORD_DEFAULT) antes de ir para o banco de dados .

No login, utiliza-se password_verify() para comparação segura de hashes.

4. Validação e Sanitização de Entrada

    Trim e validação de campos vindos de $_POST garantem formato e presença de dados mínimos (senhas fortes via regex, comparação de confirmação de senha, campos obrigatórios por tipo de usuário) .

Todos os valores exibidos ao usuário (nomes, erros, campos selecionados) passam por htmlspecialchars(), evitando XSS em saídas (por exemplo, em mensagens de erro e <?= htmlspecialchars(...) ?>).

5. Confirmação de E-mail com Token Seguro

    Geração de código de 6 dígitos via random_int(), armazenado com timestamp de expiração no banco (EmailConfirm) .

Somente após confirmação do token e validade de tempo é que o usuário de fato assume sessão de “usuário ativo”.

6. Integridade Referencial no Banco de Dados

    Usamos InnoDB com foreign keys e ON DELETE CASCADE/RESTRICT, garantindo que relacionamentos (Usuário ↔ Aluno/Coordenador/Orientador/EmailConfirm) permaneçam consistentes .
