<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações da Conta - ACC Discente</title>
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
                    <span id="nomeUsuario" class="text-white mr-4 font-extralight">Carregando...</span>
                    <button onclick="AuthClient.logout()" class="text-white hover:text-gray-200">Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex-grow pt-24 flex" style="background-color: #0D1117">
        <div class="container mx-auto flex flex-col lg:flex-row p-4">
            <aside class="lg:w-1/4 p-6 rounded-lg mb-4 lg:mb-0 mr-0 lg:mr-4" style="background-color: #F6F8FA">
                <nav class="space-y-2">
                    <a href="home_aluno.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Início
                    </a>
                    <a href="configuracoes_aluno.php" class="block p-3 rounded bg-gray-200 text-[#0969DA] font-medium">
                        Configurações da Conta
                    </a>
                </nav>
            </aside>
            
            <main class="lg:w-3/4 p-6 rounded-lg" style="background-color: #F6F8FA">
                <div class="mb-8">
                    <h2 class="text-3xl font-extralight mb-2" style="color: #0969DA">
                        Configurações da Conta
                    </h2>
                    <p class="text-gray-600">Gerencie suas informações pessoais e preferências do sistema.</p>
                </div>
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button onclick="mostrarAba('dados-pessoais')" 
                                    class="aba-btn py-2 px-1 border-b-2 font-medium text-sm active"
                                    data-aba="dados-pessoais">
                                Dados Pessoais
                            </button>
                            <button onclick="mostrarAba('senha')" 
                                    class="aba-btn py-2 px-1 border-b-2 font-medium text-sm"
                                    data-aba="senha">
                                Alterar Senha
                            </button>
                        </nav>
                    </div>
                </div>
                <div id="aba-dados-pessoais" class="aba-conteudo">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-xl font-bold mb-6" style="color: #0969DA">Informações Pessoais</h3>
                        
                        <form class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #0969DA">Nome Completo</label>
                                    <input type="text" id="nomeCompleto"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" 
                                           disabled>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #0969DA">Matrícula</label>
                                    <input type="text" id="matricula"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" 
                                           disabled>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #0969DA">E-mail</label>
                                    <input type="email" id="email"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Digite seu novo email">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #0969DA">Curso</label>
                                    <input type="text" id="curso"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" 
                                           disabled>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-4">
                                <button type="button" onclick="salvarAlteracoes()" 
                                        class="px-6 py-2 text-white rounded-lg hover:opacity-90 transition duration-200"
                                        style="background-color: #1A7F37">
                                    Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="aba-senha" class="aba-conteudo hidden">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-xl font-bold mb-6" style="color: #0969DA">Alterar Senha</h3>
                        
                        <form class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #0969DA">Senha Atual</label>
                                <input type="password" id="senhaAtual" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #0969DA">Nova Senha</label>
                                <input type="password" id="novaSenha" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Mínimo de 6 caracteres</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #0969DA">Confirmar Nova Senha</label>
                                <input type="password" id="confirmarSenha" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <h4 class="font-medium text-yellow-800 mb-2">Critérios de Segurança</h4>
                                <ul class="text-sm text-yellow-700 space-y-1">
                                    <li>• Mínimo de 6 caracteres</li>
                                    <li>• Pelo menos uma letra maiúscula</li>
                                    <li>• Pelo menos um número</li>
                                </ul>
                            </div>

                            <div class="flex justify-end">
                                <button type="button" onclick="alterarSenha()" 
                                        class="px-6 py-2 text-white rounded-lg hover:opacity-90 transition duration-200"
                                        style="background-color: #1A7F37">
                                    Alterar Senha
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de Informações -->
    <div id="modalInfo" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4">
            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Informações sobre Edição de Dados</h3>
            <div class="space-y-3 text-sm text-gray-700">
                <p><strong>Dados não editáveis:</strong></p>
                <ul class="list-disc list-inside space-y-1 ml-2">
                    <li>Nome Completo</li>
                    <li>Matrícula</li>
                    <li>Curso</li>
                </ul>
                <p><strong>Email:</strong> Clique no campo do email para fazer login novamente e atualizar sua sessão.</p>
                <p class="text-xs text-gray-500 mt-4">Para alterar dados pessoais, entre em contato com a coordenação do seu curso.</p>
            </div>
            <div class="flex justify-end mt-6">
                <button onclick="fecharModalInfo()" 
                        class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Fechar
                </button>
            </div>
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
        // Verificar autenticação JWT
        if (!AuthClient.isLoggedIn()) {
            window.location.href = 'login.php';
        }
        
        const user = AuthClient.getUser();
        if (user.tipo !== 'aluno') {
            AuthClient.logout();
        }

        // Atualizar nome do usuário na interface
        if (user && user.nome) {
            document.getElementById('nomeUsuario').textContent = user.nome;
        }

        // Carregar dados do usuário nos campos
        document.addEventListener('DOMContentLoaded', function() {
            carregarDadosUsuario();
        });

        // Função para redirecionar para o login
        function redirecionarParaLogin() {
            const confirmar = confirm('🔄 Você será redirecionado para a tela de login para atualizar sua sessão. Deseja continuar?');
            if (confirmar) {
                // Fazer logout e redirecionar
                AuthClient.logout();
                window.location.href = 'login.php';
            }
        }

        function mostrarModalInfo() {
            document.getElementById('modalInfo').classList.remove('hidden');
        }

        function fecharModalInfo() {
            document.getElementById('modalInfo').classList.add('hidden');
        }

        async function carregarDadosUsuario() {
            console.log('Carregando dados do usuário:', user);
            
            try {
                console.log('Fazendo requisição para a API...');
                const response = await AuthClient.fetch('/Gerenciamento-de-ACC/backend/api/routes/configuracoes_usuarios.php');
                
                console.log('Response status:', response.status);
                
                // Verificar se a resposta é válida
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Verificar se há conteúdo na resposta
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                if (!responseText.trim()) {
                    throw new Error('Resposta vazia da API');
                }
                
                // Tentar fazer parse do JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Erro ao fazer parse do JSON:', parseError);
                    console.error('Texto da resposta:', responseText);
                    throw new Error('Resposta inválida da API: ' + responseText.substring(0, 100));
                }
                
                console.log('=== RESPOSTA DA API ===');
                console.log(result);
                
                if (result.success) {
                    const dados = result.data;
                    console.log('=== DADOS RECEBIDOS ===');
                    console.log(dados);
                    
                    // Preencher campos com dados completos do banco
                    document.getElementById('nomeCompleto').value = dados.nome || 'Nome não informado';
                    document.getElementById('matricula').value = dados.matricula || 'Matrícula não informada';
                    document.getElementById('email').value = dados.email || '';
                    document.getElementById('curso').value = dados.curso_nome || 'Curso não informado';
                } else {
                    console.error('Erro ao carregar dados:', result.error);
                    alert('❌ Erro ao carregar dados: ' + result.error);
                    // Fallback para dados do JWT (limitados)
                    document.getElementById('nomeCompleto').value = user.nome || 'Nome não informado';
                    document.getElementById('matricula').value = 'Erro: ' + result.error;
                    document.getElementById('email').value = user.email || '';
                    document.getElementById('curso').value = 'Erro: ' + result.error;
                }
            } catch (error) {
                console.error('Erro ao carregar dados do aluno:', error);
                alert('❌ Erro ao carregar dados: ' + error.message);
                // Fallback para dados do JWT (limitados)
                document.getElementById('nomeCompleto').value = user.nome || 'Nome não informado';
                document.getElementById('matricula').value = 'Erro de conexão';
                document.getElementById('email').value = user.email || '';
                document.getElementById('curso').value = 'Erro de conexão';
            }
        }

        // Função para salvar alterações
        async function salvarAlteracoes() {
            const novoEmail = document.getElementById('email').value;
            
            if (!novoEmail || !novoEmail.trim()) {
                alert('❌ Email é obrigatório');
                return;
            }

            // Validar formato do email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(novoEmail)) {
                alert('❌ Formato de email inválido');
                return;
            }

            // Confirmar a alteração
            const confirmarAlteracao = confirm('🔄 Após alterar o email, você precisará fazer login novamente. Deseja continuar?');
            if (!confirmarAlteracao) {
                return;
            }

            try {
                const response = await AuthClient.fetch('/Gerenciamento-de-ACC/backend/api/routes/configuracoes_usuarios.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: novoEmail.trim()
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Email atualizado com sucesso! Redirecionando para o login...');
                    
                    // Redirecionar imediatamente após o usuário clicar OK
                    AuthClient.logout();
                    window.location.href = 'login.php';
                    
                } else {
                    alert('❌ Erro ao atualizar email: ' + result.error);
                }
            } catch (error) {
                console.error('Erro ao salvar alterações:', error);
                alert('❌ Erro ao salvar alterações: ' + error.message);
            }
        }

        function mostrarAba(abaId) {
            // Esconder todas as abas
            document.querySelectorAll('.aba-conteudo').forEach(aba => {
                aba.classList.add('hidden');
            });
            
            document.querySelectorAll('.aba-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.style.borderColor = 'transparent';
                btn.style.color = '#6B7280';
            });
            
            // Mostrar aba selecionada
            document.getElementById(`aba-${abaId}`).classList.remove('hidden');
            
            // Adicionar classe active ao botão selecionado
            const btnAtivo = document.querySelector(`[data-aba="${abaId}"]`);
            btnAtivo.classList.add('active');
            btnAtivo.style.borderColor = '#0969DA';
            btnAtivo.style.color = '#0969DA';
        }

        function alterarSenha() {
            const senhaAtual = document.getElementById('senhaAtual').value;
            const novaSenha = document.getElementById('novaSenha').value;
            const confirmarSenha = document.getElementById('confirmarSenha').value;

            if (!senhaAtual || !novaSenha || !confirmarSenha) {
                alert('❌ Por favor, preencha todos os campos.');
                return;
            }

            if (novaSenha.length < 6) {
                alert('❌ A nova senha deve ter pelo menos 6 caracteres.');
                return;
            }

            if (novaSenha !== confirmarSenha) {
                alert('❌ A confirmação da senha não confere.');
                return;
            }

            // Implementar alteração de senha via API
            alterarSenhaAPI(senhaAtual, novaSenha);
        }

        async function alterarSenhaAPI(senhaAtual, novaSenha) {
            try {
                const response = await AuthClient.fetch('/Gerenciamento-de-ACC/backend/api/routes/configuracoes_usuarios.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        acao: 'alterar_senha',
                        senha_atual: senhaAtual,
                        nova_senha: novaSenha
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Senha alterada com sucesso!');
                    // Limpar campos
                    document.getElementById('senhaAtual').value = '';
                    document.getElementById('novaSenha').value = '';
                    document.getElementById('confirmarSenha').value = '';
                } else {
                    alert('❌ Erro ao alterar senha: ' + result.error);
                }
            } catch (error) {
                console.error('Erro ao alterar senha:', error);
                alert('❌ Erro ao alterar senha: ' + error.message);
            }
        }

        // Inicializar com aba de dados pessoais
        mostrarAba('dados-pessoais');

        // Fechar modal ao clicar fora dele
        document.getElementById('modalInfo').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalInfo();
            }
        });
    </script>

    <style>
        .aba-btn.active {
            border-color: #0969DA !important;
            color: #0969DA !important;
        }
        
        #email {
            transition: all 0.2s ease;
        }
        
        #email:focus {
            background-color: #FFFFFF !important;
            border-color: #0969DA !important;
        }
    </style>
</body>
</html>