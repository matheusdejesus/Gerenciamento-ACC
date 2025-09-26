<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividades de Estágio - Sistema ACC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../assets/js/auth.js"></script>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-gray-50">
    <!-- Navegação -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold" style="color: #0969DA">Sistema ACC</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="nova_atividade.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        ← Voltar às Categorias
                    </a>
                    <button onclick="AuthClient.logout()" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Sair
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <!-- Cabeçalho -->
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <div class="text-4xl mr-4">💼</div>
                    <div>
                        <h2 class="text-3xl font-bold" style="color: #F59E0B">Atividades de Estágio</h2>
                        <p class="text-gray-600 mt-2">Selecione uma atividade para enviar seu certificado</p>
                    </div>
                </div>
            </div>

            <!-- Alerta de erro -->
            <div id="alertaAtividades" class="hidden bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Erro ao carregar atividades</h3>
                        <p class="text-sm text-red-700 mt-1">Não foi possível carregar as atividades de estágio. Tente novamente.</p>
                    </div>
                </div>
            </div>

            <!-- Container das atividades -->
            <div id="atividadesContainer" class="mb-8">
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-600 mx-auto"></div>
                    <p class="text-gray-500 mt-4">Carregando atividades...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div id="modalDetalhes" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Detalhes da Atividade</h3>
                    <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="conteudoDetalhes" class="mb-6">
                    <!-- Conteúdo será inserido dinamicamente -->
                </div>
                <div class="flex justify-end space-x-3">
                    <button onclick="fecharModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                        Fechar
                    </button>
                    <button id="btnSelecionarModal" class="px-4 py-2 text-white rounded-lg hover:opacity-90 transition duration-200" style="background-color: #F59E0B">
                        Selecionar Atividade
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Seleção/Cadastro -->
    <div id="modalSelecao" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold" style="color: #F59E0B">Cadastrar Atividade de Estágio</h3>
                <button onclick="fecharModalSelecao()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="formCadastro" class="space-y-4">
                <input type="hidden" id="atividadeId">
                
                <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                    <h4 class="font-semibold text-yellow-800">Atividade Selecionada:</h4>
                    <p id="nomeAtividade" class="text-yellow-700"></p>
                    <p class="text-sm text-yellow-600">Horas máximas: <span id="horasMaximas"></span></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="empresa" class="block text-sm font-medium text-gray-700 mb-1">
                            Empresa *
                        </label>
                        <input type="text" id="empresa" name="empresa" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                               placeholder="Nome da empresa onde realizou o estágio">
                    </div>

                    <div>
                        <label for="area" class="block text-sm font-medium text-gray-700 mb-1">
                            Área *
                        </label>
                        <input type="text" id="area" name="area" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                               placeholder="Área de atuação do estágio">
                    </div>

                    <div>
                        <label for="dataInicio" class="block text-sm font-medium text-gray-700 mb-1">
                            Data de Início *
                        </label>
                        <input type="date" id="dataInicio" name="dataInicio" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    </div>

                    <div>
                        <label for="dataFim" class="block text-sm font-medium text-gray-700 mb-1">
                            Data de Fim *
                        </label>
                        <input type="date" id="dataFim" name="dataFim" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="horas" class="block text-sm font-medium text-gray-700 mb-1">
                            Horas *
                        </label>
                        <input type="number" id="horas" name="horas" required min="1" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                               placeholder="Total de horas do estágio">
                    </div>
                </div>

                <div>
                    <label for="declaracao" class="block text-sm font-medium text-gray-700 mb-1">
                        Declaração/Certificado *
                    </label>
                    <input type="file" id="declaracao" name="declaracao" required accept=".pdf,.jpg,.jpeg,.png" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG</p>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="fecharModalSelecao()" 
                            class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" id="btnSubmit" 
                            class="px-6 py-2 text-white rounded-md transition-colors hover:bg-yellow-600" 
                            style="background-color: #F59E0B">
                        Cadastrar Atividade
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rodapé -->
    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">© 2024 Sistema de Gerenciamento de Atividades Complementares</p>
        </div>
    </footer>

    <script>
        // Verificar autenticação
        function verificarAutenticacao() {
            if (!AuthClient.isLoggedIn()) {
                window.location.href = '/Gerenciamento-ACC/frontend/pages/login.php';
                return false;
            }
            const user = AuthClient.getUser();
            if (!user || user.tipo !== 'aluno') {
                AuthClient.logout();
                return false;
            }
            return true;
        }
        
        verificarAutenticacao();

        // Carregar atividades de estágio via JWT
        let todasAtividades = [];

        async function carregarAtividades() {
            try {
                const response = await fetch('/Gerenciamento-ACC/backend/api/routes/listar_atividades.php', {
                    method: 'GET',
                    headers: {
                        'X-API-Key': 'frontend-gerenciamento-acc-2025'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    // Filtrar apenas atividades de estágio
                    todasAtividades = (data.data || []).filter(atividade => 
                        atividade.categoria && atividade.categoria.toLowerCase().includes('estágio')
                    );
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
                    <div class="text-6xl mb-4">💼</div>
                    <p class="text-gray-500 text-lg mb-2">Nenhuma atividade de estágio encontrada.</p>
                    <p class="text-gray-400 text-sm">Entre em contato com a coordenação para mais informações.</p>
                </div>`;
                return;
            }
            
            container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ${todasAtividades.map(atividade => `
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-4" style="background-color: #F59E0B">
                            <h3 class="text-lg font-bold text-white">${atividade.nome}</h3>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 mt-2">
                                ${atividade.categoria}
                            </span>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 text-sm mb-4">${atividade.descricao}</p>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #F59E0B">Tipo:</span>
                                    <span class="text-gray-600">${atividade.tipo}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #F59E0B">Horas Máximas:</span>
                                    <span class="text-gray-600">${atividade.horas_max}h</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="verDetalhes(${atividade.id})"
                                        class="flex-1 px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200"
                                        style="color: #F59E0B">
                                    Ver Detalhes
                                </button>
                                <button onclick="abrirModalSelecao(${atividade.id}, '${atividade.nome}', ${atividade.horas_max})"
                                        class="flex-1 px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition duration-200"
                                        style="background-color: #F59E0B">
                                    Selecionar
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>`;
        }

        function verDetalhes(id) {
            const atividade = todasAtividades.find(a => a.id === id);
            if (!atividade) return;
            
            const detalhes = `
                <h4 class="text-xl font-bold mb-4" style="color: #F59E0B">${atividade.nome}</h4>
                <div class="space-y-3">
                    <div>
                        <span class="font-medium" style="color: #F59E0B">Categoria:</span>
                        <span class="ml-2">${atividade.categoria}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #F59E0B">Tipo:</span>
                        <span class="ml-2">${atividade.tipo}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #F59E0B">Horas Máximas:</span>
                        <span class="ml-2">${atividade.horas_max} horas</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #F59E0B">Descrição:</span>
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

        // Função para abrir modal de seleção de atividade
        function abrirModalSelecao(id, nome, horasMaximas) {
            document.getElementById('atividadeId').value = id;
            document.getElementById('nomeAtividade').textContent = nome;
            document.getElementById('horasMaximas').textContent = horasMaximas + 'h';
            
            // Limpar formulário
            document.getElementById('formCadastro').reset();
            document.getElementById('atividadeId').value = id; // Manter o ID após reset
            
            // Abrir modal
            document.getElementById('modalSelecao').classList.remove('hidden');
        }

        function selecionarAtividade(id) {
            // Armazenar o ID da atividade selecionada
            document.getElementById('atividadeId').value = id;
            
            // Buscar dados da atividade para preencher o formulário
            const atividade = todasAtividades.find(a => a.id == id);
            if (atividade) {
                document.getElementById('nomeAtividade').textContent = atividade.nome;
                document.getElementById('horasMaximas').textContent = atividade.horas_max;
            }
            
            fecharModal();
            document.getElementById('modalSelecao').classList.remove('hidden');
        }

        function fecharModalSelecao() {
            document.getElementById('modalSelecao').classList.add('hidden');
            // Limpar formulário
            document.getElementById('formCadastro').reset();
        }

        // Função para submeter o formulário de cadastro
        document.getElementById('formCadastro').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validar se todos os campos obrigatórios estão preenchidos
            const empresa = document.getElementById('empresa').value.trim();
            const area = document.getElementById('area').value.trim();
            const dataInicio = document.getElementById('dataInicio').value;
            const dataFim = document.getElementById('dataFim').value;
            const horas = document.getElementById('horas').value;
            const declaracao = document.getElementById('declaracao').files[0];
            
            if (!empresa || !area || !dataInicio || !dataFim || !horas || !declaracao) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                return;
            }
            
            // Validar se data fim não é anterior à data início
            if (new Date(dataFim) < new Date(dataInicio)) {
                alert('A data de fim não pode ser anterior à data de início.');
                return;
            }
            
            // Validar se as horas são positivas
            if (parseInt(horas) <= 0) {
                alert('O número de horas deve ser maior que zero.');
                return;
            }
            
            const formData = new FormData();
            formData.append('atividade_disponivel_id', document.getElementById('atividadeId').value);
            formData.append('empresa', empresa);
            formData.append('area', area);
            formData.append('data_inicio', dataInicio);
            formData.append('data_fim', dataFim);
            formData.append('horas', parseInt(horas));
            formData.append('declaracao', declaracao);
            
            // Desabilitar botão de submit para evitar duplo envio
            const btnSubmit = document.getElementById('btnSubmit');
            const textoOriginal = btnSubmit.textContent;
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Cadastrando...';
            
            try {
                // Enviar via AuthClient para garantir cabeçalhos corretos
                const resp = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/atividades_estagio.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await resp.json();
                if (result.success) {
                    alert('Atividade de estágio cadastrada com sucesso!');
                    
                    // Atualizar automaticamente a seção "Minhas Atividades"
                    await atualizarMinhasAtividades();
                    
                    fecharModalSelecao();
                } else {
                    alert('Erro ao cadastrar atividade: ' + (result.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao cadastrar atividade: ' + error.message);
            } finally {
                // Reabilitar botão de submit
                btnSubmit.disabled = false;
                btnSubmit.textContent = textoOriginal;
            }
        });

        // Função para atualizar "Minhas Atividades" automaticamente
        async function atualizarMinhasAtividades() {
            try {
                // Fazer requisição para buscar as atividades atualizadas do aluno
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/minhas_atividades.php');
                const data = await response.json();
                
                if (data.success) {
                    console.log('Atividades atualizadas automaticamente:', data.data);
                    // Aqui você pode adicionar lógica adicional se necessário
                    // Por exemplo, mostrar uma notificação de que as atividades foram atualizadas
                } else {
                    console.error('Erro ao atualizar atividades:', data.error);
                }
            } catch (error) {
                console.error('Erro na requisição de atualização:', error);
            }
        }

        // Carregar atividades ao inicializar a página
        carregarAtividades();
    </script>
</body>
</html>