<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações da Conta - SACC UFOPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wght@0,200..900;1,200..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        .bg-pattern {
            background-color: #0D1117;
        }
    </style>
</head>
<body class="bg-pattern font-montserrat min-h-screen flex flex-col">
    <nav class="bg-white shadow-lg fixed top-0 w-full z-50" style="background-color: #151B23">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-regular text-white">SACC</span>
                </div>
                <div class="flex items-center">
                    <a href="home_coordenador.php" class="text-white hover:text-gray-200 mr-4">Voltar</a>
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
                    <a href="home_coordenador.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Dashboard
                    </a>
                    <a href="configuracoes_coordenador.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
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
                        
                        <form id="formDadosPessoais" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #0969DA">Nome Completo</label>
                                <input type="text" id="nomeCompleto" readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                                <p class="text-xs text-gray-500 mt-1">Campo não editável</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #0969DA">SIAPE</label>
                                <input type="text" id="siape" readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                                <p class="text-xs text-gray-500 mt-1">Campo não editável</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #0969DA">E-mail</label>
                                <input type="email" id="email" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Este campo pode ser editado</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #0969DA">Curso</label>
                                <input type="text" id="curso" readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                                <p class="text-xs text-gray-500 mt-1">Campo não editável</p>
                            </div>

                            <div class="flex justify-end">
                                <button type="button" onclick="salvarDadosPessoais()" 
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
                        
                        <form id="formSenha" class="space-y-6">
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
    <script src="../assets/js/auth.js"></script>
    <script>
        // Verificar autenticação JWT
        if (!AuthClient.isLoggedIn()) {
            window.location.href = 'login.php';
        }
        
        const user = AuthClient.getUser();
        if (user.tipo !== 'coordenador') {
            AuthClient.logout();
        }
        
        // Atualizar nome do usuário na interface
        if (user && user.nome) {
            document.getElementById('nomeUsuario').textContent = user.nome;
        }

        // Carregar dados do usuário
        document.addEventListener('DOMContentLoaded', function() {
            carregarDadosUsuario();
        });

        async function carregarDadosUsuario() {
            try {
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/configuracoes_usuarios.php');
                const data = await response.json();
                
                console.log('Dados recebidos:', data);
                
                if (data.success) {
                    const usuario = data.data;
                    document.getElementById('nomeCompleto').value = usuario.nome || '';
                    document.getElementById('siape').value = usuario.siape || '';
                    document.getElementById('email').value = usuario.email || '';
                    document.getElementById('curso').value = usuario.curso_nome || '';
                } else {
                    alert('Erro ao carregar dados do usuário: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro ao carregar dados:', error);
                alert('Erro ao carregar dados do usuário');
            }
        }

        function mostrarAba(abaId) {
            document.querySelectorAll('.aba-conteudo').forEach(aba => {
                aba.classList.add('hidden');
            });
            
            // Remover classe active de todos os botões
            document.querySelectorAll('.aba-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.style.color = '#6B7280';
                btn.style.borderColor = 'transparent';
            });
            
            // Mostrar aba selecionada
            document.getElementById(`aba-${abaId}`).classList.remove('hidden');
            
            // Ativar botão correspondente
            const btnAtivo = document.querySelector(`[data-aba="${abaId}"]`);
            btnAtivo.classList.add('active');
            btnAtivo.style.color = '#0969DA';
            btnAtivo.style.borderColor = '#0969DA';
        }

        async function salvarDadosPessoais() {
            try {
                const email = document.getElementById('email').value;
                
                if (!email) {
                    alert('Por favor, preencha o e-mail.');
                    return;
                }

                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/configuracoes_usuarios.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('Dados salvos com sucesso!');
                } else {
                    alert('Erro ao salvar dados: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro ao salvar dados:', error);
                alert('Erro ao salvar dados');
            }
        }

        async function alterarSenha() {
            try {
                const senhaAtual = document.getElementById('senhaAtual').value;
                const novaSenha = document.getElementById('novaSenha').value;
                const confirmarSenha = document.getElementById('confirmarSenha').value;
                
                if (!senhaAtual || !novaSenha || !confirmarSenha) {
                    alert('Por favor, preencha todos os campos.');
                    return;
                }

                if (novaSenha !== confirmarSenha) {
                    alert('As senhas não coincidem.');
                    return;
                }

                if (novaSenha.length < 6) {
                    alert('A nova senha deve ter pelo menos 6 caracteres.');
                    return;
                }

                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/configuracoes_usuarios.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        acao: 'alterar_senha',
                        senha_atual: senhaAtual,
                        nova_senha: novaSenha
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('Senha alterada com sucesso!');
                    // Limpar campos
                    document.getElementById('senhaAtual').value = '';
                    document.getElementById('novaSenha').value = '';
                    document.getElementById('confirmarSenha').value = '';
                } else {
                    alert('Erro ao alterar senha: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro ao alterar senha:', error);
                alert('Erro ao alterar senha');
            }
        }

        // Inicializar primeira aba
        mostrarAba('dados-pessoais');
    </script>
</body>
</html>