<?php
session_start();
if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'coordenador') {
    header('Location: login.php');
    exit;
}
// Dados simulados do coordenador
$coordenador = [
    'id' => 1,
    'nome' => $_SESSION['usuario']['nome'] ?? 'Nome não informado',
    'email' => $_SESSION['usuario']['email'] ?? 'email@exemplo.com',
    'senha' => $_SESSION['usuario']['senha'] ?? '',
    'siape' => $_SESSION['usuario']['siape'] ?? 'SIAPE não informado',
    'curso_coordenado' => $_SESSION['usuario']['curso_coordenado'] ?? 'Curso não informado',
    'departamento' => $_SESSION['usuario']['departamento'] ?? 'Departamento não informado',
];

// Processar formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tipo_form'])) {
        switch ($_POST['tipo_form']) {
            case 'dados_pessoais':
                $success_message = "Dados pessoais atualizados com sucesso!";
                break;
            case 'senha':
                $success_message = "Senha alterada com sucesso!";
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações da Conta - SACC UFOPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wght@0,200..900;1,200..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="bg-pattern font-montserrat min-h-screen flex flex-col">
    <nav class="bg-white shadow-lg fixed top-0 w-full z-50" style="background-color: #151B23">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-regular text-white">SACC</span>
                </div>
                <div class="flex items-center">
                    <span class="text-white mr-4 font-extralight">Olá, <?= htmlspecialchars($coordenador['nome']) ?></span>
                    <a href="login.php?logout=1" class="text-white hover:text-gray-200">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex-grow pt-24" style="background-color: #0D1117">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-6">
                <aside class="lg:w-1/4 flex justify-center pr-4">
                    <nav class="space-y-2 w-full max-w-xs">
                        <a href="home_coordenador.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200 text-center">
                            Dashboard
                        </a>
                        <a href="#" class="block p-3 rounded text-white font-medium text-center" style="background-color: #0969DA">
                            Configurações da Conta
                        </a>
                    </nav>
                </aside>
                <main class="lg:w-3/4">
                    <div class="p-6 rounded-lg" style="background-color: #F6F8FA">
                        <div class="mb-8">
                            <h2 class="text-3xl font-extralight mb-2" style="color: #0969DA">
                                Configurações da Conta
                            </h2>
                            <p class="text-gray-600">Gerencie suas informações pessoais e configurações de segurança.</p>
                        </div>

                        <?php if (isset($success_message)): ?>
                            <div class="mb-6 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <?= $success_message ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="mb-6">
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-8">
                                    <button onclick="showTab('perfil')" id="tab-perfil" class="tab-button border-b-2 border-blue-500 py-2 px-1 text-sm font-medium" style="color: #0969DA">
                                        Perfil
                                    </button>
                                    <button onclick="showTab('seguranca')" id="tab-seguranca" class="tab-button border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                        Segurança
                                    </button>
                                </nav>
                            </div>
                        </div>
                        <div id="content-perfil" class="tab-content">
                            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                                <h3 class="text-xl font-semibold mb-4" style="color: #0969DA">Informações Pessoais</h3>
                                <form method="POST" class="space-y-4">
                                    <input type="hidden" name="tipo_form" value="dados_pessoais">
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-2" style="color: #0969DA">Nome Completo</label>
                                            <input type="text" name="nome" value="<?= htmlspecialchars($coordenador['nome']) ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-2" style="color: #0969DA">E-mail Institucional</label>
                                            <input type="email" name="email" value="<?= htmlspecialchars($coordenador['email']) ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-2" style="color: #0969DA">SIAPE</label>
                                            <input type="text" name="siape" value="<?= htmlspecialchars($coordenador['siape']) ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-2" style="color: #0969DA">Departamento</label>
                                            <input type="text" name="departamento" value="<?= htmlspecialchars($coordenador['departamento']) ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium mb-2" style="color: #0969DA">Curso Coordenado</label>
                                            <input type="text" name="curso_coordenado" value="<?= htmlspecialchars($coordenador['curso_coordenado']) ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-6 py-2 text-white rounded-lg hover:opacity-90 transition duration-200" 
                                                style="background-color: #0969DA">
                                            Salvar Alterações
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div id="content-seguranca" class="tab-content hidden">
                            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                                <h3 class="text-xl font-semibold mb-4" style="color: #0969DA">Alterar Senha</h3>
                                <form method="POST" class="space-y-4">
                                    <input type="hidden" name="tipo_form" value="senha">
                                    
                                    <div>
                                        <label class="block text-sm font-medium mb-2" style="color: #0969DA">Senha Atual</label>
                                        <input type="password" name="senha_atual" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-2" style="color: #0969DA">Nova Senha</label>
                                        <input type="password" name="nova_senha" required minlength="8"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="text-xs text-gray-500 mt-1">Mínimo de 8 caracteres</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-2" style="color: #0969DA">Confirmar Nova Senha</label>
                                        <input type="password" name="confirmar_senha" required minlength="8"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-6 py-2 text-white rounded-lg hover:opacity-90 transition duration-200" 
                                                style="background-color: #0969DA">
                                            Alterar Senha
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <div class="pb-8"></div>
    </div>

    <script>
        function showTab(tabName) {
            // Esconder todas as abas
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            // Remover estilo ativo de todos os botões
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('border-blue-500');
                button.classList.add('border-transparent', 'text-gray-500');
                button.style.color = '#6B7280';
            });

            // Mostrar aba selecionada
            document.getElementById(`content-${tabName}`).classList.remove('hidden');

            // Ativar botão selecionado
            const activeButton = document.getElementById(`tab-${tabName}`);
            activeButton.classList.remove('border-transparent', 'text-gray-500');
            activeButton.classList.add('border-blue-500');
            activeButton.style.color = '#0969DA';
        }

        // Validação de senha
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form[method="POST"]');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const formType = this.querySelector('input[name="tipo_form"]');
                    
                    if (formType && formType.value === 'senha') {
                        const novaSenha = this.querySelector('input[name="nova_senha"]').value;
                        const confirmarSenha = this.querySelector('input[name="confirmar_senha"]').value;
                        
                        if (novaSenha !== confirmarSenha) {
                            e.preventDefault();
                            alert('As senhas não coincidem. Por favor, verifique e tente novamente.');
                            return;
                        }
                        
                        if (novaSenha.length < 8) {
                            e.preventDefault();
                            alert('A nova senha deve ter pelo menos 8 caracteres.');
                            return;
                        }
                    }
                });
            });
        });

        // Inicializar primeira aba
        showTab('perfil');
    </script>
</body>
</html>