📋 Descrição do Projeto

Este projeto implementa um sistema de cadastro, confirmação de e-mail e login para três tipos de usuário (aluno, coordenador e orientador), com controle de acesso a páginas específicas e gestão de sessão.

Como rodar?

1 - baixe o repositório
2 - abra o xampp e bote para rodar o apache e o mysql
3 - abra o http://localhost/phpmyadmin/
    3.1 - copiei e cole o código sql no phpmyadmin para criar o banco
4 - copie e cole o pasta "páginas" no htdocs do xampp
5 - acesse http://localhost/páginas/

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
