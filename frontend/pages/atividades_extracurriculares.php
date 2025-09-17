<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividades Extracurriculares - Sistema ACC</title>
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
                    <div class="text-4xl mr-4">🎓</div>
                    <div>
                        <h2 class="text-3xl font-bold" style="color: #8B5CF6">Atividades Extracurriculares</h2>
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
                        <p class="text-sm text-red-700 mt-1">Não foi possível carregar as atividades de extensão. Tente novamente.</p>
                    </div>
                </div>
            </div>

            <!-- Container das atividades -->
            <div id="atividadesContainer" class="mb-8">
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto"></div>
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
                    <button onclick="abrirModalSelecao()" class="px-4 py-2 text-white rounded-lg hover:opacity-90 transition duration-200" style="background-color: #8B5CF6">
                        Selecionar Atividade
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Seleção com Campos Adicionais -->
    <div id="modalSelecao" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 overflow-y-auto z-50">
        <div class="relative w-full max-w-5xl bg-white rounded-lg shadow-xl max-h-[90vh] overflow-y-auto mx-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900" style="color: #8B5CF6">Cadastrar Atividade de Extensão</h3>
                    <button onclick="fecharModalSelecao()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="formSelecaoAtividade" class="space-y-6">
                    <!-- Informações da Atividade Selecionada -->
                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                        <h4 class="font-semibold text-purple-800 mb-2">Atividade Selecionada:</h4>
                        <div id="infoAtividadeSelecionada" class="text-sm text-purple-700">
                            <!-- Será preenchido dinamicamente -->
                        </div>
                    </div>

                    <!-- Campos do Formulário -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nome do Curso -->
                        <div id="campoCurso">
                            <label for="cursoNome" class="block text-sm font-medium text-gray-700 mb-2">
                                Curso *
                            </label>
                            <input type="text" id="cursoNome" name="cursoNome"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Digite o nome do curso" required>
                        </div>
                        
                        <!-- Horas Realizadas -->
                        <div>
                            <label for="horasRealizadas" class="block text-sm font-medium text-gray-700 mb-2">
                                Horas Realizadas *
                            </label>
                            <input type="number" id="horasRealizadas" name="horasRealizadas" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Ex: 10" min="1" max="" required>
                            <p class="text-xs text-gray-500 mt-1">Máximo: <span id="maxHoras">--</span> horas</p>
                        </div>

                        <!-- Data de Início -->
                        <div>
                            <label for="dataInicio" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Início *
                            </label>
                            <input type="date" id="dataInicio" name="dataInicio" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   required>
                        </div>

                        <!-- Data de Fim -->
                        <div>
                            <label for="dataFim" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Fim *
                            </label>
                            <input type="date" id="dataFim" name="dataFim" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   required>
                        </div>

                        <!-- Local/Instituição -->
                        <div>
                            <label for="local" class="block text-sm font-medium text-gray-700 mb-2">
                                Local/Instituição *
                            </label>
                            <input type="text" id="local" name="local" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Ex: Universidade Federal, ONG Esperança" required>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div>
                        <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">
                            Observações
                        </label>
                        <textarea id="observacoes" name="observacoes" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                  placeholder="Descreva detalhes adicionais sobre a atividade realizada..."></textarea>
                    </div>

                    <!-- Upload de Declaração -->
                    <div>
                        <label for="declaracao" class="block text-sm font-medium text-gray-700 mb-2">
                            Declaração/Certificado *
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-400 transition-colors">
                            <input type="file" id="declaracao" name="declaracao" accept=".pdf,.jpg,.jpeg,.png" 
                                   class="hidden" onchange="mostrarArquivoSelecionado(this)" required>
                            <label for="declaracao" class="cursor-pointer">
                                <div class="text-gray-400 mb-2">
                                    <svg class="mx-auto h-12 w-12" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium text-purple-600">Clique para enviar</span> ou arraste o arquivo
                                </p>
                                <p class="text-xs text-gray-500 mt-1">PDF, JPG, JPEG ou PNG (máx. 10MB)</p>
                            </label>
                            <div id="arquivoSelecionado" class="hidden mt-3 p-2 bg-green-50 border border-green-200 rounded text-sm text-green-700">
                                <!-- Nome do arquivo será mostrado aqui -->
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" onclick="fecharModalSelecao()" 
                                class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 text-white rounded-lg hover:opacity-90 transition duration-200" 
                                style="background-color: #8B5CF6">
                            Cadastrar Atividade
                        </button>
                    </div>
                </form>
                </div>
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

        // Carregar atividades de extensão via JWT
        let todasAtividades = [];

        async function carregarAtividades() {
            try {
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/listar_atividades.php', {
                    method: 'GET'
                });
                const data = await response.json();
                if (data.success) {
                    // Filtrar apenas atividades extracurriculares
                    todasAtividades = (data.data || []).filter(atividade => 
                        atividade.categoria && atividade.categoria.toLowerCase() === 'atividades extracurriculares'
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
                    <div class="text-6xl mb-4">🤝</div>
                    <p class="text-gray-500 text-lg mb-2">Nenhuma atividade de extensão encontrada.</p>
                    <p class="text-gray-400 text-sm">Entre em contato com a coordenação para mais informações.</p>
                </div>`;
                return;
            }
            
            container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ${todasAtividades.map(atividade => `
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-4" style="background-color: #8B5CF6">
                            <h3 class="text-lg font-bold text-white">${atividade.nome}</h3>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 mt-2">
                                ${atividade.categoria}
                            </span>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 text-sm mb-4">${atividade.descricao}</p>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #8B5CF6">Tipo:</span>
                                    <span class="text-gray-600">${atividade.tipo}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #8B5CF6">Horas Máximas:</span>
                                    <span class="text-gray-600">${atividade.horas_max}h</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="verDetalhes(${atividade.id})"
                                        class="flex-1 px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200"
                                        style="color: #8B5CF6">
                                    Ver Detalhes
                                </button>
                                <button onclick="selecionarAtividade(${atividade.id})"
                                        class="flex-1 px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition duration-200"
                                        style="background-color: #8B5CF6">
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
                <h4 class="text-xl font-bold mb-4" style="color: #8B5CF6">${atividade.nome}</h4>
                <div class="space-y-3">
                    <div>
                        <span class="font-medium" style="color: #8B5CF6">Categoria:</span>
                        <span class="ml-2">${atividade.categoria}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #8B5CF6">Tipo:</span>
                        <span class="ml-2">${atividade.tipo}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #8B5CF6">Horas Máximas:</span>
                        <span class="ml-2">${atividade.horas_max} horas</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #8B5CF6">Descrição:</span>
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

        let atividadeSelecionada = null;

        function selecionarAtividade(id) {
            atividadeSelecionada = todasAtividades.find(a => a.id === id);
            if (!atividadeSelecionada) return;
            
            // Preencher informações da atividade no modal
            const infoDiv = document.getElementById('infoAtividadeSelecionada');
            infoDiv.innerHTML = `
                <div class="space-y-1">
                    <p><strong>Nome:</strong> ${atividadeSelecionada.nome}</p>
                    <p><strong>Categoria:</strong> ${atividadeSelecionada.categoria}</p>
                    <p><strong>Tipo:</strong> ${atividadeSelecionada.tipo}</p>
                    <p><strong>Horas Máximas:</strong> ${atividadeSelecionada.horas_max}h</p>
                </div>
            `;
            
            // Configurar limite máximo de horas
            const inputHoras = document.getElementById('horasRealizadas');
            const spanMaxHoras = document.getElementById('maxHoras');
            inputHoras.max = atividadeSelecionada.horas_max;
            spanMaxHoras.textContent = atividadeSelecionada.horas_max;
            
            // Ajustar rótulo e placeholder conforme o tipo de atividade
            const labelCurso = document.querySelector('label[for="cursoNome"]');
            const inputCurso = document.getElementById('cursoNome');
            const campoCurso = document.getElementById('campoCurso');
            // determinar se há necessidade do campo curso
            if (atividadeSelecionada.nome && atividadeSelecionada.nome.toLowerCase().includes('missões')) {
                // Esconde o campo de curso para missões
                campoCurso.classList.add('hidden');
                inputCurso.required = false;
            } else if (atividadeSelecionada.nome && /(evento|eventos|semin[áa]rio|simp[óo]sio|confer[êe]ncia|congresso|jornada|f[óo]rum|debate|visita|workshop|palestra|treinamento)/i.test(atividadeSelecionada.nome)) {
                campoCurso.classList.remove('hidden');
                labelCurso.textContent = 'Evento *';
                inputCurso.placeholder = 'Digite o nome do evento';
                inputCurso.required = true;
            } else {
                campoCurso.classList.remove('hidden');
                labelCurso.textContent = 'Curso *';
                inputCurso.placeholder = 'Digite o nome do curso';
                inputCurso.required = true;
            }
            
            // Limpar formulário
            document.getElementById('formSelecaoAtividade').reset();
            document.getElementById('arquivoSelecionado').classList.add('hidden');
            
            // Abrir modal de seleção
            document.getElementById('modalSelecao').classList.remove('hidden');
            document.getElementById('modalSelecao').classList.add('flex');
        }

        function abrirModalSelecao() {
            if (!atividadeSelecionada) return;
            fecharModal();
            selecionarAtividade(atividadeSelecionada.id);
        }

        function fecharModalSelecao() {
            document.getElementById('modalSelecao').classList.add('hidden');
            document.getElementById('modalSelecao').classList.remove('flex');
        }

        function mostrarArquivoSelecionado(input) {
            const arquivo = input.files[0];
            const divArquivo = document.getElementById('arquivoSelecionado');
            
            if (arquivo) {
                // Validar tamanho do arquivo (10MB)
                if (arquivo.size > 10 * 1024 * 1024) {
                    alert('O arquivo deve ter no máximo 10MB.');
                    input.value = '';
                    divArquivo.classList.add('hidden');
                    return;
                }
                
                // Validar tipo do arquivo
                const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!tiposPermitidos.includes(arquivo.type)) {
                    alert('Apenas arquivos PDF, JPG, JPEG e PNG são permitidos.');
                    input.value = '';
                    divArquivo.classList.add('hidden');
                    return;
                }
                
                divArquivo.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span>📄 ${arquivo.name}</span>
                        <span class="text-xs">(${(arquivo.size / 1024 / 1024).toFixed(2)} MB)</span>
                    </div>
                `;
                divArquivo.classList.remove('hidden');
            } else {
                divArquivo.classList.add('hidden');
            }
        }

        // Validação e envio do formulário
        document.getElementById('formSelecaoAtividade').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validar campos obrigatórios
            const horasRealizadas = document.getElementById('horasRealizadas').value;
            const dataInicio = document.getElementById('dataInicio').value;
            const dataFim = document.getElementById('dataFim').value;
            const local = document.getElementById('local').value;
            const declaracao = document.getElementById('declaracao').files[0];
            const cursoNome = document.getElementById('cursoNome').value.trim();
            const campoCurso = document.getElementById('campoCurso');
            
            if (!horasRealizadas || !dataInicio || !dataFim || !local || !declaracao || (!campoCurso.classList.contains('hidden') && !cursoNome)) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                return;
            }
            
            // Validar datas
            const inicio = new Date(dataInicio);
            const fim = new Date(dataFim);
            
            // Removida validação que impedia data início posterior à data fim
            
            // Removida validação que impedia datas futuras para dataFim
            
            // Validar horas
            if (parseInt(horasRealizadas) > parseInt(atividadeSelecionada.horas_max)) {
                alert(`As horas realizadas não podem exceder ${atividadeSelecionada.horas_max} horas.`);
                return;
            }
            
            // Preparar dados para envio
            const formData = new FormData();
            formData.append('atividade_disponivel_id', atividadeSelecionada.id);
            formData.append('horas_realizadas', horasRealizadas);
            formData.append('data_inicio', dataInicio);
            formData.append('data_fim', dataFim);
            formData.append('local_instituicao', local);
            formData.append('observacoes', document.getElementById('observacoes').value);
            if (!campoCurso.classList.contains('hidden')) {
                formData.append('curso_nome', cursoNome);
            }
            formData.append('declaracao', declaracao);
            
            // Desabilitar botão de envio
            const btnSubmit = e.target.querySelector('button[type="submit"]');
            const textoOriginal = btnSubmit.textContent;
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Enviando...';
            
            try {
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/atividade_complementar_acc.php', {
                    method: 'POST',
                    body: formData
                });
                
                // Não precisamos chamar response.json() novamente, pois o AuthClient.fetch já retorna os dados processados
                const result = response.data;
                
                if (result.success) {
                    alert('Atividade cadastrada com sucesso!');
                    fecharModalSelecao();
                    // Redirecionar para página de atividades do aluno
                    window.location.href = 'home_aluno.php';
                } else {
                    alert('Erro ao cadastrar atividade: ' + (result.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao enviar dados. Tente novamente.');
            } finally {
                // Reabilitar botão
                btnSubmit.disabled = false;
                btnSubmit.textContent = textoOriginal;
            }
        });
        
        // Removida validação que definia data mínima do campo dataFim
        
        // Removida validação que impedia data fim anterior à data início no evento change

        // Carregar atividades ao inicializar a página
        carregarAtividades();
    </script>
</body>
</html>