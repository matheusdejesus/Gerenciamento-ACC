<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividades de Ensino - Sistema ACC</title>
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
                    <div class="text-4xl mr-4">📚</div>
                    <div>
                        <h2 class="text-3xl font-bold" style="color: #1A7F37">Atividades de Ensino</h2>
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
                        <p class="text-sm text-red-700 mt-1">Não foi possível carregar as atividades de ensino. Tente novamente.</p>
                    </div>
                </div>
            </div>

            <!-- Barra de busca e filtros -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" id="campoBusca" placeholder="Buscar atividades de ensino..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                onkeypress="if(event.key==='Enter') buscarAtividades()">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="buscarAtividades()"
                            class="px-4 py-2 text-white rounded-lg hover:opacity-90 transition duration-200"
                            style="background-color: #1A7F37">
                            Buscar
                        </button>
                        <button onclick="ordenarAtividades('nome')"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                            Ordenar A-Z
                        </button>
                        <button onclick="ordenarAtividades('carga_horaria_maxima')"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                            Por Horas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
                <p class="text-gray-500 mt-4">Carregando atividades...</p>
            </div>

            <!-- Container das atividades -->
            <div id="atividadesContainer" class="mb-8 hidden">
                <!-- Atividades serão carregadas aqui -->
            </div>

            <!-- Container de paginação -->
            <div id="paginacaoContainer" class="mb-8">
                <!-- Paginação será renderizada aqui -->
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div id="modalDetalhes" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative mx-auto p-6 border w-11/12 md:w-3/4 lg:w-1/2 max-w-2xl shadow-lg rounded-lg bg-white mt-8">
            <button onclick="fecharModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="text-center">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">Detalhes da Atividade</h3>
                <div id="conteudoDetalhes" class="text-left">
                    <!-- Conteúdo será inserido dinamicamente -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Seleção -->
    <div id="modalSelecao" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative mx-auto p-6 border w-11/12 md:w-3/4 lg:w-1/2 max-w-2xl shadow-lg rounded-lg bg-white mt-8">
            <button onclick="fecharModalSelecao()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="text-center">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">Informações da Atividade</h3>
                <form id="formSelecao" class="text-left space-y-4" enctype="multipart/form-data">
                    <input type="hidden" id="atividadeId" name="atividade_id">
                    <input type="hidden" id="categoriaId" name="categoria_id">
                    <input type="hidden" id="tipoAtividade" value="">

                    <!-- Campos específicos para Monitoria -->
                    <div id="campoMonitoriaDisciplina" class="hidden">
                        <label for="nomeDisciplinaLab" class="block text-sm font-medium text-gray-700 mb-2">Nome da Disciplina/Laboratório *</label>
                        <input type="text" id="nomeDisciplinaLab" name="nome_disciplina_laboratorio"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Digite o nome da disciplina ou laboratório">
                    </div>



                    <div id="campoHorasMonitoria" class="hidden">
                        <label for="horasMonitoria" class="block text-sm font-medium text-gray-700 mb-2">Carga Horária *</label>
                        <input type="number" id="horasMonitoria" name="horas_monitoria" min="1" max="500"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Digite a quantidade de horas">
                    </div>





                    <!-- Campos específicos para Disciplinas em outras IES -->
                    <div id="campoDisciplinaOutrasIES" class="hidden">
                        <label for="nomeDisciplinaIES" class="block text-sm font-medium text-gray-700 mb-2">Nome da Disciplina *</label>
                        <input type="text" id="nomeDisciplinaIES" name="nome_disciplina"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Digite o nome da disciplina">
                    </div>



                    <div id="campoHorasOutrasIES" class="hidden">
                        <label for="horasOutrasIES" class="block text-sm font-medium text-gray-700 mb-2">Carga Horária *</label>
                        <input type="number" id="horasOutrasIES" name="carga_horaria" min="1" max="500"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Digite a quantidade de horas">
                    </div>



                    <!-- Campos específicos para Disciplinas na UFOPA -->
                    <div id="campoDisciplinaUFOPA" class="hidden">
                        <label for="nomeDisciplinaUFOPA" class="block text-sm font-medium text-gray-700 mb-2">Nome da Disciplina *</label>
                        <input type="text" id="nomeDisciplinaUFOPA" name="nome_disciplina"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Digite o nome da disciplina">
                    </div>

                    <div id="campoHorasUFOPA" class="hidden">
                        <label for="horasUFOPA" class="block text-sm font-medium text-gray-700 mb-2">Carga Horária *</label>
                        <input type="number" id="horasUFOPA" name="carga_horaria" min="1" max="500"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Digite a quantidade de horas">
                    </div>



                    <div id="campoComprovanteUFOPA" class="hidden">
                        <label for="comprovanteUFOPA" class="block text-sm font-medium text-gray-700 mb-2">Comprovante de Declaração da Disciplina *</label>
                        <input type="file" id="comprovanteUFOPA" name="comprovante_ufopa" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, JPEG, PNG (máx. 5MB)</p>
                    </div>

                    <div id="campoDeclaracaoIES" class="hidden">
                        <label for="declaracaoIES" class="block text-sm font-medium text-gray-700 mb-2">Declaração Comprovando a Disciplina *</label>
                        <input type="file" id="declaracaoIES" name="declaracao_ies" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, JPEG, PNG (máx. 5MB)</p>
                    </div>

                    <div id="campoComprovanteMonitoria" class="hidden">
                        <label for="comprovante" class="block text-sm font-medium text-gray-700 mb-2">Comprovante *</label>
                        <input type="file" id="comprovante" name="comprovante" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, JPEG, PNG (máx. 5MB)</p>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="fecharModalSelecao()"
                            class="flex-1 px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition duration-200"
                            style="background-color: #1A7F37">
                            Enviar Solicitação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">© 2025 Sistema de Gerenciamento de Atividades Complementares</p>
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

        // Carregar atividades de ensino via JWT
        let todasAtividades = [];
        let paginaAtual = 1;
        let totalPaginas = 1;
        let buscaAtual = '';
        let ordenacaoAtual = 'nome';
        let direcaoAtual = 'ASC';

        async function carregarAtividades(pagina = 1, busca = '', ordenacao = 'nome', direcao = 'ASC') {
            try {
                // Mostrar loading
                document.getElementById('loadingSpinner').classList.remove('hidden');
                document.getElementById('atividadesContainer').classList.add('hidden');

                const params = new URLSearchParams({
                    type: 'ensino',
                    pagina: pagina,
                    limite: 20,
                    busca: busca,
                    ordenacao: ordenacao,
                    direcao: direcao
                });

                const response = await AuthClient.fetch(`../../backend/api/routes/listar_atividades_disponiveis.php?${params}`, {
                    method: 'GET'
                });

                const data = await response.json();
                console.log('Resposta da API:', data);

                if (data.success || data.sucesso) {
                    todasAtividades = data.data.atividades || [];

                    // Atualizar informações de paginação
                    if (data.data.paginacao) {
                        paginaAtual = data.data.paginacao.pagina_atual;
                        totalPaginas = data.data.paginacao.total_paginas;
                        buscaAtual = busca;
                        ordenacaoAtual = ordenacao;
                        direcaoAtual = direcao;
                    }

                    renderizarAtividades();
                    renderizarPaginacao();
                    document.getElementById('alertaAtividades').classList.add('hidden');
                } else {
                    console.error('Erro na API:', data.error || data.erro);
                    document.getElementById('alertaAtividades').classList.remove('hidden');
                    todasAtividades = [];
                    renderizarAtividades();
                }
            } catch (e) {
                console.error('Erro ao carregar atividades:', e);
                document.getElementById('alertaAtividades').classList.remove('hidden');
                todasAtividades = [];
                renderizarAtividades();
            } finally {
                // Esconder loading
                document.getElementById('loadingSpinner').classList.add('hidden');
                document.getElementById('atividadesContainer').classList.remove('hidden');
            }
        }

        function renderizarAtividades() {
            const container = document.getElementById('atividadesContainer');
            if (!todasAtividades.length) {
                container.innerHTML = `<div class="text-center py-12">
                    <div class="text-6xl mb-4">📚</div>
                    <p class="text-gray-500 text-lg mb-2">Nenhuma atividade de ensino encontrada.</p>
                    <p class="text-gray-400 text-sm">Entre em contato com a coordenação para mais informações.</p>
                </div>`;
                return;
            }

            container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ${todasAtividades.map(atividade => `
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-4" style="background-color: #1A7F37">
                            <h3 class="text-lg font-bold text-white">${atividade.nome || 'Nome não disponível'}</h3>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mt-2">
                                Ensino
                            </span>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 text-sm mb-4">${atividade.descricao || 'Descrição não disponível'}</p>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #1A7F37">Categoria:</span>
                                    <span class="text-gray-600">${atividade.categoria || 'Ensino'}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #1A7F37">Horas Máximas:</span>
                                    <span class="text-gray-600">${atividade.carga_horaria_maxima || atividade.horas_max || 0}h</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="verDetalhes(${atividade.id})"
                                        class="flex-1 px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200"
                                        style="color: #1A7F37">
                                    Ver Detalhes
                                </button>
                                <button onclick="abrirModalSelecao(${atividade.id})"
                                        class="flex-1 px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition duration-200"
                                        style="background-color: #1A7F37">
                                    Selecionar
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>`;
        }

        function renderizarPaginacao() {
            const container = document.getElementById('paginacaoContainer');
            if (!container) return;

            if (totalPaginas <= 1) {
                container.innerHTML = '';
                return;
            }

            let paginacao = '<div class="flex justify-center items-center space-x-2 mt-6">';

            // Botão anterior
            if (paginaAtual > 1) {
                paginacao += `<button onclick="carregarAtividades(${paginaAtual - 1}, '${buscaAtual}', '${ordenacaoAtual}', '${direcaoAtual}')" 
                             class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Anterior</button>`;
            }

            // Números das páginas
            for (let i = Math.max(1, paginaAtual - 2); i <= Math.min(totalPaginas, paginaAtual + 2); i++) {
                if (i === paginaAtual) {
                    paginacao += `<button class="px-3 py-2 text-sm text-white rounded-lg" style="background-color: #1A7F37">${i}</button>`;
                } else {
                    paginacao += `<button onclick="carregarAtividades(${i}, '${buscaAtual}', '${ordenacaoAtual}', '${direcaoAtual}')" 
                                 class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">${i}</button>`;
                }
            }

            // Botão próximo
            if (paginaAtual < totalPaginas) {
                paginacao += `<button onclick="carregarAtividades(${paginaAtual + 1}, '${buscaAtual}', '${ordenacaoAtual}', '${direcaoAtual}')" 
                             class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Próximo</button>`;
            }

            paginacao += '</div>';
            container.innerHTML = paginacao;
        }

        function buscarAtividades() {
            const termoBusca = document.getElementById('campoBusca')?.value || '';
            carregarAtividades(1, termoBusca, ordenacaoAtual, direcaoAtual);
        }

        function ordenarAtividades(campo) {
            const novaDirecao = (ordenacaoAtual === campo && direcaoAtual === 'ASC') ? 'DESC' : 'ASC';
            carregarAtividades(paginaAtual, buscaAtual, campo, novaDirecao);
        }

        function verDetalhes(id) {
            const atividade = todasAtividades.find(a => a.id === id);
            if (!atividade) return;

            const detalhes = `
                <h4 class="text-xl font-bold mb-4" style="color: #1A7F37">${atividade.nome}</h4>
                <div class="space-y-3">
                    <div>
                        <span class="font-medium" style="color: #1A7F37">Categoria:</span>
                        <span class="ml-2">${atividade.categoria}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #1A7F37">Tipo:</span>
                        <span class="ml-2">${atividade.tipo}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #1A7F37">Horas Máximas:</span>
                        <span class="ml-2">${atividade.horas_max} horas</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #1A7F37">Descrição:</span>
                        <p class="mt-1 text-gray-600">${atividade.descricao}</p>
                    </div>
                </div>
            `;

            document.getElementById('conteudoDetalhes').innerHTML = detalhes;
            document.getElementById('modalDetalhes').classList.remove('hidden');

            // Fechar modal ao clicar fora dele
            document.getElementById('modalDetalhes').onclick = (e) => {
                if (e.target.id === 'modalDetalhes') {
                    fecharModal();
                }
            };
        }

        function fecharModal() {
            document.getElementById('modalDetalhes').classList.add('hidden');
        }

        function selecionarAtividade(id) {
            window.location.href = `enviar_certificado_ensino.php?id=${id}`;
        }

        function abrirModalSelecao(id) {
            // Resetar atributos required em todos os campos condicionais
            const camposCondicionais = [
                'nomeDisciplinaLab', 'horasMonitoria', 'coordenadorIdMonitoria',
                'comprovante',
                'nomeDisciplinaIES', 'horasOutrasIES', 'coordenadorIdOutrasIES',
                'nomeDisciplinaUFOPA', 'horasUFOPA', 'coordenadorIdUFOPA'
            ];
            camposCondicionais.forEach(id => {
                const element = document.getElementById(id);
                if (element) element.removeAttribute('required');
            });

            const atividade = todasAtividades.find(a => a.id === id);
            document.getElementById('atividadeId').value = id;

            // Definir categoria_id como 1 para todas atividades de ensino
            let categoriaId = 1;
            document.getElementById('categoriaId').value = categoriaId;

            // Elementos dos campos específicos para monitoria
            const campoMonitoriaDisciplina = document.getElementById('campoMonitoriaDisciplina');
            const campoHorasMonitoria = document.getElementById('campoHorasMonitoria');
            const campoComprovanteMonitoria = document.getElementById('campoComprovanteMonitoria');

            // Elementos dos campos específicos para disciplinas em outras IES
            const campoDisciplinaOutrasIES = document.getElementById('campoDisciplinaOutrasIES');
            const campoHorasOutrasIES = document.getElementById('campoHorasOutrasIES');
            const campoDeclaracaoIES = document.getElementById('campoDeclaracaoIES');

            // Elementos dos campos específicos para disciplinas na UFOPA
            const campoDisciplinaUFOPA = document.getElementById('campoDisciplinaUFOPA');
            const campoHorasUFOPA = document.getElementById('campoHorasUFOPA');
            const campoComprovanteUFOPA = document.getElementById('campoComprovanteUFOPA');

            // Verificar se é atividade de monitoria
            const isMonitoria = atividade && atividade.nome.toLowerCase().includes('monitoria');

            // Verificar se é disciplina em outras IES
            const isOutrasIES = atividade && atividade.nome.toLowerCase().includes('outras ies');

            // Verificar se é disciplina na UFOPA
            const isUFOPA = atividade && atividade.nome.toLowerCase().includes('ufopa');

            if (isMonitoria) {
                // Definir tipo de atividade
                document.getElementById('tipoAtividade').value = 'Monitoria';

                // Mostrar campos específicos para monitoria
                campoMonitoriaDisciplina.classList.remove('hidden');
                campoHorasMonitoria.classList.remove('hidden');
                campoComprovanteMonitoria.classList.remove('hidden');

                // Ocultar campos de outras IES e UFOPA
                campoDisciplinaOutrasIES.classList.add('hidden');
                campoHorasOutrasIES.classList.add('hidden');
                campoDeclaracaoIES.classList.add('hidden');
                campoDisciplinaUFOPA.classList.add('hidden');
                campoHorasUFOPA.classList.add('hidden');
                campoComprovanteUFOPA.classList.add('hidden');

                // Tornar campos de monitoria obrigatórios
                document.getElementById('nomeDisciplinaLab').setAttribute('required', 'required');
                document.getElementById('horasMonitoria').setAttribute('required', 'required');
                document.getElementById('comprovante').setAttribute('required', 'required');

                // Remover obrigatoriedade dos campos de outras IES e UFOPA
                document.getElementById('nomeDisciplinaIES').removeAttribute('required');
                document.getElementById('horasOutrasIES').removeAttribute('required');
                document.getElementById('nomeDisciplinaUFOPA').removeAttribute('required');
                document.getElementById('horasUFOPA').removeAttribute('required');

            } else if (isOutrasIES) {
                // Definir tipo de atividade
                document.getElementById('tipoAtividade').value = 'Outras IES';

                // Mostrar campos específicos para disciplinas em outras IES
                campoDisciplinaOutrasIES.classList.remove('hidden');
                campoHorasOutrasIES.classList.remove('hidden');
                campoDeclaracaoIES.classList.remove('hidden');

                // Ocultar campos de monitoria e UFOPA
                campoMonitoriaDisciplina.classList.add('hidden');
                campoHorasMonitoria.classList.add('hidden');
                campoComprovanteMonitoria.classList.add('hidden');
                campoDisciplinaUFOPA.classList.add('hidden');
                campoHorasUFOPA.classList.add('hidden');
                campoComprovanteUFOPA.classList.add('hidden');

                // Tornar campos de outras IES obrigatórios
                document.getElementById('nomeDisciplinaIES').setAttribute('required', 'required');
                document.getElementById('horasOutrasIES').setAttribute('required', 'required');

                // Remover obrigatoriedade dos campos de monitoria e UFOPA
                document.getElementById('nomeDisciplinaLab').removeAttribute('required');
                document.getElementById('horasMonitoria').removeAttribute('required');
                document.getElementById('nomeDisciplinaUFOPA').removeAttribute('required');
                document.getElementById('horasUFOPA').removeAttribute('required');

            } else if (isUFOPA) {
                // Definir tipo de atividade
                document.getElementById('tipoAtividade').value = 'UFOPA';

                // Mostrar campos específicos para disciplinas na UFOPA
                campoDisciplinaUFOPA.classList.remove('hidden');
                campoHorasUFOPA.classList.remove('hidden');
                campoComprovanteUFOPA.classList.remove('hidden');

                // Ocultar campos de monitoria e outras IES
                campoMonitoriaDisciplina.classList.add('hidden');
                campoHorasMonitoria.classList.add('hidden');
                campoComprovanteMonitoria.classList.add('hidden');
                campoDisciplinaOutrasIES.classList.add('hidden');
                campoHorasOutrasIES.classList.add('hidden');
                campoDeclaracaoIES.classList.add('hidden');

                // Tornar campos da UFOPA obrigatórios
                document.getElementById('nomeDisciplinaUFOPA').setAttribute('required', 'required');
                document.getElementById('horasUFOPA').setAttribute('required', 'required');

                // Remover obrigatoriedade dos outros campos
                document.getElementById('nomeDisciplinaLab').removeAttribute('required');
                document.getElementById('horasMonitoria').removeAttribute('required');
                document.getElementById('nomeDisciplinaIES').removeAttribute('required');
                document.getElementById('horasOutrasIES').removeAttribute('required');

            } else {
                // Ocultar todos os campos específicos
                campoMonitoriaDisciplina.classList.add('hidden');
                campoHorasMonitoria.classList.add('hidden');
                campoComprovanteMonitoria.classList.add('hidden');
                campoDisciplinaOutrasIES.classList.add('hidden');
                campoHorasOutrasIES.classList.add('hidden');
                campoDeclaracaoIES.classList.add('hidden');
                campoDisciplinaUFOPA.classList.add('hidden');
                campoHorasUFOPA.classList.add('hidden');
                campoComprovanteUFOPA.classList.add('hidden');

                // Remover obrigatoriedade de todos os campos
                document.getElementById('nomeDisciplinaLab').removeAttribute('required');
                document.getElementById('horasMonitoria').removeAttribute('required');
                document.getElementById('nomeDisciplinaIES').removeAttribute('required');
                document.getElementById('horasOutrasIES').removeAttribute('required');
                document.getElementById('nomeDisciplinaUFOPA').removeAttribute('required');
                document.getElementById('horasUFOPA').removeAttribute('required');

                // Limpar valores dos campos
                document.getElementById('nomeDisciplinaLab').value = '';
                document.getElementById('horasMonitoria').value = '';
                document.getElementById('nomeDisciplinaIES').value = '';
                document.getElementById('horasOutrasIES').value = '';
                document.getElementById('nomeDisciplinaUFOPA').value = '';
                document.getElementById('horasUFOPA').value = '';
            }

            document.getElementById('modalSelecao').classList.remove('hidden');

            // Fechar modal ao clicar fora dele
            document.getElementById('modalSelecao').onclick = (e) => {
                if (e.target.id === 'modalSelecao') {
                    fecharModalSelecao();
                }
            };
        }

        function fecharModalSelecao() {
            document.getElementById('modalSelecao').classList.add('hidden');
            // Limpar formulário
            document.getElementById('formSelecao').reset();
        }

        // Processar envio do formulário
        document.getElementById('formSelecao').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData();
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            const tipoAtividade = document.getElementById('tipoAtividade').value;

            try {
                const tipoAtividade = document.getElementById('tipoAtividade').value;

                if (!tipoAtividade) {
                    alert('Por favor, selecione um tipo de atividade.');
                    return;
                }

                // Validação específica por tipo de atividade
                if (tipoAtividade === 'Outras IES') {
                    const nomeDisciplina = document.getElementById('nomeDisciplinaIES').value.trim();
                    const horas = document.getElementById('horasOutrasIES').value;
                    const arquivo = document.getElementById('declaracaoIES').files[0];
                    
                    if (!nomeDisciplina) {
                        alert('Por favor, informe o nome da disciplina.');
                        return;
                    }
                    if (!horas || horas <= 0) {
                        alert('Por favor, informe uma carga horária válida.');
                        return;
                    }
                    if (!arquivo) {
                        alert('Por favor, anexe a declaração da IES.');
                        return;
                    }
                } else if (tipoAtividade === 'UFOPA') {
                    const nomeDisciplina = document.getElementById('nomeDisciplinaUFOPA').value.trim();
                    const horas = document.getElementById('horasUFOPA').value;
                    const arquivo = document.getElementById('comprovanteUFOPA').files[0];
                    
                    if (!nomeDisciplina) {
                        alert('Por favor, informe o nome da disciplina.');
                        return;
                    }
                    if (!horas || horas <= 0) {
                        alert('Por favor, informe uma carga horária válida.');
                        return;
                    }
                    if (!arquivo) {
                        alert('Por favor, anexe o comprovante.');
                        return;
                    }
                } else if (tipoAtividade === 'Monitoria') {
                    const nomeDisciplina = document.getElementById('nomeDisciplinaLab').value.trim();
                    const horas = document.getElementById('horasMonitoria').value;
                    const arquivo = document.getElementById('comprovante').files[0];
                    
                    if (!nomeDisciplina) {
                        alert('Por favor, informe o nome da disciplina/laboratório.');
                        return;
                    }
                    if (!horas || horas <= 0) {
                        alert('Por favor, informe uma carga horária válida.');
                        return;
                    }
                    if (!arquivo) {
                        alert('Por favor, anexe o comprovante de monitoria.');
                        return;
                    }
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Enviando...';

                // Mapear dados para a estrutura esperada pela API
                let titulo, descricao, chSolicitada, arquivo;
                let atividadesPorResolucaoId;

                if (tipoAtividade === 'Outras IES') {
                    const nomeDisciplina = document.getElementById('nomeDisciplinaIES').value;
                    const horas = document.getElementById('horasOutrasIES').value;
                    titulo = `Disciplina em outras IES: ${nomeDisciplina}`;
                    descricao = `Disciplina cursada em outras instituições de ensino superior: ${nomeDisciplina} - ${horas}h`;
                    chSolicitada = parseInt(horas);
                    arquivo = document.getElementById('declaracaoIES').files[0];
                    atividadesPorResolucaoId = 1; // ID para "Disciplinas em áreas correlatas cursadas em outras IES"
                } else if (tipoAtividade === 'UFOPA') {
                    const nomeDisciplina = document.getElementById('nomeDisciplinaUFOPA').value;
                    const horas = document.getElementById('horasUFOPA').value;
                    titulo = `Disciplina na UFOPA: ${nomeDisciplina}`;
                    descricao = `Disciplina cursada na própria UFOPA: ${nomeDisciplina} - ${horas}h`;
                    chSolicitada = parseInt(horas);
                    arquivo = document.getElementById('comprovanteUFOPA').files[0];
                    atividadesPorResolucaoId = 2; // ID para "Disciplinas em áreas correlatas cursadas na UFOPA"
                } else if (tipoAtividade === 'Monitoria') {
                    const nomeDisciplina = document.getElementById('nomeDisciplinaLab').value;
                    const horas = document.getElementById('horasMonitoria').value;
                    titulo = `Monitoria: ${nomeDisciplina}`;
                    descricao = `Atividade de monitoria em disciplina de graduação ou laboratório: ${nomeDisciplina} - ${horas}h`;
                    chSolicitada = parseInt(horas);
                    arquivo = document.getElementById('comprovante').files[0];
                    atividadesPorResolucaoId = 3; // ID para "Monitoria em disciplina de graduação ou laboratório"
                }

                // Verificar se o usuário está autenticado
                const user = AuthClient.getUser();
                if (!user || !user.id) {
                    alert('Erro: Usuário não autenticado. Faça login novamente.');
                    AuthClient.logout();
                    return;
                }

                // Preparar dados para envio (aluno_id será obtido automaticamente do token JWT)
                formData.append('atividades_por_resolucao_id', atividadesPorResolucaoId);
                formData.append('titulo', titulo);
                formData.append('descricao', descricao);
                formData.append('ch_solicitada', chSolicitada);
                
                if (arquivo) {
                    formData.append('declaracao', arquivo);
                }

                // Obter token JWT
                const token = localStorage.getItem('acc_jwt_token');
                if (!token) {
                    alert('Erro: Token de autenticação não encontrado. Faça login novamente.');
                    AuthClient.logout();
                    return;
                }

                // Enviar para a rota correta
                const response = await fetch('../../backend/api/routes/cadastrar_atividades.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    body: formData
                });

                const resultado = await response.json();

                if (response.ok && (resultado.sucesso || resultado.success)) {
                    alert('Atividade de ensino cadastrada com sucesso!');
                    fecharModalSelecao();

                    // Limpar o formulário
                    document.getElementById('formSelecao').reset();

                    // Atualizar automaticamente a seção "Minhas Atividades"
                    await atualizarMinhasAtividades();

                    // Recarregar a página para mostrar a nova atividade
                    window.location.reload();
                } else {
                    throw new Error(resultado.erro || resultado.error || 'Erro desconhecido');
                }

            } catch (error) {
                console.error('Erro ao cadastrar atividade de ensino:', error);

                // Mostrar erro mais específico baseado no status da resposta
                let mensagemErro = 'Erro ao cadastrar atividade de ensino: ';
                if (error.message.includes('400')) {
                    mensagemErro += 'Dados inválidos ou incompletos. Verifique se todos os campos obrigatórios foram preenchidos corretamente.';
                } else if (error.message.includes('401') || error.message.includes('403')) {
                    mensagemErro += 'Sessão expirada. Faça login novamente.';
                    AuthClient.logout();
                    return;
                } else if (error.message.includes('500')) {
                    mensagemErro += 'Erro interno do servidor. Tente novamente em alguns minutos.';
                } else if (error.message.includes('413')) {
                    mensagemErro += 'Arquivo muito grande. Reduza o tamanho do arquivo e tente novamente.';
                } else if (error.message.includes('415')) {
                    mensagemErro += 'Tipo de arquivo não suportado. Use apenas PDF, DOC, DOCX ou imagens.';
                } else {
                    mensagemErro += error.message;
                }

                alert(mensagemErro);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
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
        document.addEventListener('DOMContentLoaded', function() {
            carregarAtividades();
        });
    </script>
</body>

</html>