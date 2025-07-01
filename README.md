📋 Descrição do Projeto

Aplicação web em PHP e MySQL para gerenciamento de atividades de ACC para a UFOPA. O sistema possui autenticação segura baseada em tokens JWT, controle de acesso por perfis (Aluno, Coordenador e Orientador), cadastro com confirmação por e‑mail, login seguro com hashing de senhas, redirecionamento automático baseado no tipo de usuário e áreas dedicadas para cada perfil.

## Instalação e Setup

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
   http://localhost/Gerenciamento-de-ACC/

---

🔐 **Mecanismos de Segurança Implementados**

### 1. Autenticação e Autorização via JWT

- A autenticação de usuários é realizada via API utilizando tokens JWT (JSON Web Token).
- O backend (PHP) gera e valida os tokens JWT para as rotas protegidas.
- O frontend consome a API, armazena o token JWT de forma segura (em localStorage) e o envia em cada requisição autenticada.
- O middleware da API valida o JWT antes de permitir acesso a recursos protegidos.


## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP
- **Banco de Dados:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Autenticação:** JWT (JSON Web Token)
- **Servidor Local:** XAMPP (Apache + MySQL)