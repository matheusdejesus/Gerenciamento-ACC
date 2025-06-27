<?php
session_start();
$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha - SACC UFOPA</title>
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
            <div id="success-message" class="hidden">
                <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                    Senha Alterada!
                </h2>
                <div class="bg-green-50 p-4 rounded-md">
                    <p class="text-sm text-green-600">Senha alterada com sucesso!</p>
                </div>
                <div class="mt-6">
                    <a href="login.php" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                       style="background-color: #0969DA">
                        Fazer Login
                    </a>
                </div>
            </div>
            <div id="invalid-token" class="hidden">
                <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                    Link Inválido
                </h2>
                <div class="bg-red-50 p-4 rounded-md">
                    <p class="text-sm text-red-600" id="invalid-token-text">Token inválido ou expirado</p>
                </div>
                <div class="mt-6">
                    <a href="recuperar_senha.php" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                       style="background-color: #0969DA">
                        Solicitar Novo Link
                    </a>
                </div>
            </div>
            <div id="form-container">
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                        Nova Senha
                    </h2>
                </div>
                <div id="password-criteria" class="mt-4 p-3 bg-gray-50 rounded-md">
                    <p class="text-sm font-medium text-gray-700 mb-2">Critérios da senha:</p>
                    <ul class="space-y-1 text-xs">
                        <li id="criteria-length" class="flex items-center">
                            <span class="w-4 h-4 mr-2">❌</span>
                            <span>Mínimo 6 caracteres</span>
                        </li>
                        <li id="criteria-uppercase" class="flex items-center">
                            <span class="w-4 h-4 mr-2">❌</span>
                            <span>Uma letra maiúscula</span>
                        </li>
                        <li id="criteria-number" class="flex items-center">
                            <span class="w-4 h-4 mr-2">❌</span>
                            <span>Um número</span>
                        </li>
                        <li id="criteria-symbol" class="flex items-center">
                            <span class="w-4 h-4 mr-2">❌</span>
                            <span>Um símbolo (!@#$%^&*)</span>
                        </li>
                    </ul>
                </div>

                <form id="change-password-form" class="mt-8 space-y-6">
                    <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="nova_senha" class="block text-sm font-regular" style="color: #0969DA">Nova Senha:</label>
                            <input type="password" name="nova_senha" id="nova_senha" required
                                   class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                        </div>
                        
                        <div>
                            <label for="confirmar_senha" class="block text-sm font-regular" style="color: #0969DA">Confirmar Senha:</label>
                            <input type="password" name="confirmar_senha" id="confirmar_senha" required
                                   class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                            <div id="password-match" class="mt-1 text-sm hidden">
                                <span id="match-message"></span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div>
                            <button type="submit" id="submit-btn" disabled
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white opacity-50 cursor-not-allowed" 
                                    style="background-color: #1A7F37">
                                <span id="btn-text">Alterar Senha</span>
                                <span id="loading" class="hidden">Alterando...</span>
                            </button>
                        </div>
                        
                        <div>
                            <a href="login.php" 
                               class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                               style="background-color: #0969DA">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    const token = document.getElementById('token').value;
    if (token) {
        console.log('Token encontrado:', token);
        validateToken();
    } else {
        console.log('Token não fornecido');
        showInvalidToken('Token não fornecido');
    }

    async function validateToken() {
        console.log('Validando token...');
        try {
            const url = `/Gerenciamento-ACC/backend/api/routes/alterar_senha.php?token=${token}`;
            console.log('URL:', url);
            
            const response = await fetch(url);
            console.log('Status:', response.status);
            
            const data = await response.json();
            console.log('Resposta:', data);
            
            if (!data.success) {
                showInvalidToken(data.error);
            } else {
                console.log('Token válido!');
            }
        } catch (error) {
            console.error('Erro:', error);
            showInvalidToken('Erro ao validar token');
        }
    }

    function showInvalidToken(message) {
        document.getElementById('invalid-token-text').textContent = message;
        document.getElementById('form-container').classList.add('hidden');
        document.getElementById('invalid-token').classList.remove('hidden');
    }
    // Função para validar critérios da senha
    function validatePasswordCriteria(password) {
        const criteria = {
            length: password.length >= 6,
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            symbol: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\?]/.test(password)
        };

        // Atualizar indicadores visuais
        updateCriteriaIndicator('criteria-length', criteria.length);
        updateCriteriaIndicator('criteria-uppercase', criteria.uppercase);
        updateCriteriaIndicator('criteria-number', criteria.number);
        updateCriteriaIndicator('criteria-symbol', criteria.symbol);

        return Object.values(criteria).every(Boolean);
    }

    function updateCriteriaIndicator(elementId, isValid) {
        const element = document.getElementById(elementId);
        const icon = element.querySelector('span');
        
        if (isValid) {
            icon.textContent = '✅';
            element.classList.add('text-green-600');
            element.classList.remove('text-red-600');
        } else {
            icon.textContent = '❌';
            element.classList.add('text-red-600');
            element.classList.remove('text-green-600');
        }
    }

    function checkPasswordMatch() {
        const password = document.getElementById('nova_senha').value;
        const confirmPassword = document.getElementById('confirmar_senha').value;
        const matchDiv = document.getElementById('password-match');
        const matchMessage = document.getElementById('match-message');

        if (confirmPassword.length > 0) {
            matchDiv.classList.remove('hidden');
            if (password === confirmPassword) {
                matchMessage.textContent = '✅ Senhas coincidem';
                matchMessage.className = 'text-green-600';
                return true;
            } else {
                matchMessage.textContent = '❌ Senhas não coincidem';
                matchMessage.className = 'text-red-600';
                return false;
            }
        } else {
            matchDiv.classList.add('hidden');
            return false;
        }
    }

    function updateSubmitButton() {
        const password = document.getElementById('nova_senha').value;
        const confirmPassword = document.getElementById('confirmar_senha').value;
        const submitBtn = document.getElementById('submit-btn');
        
        const isPasswordValid = validatePasswordCriteria(password);
        const doPasswordsMatch = password === confirmPassword && confirmPassword.length > 0;
        
        if (isPasswordValid && doPasswordsMatch) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Event listeners para validação em tempo real
    document.getElementById('nova_senha').addEventListener('input', function() {
        updateSubmitButton();
    });

    document.getElementById('confirmar_senha').addEventListener('input', function() {
        checkPasswordMatch();
        updateSubmitButton();
    });

    document.getElementById('change-password-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const nova_senha = document.getElementById('nova_senha').value;
        const confirmar_senha = document.getElementById('confirmar_senha').value;
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const loading = document.getElementById('loading');
        
        // Validação final antes do envio
        if (!validatePasswordCriteria(nova_senha)) {
            alert('A senha deve ter:\n• Mínimo 6 caracteres\n• Uma letra maiúscula\n• Um número\n• Um símbolo (!@#$%^&*)');
            return;
        }

        if (nova_senha !== confirmar_senha) {
            alert('As senhas não coincidem');
            return;
        }

        submitBtn.disabled = true;
        btnText.classList.add('hidden');
        loading.classList.remove('hidden');
        
        try {
            const response = await fetch('/Gerenciamento-ACC/backend/api/routes/alterar_senha.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    token: token,
                    nova_senha: nova_senha,
                    confirmar_senha: confirmar_senha
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('form-container').classList.add('hidden');
                document.getElementById('success-message').classList.remove('hidden');
            } else {
                alert(data.error || 'Erro ao alterar senha');
            }
            
        } catch (error) {
            alert('Erro de conexão. Tente novamente.');
        } finally {

            submitBtn.disabled = false;
            btnText.classList.remove('hidden');
            loading.classList.add('hidden');
        }
    });
    </script>
</body>
</html>