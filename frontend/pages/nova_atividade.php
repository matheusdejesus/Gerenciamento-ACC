<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Atividade - ACC Discente</title>
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
                    <a href="home_aluno.php" class="text-white hover:text-gray-200 mr-4">Voltar</a>
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
                    <a href="nova_atividade.php" class="block p-3 rounded bg-gray-200 text-[#0969DA]">
                        Nova Atividade
                    </a>
                    <a href="enviar_comprovante.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Enviar Comprovante
                    </a>
                    <a href="configuracoes_aluno.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Configurações da Conta
                    </a>
                </nav>
            </aside>
            <main class="lg:w-3/4 p-6 rounded-lg" style="background-color: #F6F8FA">
                <div class="mb-8">
                    <h2 class="text-3xl font-extralight mb-2" style="color: #0969DA">
                        Escolher Nova Atividade
                    </h2>
                    <p class="text-gray-600">Selecione uma atividade para se cadastrar</p>
                    <div id="alertaAtividades" class="mt-4 hidden p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-yellow-800">⚠️ Não foi possível carregar as atividades. Verifique a conexão com o banco de dados.</p>
                    </div>
                </div>
                <form id="formFiltro" class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                </form>
                <div id="atividadesContainer"></div>
                
                <div class="mt-8 p-6 rounded-lg" style="background-color: #E6F3FF; border-left: 4px solid #0969DA">
                    <h4 class="font-bold mb-2" style="color: #0969DA">Informações Importantes</h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>• Você pode se cadastrar em múltiplas atividades</li>
                        <li>• Após selecionar, você precisará enviar comprovantes</li>
                        <li>• As atividades são organizadas por categoria</li>
                        <li>• Verifique os requisitos antes de se inscrever</li>
                    </ul>
                </div>
                
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="cadastrar_atividade.php?id=1" class="p-4 rounded-lg text-center transition duration-200 text-white" style="background-color: #1A7F37">
                        <h4 class="font-bold mb-2">Nova Atividade</h4>
                        <p class="text-sm">Cadastrar em uma nova atividade</p>
                    </a>
                    <a href="enviar_comprovante.php" class="p-4 rounded-lg text-center transition duration-200" style="background-color: #0969DA">
                        <h4 class="font-bold mb-2 text-white">Enviar Comprovante</h4>
                        <p class="text-sm text-white">Fazer upload de comprovantes</p>
                    </a>
                </div>
            </main>
        </div>
    </div>
    <div id="modalDetalhes" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
            <div class="p-4" style="background-color: #151B23">
                <h3 class="text-xl font-bold text-white">Detalhes da Atividade</h3>
            </div>
            <div class="p-6">
                <div id="conteudoDetalhes">
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button onclick="fecharModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Fechar
                    </button>
                    <button id="btnSelecionarModal" class="px-4 py-2 text-white rounded-lg" style="background-color: #1A7F37">
                        Selecionar Atividade
                    </button>
                </div>
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
        if (user && user.nome) {
            document.getElementById('nomeUsuario').textContent = user.nome;
        }

        // Carregar atividades via JWT
        let todasAtividades = [];

        async function carregarAtividades() {
            try {
                const response = await AuthClient.fetch('/Gerenciamento-de-ACC/backend/api/routes/listar_atividades.php');
                const data = await response.json();
                if (data.success) {
                    todasAtividades = data.data || [];
                    renderizarAtividades();
                    document.getElementById('alertaAtividades').classList.add('hidden');
                } else {
                    document.getElementById('alertaAtividades').classList.remove('hidden');
                }
            } catch (e) {
                document.getElementById('alertaAtividades').classList.remove('hidden');
            }
        }

        function renderizarAtividades() {
            const container = document.getElementById('atividadesContainer');
            if (!todasAtividades.length) {
                container.innerHTML = `<div class="text-center py-12">
                    <p class="text-gray-500 text-lg">Nenhuma atividade encontrada.</p>
                </div>`;
                return;
            }
            container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ${todasAtividades.map(atividade => `
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-4" style="background-color: #151B23">
                            <h3 class="text-lg font-bold text-white">${atividade.nome}</h3>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mt-2">
                                ${atividade.categoria}
                            </span>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 text-sm mb-4">${atividade.descricao}</p>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #0969DA">Tipo:</span>
                                    <span class="text-gray-600">${atividade.tipo}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #0969DA">Horas Máximas:</span>
                                    <span class="text-gray-600">${atividade.horas_max}h</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="verDetalhes(${atividade.id})"
                                        class="flex-1 px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200"
                                        style="color: #0969DA">
                                    Ver Detalhes
                                </button>
                                <a href="cadastrar_atividade.php?id=${atividade.id}"
                                   class="flex-1 px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition duration-200 text-center"
                                   style="background-color: #1A7F37">
                                    Selecionar
                                </a>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>`;
        }

        // Modal de detalhes
        function verDetalhes(id) {
            const atividade = todasAtividades.find(a => a.id === id);
            if (!atividade) return;
            const detalhes = `
                <h4 class="text-xl font-bold mb-4" style="color: #0969DA">${atividade.nome}</h4>
                <div class="space-y-3">
                    <div>
                        <span class="font-medium" style="color: #0969DA">Categoria:</span>
                        <span class="ml-2">${atividade.categoria}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #0969DA">Tipo:</span>
                        <span class="ml-2">${atividade.tipo}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #0969DA">Horas Máximas:</span>
                        <span class="ml-2">${atividade.horas_max} horas</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #0969DA">Descrição:</span>
                        <p class="mt-1 text-gray-600">${atividade.descricao}</p>
                    </div>
                </div>
            `;
            document.getElementById('conteudoDetalhes').innerHTML = detalhes;
            document.getElementById('btnSelecionarModal').onclick = () => selecionarAtividade(id);
            document.getElementById('modalDetalhes').classList.remove('hidden');
            document.getElementById('modalDetalhes').classList.add('flex');
        }
        function fecharModal() {
            document.getElementById('modalDetalhes').classList.add('hidden');
            document.getElementById('modalDetalhes').classList.remove('flex');
        }
        function selecionarAtividade(id) {
            window.location.href = `cadastrar_atividade.php?id=${id}`;
            fecharModal();
        }

        carregarAtividades();
    </script>
</body>
</html>