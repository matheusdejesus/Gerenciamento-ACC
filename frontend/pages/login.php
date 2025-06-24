<?php
if (isset($_GET['logout'])) {
    session_start();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SACC UFOPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wght@0,200..900;1,200..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="bg-pattern font-montserrat min-h-screen flex flex-col">
    <nav class="bg-white shadow-lg fixed top-0 w-full z-50" style="background-color: #151B23">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="#" class="flex-shrink-0 flex items-center">
                        <span class="text-2xl font-regular" style="color: #FFFFFF">SACC</span>
                    </a>
                </div> 
            </div>
        </div>
    </nav>

    <div class="flex-grow pt-24 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" style="background-color: #0D1117">
        <div class="max-w-md w-full space-y-8 bg-white/90 p-8 rounded-xl shadow-md backdrop-blur-sm form-container" style="background-color: #F6F8FA">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                    Entrar
                </h2>
            </div>
            <div id="errorDiv" class="bg-red-50 p-4 rounded-md hidden">
                <div class="text-sm text-red-600" id="errorMessage"></div>
            </div>
            
            <form class="mt-8 space-y-6" id="loginForm">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Email</label>
                        <input type="email" name="email" id="email" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm">
                    </div>
                    <div>
                        <label for="senha" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Senha</label>
                        <input type="password" name="senha" id="senha" required
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm">
                    </div>
                </div>

                <div class="space-y-2">
                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="submitBtn">
                            Entrar
                        </button>
                    </div>
                    <div class="flex justify-center space-x-4">
                        <a href="cadastro.php" class="text-sm text-indigo-600 hover:text-indigo-500 hover:underline">
                            Criar conta
                        </a>
                        <a href="recuperar_senha.php" class="text-sm text-indigo-600 hover:text-indigo-500 hover:underline">
                            Esqueceu a senha?
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <footer class="w-full py-6" style="background-color: #151B23">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col items-center justify-center space-y-4">
                <div class="text-[#FFFFFF] text-sm">
                    <p>Sistema de Acompanhamento e Controle de ACC</p>
                </div>
                <div class="text-[#FFFFFF] text-xs">
                    <p>&copy; 2025 UFOPA</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="../assets/js/auth.js"></script>
    <script>
        // Verificar se já está logado via JWT
        if (AuthClient.isLoggedIn()) {
            const user = AuthClient.getUser();
            console.log('Usuário já logado:', user);
            
            switch (user.tipo) {
                case 'aluno':
                    window.location.href = 'home_aluno.php';
                    break;
                case 'coordenador':
                    window.location.href = 'home_coordenador.php';
                    break;
                case 'orientador':
                    window.location.href = 'home_orientador.php';
                    break;
                default:
                    console.error('Tipo de usuário desconhecido:', user.tipo);
            }
        }
        // Handler do formulário de login
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const errorDiv = document.getElementById('errorDiv');
            const errorMessage = document.getElementById('errorMessage');
            const submitBtn = document.getElementById('submitBtn');
            
            // Limpar erro anterior
            errorDiv.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Entrando...';
            
            console.log('Tentando fazer login com:', email);
            
            try {
                const result = await AuthClient.login(email, senha);
                console.log('Login bem-sucedido:', result);
                
                // Redirecionar baseado no tipo de usuário
                switch (result.usuario.tipo) {
                    case 'aluno':
                        console.log('Redirecionando para home_aluno.php');
                        window.location.href = 'home_aluno.php';
                        break;
                    case 'coordenador':
                        console.log('Redirecionando para home_coordenador.php');
                        window.location.href = 'home_coordenador.php';
                        break;
                    case 'orientador':
                        console.log('Redirecionando para home_orientador.php');
                        window.location.href = 'home_orientador.php';
                        break;
                    default:
                        console.error('Tipo de usuário não reconhecido:', result.usuario.tipo);
                        throw new Error('Tipo de usuário não reconhecido');
                }
            } catch (error) {
                console.error('Erro no login:', error);
                errorMessage.textContent = error.message;
                errorDiv.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Entrar';
            }
        });
    </script>
    <script>
        // Debug temporário
        console.log('=== DEBUG LOGIN ===');
        console.log('AuthClient existe?', typeof AuthClient !== 'undefined');
        console.log('Token atual:', AuthClient ? AuthClient.getToken() : 'N/A');
        console.log('Usuário atual:', AuthClient ? AuthClient.getUser() : 'N/A');
        console.log('Está logado?', AuthClient ? AuthClient.isLoggedIn() : 'N/A');
    </script>
</body>
</html>