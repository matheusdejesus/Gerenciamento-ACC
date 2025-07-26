<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro do Administrador - SACC UFOPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="bg-pattern font-montserrat min-h-screen flex flex-col">
    <nav class="bg-white shadow-lg fixed top-0 w-full z-50" style="background-color: #151B23">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-regular text-white">SACC</span>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="flex-grow pt-24 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" style="background-color: #0D1117">
        <div class="max-w-md w-full space-y-8 bg-white/90 p-8 rounded-xl shadow-md backdrop-blur-sm" style="background-color: #F6F8FA">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                    Cadastro Admin
                </h2>
            </div>

            <div id="error-message" class="hidden bg-red-50 p-4 rounded-md">
                <p class="text-sm text-red-600" id="error-text"></p>
            </div>

            <form id="cadastro-admin-form" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <div>
                        <label for="nome" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Nome Completo</label>
                        <input id="nome" name="nome" type="text" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-regular text-gray-700" style="color: #0969DA">E-mail</label>
                        <input id="email" name="email" type="email" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>
                    <div>
                        <label for="senha" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Senha</label>
                        <input id="senha" name="senha" type="password" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                        <p class="text-xs text-gray-500 mt-1">Mín. 8 chars, 1 maiúscula, 1 minúscula, 1 número, 1 símbolo</p>
                    </div>
                    <div>
                        <label for="conf_senha" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Confirmar Senha</label>
                        <input id="conf_senha" name="conf_senha" type="password" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>
                </div>
                <div>
                    <button type="submit" id="submit-btn"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                            style="background-color: #1A7F37">
                        <span id="btn-text">Cadastrar</span>
                        <span id="loading" class="hidden">Enviando...</span>
                    </button>
                </div>
            </form>
            <div id="mensagem" class="mt-4 text-center text-red-600"></div>
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

    <script>
        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            errorText.textContent = message;
            errorDiv.classList.remove('hidden');
        }
        function hideError() {
            document.getElementById('error-message').classList.add('hidden');
        }

        document.getElementById('cadastro-admin-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideError();
            document.getElementById('mensagem').textContent = '';

            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');
            const loading = document.getElementById('loading');

            const senha = document.getElementById('senha').value;
            const confSenha = document.getElementById('conf_senha').value;

            if (senha !== confSenha) {
                showError('As senhas não coincidem');
                return;
            }

            const senhaRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            if (!senhaRegex.test(senha)) {
                showError('Senha deve ter: mín. 8 chars, 1 maiúscula, 1 minúscula, 1 número, 1 símbolo');
                return;
            }

            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            loading.classList.remove('hidden');

            try {
                const data = {
                    nome: document.getElementById('nome').value,
                    email: document.getElementById('email').value,
                    senha: senha,
                    conf_senha: confSenha,
                    tipo: 'admin'
                };

                const response = await fetch('/Gerenciamento-ACC/backend/api/routes/cadastro.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.success) {
                    window.location.href = 'confirmacao.php?email=' + encodeURIComponent(data.email);
                } else {
                    showError(result.error || 'Erro ao cadastrar');
                }
            } catch (error) {
                showError('Erro de conexão. Tente novamente.');
            } finally {
                submitBtn.disabled = false;
                btnText.classList.remove('hidden');
                loading.classList.add('hidden');
            }
        });
    </script>
</body>
</html>