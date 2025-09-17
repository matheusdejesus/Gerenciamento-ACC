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

            <!-- Container das atividades -->
            <div id="atividadesContainer" class="mb-8">
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="text-gray-500 mt-4">Carregando atividades...</p>
                </div>
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
                    
                    <div id="campoMonitoriaMonitor" class="hidden">
                        <label for="nomeMonitor" class="block text-sm font-medium text-gray-700 mb-2">Monitor *</label>
                        <input type="text" id="nomeMonitor" name="monitor"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Digite o nome do monitor">
                    </div>
                    
                    <div id="campoHorasMonitoria" class="hidden">
                        <label for="horasMonitoria" class="block text-sm font-medium text-gray-700 mb-2">Carga Horária *</label>
                        <input type="number" id="horasMonitoria" name="horas_monitoria" min="1" max="500"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Digite a quantidade de horas">
                    </div>
                    

                    
                    <div id="campoMonitoriaDatas" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                        <div>
                            <label for="dataInicio" class="block text-sm font-medium text-gray-700 mb-2">Data de Início *</label>
                            <input type="date" id="dataInicio" name="data_inicio"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="dataFim" class="block text-sm font-medium text-gray-700 mb-2">Data de Fim *</label>
                            <input type="date" id="dataFim" name="data_fim"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                    </div>
                    
                    <!-- Campos específicos para Disciplinas em outras IES -->
                    <div id="campoDisciplinaOutrasIES" class="hidden">
                        <label for="nomeDisciplinaIES" class="block text-sm font-medium text-gray-700 mb-2">Nome da Disciplina *</label>
                        <input type="text" id="nomeDisciplinaIES" name="nome_disciplina"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Digite o nome da disciplina">
                    </div>
                    
                    <div id="campoInstituicao" class="hidden">
                        <label for="nomeInstituicao" class="block text-sm font-medium text-gray-700 mb-2">Nome da Instituição *</label>
                        <input type="text" id="nomeInstituicao" name="nome_instituicao"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Digite o nome da instituição">
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

        async function carregarAtividades() {
            try {
                const response = await AuthClient.fetch('../../backend/api/routes/listar_atividades.php', {
                    method: 'GET'
                });
                const data = await response.json();
                if (data.success) {
                    // Filtrar apenas atividades de ensino
                    todasAtividades = (data.data || []).filter(atividade => 
                        atividade.categoria && atividade.categoria.toLowerCase() === 'ensino'
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
                            <h3 class="text-lg font-bold text-white">${atividade.nome}</h3>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mt-2">
                                ${atividade.categoria}
                            </span>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 text-sm mb-4">${atividade.descricao}</p>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #1A7F37">Tipo:</span>
                                    <span class="text-gray-600">${atividade.tipo}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #1A7F37">Horas Máximas:</span>
                                    <span class="text-gray-600">${atividade.horas_max}h</span>
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
                'nomeDisciplinaLab', 'nomeMonitor', 'horasMonitoria', 'coordenadorIdMonitoria',
                'dataInicio', 'dataFim', 'comprovante',
                'nomeDisciplinaIES', 'horasOutrasIES', 'coordenadorIdOutrasIES', 'nomeInstituicao',
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
             const campoMonitoriaMonitor = document.getElementById('campoMonitoriaMonitor');
             const campoHorasMonitoria = document.getElementById('campoHorasMonitoria');
             const campoMonitoriaDatas = document.getElementById('campoMonitoriaDatas');
             const campoComprovanteMonitoria = document.getElementById('campoComprovanteMonitoria');
            
            // Elementos dos campos específicos para disciplinas em outras IES
             const campoDisciplinaOutrasIES = document.getElementById('campoDisciplinaOutrasIES');
             const campoHorasOutrasIES = document.getElementById('campoHorasOutrasIES');
             const campoInstituicao = document.getElementById('campoInstituicao');
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
                 campoMonitoriaMonitor.classList.remove('hidden');
                 campoHorasMonitoria.classList.remove('hidden');
                 campoMonitoriaDatas.classList.remove('hidden');
                 campoComprovanteMonitoria.classList.remove('hidden');
                
                // Ocultar campos de outras IES e UFOPA
                  campoDisciplinaOutrasIES.classList.add('hidden');
                  campoHorasOutrasIES.classList.add('hidden');
                  campoInstituicao.classList.add('hidden');
                  campoDeclaracaoIES.classList.add('hidden');
                  campoDisciplinaUFOPA.classList.add('hidden');
                  campoHorasUFOPA.classList.add('hidden');
                  campoComprovanteUFOPA.classList.add('hidden');
                 
                 // Tornar campos de monitoria obrigatórios
                 document.getElementById('nomeDisciplinaLab').setAttribute('required', 'required');
                 document.getElementById('nomeMonitor').setAttribute('required', 'required');
                 document.getElementById('horasMonitoria').setAttribute('required', 'required');
                 document.getElementById('dataInicio').setAttribute('required', 'required');
                 document.getElementById('dataFim').setAttribute('required', 'required');
                 document.getElementById('comprovante').setAttribute('required', 'required');
                
                // Remover obrigatoriedade dos campos de outras IES e UFOPA
                 document.getElementById('nomeDisciplinaIES').removeAttribute('required');
                 document.getElementById('horasOutrasIES').removeAttribute('required');
                 document.getElementById('nomeInstituicao').removeAttribute('required');
                 document.getElementById('nomeDisciplinaUFOPA').removeAttribute('required');
                 document.getElementById('horasUFOPA').removeAttribute('required');
                
            } else if (isOutrasIES) {
                // Definir tipo de atividade
                document.getElementById('tipoAtividade').value = 'Outras IES';
                
                // Mostrar campos específicos para disciplinas em outras IES
                campoDisciplinaOutrasIES.classList.remove('hidden');
                campoHorasOutrasIES.classList.remove('hidden');
                campoInstituicao.classList.remove('hidden');
                campoDeclaracaoIES.classList.remove('hidden');
                
                // Ocultar campos de monitoria e UFOPA
                  campoMonitoriaDisciplina.classList.add('hidden');
                  campoMonitoriaMonitor.classList.add('hidden');
                  campoHorasMonitoria.classList.add('hidden');
                  campoMonitoriaDatas.classList.add('hidden');
                  campoComprovanteMonitoria.classList.add('hidden');
                  campoDisciplinaUFOPA.classList.add('hidden');
                  campoHorasUFOPA.classList.add('hidden');
                  campoComprovanteUFOPA.classList.add('hidden');
                
                // Tornar campos de outras IES obrigatórios
                document.getElementById('nomeDisciplinaIES').setAttribute('required', 'required');
                document.getElementById('horasOutrasIES').setAttribute('required', 'required');
                document.getElementById('nomeInstituicao').setAttribute('required', 'required');
                
                // Remover obrigatoriedade dos campos de monitoria e UFOPA
                  document.getElementById('nomeDisciplinaLab').removeAttribute('required');
                  document.getElementById('nomeMonitor').removeAttribute('required');
                  document.getElementById('horasMonitoria').removeAttribute('required');
                  document.getElementById('dataInicio').removeAttribute('required');
                  document.getElementById('dataFim').removeAttribute('required');
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
                  campoMonitoriaMonitor.classList.add('hidden');
                  campoHorasMonitoria.classList.add('hidden');
                  campoMonitoriaDatas.classList.add('hidden');
                  campoComprovanteMonitoria.classList.add('hidden');
                  campoDisciplinaOutrasIES.classList.add('hidden');
                  campoHorasOutrasIES.classList.add('hidden');
                  campoInstituicao.classList.add('hidden');
                  campoDeclaracaoIES.classList.add('hidden');
                 
                 // Tornar campos da UFOPA obrigatórios
                 document.getElementById('nomeDisciplinaUFOPA').setAttribute('required', 'required');
                 document.getElementById('horasUFOPA').setAttribute('required', 'required');
                 
                 // Remover obrigatoriedade dos outros campos
                  document.getElementById('nomeDisciplinaLab').removeAttribute('required');
                  document.getElementById('nomeMonitor').removeAttribute('required');
                  document.getElementById('horasMonitoria').removeAttribute('required');
                  document.getElementById('dataInicio').removeAttribute('required');
                  document.getElementById('dataFim').removeAttribute('required');
                  document.getElementById('nomeDisciplinaIES').removeAttribute('required');
                  document.getElementById('horasOutrasIES').removeAttribute('required');
                  document.getElementById('nomeInstituicao').removeAttribute('required');
                 
             } else {
                 // Ocultar todos os campos específicos
                  campoMonitoriaDisciplina.classList.add('hidden');
                  campoMonitoriaMonitor.classList.add('hidden');
                  campoHorasMonitoria.classList.add('hidden');
                  campoMonitoriaDatas.classList.add('hidden');
                  campoComprovanteMonitoria.classList.add('hidden');
                  campoDisciplinaOutrasIES.classList.add('hidden');
                  campoHorasOutrasIES.classList.add('hidden');
                  campoInstituicao.classList.add('hidden');
                  campoDeclaracaoIES.classList.add('hidden');
                  campoDisciplinaUFOPA.classList.add('hidden');
                  campoHorasUFOPA.classList.add('hidden');
                  campoComprovanteUFOPA.classList.add('hidden');
                
                // Remover obrigatoriedade de todos os campos
                  document.getElementById('nomeDisciplinaLab').removeAttribute('required');
                  document.getElementById('nomeMonitor').removeAttribute('required');
                  document.getElementById('horasMonitoria').removeAttribute('required');
                  document.getElementById('dataInicio').removeAttribute('required');
                  document.getElementById('dataFim').removeAttribute('required');
                  document.getElementById('nomeDisciplinaIES').removeAttribute('required');
                  document.getElementById('horasOutrasIES').removeAttribute('required');
                  document.getElementById('nomeInstituicao').removeAttribute('required');
                  document.getElementById('nomeDisciplinaUFOPA').removeAttribute('required');
                  document.getElementById('horasUFOPA').removeAttribute('required');
                
                // Limpar valores dos campos
                  document.getElementById('nomeDisciplinaLab').value = '';
                  document.getElementById('nomeMonitor').value = '';
                  document.getElementById('horasMonitoria').value = '';
                  document.getElementById('dataInicio').value = '';
                  document.getElementById('dataFim').value = '';
                  document.getElementById('nomeDisciplinaIES').value = '';
                  document.getElementById('horasOutrasIES').value = '';
                  document.getElementById('nomeInstituicao').value = '';
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
                submitBtn.disabled = true;
                submitBtn.textContent = 'Enviando...';
                
                // Categoria será sempre 1 (Ensino) - definida no backend
                
                if (tipoAtividade === 'Outras IES') { // Outras IES
                    formData.append('nome_disciplina', document.getElementById('nomeDisciplinaIES').value);
                    formData.append('nome_instituicao', document.getElementById('nomeInstituicao').value);
                    formData.append('carga_horaria', document.getElementById('horasOutrasIES').value);
                    const fileIES = document.getElementById('declaracaoIES').files[0];
                    if (fileIES) formData.append('declaracao_ies', fileIES);
                } else if (tipoAtividade === 'UFOPA') { // UFOPA
                    formData.append('nome_disciplina', document.getElementById('nomeDisciplinaUFOPA').value);
                    formData.append('carga_horaria', document.getElementById('horasUFOPA').value);
                    const fileUFOPA = document.getElementById('comprovanteUFOPA').files[0];
                    if (fileUFOPA) formData.append('comprovante_ufopa', fileUFOPA);
                } else if (tipoAtividade === 'Monitoria') { // Monitoria
                    formData.append('nome_disciplina_laboratorio', document.getElementById('nomeDisciplinaLab').value);
                    formData.append('monitor', document.getElementById('nomeMonitor').value);
                    formData.append('carga_horaria', document.getElementById('horasMonitoria').value);
                    formData.append('data_inicio', document.getElementById('dataInicio').value);
                    formData.append('data_fim', document.getElementById('dataFim').value);
                    const fileMonitoria = document.getElementById('comprovante').files[0];
                    if (fileMonitoria) formData.append('comprovante', fileMonitoria);
                }
                
                // Adicionar tipo de atividade para validação no backend
                formData.append('tipo_atividade', tipoAtividade);
                
                // Enviar FormData diretamente para suportar uploads
                const response = await AuthClient.fetch('../../backend/api/routes/atividade_complementar_ensino.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('acc_jwt_token')
                    },
                    body: formData
                });
                
                const resultado = await response.json();
                
                if (response.ok && resultado.sucesso) {
                    alert('Atividade complementar cadastrada com sucesso!');
                    fecharModalSelecao();
                    // Recarregar a página para mostrar a nova atividade
                    window.location.reload();
                } else {
                    throw new Error(resultado.erro || 'Erro desconhecido');
                }
                
            } catch (error) {
                console.error('Erro ao cadastrar atividade:', error);
                
                // Mostrar erro mais específico baseado no status da resposta
                let mensagemErro = 'Erro ao cadastrar atividade: ';
                if (error.message.includes('400')) {
                    mensagemErro += 'Dados inválidos ou incompletos. Verifique se todos os campos obrigatórios foram preenchidos.';
                } else if (error.message.includes('401') || error.message.includes('403')) {
                    mensagemErro += 'Sessão expirada. Faça login novamente.';
                    AuthClient.logout();
                    return;
                } else if (error.message.includes('500')) {
                    mensagemErro += 'Erro interno do servidor. Tente novamente em alguns minutos.';
                } else {
                    mensagemErro += error.message;
                }
                
                alert(mensagemErro);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });

        // Carregar atividades ao inicializar a página
        carregarAtividades();
    </script>
</body>
</html>