## Link do vídeo explicando o trabalho

https://drive.google.com/file/d/12LAJtGZ6I4jrJP66aEGO6F_iU6HxDuM2/view?usp=sharing

## 📋 Descrição do Projeto

O projeto Gerenciamento-ACC é um sistema web desenvolvido para gerenciar Atividades Complementares de Curso (ACC) em uma instituição acadêmica. Ele possui funcionalidades voltadas para diferentes tipos de usuários, como alunos, coordenadores e orientadores, permitindo o acompanhamento, avaliação e controle de atividades complementares.

## ⭐ Principais Funcionalidades

### Gestão de Usuários

**Cadastro e Login**
   - Os usuários podem se registrar e fazer login no sistema.
   - Autenticação baseada em JWT (JSON Web Token) para proteger as rotas.

**Recuperação de Senha**
   - Envio de links de recuperação de senha por e-mail.
   - Validação de tokens para redefinição de senha.

**Alteração de Dados Pessoais**
   - Usuários podem atualizar informações como e-mail e senha.

### Gestão de Atividades Complementares

**Cadastro de Atividades**
   - Alunos podem cadastrar atividades complementares, anexando documentos comprobatórios.
   - Validação de arquivos

**Avaliação de Atividades**
   - Orientadores e coordenadores podem aprovar ou rejeitar atividades.
   - Possibilidade de adicionar observações durante a avaliação.

**Certificados**
   - Geração e envio de certificados para atividades aprovadas.
   - Histórico de certificados processados e pendentes.
   - Envio de certificados avulsos independente de atividades cadastradas.

### Painéis Personalizados

**Painel do Aluno**
   - Visualização de atividades cadastradas e status de avaliação.
   - Configurações de perfil.

**Painel do Coordenador**
   - Gerenciamento de certificados pendentes e processados.

**Painel do Orientador**
   - Avaliação de atividades submetidas pelos alunos.

**Painel Administrativo**
   - Gerenciamento completo do sistema com privilégios administrativos.
   - Controle de usuários, atividades e configurações gerais do sistema.
   - Acesso a auditoria detalhada.

### Auditoria e Logs
   - Registro de ações importantes, como alterações de senha, login/logout e avaliações de atividades.
   - Manutenção de logs detalhados no banco de dados para rastreamento de alterações e acessos no sistema.

## 🔐 Mecanismos de Segurança

### Autenticação e Autorização:
   - Uso de JWT para autenticação segura.
   Controle de acesso baseado no tipo de usuário (aluno, orientador, coordenador).
   - Redirecionamento automático para login em caso de sessão expirada.

### Validação de Dados:
   - Validação de entradas no backend para evitar ataques como SQL Injection.
   - Regras de validação de senha (mínimo de 6 caracteres, letra maiúscula, número, símbolo).

### Proteção de API:
   - Uso de API Keys para proteger endpoints críticos.
   - Middleware para validação de tokens e   permissões.

### Criptografia:
   - Senhas armazenadas com hash.
   - Tokens de recuperação de senha com validade limitada.

### Segurança de Arquivos:
   - Limitação de tamanho e tipo de arquivos enviados.

### Auditoria:
   - Registro das ações dos usuários no banco de dados, incluindo alterações de senha e acessos a páginas.
   - Logs detalhados para monitoramento de atividades suspeitas.

## ⚙️ Instalação e Setup

Siga os passos abaixo para colocar o projeto em funcionamento na sua máquina local:

1. **Baixar o repositório**  
   Baixe o repositório normalmente.

2. **Iniciar o XAMPP**

   Abra o painel de controle do XAMPP e inicie os serviços Apache e MySQL.

3. **Criar o banco de dados**

   Acesse o phpMyAdmin em:
   http://localhost/phpmyadmin/

   No menu lateral, clique em Novo para criar um novo banco de dados.

   Copie e cole o script SQL disponível em `banco de dados.sql` e execute-o para criar as tabelas e inserir dados iniciais.

4. **Instalar os arquivos do projeto**

   Copie a pasta `Gerenciamento-ACC` para dentro da pasta `htdocs` do XAMPP:

   ```bash
   cp -r Gerenciamento-ACC/ /caminho/para/xampp/htdocs/
   ```

5. **Acessar a aplicação**

   No navegador, abra:
   http://localhost/Gerenciamento-ACC/

## 📝 Como Fazer Cadastro no Sistema

Antes de acessar qualquer página do sistema, é necessário criar uma conta no sistema:

### Cadastro de Usuários (Alunos, Orientadores, Coordenadores)
1. Acesse: `http://localhost/Gerenciamento-ACC/frontend/pages/cadastro.php`
2. Preencha todos os campos obrigatórios:
   - Nome completo
   - E-mail
   - Senha (mínimo 6 caracteres, com letra maiúscula, número e símbolo)
   - Confirmação de senha
   - Tipo de usuário (Aluno, Orientador ou Coordenador)
3. Clique em "Cadastrar"
4. Aguarde a confirmação do cadastro

### Cadastro de Administradores
1. Acesse: `http://localhost/Gerenciamento-ACC/frontend/pages/cadastro_admin.php`
2. Preencha os dados necessários
3. Este cadastro requer privilégios especiais.

## 🚀 Como Acessar as Páginas do Sistema

Após o cadastro e configuração do sistema, você pode acessar as diferentes páginas conforme seu tipo de usuário:

### Painel Administrativo
1. Acesse: `http://localhost/Gerenciamento-ACC/frontend/pages/login.php`
2. Faça login com credenciais de administrador
3. Será redirecionado para: `http://localhost/Gerenciamento-ACC/frontend/pages/home_admin.php`
4. No painel administrativo você terá acesso completo ao sistema

### Página do Aluno
1. Acesse: `http://localhost/Gerenciamento-ACC/frontend/pages/login.php`
2. Faça login com credenciais de aluno
3. Será redirecionado para: `http://localhost/Gerenciamento-ACC/frontend/pages/home_aluno.php`
4. Na página do aluno você pode cadastrar atividades e acompanhar avaliações

### Página do Orientador
1. Acesse: `http://localhost/Gerenciamento-ACC/frontend/pages/login.php`
2. Faça login com credenciais de orientador
3. Será redirecionado para: `http://localhost/Gerenciamento-ACC/frontend/pages/home_orientador.php`
4. Na página do orientador você pode avaliar atividades submetidas pelos alunos

### Página do Coordenador
1. Acesse: `http://localhost/Gerenciamento-ACC/frontend/pages/login.php`
2. Faça login com credenciais de coordenador
3. Será redirecionado para: `http://localhost/Gerenciamento-ACC/frontend/pages/home_coordenador.php`
4. Na página do coordenador você pode gerenciar certificados

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP
- **Banco de Dados:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Autenticação:** JWT (JSON Web Token)
- **Servidor Local:** XAMPP (Apache + MySQL)
