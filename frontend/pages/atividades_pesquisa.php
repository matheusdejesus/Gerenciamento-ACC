<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividades de Pesquisa - Sistema ACC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../assets/js/auth.js"></script>
    <script>
        // Verificar se o usuário está logado
        if (!localStorage.getItem('acc_jwt_token')) {
            window.location.href = 'login.php';
        }

        // Debug global para verificar se as funções estão sendo chamadas
        window.addEventListener('click', function(e) {
            if (e.target.tagName === 'BUTTON' && e.target.textContent.includes('Selecionar')) {
                console.log('🔴 CLICK DETECTADO NO BOTÃO SELECIONAR:', e.target);
                console.log('🔴 ONCLICK ATTRIBUTE:', e.target.getAttribute('onclick'));
            }
        });
    </script>
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
                    <div class="text-4xl mr-4">🔬</div>
                    <div>
                        <h2 class="text-3xl font-bold" style="color: #0969DA">Atividades de Pesquisa</h2>
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
                        <p class="text-sm text-red-700 mt-1">Não foi possível carregar as atividades de pesquisa. Tente novamente.</p>
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

    <!-- Modal de Seleção de Atividade -->
    <div id="modalSelecao" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" role="dialog" aria-labelledby="modalSelecaoTitulo" aria-modal="true">
        <div class="relative top-4 mx-auto p-6 border w-11/12 md:w-3/4 lg:w-1/2 xl:w-2/5 shadow-lg rounded-lg bg-white max-h-screen overflow-y-auto">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="modalSelecaoTitulo" class="text-xl font-semibold text-gray-900">Cadastrar Atividade de Pesquisa</h3>
                    <button onclick="fecharModalSelecao()" class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded" aria-label="Fechar modal">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="formSelecaoAtividade" class="space-y-6" novalidate>
                    <!-- Campo Evento -->
                    <div>
                        <label for="tema" class="block text-sm font-medium text-gray-700 mb-2">
                            Evento <span class="text-red-500" aria-label="obrigatório">*</span>
                        </label>
                        <input type="text" id="tema" name="tema" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            placeholder="Digite o nome do evento"
                            aria-describedby="tema-error tema-help">
                        <p id="tema-help" class="text-sm text-gray-500 mt-1">Informe o nome do evento</p>
                        <p id="tema-error" class="text-sm text-red-600 mt-1 hidden" role="alert"></p>
                    </div>

                    <!-- Campo Carga Horária -->
                    <div>
                        <label for="cargaHoraria" class="block text-sm font-medium text-gray-700 mb-2">
                            Carga Horária <span class="text-red-500" aria-label="obrigatório">*</span>
                        </label>
                        <input type="number" id="cargaHoraria" name="cargaHoraria" required min="1" max="999"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            placeholder="Digite a carga horária em horas"
                            aria-describedby="cargaHoraria-error cargaHoraria-help">
                        <p id="cargaHoraria-help" class="text-sm text-gray-500 mt-1">Informe a carga horária em horas (mínimo 1)</p>
                        <p class="text-xs text-gray-600 mt-1">Restante disponível: <span id="restantePesquisaSelecao">--</span> horas</p>
                        <p class="text-xs text-blue-700 mt-1" id="sugestoesPesquisaSelecao">Sugestões: --</p>
                        <p class="text-xs font-medium mt-1 hidden" id="mensagemLimitePesquisaSelecao" style="color:#DC2626"></p>
                        <p id="cargaHoraria-error" class="text-sm text-red-600 mt-1 hidden" role="alert"></p>
                    </div>

                    <!-- Campo Quantidade de Apresentações -->
                    <div>
                        <label for="quantidade" class="block text-sm font-medium text-gray-700 mb-2">
                            Quantidade de Apresentações <span class="text-red-500" aria-label="obrigatório">*</span>
                        </label>
                        <input type="number" id="quantidade" name="quantidade" required min="1" max="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            placeholder="1"
                            aria-describedby="quantidade-error quantidade-help">
                        <p id="quantidade-help" class="text-sm text-gray-500 mt-1">Informe quantas apresentações foram realizadas (mínimo 1)</p>
                        <p id="quantidade-error" class="text-sm text-red-600 mt-1 hidden" role="alert"></p>
                    </div>

                    <!-- Campo Comprovante -->
                    <div>
                        <label for="comprovante" class="block text-sm font-medium text-gray-700 mb-2">
                            Comprovante <span class="text-red-500" aria-label="obrigatório">*</span>
                        </label>
                        <div class="relative">
                            <input type="file" id="comprovante" name="comprovante" required
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                aria-describedby="comprovante-error comprovante-help">
                        </div>
                        <p id="comprovante-help" class="text-sm text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG. Tamanho máximo: 5MB</p>
                        <p id="comprovante-error" class="text-sm text-red-600 mt-1 hidden" role="alert"></p>
                        <div id="arquivo-info" class="hidden mt-2 p-2 bg-green-50 border border-green-200 rounded text-sm text-green-700">
                            <span id="arquivo-nome"></span> (<span id="arquivo-tamanho"></span>)
                        </div>
                    </div>
                </form>

                <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                    <button type="button" onclick="fecharModalSelecao()"
                        class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-200">
                        Cancelar
                    </button>
                    <button type="button" id="btnConfirmar" disabled
                        class="px-6 py-2 text-white rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background-color: #0969DA"
                        onclick="confirmarSelecao()">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Evento Científico -->
    <div id="modalEventoCientifico" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" role="dialog" aria-labelledby="modalEventoCientificoTitulo" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                <!-- Cabeçalho do Modal -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h2 id="modalEventoCientificoTitulo" class="text-xl font-semibold text-gray-900">
                        Apresentação em Eventos Científicos
                    </h2>
                    <button onclick="fecharModalEventoCientifico()" class="text-gray-400 hover:text-gray-600 transition-colors" aria-label="Fechar modal">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Conteúdo do Modal -->
                <div class="p-6">
                    <form id="formEventoCientifico" class="space-y-6">
                        <!-- Tema da Apresentação -->
                        <div>
                            <label for="temaApresentacao" class="block text-sm font-medium text-gray-700 mb-2">
                                Tema da Apresentação <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="temaApresentacao" name="temaApresentacao"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                placeholder="Digite o tema da apresentação"
                                oninput="validarCampoEvento('temaApresentacao')" required>
                            <div id="temaApresentacao-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                            <p class="text-xs text-gray-500 mt-1">Informe o tema principal da sua apresentação</p>
                        </div>

                        <!-- Quantidade de Apresentações -->
                        <div>
                            <label for="quantidadeApresentacoes" class="block text-sm font-medium text-gray-700 mb-2">
                                Quantidade de Apresentações <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="quantidadeApresentacoes" name="quantidadeApresentacoes" min="1" max="999"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                placeholder="Ex: 2"
                                oninput="validarCampoEvento('quantidadeApresentacoes'); atualizarCargaHorariaEvento();" required>
                            <div id="quantidadeApresentacoes-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                            <p class="text-xs text-gray-500 mt-1">Informe quantas apresentações foram realizadas (mínimo 1)</p>
                        </div>

                        <!-- Local da Apresentação -->
                        <div>
                            <label for="localApresentacao" class="block text-sm font-medium text-gray-700 mb-2">
                                Local <span class="text-red-500">*</span>
                            </label>
                            <select id="localApresentacao" name="localApresentacao"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                onchange="validarCampoEvento('localApresentacao'); atualizarCargaHorariaEvento();" required>
                                <option value="">Selecione o local</option>
                                <option value="nacional">Nacional</option>
                                <option value="internacional">Internacional</option>
                            </select>
                            <div id="localApresentacao-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                            <p class="text-xs text-gray-500 mt-1">Selecione se a apresentação foi nacional ou internacional</p>
                        </div>

                        <!-- Carga Horária Total -->
                        <div>
                            <label for="cargaHorariaEvento" class="block text-sm font-medium text-gray-700 mb-2">
                                Carga Horária Total (em horas) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="cargaHorariaEvento" name="cargaHorariaEvento" min="0.5" max="999" step="0.5"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-gray-100"
                                placeholder="Calculado automaticamente"
                                readonly required>
                            <div id="cargaHorariaEvento-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                            <p class="text-xs text-gray-500 mt-1">Calculado automaticamente baseado na quantidade e local das apresentações</p>
                            <p class="text-xs text-gray-600 mt-1">Restante disponível: <span id="restantePesquisaEvento">--</span> horas</p>
                            <p class="text-xs text-blue-700 mt-1" id="sugestoesPesquisaEvento">Sugestões: --</p>
                            <p class="text-xs font-medium mt-1 hidden" id="mensagemLimitePesquisaEvento" style="color:#DC2626"></p>
                        </div>

                        <!-- Declaração Comprobatória -->
                        <div>
                            <label for="declaracao" class="block text-sm font-medium text-gray-700 mb-2">
                                Declaração Comprobatória <span class="text-red-500">*</span>
                            </label>
                            <input type="file" id="declaracao" name="declaracao"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                accept=".pdf,.jpg,.jpeg,.png"
                                onchange="validarCampoEvento('declaracao')" required>
                            <div id="declaracao-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                            <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG. Tamanho máximo: 5MB</p>

                            <!-- Informações do arquivo selecionado -->
                            <div id="declaracao-info" class="mt-2 hidden">
                                <div class="bg-green-50 border border-green-200 rounded-md p-3">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-sm text-green-800">
                                            Arquivo: <span id="declaracao-nome"></span> (<span id="declaracao-tamanho"></span>)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Rodapé do Modal -->
                <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                    <button onclick="fecharModalEventoCientifico()"
                        class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        Cancelar
                    </button>
                    <button id="btnConfirmarEvento" onclick="confirmarEventoCientifico()" disabled
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cadastro de Evento -->
    <div id="modalCadastroEvento" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-0 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
            <!-- Cabeçalho do Modal -->
            <div class="flex justify-between items-center p-6 border-b bg-blue-50">
                <h3 class="text-xl font-semibold text-gray-900">Cadastrar Evento</h3>
                <button onclick="fecharModalCadastroEvento()"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md p-1"
                    aria-label="Fechar modal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Corpo do Modal -->
            <div class="p-6">
                <form id="formCadastroEvento" class="space-y-6">
                    <!-- Nome do Evento -->
                    <div>
                        <label for="nomeEventoCadastro" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Evento <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nomeEventoCadastro" name="nomeEventoCadastro"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                            placeholder="Digite o nome do evento"
                            oninput="validarCampoCadastro('nomeEventoCadastro')" required>
                        <div id="nomeEventoCadastro-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                        <p class="text-xs text-gray-500 mt-1">Informe o nome completo do evento científico ou profissional</p>
                    </div>

                    <!-- Carga Horária Total -->
                    <div>
                        <label for="cargaHorariaCadastro" class="block text-sm font-medium text-gray-700 mb-2">
                            Carga Horária Total (em horas) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="cargaHorariaCadastro" name="cargaHorariaCadastro" min="0.5" max="40" step="0.5"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                            placeholder="Ex: 20.5"
                            oninput="validarCampoCadastro('cargaHorariaCadastro')" required>
                        <div id="cargaHorariaCadastro-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                        <p class="text-xs text-gray-500 mt-1">Informe a carga horária total em horas (aceita valores decimais, máximo 40h)</p>
                    </div>

                    <!-- Declaração Comprobatória -->
                    <div>
                        <label for="declaracaoCadastro" class="block text-sm font-medium text-gray-700 mb-2">
                            Declaração Comprobatória <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="declaracaoCadastro" name="declaracaoCadastro"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            accept=".pdf,.jpg,.jpeg,.png"
                            onchange="validarCampoCadastro('declaracaoCadastro')" required>
                        <div id="declaracaoCadastro-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                        <div id="arquivo-info-cadastro" class="mt-2 p-3 bg-green-50 border border-green-200 rounded-md hidden">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-green-800" id="arquivo-nome-cadastro"></p>
                                    <p class="text-xs text-green-600" id="arquivo-tamanho-cadastro"></p>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG. Tamanho máximo: 5MB</p>
                    </div>
                </form>
            </div>

            <!-- Rodapé do Modal -->
            <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                <button onclick="fecharModalCadastroEvento()"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    Cancelar
                </button>
                <button id="btnConfirmarCadastro" onclick="confirmarCadastroEvento()" disabled
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                    Confirmar
                </button>
            </div>
        </div>
    </div>



    <!-- Modal de Iniciação Científica -->
    <div id="modalIniciacaoCientifica" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-0 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
            <!-- Cabeçalho do Modal -->
            <div class="flex justify-between items-center p-6 border-b bg-blue-50">
                <h3 class="text-xl font-semibold text-gray-900">Cadastrar Projeto de Iniciação Científica</h3>
                <button onclick="fecharModalIniciacaoCientifica()"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md p-1"
                    aria-label="Fechar modal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Corpo do Modal -->
            <div class="p-6">
                <form id="formIniciacaoCientifica" class="space-y-6">
                    <!-- Projeto -->
                    <div>
                        <label for="nomeProjeto" class="block text-sm font-medium text-gray-700 mb-2">
                            Projeto <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nomeProjeto" name="nomeProjeto"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                            placeholder="Digite o nome do projeto"
                            oninput="validarCampoIniciacaoCientifica('nomeProjeto')" required>
                        <div id="nomeProjeto-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                    </div>

                    <!-- Carga Horária -->
                    <div>
                        <label for="cargaHorariaProjeto" class="block text-sm font-medium text-gray-700 mb-2">
                            Carga Horária <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="cargaHorariaProjeto" name="cargaHorariaProjeto" min="1" max="999"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                            placeholder="Digite a carga horária em horas"
                            oninput="validarCampoIniciacaoCientifica('cargaHorariaProjeto')" required>
                        <div id="cargaHorariaProjeto-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                        <p class="text-xs text-gray-600 mt-1">Restante disponível: <span id="restantePesquisaProjeto">--</span> horas</p>
                        <p class="text-xs text-blue-700 mt-1" id="sugestoesPesquisaProjeto">Sugestões: --</p>
                        <p class="text-xs font-medium mt-1 hidden" id="mensagemLimitePesquisaProjeto" style="color:#DC2626"></p>
                    </div>
                    <!-- Declaração/Certificado -->
                    <div>
                        <label for="declaracaoProjeto" class="block text-sm font-medium text-gray-700 mb-2">
                            Declaração/Certificado <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="declaracaoProjeto" name="declaracaoProjeto"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            accept=".pdf,.jpg,.jpeg,.png"
                            onchange="validarCampoIniciacaoCientifica('declaracaoProjeto')" required>
                        <div id="declaracaoProjeto-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                        <div id="arquivo-info-projeto" class="mt-2 p-3 bg-green-50 border border-green-200 rounded-md hidden">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-green-800" id="arquivo-nome-projeto"></p>
                                    <p class="text-xs text-green-600" id="arquivo-tamanho-projeto"></p>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG. Tamanho máximo: 5MB</p>
                    </div>
                </form>
            </div>

            <!-- Rodapé do Modal -->
            <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                <button onclick="fecharModalIniciacaoCientifica()"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    Cancelar
                </button>
                <button id="btnConfirmarProjeto" onclick="confirmarIniciacaoCientifica()" disabled
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Publicação de Artigo -->
    <div id="modalPublicacaoArtigo" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-0 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-lg bg-white">
            <!-- Cabeçalho do Modal -->
            <div class="p-6 border-b bg-blue-50">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Publicação de artigo em anais, periódicos ou capítulo de livro</h2>
                    <button onclick="fecharModalPublicacaoArtigo()" class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Corpo do Modal -->
            <div class="p-6">
                <form id="formPublicacaoArtigo" class="space-y-6">
                    <!-- Artigo -->
                    <div>
                        <label for="nomeArtigo" class="block text-sm font-medium text-gray-700 mb-2">
                            Artigo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nomeArtigo" name="nomeArtigo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                            placeholder="Digite o título do artigo"
                            oninput="validarCampoPublicacaoArtigo('nomeArtigo')" required>
                        <div id="nomeArtigo-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                    </div>

                    <!-- Quantidade de Publicações -->
                    <div>
                        <label for="quantidadePublicacoes" class="block text-sm font-medium text-gray-700 mb-2">
                            Quantidade de Publicações <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="quantidadePublicacoes" name="quantidadePublicacoes" min="1" max="999"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                            placeholder="Digite a quantidade de publicações"
                            oninput="calcularCargaHorariaArtigo(); validarCampoPublicacaoArtigo('quantidadePublicacoes')" required>
                        <div id="quantidadePublicacoes-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                        <p class="text-xs text-gray-500 mt-1">Quantidade ilimitada, máximo 40h total</p>
                    </div>

                    <!-- Carga Horária -->
                    <div>
                        <label for="cargaHorariaArtigo" class="block text-sm font-medium text-gray-700 mb-2">
                            Carga Horária <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="cargaHorariaArtigo" name="cargaHorariaArtigo" min="10" max="40"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                            placeholder="Calculado automaticamente baseado na quantidade"
                            readonly required>
                        <div id="cargaHorariaArtigo-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                        <p class="text-xs text-gray-600 mt-1">Restante disponível: <span id="restantePesquisaArtigo">--</span> horas</p>
                        <p class="text-xs text-blue-700 mt-1" id="sugestoesPesquisaArtigo">Sugestões: --</p>
                        <p class="text-xs font-medium mt-1 hidden" id="mensagemLimitePesquisaArtigo" style="color:#DC2626"></p>

                        <!-- Exibição do cálculo automático -->
                        <div id="calculo-artigo-info" class="mt-2 hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span id="calculo-artigo-detalhes" class="text-sm font-medium text-blue-800"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Aviso de limitação -->
                        <div id="calculo-artigo-warning" class="mt-2 hidden">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-yellow-800">Carga horária limitada ao máximo de 40h</span>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Declaração/Certificado -->
                    <div>
                        <label for="declaracaoArtigo" class="block text-sm font-medium text-gray-700 mb-2">
                            Declaração/Certificado <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="declaracaoArtigo" name="declaracaoArtigo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            accept=".pdf,.jpg,.jpeg,.png"
                            onchange="validarCampoPublicacaoArtigo('declaracaoArtigo')" required>
                        <div id="declaracaoArtigo-error" class="text-red-500 text-sm mt-1 hidden" role="alert"></div>
                        <div id="arquivo-info-artigo" class="mt-2 p-3 bg-green-50 border border-green-200 rounded-md hidden">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-green-800" id="arquivo-nome-artigo"></p>
                                    <p class="text-xs text-green-600" id="arquivo-tamanho-artigo"></p>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG. Tamanho máximo: 5MB</p>
                    </div>
                </form>
            </div>

            <!-- Rodapé do Modal -->
            <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                <button onclick="fecharModalPublicacaoArtigo()"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    Cancelar
                </button>
                <button id="btnConfirmarArtigo" onclick="confirmarPublicacaoArtigo()" disabled
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                    Confirmar
                </button>
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
                    <button id="btnSelecionarModal" class="px-4 py-2 text-white rounded-lg hover:opacity-90 transition duration-200" style="background-color: #0969DA">
                        Selecionar Atividade
                    </button>
                </div>
            </div>
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

        // Carregar atividades de pesquisa via JWT
        let todasAtividades = [];

        async function obterRestantePesquisa(aprId) {
            try {
                const resp = await AuthClient.fetch('../../backend/api/routes/listar_atividades_disponiveis.php?acao=enviadas&limite=200');
                const json = await resp.json();
                const lista = json?.data?.atividades || [];
                const relevantes = lista.filter(a => parseInt(a.atividades_por_resolucao_id) === parseInt(aprId) && ['aprovado','aprovada'].includes(String(a.status).toLowerCase()));
                const soma = relevantes.reduce((acc, a) => acc + (parseInt(a.ch_atribuida||0)||0), 0);
                const max = (todasAtividades.find(x => (x.id === aprId || x.atividades_por_resolucao_id === aprId))?.horas_max)
                    || (todasAtividades.find(x => (x.id === aprId || x.atividades_por_resolucao_id === aprId))?.carga_horaria_maxima)
                    || 0;
                const restante = Math.max(0, max - soma);
                return { restante, max };
            } catch (e) {
                const max = (todasAtividades.find(x => (x.id === aprId || x.atividades_por_resolucao_id === aprId))?.horas_max)
                    || (todasAtividades.find(x => (x.id === aprId || x.atividades_por_resolucao_id === aprId))?.carga_horaria_maxima)
                    || 0;
                return { restante: max, max };
            }
        }

        function gerarSugestoesPesquisa(restante, minCadastro, maxCadastro) {
            const sugs = [];
            const r = parseInt(restante)||0; const min = Math.max(1, parseInt(minCadastro)||1); const max = Math.max(min, parseInt(maxCadastro)||min);
            if (r <= 0) return '';
            if (r % max === 0) sugs.push(`${r/max}x${max}h`);
            if (r % min === 0) sugs.push(`${r/min}x${min}h`);
            for (let k = Math.floor(r/max); k >= 1 && sugs.length < 3; k--) {
                const left = r - k*max;
                if (left >= 0 && left % min === 0) sugs.push(`${k}x${max}h + ${left/min}x${min}h`);
            }
            if (!sugs.length) sugs.push(`${r}h`);
            return sugs.join(', ');
        }

        async function aplicarLimitesPesquisaSelecao(aprId) {
            const dados = await obterRestantePesquisa(aprId);
            const input = document.getElementById('cargaHoraria');
            if (input) input.max = dados.restante;
            if (input) input.min = dados.restante === 0 ? 0 : 1;
            const restanteEl = document.getElementById('restantePesquisaSelecao');
            if (restanteEl) restanteEl.textContent = dados.restante;
            const sugEl = document.getElementById('sugestoesPesquisaSelecao');
            if (sugEl) sugEl.textContent = `Sugestões: ${gerarSugestoesPesquisa(dados.restante, 1, dados.restante) || '--'}`;
            const msg = document.getElementById('mensagemLimitePesquisaSelecao');
            const submitBtn = document.querySelector('#formSelecao button[type="submit"]');
            if (msg) {
                if (dados.restante === 0) { msg.textContent = 'Você atingiu o limite de horas para esta atividade.'; msg.classList.remove('hidden'); if (input) { input.disabled = true; } if (submitBtn) submitBtn.disabled = true; }
                else { msg.classList.add('hidden'); if (input) { input.disabled = false; } if (submitBtn) submitBtn.disabled = false; }
            }
        }

        async function aplicarLimitesPesquisaEvento(aprId) {
            const dados = await obterRestantePesquisa(aprId);
            window.__ultimoRestantePesquisaEvento = dados;
            const restanteEl = document.getElementById('restantePesquisaEvento');
            const sugEl = document.getElementById('sugestoesPesquisaEvento');
            if (restanteEl) restanteEl.textContent = dados.restante;
            if (sugEl) sugEl.textContent = `Sugestões: ${gerarSugestoesPesquisa(dados.restante, 1, dados.restante) || '--'}`;
            const msg = document.getElementById('mensagemLimitePesquisaEvento');
            const submitBtn = document.getElementById('btnConfirmarEvento');
            if (msg) {
                if (dados.restante === 0) { msg.textContent = 'Você atingiu o limite de horas para esta atividade.'; msg.classList.remove('hidden'); if (submitBtn) submitBtn.disabled = true; }
                else { msg.classList.add('hidden'); if (submitBtn) submitBtn.disabled = false; }
            }
        }

        async function aplicarLimitesPesquisaProjeto(aprId) {
            const dados = await obterRestantePesquisa(aprId);
            window.__ultimoRestantePesquisaProjeto = dados;
            const campo = document.getElementById('cargaHorariaProjeto');
            if (campo) campo.max = dados.restante;
            if (campo) campo.min = dados.restante === 0 ? 0 : 1;
            const restanteEl = document.getElementById('restantePesquisaProjeto');
            const sugEl = document.getElementById('sugestoesPesquisaProjeto');
            if (restanteEl) restanteEl.textContent = dados.restante;
            if (sugEl) sugEl.textContent = `Sugestões: ${gerarSugestoesPesquisa(dados.restante, 1, dados.restante) || '--'}`;
            const msg = document.getElementById('mensagemLimitePesquisaProjeto');
            const submitBtn = document.getElementById('btnConfirmarProjeto');
            if (msg) {
                if (dados.restante === 0) { msg.textContent = 'Você atingiu o limite de horas para esta atividade.'; msg.classList.remove('hidden'); if (campo) campo.disabled = true; if (submitBtn) submitBtn.disabled = true; }
                else { msg.classList.add('hidden'); if (campo) campo.disabled = false; if (submitBtn) submitBtn.disabled = false; }
            }
        }

        async function aplicarLimitesPesquisaArtigo(aprId) {
            const dados = await obterRestantePesquisa(aprId);
            window.__ultimoRestantePesquisaArtigo = dados;
            const restanteEl = document.getElementById('restantePesquisaArtigo');
            const sugEl = document.getElementById('sugestoesPesquisaArtigo');
            if (restanteEl) restanteEl.textContent = dados.restante;
            if (sugEl) sugEl.textContent = `Sugestões: ${gerarSugestoesPesquisa(dados.restante, 1, dados.restante) || '--'}`;
            const msg = document.getElementById('mensagemLimitePesquisaArtigo');
            const submitBtn = document.getElementById('btnConfirmarArtigo');
            const inputCarga = document.getElementById('cargaHorariaArtigo');
            if (msg) {
                if (dados.restante === 0) { msg.textContent = 'Você atingiu o limite de horas para esta atividade.'; msg.classList.remove('hidden'); if (submitBtn) submitBtn.disabled = true; if (inputCarga) inputCarga.disabled = true; }
                else { msg.classList.add('hidden'); if (submitBtn) submitBtn.disabled = false; if (inputCarga) inputCarga.disabled = false; }
            }
        }

        async function carregarAtividades() {
            try {
                console.log('🔍 Carregando atividades de pesquisa...');

                const params = new URLSearchParams({
                    type: 'pesquisa',
                    pagina: 1,
                    limite: 20
                });

                const response = await AuthClient.fetch(`../../backend/api/routes/listar_atividades_disponiveis.php?${params}`, {
                    method: 'GET'
                });
                const data = await response.json();
                console.log('📊 Resposta da API:', data);

                if (data.success || data.sucesso) {
                    todasAtividades = data.data?.atividades || [];
                    try {
                        const user = AuthClient.getUser() || {};
                        const cursoNomeNorm = (user.curso_nome || '').toString().trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        const isBSI = (user.curso_id === 2) || cursoNomeNorm.includes('sistemas de informacao') || cursoNomeNorm.includes('si') || cursoNomeNorm.includes('bsi');
                        if (isBSI) {
                            const RTA_PESQUISA_BSI18 = 11;
                            const baseSI = [
                                { acId: 22, nome: 'Atividades de iniciação científica', horas: 45, desc: 'Participação em projetos de iniciação científica' },
                                { acId: 23, nome: 'Apresentação em eventos científicos', horas: 30, desc: 'Apresentação de trabalhos em eventos científicos' },
                                { acId: 24, nome: 'Publicação de artigo em periódicos ou capítulo de livro', horas: 60, desc: 'Publicação científica' }
                            ];
                            const orig = data.data?.atividades || [];
                            const porRta = orig.filter(a => a.resolucao_tipo_atividade_id === RTA_PESQUISA_BSI18);
                            let listaSI = porRta.length ? porRta : [];
                            if (!listaSI.length) {
                                const porIds = [];
                                for (const item of baseSI) {
                                    const encontrado = orig.find(a => a.atividade_complementar_id === item.acId);
                                    if (encontrado) {
                                        encontrado.carga_horaria_maxima = item.horas;
                                        encontrado.horas_max = item.horas;
                                        encontrado.descricao = encontrado.descricao || item.desc;
                                        encontrado.categoria = encontrado.categoria || 'Pesquisa';
                                        encontrado.tipo = encontrado.tipo || 'Pesquisa';
                                        encontrado.resolucao_tipo_atividade_id = encontrado.resolucao_tipo_atividade_id || RTA_PESQUISA_BSI18;
                                        porIds.push(encontrado);
                                    } else {
                                        porIds.push({
                                            id: item.acId,
                                            atividade_complementar_id: item.acId,
                                            nome: item.nome,
                                            categoria: 'Pesquisa',
                                            tipo: 'Pesquisa',
                                            descricao: item.desc,
                                            carga_horaria_maxima: item.horas,
                                            horas_max: item.horas,
                                            resolucao_tipo_atividade_id: RTA_PESQUISA_BSI18
                                        });
                                    }
                                }
                                listaSI = porIds;
                            }
                            const ordemSI = {
                                'Apresentação em eventos científicos': 1,
                                'Atividades de iniciação científica': 2,
                                'Publicação de artigo em periódicos ou capítulo de livro': 3
                            };
                            listaSI.sort((a,b) => (ordemSI[a.nome]||99) - (ordemSI[b.nome]||99));
                            todasAtividades = listaSI;
                        }
                    } catch (regraErr) {
                        console.warn('Falha ao aplicar regra BSI18 em Pesquisa:', regraErr);
                    }
                    console.log('✅ Atividades carregadas:', todasAtividades.length);
                renderizarAtividades();
                document.getElementById('alertaAtividades').classList.add('hidden');
                try {
                    const resp = await AuthClient.fetch('../../backend/api/routes/calcular_horas_categorias.php', { method: 'POST' });
                    const json = await resp.json();
                    const categorias = json?.data?.categorias || {}; const limites = json?.data?.limites || {};
                    const atual = categorias['pesquisa'] || 0; const lim = limites['pesquisa'] || 0;
                    if (lim > 0 && atual >= lim) {
                    }
                } catch (e) {}
                } else {
                    console.error('❌ Erro ao carregar atividades:', data.message || data.erro);
                    document.getElementById('alertaAtividades').classList.remove('hidden');
                }
            } catch (e) {
                console.error('❌ Erro na requisição:', e);
                document.getElementById('alertaAtividades').classList.remove('hidden');
            }
        }

        function renderizarAtividades() {
            const container = document.getElementById('atividadesContainer');
            if (!todasAtividades.length) {
                container.innerHTML = `<div class="text-center py-12">
                    <div class="text-6xl mb-4">🔬</div>
                    <p class="text-gray-500 text-lg mb-2">Nenhuma atividade de pesquisa encontrada.</p>
                    <p class="text-gray-400 text-sm">Entre em contato com a coordenação para mais informações.</p>
                </div>`;
                return;
            }

            container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ${todasAtividades.map(atividade => {
                    // Determinar qual função chamar baseado no nome da atividade
                    let funcaoSelecionar = '';
                    const nomeAtividade = atividade.nome.toLowerCase().trim();
                    
                    // Comparações mais flexíveis usando includes() para capturar variações
                    if (nomeAtividade.includes('membro efetivo') && nomeAtividade.includes('eventos científicos')) {
                        funcaoSelecionar = `abrirModalCadastroEvento(${atividade.id})`;
                    } else if (nomeAtividade.includes('apresentação') && nomeAtividade.includes('eventos científicos')) {
                        funcaoSelecionar = `abrirModalEventoCientifico(${atividade.id})`;
                    } else if (nomeAtividade.includes('participação') && nomeAtividade.includes('iniciação científica')) {
                        funcaoSelecionar = `abrirModalIniciacaoCientifica(${atividade.id})`;
                    } else if (nomeAtividade.includes('atividades') && nomeAtividade.includes('iniciação científica')) {
                        funcaoSelecionar = `selecionarAtividadeIniciacaoCientifica(${atividade.id})`;
                    } else if (nomeAtividade.includes('publicação') && (nomeAtividade.includes('artigo') || nomeAtividade.includes('periódicos') || nomeAtividade.includes('capítulo'))) {
                        funcaoSelecionar = `abrirModalPublicacaoArtigo(${atividade.id})`;
                    } else {
                        funcaoSelecionar = `abrirModalSelecao(${atividade.id})`;
                    }
                    
                    return `
                <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <div class="p-4" style="background-color: #0969DA">
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
                            <button onclick="console.log('🔴 BOTÃO CLICADO - Atividade:', '${atividade.nome}', 'ID:', ${atividade.id}); ${funcaoSelecionar}" 
                                class="flex-1 px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition duration-200" 
                                style="background-color: #0969DA">
                                Selecionar
                            </button>
                        </div>
                    </div>
                </div>
            `;
                }).join('')}
            </div>`;
        }

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

        // Variáveis globais para o modal de seleção
        let atividadeSelecionadaId = null;
        let elementoAnteriorFoco = null;

        // Função para abrir modal de seleção
        function abrirModalSelecao(id) {
            atividadeSelecionadaId = id;
            elementoAnteriorFoco = document.activeElement;

            const modal = document.getElementById('modalSelecao');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Focar no primeiro campo
            setTimeout(() => {
                document.getElementById('tema').focus();
            }, 100);

            // Limpar formulário
            limparFormulario();

            // Adicionar listeners de eventos
            adicionarEventListeners();

            aplicarLimitesPesquisaSelecao(id);
        }

        // Função para fechar modal de seleção
        function fecharModalSelecao() {
            const modal = document.getElementById('modalSelecao');
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // Restaurar foco
            if (elementoAnteriorFoco) {
                elementoAnteriorFoco.focus();
            }

            // Limpar dados
            atividadeSelecionadaId = null;
            limparFormulario();
        }

        // Função para limpar formulário
        function limparFormulario() {
            const form = document.getElementById('formSelecaoAtividade');
            form.reset();

            // Limpar mensagens de erro
            const erros = form.querySelectorAll('[id$="-error"]');
            erros.forEach(erro => {
                erro.classList.add('hidden');
                erro.textContent = '';
            });

            // Resetar estilos dos campos
            const campos = form.querySelectorAll('input');
            campos.forEach(campo => {
                campo.classList.remove('border-red-500', 'border-green-500');
                campo.classList.add('border-gray-300');
            });

            // Ocultar info do arquivo
            document.getElementById('arquivo-info').classList.add('hidden');

            // Desabilitar botão confirmar
            document.getElementById('btnConfirmar').disabled = true;
        }

        // Função para adicionar event listeners
        function adicionarEventListeners() {
            const campos = ['tema', 'local', 'quantidade', 'comprovante'];

            campos.forEach(campo => {
                const elemento = document.getElementById(campo);
                elemento.addEventListener('input', validarCampo);
                elemento.addEventListener('blur', validarCampo);
            });
        }

        // Função de validação em tempo real
        function validarCampo(event) {
            const campo = event.target;
            const valor = campo.value.trim();
            const nome = campo.name;
            const errorElement = document.getElementById(`${nome}-error`);

            let valido = true;
            let mensagem = '';

            // Validações específicas por campo
            switch (nome) {
                case 'tema':
                    if (!valor) {
                        valido = false;
                        mensagem = 'O tema é obrigatório';
                    } else if (valor.length < 3) {
                        valido = false;
                        mensagem = 'O tema deve ter pelo menos 3 caracteres';
                    }
                    break;

                case 'local':
                    if (!valor) {
                        valido = false;
                        mensagem = 'O local/instituição é obrigatório';
                    } else if (valor.length < 2) {
                        valido = false;
                        mensagem = 'O local deve ter pelo menos 2 caracteres';
                    }
                    break;

                case 'quantidade':
                    const num = parseInt(valor);
                    if (!valor) {
                        valido = false;
                        mensagem = 'A quantidade é obrigatória';
                    } else if (isNaN(num) || num < 1) {
                        valido = false;
                        mensagem = 'A quantidade deve ser um número positivo';
                    } else if (num > 100) {
                        valido = false;
                        mensagem = 'A quantidade não pode ser maior que 100';
                    }
                    break;

                case 'comprovante':
                    if (!campo.files || campo.files.length === 0) {
                        valido = false;
                        mensagem = 'O comprovante é obrigatório';
                    } else {
                        const arquivo = campo.files[0];
                        const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                        const tamanhoMaximo = 5 * 1024 * 1024; // 5MB

                        if (!tiposPermitidos.includes(arquivo.type)) {
                            valido = false;
                            mensagem = 'Formato não permitido. Use PDF, JPG ou PNG';
                        } else if (arquivo.size > tamanhoMaximo) {
                            valido = false;
                            mensagem = 'Arquivo muito grande. Máximo 5MB';
                        } else {
                            // Mostrar informações do arquivo
                            mostrarInfoArquivo(arquivo);
                        }
                    }
                    break;
            }

            // Aplicar estilos visuais
            if (valido) {
                campo.classList.remove('border-red-500');
                campo.classList.add('border-green-500');
                errorElement.classList.add('hidden');
                errorElement.textContent = '';
            } else {
                campo.classList.remove('border-green-500');
                campo.classList.add('border-red-500');
                errorElement.classList.remove('hidden');
                errorElement.textContent = mensagem;

                if (nome === 'comprovante') {
                    document.getElementById('arquivo-info').classList.add('hidden');
                }
            }

            // Verificar se todos os campos são válidos
            verificarFormularioValido();
        }

        // Função para mostrar informações do arquivo
        function mostrarInfoArquivo(arquivo) {
            const nomeElement = document.getElementById('arquivo-nome');
            const tamanhoElement = document.getElementById('arquivo-tamanho');
            const infoElement = document.getElementById('arquivo-info');

            nomeElement.textContent = arquivo.name;
            tamanhoElement.textContent = formatarTamanhoArquivo(arquivo.size);
            infoElement.classList.remove('hidden');
        }

        // Função para formatar tamanho do arquivo
        function formatarTamanhoArquivo(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Função para verificar se o formulário é válido
        function verificarFormularioValido() {
            const tema = document.getElementById('tema').value.trim();
            const cargaHoraria = document.getElementById('cargaHoraria').value;
            const quantidade = document.getElementById('quantidade').value;
            const comprovante = document.getElementById('comprovante').files;

            const temaValido = tema.length >= 3;
            const cargaHorariaValida = cargaHoraria && parseInt(cargaHoraria) >= 1 && parseInt(cargaHoraria) <= 999;
            const quantidadeValida = quantidade && parseInt(quantidade) >= 1 && parseInt(quantidade) <= 100;
            const comprovanteValido = comprovante && comprovante.length > 0;

            const formularioValido = temaValido && cargaHorariaValida && quantidadeValida && comprovanteValido;

            document.getElementById('btnConfirmar').disabled = !formularioValido;
        }

        // Função para validar campo específico na seleção
        function validarCampoSelecao(nomeCampo) {
            const campo = document.getElementById(nomeCampo);
            const valor = campo.value.trim();
            const errorElement = document.getElementById(`${nomeCampo}-error`);

            let valido = true;
            let mensagem = '';

            // Validações específicas por campo
            switch (nomeCampo) {
                case 'tema':
                    if (!valor) {
                        valido = false;
                        mensagem = 'O tema é obrigatório';
                    } else if (valor.length < 3) {
                        valido = false;
                        mensagem = 'O tema deve ter pelo menos 3 caracteres';
                    }
                    break;

                case 'local':
                    if (!valor) {
                        valido = false;
                        mensagem = 'O local/instituição é obrigatório';
                    } else if (valor.length < 2) {
                        valido = false;
                        mensagem = 'O local deve ter pelo menos 2 caracteres';
                    }
                    break;

                case 'quantidade':
                    const num = parseInt(valor);
                    if (!valor) {
                        valido = false;
                        mensagem = 'A quantidade é obrigatória';
                    } else if (isNaN(num) || num < 1) {
                        valido = false;
                        mensagem = 'A quantidade deve ser um número positivo';
                    } else if (num > 100) {
                        valido = false;
                        mensagem = 'A quantidade não pode ser maior que 100';
                    }
                    break;

                case 'comprovante':
                    if (!campo.files || campo.files.length === 0) {
                        valido = false;
                        mensagem = 'O comprovante é obrigatório';
                    } else {
                        const arquivo = campo.files[0];
                        const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                        const tamanhoMaximo = 5 * 1024 * 1024; // 5MB

                        if (!tiposPermitidos.includes(arquivo.type)) {
                            valido = false;
                            mensagem = 'Formato não permitido. Use PDF, JPG ou PNG';
                        } else if (arquivo.size > tamanhoMaximo) {
                            valido = false;
                            mensagem = 'Arquivo muito grande. Máximo 5MB';
                        }
                    }
                    break;
            }

            // Aplicar estilos visuais se houver erro
            if (!valido) {
                campo.classList.remove('border-green-500');
                campo.classList.add('border-red-500');
                if (errorElement) {
                    errorElement.classList.remove('hidden');
                    errorElement.textContent = mensagem;
                }
            }

            return valido;
        }

        // Função para confirmar seleção
        async function confirmarSelecao() {
            if (!atividadeSelecionadaId) {
                alert('Erro: ID da atividade não foi definido. Tente fechar e abrir o modal novamente.');
                return;
            }

            // Validar todos os campos antes de submeter
            const campos = ['tema', 'local', 'quantidade', 'comprovante'];
            let todosValidos = true;

            campos.forEach(campo => {
                if (!validarCampoSelecao(campo)) {
                    todosValidos = false;
                }
            });

            if (todosValidos) {
                // Desabilitar botão para evitar duplo clique
                const btnConfirmar = document.getElementById('btnConfirmar');
                btnConfirmar.disabled = true;
                btnConfirmar.textContent = 'Cadastrando...';

                // Obter valores dos campos
                const tema = document.getElementById('tema').value.trim();
                const local = document.getElementById('local').value.trim();
                const quantidade = document.getElementById('quantidade').value;
                const cargaHoraria = document.getElementById('cargaHoraria').value;

                // Validação contra restante calculado
                const rest = await obterRestantePesquisa(atividadeSelecionadaId);
                if (parseInt(cargaHoraria) > rest.restante) {
                    alert(`As horas informadas excedem o restante disponível (${rest.restante}h). ${gerarSugestoesPesquisa(rest.restante, 1, rest.restante) ? 'Sugestões: ' + gerarSugestoesPesquisa(rest.restante, 1, rest.restante) : ''}`);
                    btnConfirmar.disabled = false;
                    btnConfirmar.textContent = 'Confirmar';
                    return;
                }

                // Preparar dados do formulário para a nova rota
                const formData = new FormData();
                formData.append('atividades_por_resolucao_id', atividadeSelecionadaId);
                formData.append('titulo', tema); // Campo tema vai para coluna titulo
                formData.append('ch_solicitada', cargaHoraria);
                formData.append('descricao', `Apresentação - Local: ${local} - Quantidade: ${quantidade} apresentação(ões)`);

                // Adicionar arquivo se selecionado
                const arquivo = document.getElementById('comprovante').files[0];
                if (arquivo) {
                    formData.append('declaracao', arquivo);
                }

                console.log('Dados sendo enviados para seleção geral:');
                console.log('atividades_por_resolucao_id:', atividadeSelecionadaId);
                console.log('titulo (tema):', tema);
                console.log('ch_solicitada:', cargaHoraria);
                console.log('descricao:', `Apresentação - Local: ${local} - Quantidade: ${quantidade} apresentação(ões)`);

                // Enviar via AJAX para a nova rota
                fetch('../../backend/api/routes/cadastrar_atividades.php', {
                        method: 'POST',
                        headers: {
                            'X-API-Key': 'frontend-gerenciamento-acc-2025',
                            'Authorization': 'Bearer ' + localStorage.getItem('acc_jwt_token')
                        },
                        body: formData
                    })
                    .then(response => response.text().then(text => {
                        console.log('Resposta bruta do servidor:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Erro ao fazer parse do JSON:', e);
                            throw new Error('Resposta inválida do servidor: ' + text);
                        }
                    }))
                    .then(async data => {
                        console.log('Dados processados:', data);
                        if (data.success) {
                            // Mostrar mensagem de sucesso
                            alert('Atividade cadastrada com sucesso!');

                            // Atualizar automaticamente a seção "Minhas Atividades"
                            await atualizarMinhasAtividades();

                            // Fechar modal
                            fecharModalSelecao();

                            // Recarregar lista de atividades
                            carregarAtividades();
                        } else {
                            console.error('Erro retornado pelo backend:', data);
                            alert('Erro ao cadastrar atividade: ' + (data.error || 'Erro desconhecido'));
                        }
                    })
                    .catch(error => {
                        console.error('Erro na requisição:', error);
                        alert('Erro ao cadastrar atividade. Tente novamente.');
                    })
                    .finally(() => {
                        // Reabilitar botão
                        btnConfirmar.disabled = false;
                        btnConfirmar.textContent = 'Confirmar';
                    });
            }
        }

        // Event listeners globais
        document.addEventListener('DOMContentLoaded', function() {
            // Fechar modal com ESC
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const modalSelecao = document.getElementById('modalSelecao');
                    if (!modalSelecao.classList.contains('hidden')) {
                        fecharModalSelecao();
                    }

                    const modalDetalhes = document.getElementById('modalDetalhes');
                    if (!modalDetalhes.classList.contains('hidden')) {
                        fecharModal();
                    }

                    const modalEventoCientifico = document.getElementById('modalEventoCientifico');
                    if (!modalEventoCientifico.classList.contains('hidden')) {
                        fecharModalEventoCientifico();
                    }
                }
            });

            // Fechar modal clicando fora
            document.getElementById('modalSelecao').addEventListener('click', function(event) {
                if (event.target === this) {
                    fecharModalSelecao();
                }
            });

            // Fechar modal evento científico clicando fora
            document.getElementById('modalEventoCientifico').addEventListener('click', function(event) {
                if (event.target === this) {
                    fecharModalEventoCientifico();
                }
            });

            // Gerenciar foco no modal (trap focus)
            document.getElementById('modalSelecao').addEventListener('keydown', function(event) {
                if (event.key === 'Tab') {
                    const focusableElements = this.querySelectorAll(
                        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    );
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];

                    if (event.shiftKey) {
                        if (document.activeElement === firstElement) {
                            lastElement.focus();
                            event.preventDefault();
                        }
                    } else {
                        if (document.activeElement === lastElement) {
                            firstElement.focus();
                            event.preventDefault();
                        }
                    }
                }
            });

            // Gerenciar foco no modal evento científico (trap focus)
            document.getElementById('modalEventoCientifico').addEventListener('keydown', function(event) {
                if (event.key === 'Tab') {
                    const focusableElements = this.querySelectorAll(
                        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    );
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];

                    if (event.shiftKey) {
                        if (document.activeElement === firstElement) {
                            lastElement.focus();
                            event.preventDefault();
                        }
                    } else {
                        if (document.activeElement === lastElement) {
                            firstElement.focus();
                            event.preventDefault();
                        }
                    }
                }
            });

            // Event listeners para validação em tempo real do modalSelecao
            const temaInput = document.getElementById('tema');
            if (temaInput) {
                temaInput.addEventListener('input', verificarFormularioValido);
            }

            const cargaHorariaInput = document.getElementById('cargaHoraria');
            if (cargaHorariaInput) {
                cargaHorariaInput.addEventListener('input', verificarFormularioValido);
            }

            const quantidadeInput = document.getElementById('quantidade');
            if (quantidadeInput) {
                quantidadeInput.addEventListener('input', verificarFormularioValido);
            }

            const comprovanteInput = document.getElementById('comprovante');
            if (comprovanteInput) {
                comprovanteInput.addEventListener('change', verificarFormularioValido);
            }

            // Event listeners para o modal de evento científico
            const quantidadeApresentacoesInput = document.getElementById('quantidadeApresentacoes');
            if (quantidadeApresentacoesInput) {
                quantidadeApresentacoesInput.addEventListener('input', function() {
                    atualizarCargaHorariaEvento();
                    validarCampoEvento('quantidadeApresentacoes');
                });
            }

            const localApresentacaoSelect = document.getElementById('localApresentacao');
            if (localApresentacaoSelect) {
                localApresentacaoSelect.addEventListener('change', function() {
                    atualizarCargaHorariaEvento();
                    validarCampoEvento('localApresentacao');
                });
            }

            // Event listener para validação do tema
            const temaApresentacaoInput = document.getElementById('temaApresentacao');
            if (temaApresentacaoInput) {
                temaApresentacaoInput.addEventListener('input', function() {
                    validarCampoEvento('temaApresentacao');
                });
            }

            // Event listener para validação da declaração
            const declaracaoInput = document.getElementById('declaracao');
            if (declaracaoInput) {
                declaracaoInput.addEventListener('change', function() {
                    validarCampoEvento('declaracao');
                });
            }
        });

        // Variáveis globais para o modal de evento científico
        let atividadeEventoCientificoId = null;
        let elementoAnteriorFocoEvento = null;
        let horasMaximasEventoCientifico = null;

        // Função para abrir modal de evento científico
        function abrirModalEventoCientifico(id) {
            console.log('🔵 ABRINDO MODAL EVENTO CIENTÍFICO - ID:', id);
            console.log('🔍 Elemento modal encontrado:', document.getElementById('modalEventoCientifico'));

            atividadeEventoCientificoId = id;
            elementoAnteriorFocoEvento = document.activeElement;

            // Obter dados do usuário para verificar o ano da matrícula
            const userData = localStorage.getItem('acc_user_data');
            let limiteHoras = 20; // Padrão para matrículas anteriores a 2023

            if (userData) {
                try {
                    const user = JSON.parse(userData);
                    if (user && user.matricula) {
                        const anoMatricula = parseInt(user.matricula.substring(0, 4));
                        if (anoMatricula >= 2023) {
                            limiteHoras = 9;
                        }
                    }
                } catch (e) {
                    console.error('Erro ao processar dados do usuário:', e);
                }
            }

            // Atualizar textos do formulário baseado no limite de horas
            const limiteHorasTexto = document.getElementById('limite-horas-texto');
            const tipoEventoTexto = document.getElementById('tipo-evento-texto');

            if (limiteHorasTexto) {
                limiteHorasTexto.textContent = `Qualquer quantidade permitida - carga horária limitada a ${limiteHoras}h no total`;
            }

            if (tipoEventoTexto) {
                tipoEventoTexto.textContent = `Local/Nacional: 10h por apresentação | Internacional: 15h por apresentação (máximo ${limiteHoras}h)`;
            }

            // Buscar dados da atividade para obter horas máximas
            const atividade = todasAtividades.find(a => a.atividades_por_resolucao_id === id);
            console.log('🔍 Atividade encontrada:', atividade);

            if (atividade) {
                horasMaximasEventoCientifico = parseInt(atividade.horas_max);
            }

            const modal = document.getElementById('modalEventoCientifico');
            console.log('🔍 Modal antes de mostrar - classes:', modal.className);

            modal.classList.remove('hidden');
            console.log('🔍 Modal após remover hidden - classes:', modal.className);

            // Focar no primeiro campo
            setTimeout(() => {
                const nomeEventoField = document.getElementById('nomeEvento');
                console.log('🔍 Campo nomeEvento encontrado:', nomeEventoField);
                if (nomeEventoField) {
                    nomeEventoField.focus();
                }
            }, 100);

            // Limpar formulário
            limparFormularioEvento();
            aplicarLimitesPesquisaEvento(id);
        }

        // Função para fechar modal de evento científico
        function fecharModalEventoCientifico() {
            const modal = document.getElementById('modalEventoCientifico');
            modal.classList.add('hidden');

            // Restaurar foco
            if (elementoAnteriorFocoEvento) {
                elementoAnteriorFocoEvento.focus();
            }

            // Limpar dados
            atividadeEventoCientificoId = null;
            limparFormularioEvento();
        }

        // Função para limpar formulário do evento científico
        function limparFormularioEvento() {
            const form = document.getElementById('formEventoCientifico');
            form.reset();

            // Limpar mensagens de erro
            const erros = form.querySelectorAll('[id$="-error"]');
            erros.forEach(erro => {
                erro.classList.add('hidden');
                erro.textContent = '';
            });

            // Resetar estilos dos campos
            const campos = form.querySelectorAll('input');
            campos.forEach(campo => {
                campo.classList.remove('border-red-500', 'border-green-500');
                campo.classList.add('border-gray-300');
            });

            // Desabilitar botão confirmar
            document.getElementById('btnConfirmarEvento').disabled = true;
        }

        // Função para calcular carga horária do evento científico
        function calcularCargaHorariaEvento() {
            const quantidadeInput = document.getElementById('quantidadeApresentacoes');
            const tipoEventoSelect = document.getElementById('tipoEvento');
            const cargaHorariaInput = document.getElementById('cargaHorariaEvento');
            const infoDiv = document.getElementById('cargaHorariaEvento-info');
            const warningDiv = document.getElementById('cargaHorariaEvento-warning');
            const calculoDetalhes = document.getElementById('calculo-detalhes');

            if (!quantidadeInput || !tipoEventoSelect || !cargaHorariaInput) {
                return;
            }

            const quantidade = parseInt(quantidadeInput.value) || 0;
            const tipoEvento = tipoEventoSelect.value;

            let cargaHoraria = 0;
            let detalhes = '';

            if (quantidade > 0 && tipoEvento) {
                let cargaCalculada = 0;
                if (tipoEvento === 'local') {
                    cargaCalculada = quantidade * 10;
                    detalhes = `${quantidade} apresentação${quantidade > 1 ? 'ões' : ''} × 10h = ${cargaCalculada}h`;
                } else if (tipoEvento === 'internacional') {
                    cargaCalculada = quantidade * 15;
                    detalhes = `${quantidade} apresentação${quantidade > 1 ? 'ões' : ''} × 15h = ${cargaCalculada}h`;
                }

                // Obter dados do usuário para verificar o ano da matrícula
                const userData = localStorage.getItem('acc_user_data');
                let limiteMaximo = 20; // Padrão para matrículas anteriores a 2023

                if (userData) {
                    try {
                        const user = JSON.parse(userData);
                        if (user && user.matricula) {
                            const anoMatricula = parseInt(user.matricula.substring(0, 4));
                            if (anoMatricula >= 2023) {
                                limiteMaximo = 9;
                            }
                        }
                    } catch (e) {
                        console.error('Erro ao processar dados do usuário:', e);
                    }
                }

                // Aplicar limite máximo baseado no ano da matrícula
                cargaHoraria = Math.min(cargaCalculada, limiteMaximo);

                // Atualizar detalhes se houve limitação
                if (cargaCalculada > limiteMaximo) {
                    detalhes += ` → limitado a ${limiteMaximo}h`;
                }
            }

            // Atualizar campo de carga horária
            cargaHorariaInput.value = cargaHoraria || '';

            // Mostrar/ocultar informações e avisos
            if (quantidade >= 1 && tipoEvento) {
                // Atualizar texto do cálculo detalhado
                calculoDetalhes.textContent = detalhes;
                infoDiv.classList.remove('hidden');

                // Verificar se houve limitação
                if (cargaCalculada > limiteMaximo) {
                    warningDiv.classList.remove('hidden');
                } else {
                    warningDiv.classList.add('hidden');
                }

                // Aplicar estilos visuais aos campos
                quantidadeInput.classList.remove('border-red-500');
                quantidadeInput.classList.add('border-green-500');
                tipoEventoSelect.classList.remove('border-red-500');
                tipoEventoSelect.classList.add('border-green-500');
                cargaHorariaInput.classList.remove('border-red-500');
                cargaHorariaInput.classList.add('border-green-500');

                // Limpar qualquer erro do campo carga horária
                const cargaHorariaError = document.getElementById('cargaHorariaEvento-error');
                if (cargaHorariaError) {
                    cargaHorariaError.classList.add('hidden');
                    cargaHorariaError.textContent = '';
                }
            } else {
                // Ocultar informações quando não há dados válidos
                infoDiv.classList.add('hidden');
                warningDiv.classList.add('hidden');

                // Resetar estilos dos campos
                quantidadeInput.classList.remove('border-red-500', 'border-green-500');
                quantidadeInput.classList.add('border-gray-300');
                tipoEventoSelect.classList.remove('border-red-500', 'border-green-500');
                tipoEventoSelect.classList.add('border-gray-300');
                cargaHorariaInput.classList.remove('border-red-500', 'border-green-500');
                cargaHorariaInput.classList.add('border-gray-300');
            }

            // Verificar formulário após cálculo
            verificarFormularioEventoValido();
        }

        // Função de validação para campos do evento científico
        function validarCampoEvento(nomeCampo) {
            console.log('🔍 Validando campo:', nomeCampo);

            const campo = document.getElementById(nomeCampo);
            if (!campo) {
                console.error('❌ Campo não encontrado:', nomeCampo);
                return false;
            }

            const valor = campo.value ? campo.value.trim() : '';
            const errorElement = document.getElementById(`${nomeCampo}-error`);

            let valido = true;
            let mensagem = '';

            // Validações específicas por campo
            switch (nomeCampo) {
                case 'temaApresentacao':
                    if (!valor) {
                        valido = false;
                        mensagem = 'O tema da apresentação é obrigatório';
                    } else if (valor.length < 3) {
                        valido = false;
                        mensagem = 'O tema deve ter pelo menos 3 caracteres';
                    }
                    break;

                case 'quantidadeApresentacoes':
                    const quantidade = parseInt(valor);
                    if (!valor) {
                        valido = false;
                        mensagem = 'A quantidade de apresentações é obrigatória';
                    } else if (isNaN(quantidade) || quantidade < 1) {
                        valido = false;
                        mensagem = 'A quantidade deve ser um número inteiro positivo';
                    }
                    break;

                case 'localApresentacao':
                    if (!valor) {
                        valido = false;
                        mensagem = 'O local da apresentação é obrigatório';
                    } else if (valor !== 'nacional' && valor !== 'internacional') {
                        valido = false;
                        mensagem = 'Selecione um local válido';
                    }
                    break;

                case 'cargaHorariaEvento':
                    const cargaHoraria = parseFloat(valor);
                    if (!valor) {
                        valido = false;
                        mensagem = 'A carga horária é obrigatória';
                    } else if (isNaN(cargaHoraria) || cargaHoraria < 0.5) {
                        valido = false;
                        mensagem = 'A carga horária deve ser pelo menos 0.5 horas';
                    }
                    break;

                case 'declaracao':
                    if (!campo.files || campo.files.length === 0) {
                        valido = false;
                        mensagem = 'O comprovante é obrigatório';
                    } else {
                        const arquivo = campo.files[0];
                        const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                        const tamanhoMaximo = 5 * 1024 * 1024; // 5MB

                        if (!tiposPermitidos.includes(arquivo.type)) {
                            valido = false;
                            mensagem = 'Formato não permitido. Use PDF, JPG ou PNG';
                        } else if (arquivo.size > tamanhoMaximo) {
                            valido = false;
                            mensagem = 'Arquivo muito grande. Máximo 5MB';
                        }
                    }
                    break;
            }

            console.log(`✓ Campo ${nomeCampo} - Válido: ${valido}, Mensagem: ${mensagem}`);

            // Aplicar estilos visuais
            if (errorElement) {
                if (valido) {
                    campo.classList.remove('border-red-500');
                    campo.classList.add('border-green-500');
                    errorElement.classList.add('hidden');
                    errorElement.textContent = '';
                } else {
                    campo.classList.remove('border-green-500');
                    campo.classList.add('border-red-500');
                    errorElement.classList.remove('hidden');
                    errorElement.textContent = mensagem;
                }
            }

            // Verificar se todos os campos são válidos
            verificarFormularioEventoValido();

            // IMPORTANTE: Retornar o resultado da validação
            return valido;
        }

        // Função para verificar se o formulário do evento é válido
        function verificarFormularioEventoValido() {
            const temaApresentacao = document.getElementById('temaApresentacao').value.trim();
            const quantidadeApresentacoes = document.getElementById('quantidadeApresentacoes').value;
            const localApresentacao = document.getElementById('localApresentacao').value;
            const cargaHorariaEvento = document.getElementById('cargaHorariaEvento').value;
            const declaracao = document.getElementById('declaracao').files;

            const temaValido = temaApresentacao.length >= 3;
            const quantidadeValida = quantidadeApresentacoes && parseInt(quantidadeApresentacoes) >= 1;
            const localValido = localApresentacao && (localApresentacao === 'nacional' || localApresentacao === 'internacional');
            const cargaValida = cargaHorariaEvento && parseFloat(cargaHorariaEvento) >= 0.5;
            const declaracaoValida = declaracao && declaracao.length > 0;

            const formularioValido = temaValido && quantidadeValida && localValido && cargaValida && declaracaoValida;

            document.getElementById('btnConfirmarEvento').disabled = !formularioValido;
        }

        // Função para confirmar evento científico
        function confirmarEventoCientifico() {
            console.log('=== INÍCIO DA FUNÇÃO confirmarEventoCientifico ===');

            if (!atividadeEventoCientificoId) {
                console.error('❌ ERRO: atividadeEventoCientificoId não definido:', atividadeEventoCientificoId);
                alert('Erro: ID da atividade não foi definido. Tente fechar e abrir o modal novamente.');
                return;
            }

            console.log('✓ atividadeEventoCientificoId definido:', atividadeEventoCientificoId);

            // Verificar se todos os campos existem
            const campos = ['temaApresentacao', 'quantidadeApresentacoes', 'localApresentacao', 'cargaHorariaEvento', 'declaracao'];
            let todosValidos = true;

            // Validar cada campo individualmente
            campos.forEach(campo => {
                const elemento = document.getElementById(campo);
                if (!elemento) {
                    console.error(`Elemento ${campo} não encontrado`);
                    todosValidos = false;
                    return;
                }

                console.log(`Validando campo ${campo}:`, elemento.value || elemento.files);
                const valido = validarCampoEvento(campo);
                console.log(`Campo ${campo} válido:`, valido);

                if (!valido) {
                    todosValidos = false;
                }
            });

            console.log('Todos os campos válidos:', todosValidos);

            if (todosValidos) {
                // Debug: verificar valores dos campos antes do envio
                console.log('Valores dos campos antes do envio:');
                console.log('temaApresentacao:', document.getElementById('temaApresentacao').value);
                console.log('quantidadeApresentacoes:', document.getElementById('quantidadeApresentacoes').value);
                console.log('localApresentacao:', document.getElementById('localApresentacao').value);
                console.log('cargaHorariaEvento:', document.getElementById('cargaHorariaEvento').value);

                console.log('declaracao files:', document.getElementById('declaracao').files.length);

                // Desabilitar botão para evitar duplo clique
                const btnConfirmar = document.getElementById('btnConfirmarEvento');
                btnConfirmar.disabled = true;
                btnConfirmar.textContent = 'Cadastrando...';

                // Obter valores dos campos
                const temaValue = document.getElementById('temaApresentacao').value.trim();
                const quantidadeValue = document.getElementById('quantidadeApresentacoes').value;
                const localValue = document.getElementById('localApresentacao').value;
                const cargaHorariaValue = document.getElementById('cargaHorariaEvento').value;

                if (!temaValue || !quantidadeValue || !localValue || !cargaHorariaValue) {
                    alert('Erro: Todos os campos obrigatórios devem ser preenchidos');
                    btnConfirmar.disabled = false;
                    btnConfirmar.textContent = 'Confirmar';
                    return;
                }

                // Usar a carga horária calculada automaticamente
                const horasCalculadas = parseInt(cargaHorariaValue);
                console.log('🧮 Debug - Horas calculadas automaticamente:', horasCalculadas);

                // Para apresentações em eventos científicos, o limite máximo é sempre 20h
                const horasMaximas = 20;
                // Validar contra restante disponível
                const rest = window.__ultimoRestantePesquisaEvento || { restante: horasMaximas };
                if (horasCalculadas > rest.restante) {
                    alert(`As horas calculadas excedem o restante disponível (${rest.restante}h). ${gerarSugestoesPesquisa(rest.restante, 1, rest.restante) ? 'Sugestões: ' + gerarSugestoesPesquisa(rest.restante, 1, rest.restante) : ''}`);
                    btnConfirmar.disabled = false;
                    btnConfirmar.textContent = 'Confirmar';
                    return;
                }
                const horasRealizadas = Math.min(horasCalculadas, rest.restante);
                console.log('✅ Debug - Horas realizadas finais (limitadas ao máximo):', horasRealizadas);

                console.log('=== RESUMO DO CÁLCULO ===');
                console.log('- Atividade ID:', atividadeEventoCientificoId);
                console.log('- Horas máximas permitidas:', horasMaximas);
                console.log('- Quantidade de apresentações:', quantidadeValue);
                console.log('- Local da apresentação:', localValue);
                console.log('- Tema da apresentação:', temaValue);
                console.log('- Horas calculadas:', horasCalculadas);
                console.log('- Horas realizadas (final):', horasRealizadas);
                console.log('========================');

                // Preparar dados do formulário para a nova rota
                const formData = new FormData();
                formData.append('atividades_por_resolucao_id', atividadeEventoCientificoId);
                formData.append('titulo', temaValue); // Campo tema da apresentação vai para coluna titulo
                formData.append('ch_solicitada', horasRealizadas);
                formData.append('descricao', `Apresentação em evento científico - Local: ${localValue} - Quantidade: ${quantidadeValue} apresentação(ões)`);

                // Adicionar arquivo se selecionado
                const arquivo = document.getElementById('declaracao').files[0];
                if (arquivo) {
                    formData.append('declaracao', arquivo);
                    console.log('arquivo:', arquivo.name);
                }

                // Debug: verificar dados sendo enviados
                console.log('Dados sendo enviados para nova rota:');
                console.log('atividades_por_resolucao_id:', atividadeEventoCientificoId);
                console.log('titulo (tema):', temaValue);
                console.log('ch_solicitada:', horasRealizadas);
                console.log('descricao:', `Apresentação em evento científico - Local: ${localValue} - Quantidade: ${quantidadeValue} apresentação(ões)`);

                // Enviar via AJAX para a nova rota
                fetch('../../backend/api/routes/cadastrar_atividades.php', {
                        method: 'POST',
                        headers: {
                            'X-API-Key': 'frontend-gerenciamento-acc-2025',
                            'Authorization': 'Bearer ' + localStorage.getItem('acc_jwt_token')
                        },
                        body: formData
                    })
                    .then(response => {
                        console.log('🔍 DEBUG - Status da resposta:', response.status);
                        console.log('🔍 DEBUG - Headers da resposta:', response.headers);
                        return response.text().then(text => {
                            console.log('🔍 DEBUG - Resposta bruta do servidor:', text);
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('❌ Erro ao fazer parse do JSON:', e);
                                console.error('❌ Texto recebido:', text);
                                throw new Error('Resposta inválida do servidor: ' + text);
                            }
                        });
                    })
                    .then(async data => {
                        console.log('🔍 DEBUG - Dados processados:', data);
                        if (data.success) {
                            // Mostrar mensagem de sucesso
                            alert('Atividade de pesquisa cadastrada com sucesso!');

                            // Atualizar automaticamente a seção "Minhas Atividades"
                            await atualizarMinhasAtividades();

                            // Fechar modal
                            fecharModalEventoCientifico();

                            // Recarregar lista de atividades
                            carregarAtividades();
                        } else {
                            console.error('❌ Erro retornado pelo backend:', data);
                            alert('Erro ao cadastrar atividade: ' + (data.error || 'Erro desconhecido'));
                        }
                    })
                    .catch(error => {
                        console.error('Erro na requisição:', error);
                        alert('Erro ao cadastrar atividade. Tente novamente.');
                    })
                    .finally(() => {
                        // Reabilitar botão
                        btnConfirmar.disabled = false;
                        btnConfirmar.textContent = 'Confirmar';
                    });
            }
        }

        // Variáveis globais para o modal de cadastro de evento
        let atividadeCadastroEventoId = null;
        let elementoAnteriorFocoCadastro = null;

        // Função para abrir modal de cadastro de evento
        function abrirModalCadastroEvento(id) {
            atividadeCadastroEventoId = id;
            elementoAnteriorFocoCadastro = document.activeElement;

            // Buscar dados da atividade para obter horas máximas
            const atividade = todasAtividades.find(a => a.atividades_por_resolucao_id === id);
            const horasMaximas = atividade ? atividade.horas_max : 40;

            // Definir limite máximo específico para esta atividade (40h)
            const campoHoras = document.getElementById('cargaHorariaCadastro');
            campoHoras.setAttribute('max', '40');
            campoHoras.setAttribute('data-max-horas', '40');
            campoHoras.setAttribute('title', 'Carga horária máxima: 40 horas');

            const modal = document.getElementById('modalCadastroEvento');
            modal.classList.remove('hidden');

            // Focar no primeiro campo
            setTimeout(() => {
                document.getElementById('nomeEventoCadastro').focus();
            }, 100);

            // Limpar formulário
            limparFormularioCadastro();
            aplicarLimitesPesquisaCadastro(id);
        }

        // Função para fechar modal de cadastro de evento
        function fecharModalCadastroEvento() {
            const modal = document.getElementById('modalCadastroEvento');
            modal.classList.add('hidden');

            // Restaurar foco
            if (elementoAnteriorFocoCadastro) {
                elementoAnteriorFocoCadastro.focus();
            }

            // Limpar dados
            atividadeCadastroEventoId = null;
            limparFormularioCadastro();
        }

        // Função para limpar formulário do cadastro de evento
        function limparFormularioCadastro() {
            const form = document.getElementById('formCadastroEvento');
            form.reset();

            // Limpar mensagens de erro
            const erros = form.querySelectorAll('[id$="-error"]');
            erros.forEach(erro => {
                erro.classList.add('hidden');
                erro.textContent = '';
            });

            // Resetar estilos dos campos
            const campos = form.querySelectorAll('input');
            campos.forEach(campo => {
                campo.classList.remove('border-red-500', 'border-green-500');
                campo.classList.add('border-gray-300');
            });

            // Ocultar info do arquivo
            document.getElementById('arquivo-info-cadastro').classList.add('hidden');

            // Desabilitar botão confirmar
            document.getElementById('btnConfirmarCadastro').disabled = true;
        }

        // Função para detectar o ano de matrícula do aluno
        function obterAnoMatriculaAluno() {
            try {
                const user = AuthClient.getUser();
                if (user && user.matricula) {
                    const anoMatricula = parseInt(user.matricula.substring(0, 4));
                    console.log('🎓 Ano de matrícula detectado:', anoMatricula);
                    return anoMatricula;
                }
                console.warn('⚠️ Matrícula não encontrada nos dados do usuário');
                return null;
            } catch (error) {
                console.error('❌ Erro ao obter ano de matrícula:', error);
                return null;
            }
        }

        // Função para calcular horas baseado nas regras
        function calcularHorasEventoCientifico(quantidade, local) {
            const anoMatricula = obterAnoMatriculaAluno();
            
            if (!anoMatricula || !quantidade || !local) {
                console.warn('⚠️ Dados insuficientes para cálculo:', { anoMatricula, quantidade, local });
                return 0;
            }

            let horasPorApresentacao = 0;

            // Definir horas por apresentação baseado no ano de matrícula e local
            if (anoMatricula >= 2023) {
                // Alunos 2023+
                horasPorApresentacao = local === 'nacional' ? 5 : 7;
            } else if (anoMatricula >= 2017 && anoMatricula <= 2022) {
                // Alunos 2017-2022
                horasPorApresentacao = local === 'nacional' ? 10 : 15;
            } else {
                console.warn('⚠️ Ano de matrícula fora das regras definidas:', anoMatricula);
                return 0;
            }

            const horasCalculadas = quantidade * horasPorApresentacao;
            
            console.log('🧮 Cálculo de horas:', {
                anoMatricula,
                quantidade,
                local,
                horasPorApresentacao,
                horasCalculadas
            });

            return horasCalculadas;
        }

        // Função para atualizar o campo de carga horária automaticamente
        function atualizarCargaHorariaEvento() {
            const quantidade = parseInt(document.getElementById('quantidadeApresentacoes').value) || 0;
            const local = document.getElementById('localApresentacao').value;
            const campoHoras = document.getElementById('cargaHorariaEvento');

            if (quantidade > 0 && local) {
                const horasCalculadas = calcularHorasEventoCientifico(quantidade, local);
                campoHoras.value = horasCalculadas;
                
                // Atualizar placeholder com informação útil
                const anoMatricula = obterAnoMatriculaAluno();
                let horasPorApresentacao = 0;
                
                if (anoMatricula >= 2023) {
                    horasPorApresentacao = local === 'nacional' ? 5 : 7;
                } else if (anoMatricula >= 2017 && anoMatricula <= 2022) {
                    horasPorApresentacao = local === 'nacional' ? 10 : 15;
                }
                
                campoHoras.placeholder = `${quantidade} × ${horasPorApresentacao}h = ${horasCalculadas}h`;
            } else {
                campoHoras.value = '';
                campoHoras.placeholder = 'Calculado automaticamente';
            }
        }

        // Função de validação para campos do cadastro de evento
        function validarCampoCadastro(nomeCampo) {
            const campo = document.getElementById(nomeCampo);
            const valor = campo.value.trim();
            const errorElement = document.getElementById(`${nomeCampo}-error`);

            let valido = true;
            let mensagem = '';

            // Validações específicas por campo
            switch (nomeCampo) {
                case 'nomeEventoCadastro':
                    if (!valor) {
                        valido = false;
                        mensagem = 'O nome do evento é obrigatório';
                    } else if (valor.length < 3) {
                        valido = false;
                        mensagem = 'O nome deve ter pelo menos 3 caracteres';
                    }
                    break;

                case 'cargaHorariaCadastro':
                    const horas = parseFloat(valor);

                    if (!valor) {
                        valido = false;
                        mensagem = 'A carga horária é obrigatória';
                    } else if (isNaN(horas) || horas < 0.5) {
                        valido = false;
                        mensagem = 'A carga horária deve ser pelo menos 0.5 horas';
                    } else if (horas > 40) {
                        valido = false;
                        mensagem = 'A carga horária não pode ser maior que 40 horas';
                    }
                    break;

                case 'declaracaoCadastro':
                    if (!campo.files || campo.files.length === 0) {
                        valido = false;
                        mensagem = 'A declaração/certificado é obrigatória';
                    } else {
                        const arquivo = campo.files[0];
                        const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                        const tamanhoMaximo = 5 * 1024 * 1024; // 5MB

                        if (!tiposPermitidos.includes(arquivo.type)) {
                            valido = false;
                            mensagem = 'Formato não permitido. Use PDF, JPG ou PNG';
                        } else if (arquivo.size > tamanhoMaximo) {
                            valido = false;
                            mensagem = 'Arquivo muito grande. Máximo 5MB';
                        } else {
                            // Mostrar informações do arquivo
                            mostrarInfoArquivoCadastro(arquivo);
                        }
                    }
                    break;
            }

            // Aplicar estilos visuais
            if (valido) {
                campo.classList.remove('border-red-500');
                campo.classList.add('border-green-500');
                errorElement.classList.add('hidden');
                errorElement.textContent = '';
            } else {
                campo.classList.remove('border-green-500');
                campo.classList.add('border-red-500');
                errorElement.classList.remove('hidden');
                errorElement.textContent = mensagem;

                if (nomeCampo === 'declaracaoCadastro') {
                    document.getElementById('arquivo-info-cadastro').classList.add('hidden');
                }
            }

            // Verificar se todos os campos são válidos
            verificarFormularioCadastroValido();
        }

        // Função para mostrar informações do arquivo do cadastro
        function mostrarInfoArquivoCadastro(arquivo) {
            const nomeElement = document.getElementById('arquivo-nome-cadastro');
            const tamanhoElement = document.getElementById('arquivo-tamanho-cadastro');
            const infoElement = document.getElementById('arquivo-info-cadastro');

            nomeElement.textContent = arquivo.name;
            tamanhoElement.textContent = formatarTamanhoArquivo(arquivo.size);
            infoElement.classList.remove('hidden');
        }

        // Função para verificar se o formulário de cadastro é válido
        function verificarFormularioCadastroValido() {
            const nomeEvento = document.getElementById('nomeEventoCadastro').value.trim();
            const cargaHoraria = document.getElementById('cargaHorariaCadastro').value;
            const declaracao = document.getElementById('declaracaoCadastro').files;

            const nomeValido = nomeEvento.length >= 3;
            const cargaValida = cargaHoraria && parseFloat(cargaHoraria) >= 0.5 && parseFloat(cargaHoraria) <= 40;
            const declaracaoValida = declaracao && declaracao.length > 0;

            const formularioValido = nomeValido && cargaValida && declaracaoValida;

            document.getElementById('btnConfirmarCadastro').disabled = !formularioValido;
        }

        // Função para confirmar cadastro de evento
        function confirmarCadastroEvento() {
            if (!atividadeCadastroEventoId) {
                alert('Erro: ID da atividade não foi definido. Tente fechar e abrir o modal novamente.');
                return;
            }

            // Desabilitar botão para evitar duplo clique
            const btnConfirmar = document.getElementById('btnConfirmarCadastro');
            btnConfirmar.disabled = true;
            btnConfirmar.textContent = 'Cadastrando...';

            // Obter valores dos campos
            const nomeEvento = document.getElementById('nomeEventoCadastro').value.trim();
            const cargaHoraria = document.getElementById('cargaHorariaCadastro').value;
            const funcaoCargo = document.getElementById('funcaoCargoCadastro') ? document.getElementById('funcaoCargoCadastro').value : '';

            if (!nomeEvento || !cargaHoraria) {
                alert('Erro: Todos os campos obrigatórios devem ser preenchidos');
                btnConfirmar.disabled = false;
                btnConfirmar.textContent = 'Confirmar';
                return;
            }

            // Validar contra restante disponível
            const rest = window.__ultimoRestantePesquisaCadastro || { restante: parseFloat(cargaHoraria) };
            if (parseFloat(cargaHoraria) > rest.restante) {
                alert(`As horas informadas excedem o restante disponível (${rest.restante}h). ${gerarSugestoesPesquisa(rest.restante, 1, rest.restante) ? 'Sugestões: ' + gerarSugestoesPesquisa(rest.restante, 1, rest.restante) : ''}`);
                btnConfirmar.disabled = false;
                btnConfirmar.textContent = 'Confirmar';
                return;
            }

            // Preparar dados do formulário para a nova rota
            const formData = new FormData();
            formData.append('atividades_por_resolucao_id', atividadeCadastroEventoId);
            formData.append('titulo', nomeEvento); // Campo nome do evento vai para coluna titulo
            formData.append('ch_solicitada', cargaHoraria);
            formData.append('descricao', funcaoCargo ? `Participação em evento - Função/Cargo: ${funcaoCargo}` : 'Participação em evento');

            // Adicionar arquivo se selecionado
            const arquivo = document.getElementById('declaracaoCadastro').files[0];
            if (arquivo) {
                formData.append('declaracao', arquivo);
            }

            console.log('Dados sendo enviados para cadastro de evento:');
            console.log('atividades_por_resolucao_id:', atividadeCadastroEventoId);
            console.log('titulo (nome do evento):', nomeEvento);
            console.log('ch_solicitada:', cargaHoraria);
            console.log('descricao:', funcaoCargo ? `Participação em evento - Função/Cargo: ${funcaoCargo}` : 'Participação em evento');

            // Enviar via AJAX para a nova rota
            fetch('../../backend/api/routes/cadastrar_atividades.php', {
                    method: 'POST',
                    headers: {
                        'X-API-Key': 'frontend-gerenciamento-acc-2025',
                        'Authorization': 'Bearer ' + localStorage.getItem('acc_jwt_token')
                    },
                    body: formData
                })
                .then(response => response.text().then(text => {
                    console.log('Resposta bruta do servidor:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Erro ao fazer parse do JSON:', e);
                        throw new Error('Resposta inválida do servidor: ' + text);
                    }
                }))
                .then(async data => {
                    console.log('Dados processados:', data);
                    if (data.success) {
                        // Mostrar mensagem de sucesso
                        alert('Atividade de pesquisa cadastrada com sucesso!');

                        // Atualizar automaticamente a seção "Minhas Atividades"
                        await atualizarMinhasAtividades();

                        // Fechar modal
                        fecharModalCadastroEvento();

                        // Recarregar lista de atividades
                        carregarAtividades();
                    } else {
                        console.error('Erro retornado pelo backend:', data);
                        alert('Erro ao cadastrar atividade: ' + (data.error || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                    alert('Erro ao cadastrar atividade. Tente novamente.');
                })
                .finally(() => {
                    // Reabilitar botão
                    btnConfirmar.disabled = false;
                    btnConfirmar.textContent = 'Confirmar';
                });
        }

        // Event listeners para o modal de cadastro de evento
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar se os elementos existem antes de adicionar event listeners
            const btnCancelarCadastro = document.getElementById('btnCancelarCadastro');
            if (btnCancelarCadastro) {
                btnCancelarCadastro.addEventListener('click', fecharModalCadastroEvento);
            }

            const btnConfirmarCadastro = document.getElementById('btnConfirmarCadastro');
            if (btnConfirmarCadastro) {
                btnConfirmarCadastro.addEventListener('click', confirmarCadastroEvento);
            }

            // Event listeners para validação em tempo real
            const nomeEventoCadastro = document.getElementById('nomeEventoCadastro');
            if (nomeEventoCadastro) {
                nomeEventoCadastro.addEventListener('input', () => validarCampoCadastro('nomeEventoCadastro'));
            }

            const cargaHorariaCadastro = document.getElementById('cargaHorariaCadastro');
            if (cargaHorariaCadastro) {
                cargaHorariaCadastro.addEventListener('input', () => validarCampoCadastro('cargaHorariaCadastro'));
            }

            const funcaoCargoCadastro = document.getElementById('funcaoCargoCadastro');
            if (funcaoCargoCadastro) {
                funcaoCargoCadastro.addEventListener('change', () => validarCampoCadastro('funcaoCargoCadastro'));
            }

            const declaracaoCadastro = document.getElementById('declaracaoCadastro');
            if (declaracaoCadastro) {
                declaracaoCadastro.addEventListener('change', () => validarCampoCadastro('declaracaoCadastro'));
            }

            // Fechar modal ao clicar no X
            const closeModalBtn = document.querySelector('#modalCadastroEvento .close-modal');
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', fecharModalCadastroEvento);
            }

            // Fechar modal ao clicar fora
            const modalCadastroEvento = document.getElementById('modalCadastroEvento');
            if (modalCadastroEvento) {
                modalCadastroEvento.addEventListener('click', function(e) {
                    if (e.target === this) {
                        fecharModalCadastroEvento();
                    }
                });

                // Gerenciar foco no modal de cadastro
                modalCadastroEvento.addEventListener('keydown', function(e) {
                    if (e.key === 'Tab') {
                        const focusableElements = this.querySelectorAll(
                            'input:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])'
                        );
                        const firstElement = focusableElements[0];
                        const lastElement = focusableElements[focusableElements.length - 1];

                        if (e.shiftKey) {
                            if (document.activeElement === firstElement) {
                                e.preventDefault();
                                lastElement.focus();
                            }
                        } else {
                            if (document.activeElement === lastElement) {
                                e.preventDefault();
                                firstElement.focus();
                            }
                        }
                    }
                });
            }

            // Fechar modal com ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modalCadastro = document.getElementById('modalCadastroEvento');
                    if (modalCadastro && !modalCadastro.classList.contains('hidden')) {
                        fecharModalCadastroEvento();
                    }
                }
            });
        });

        // Variáveis globais para o modal de Iniciação Científica
        let atividadeIniciacaoCientificaId = null;
        let elementoAnteriorFocoIniciacaoCientifica = null;
        let horasMaximasIniciacaoCientifica = null;

        // Função para selecionar atividade de iniciação científica com verificação de matrícula
        function selecionarAtividadeIniciacaoCientifica(id) {
            console.log('🔴 FUNÇÃO CHAMADA - selecionarAtividadeIniciacaoCientifica, ID:', id);

            // Obter dados do usuário do localStorage
            const userData = localStorage.getItem('acc_user_data');
            console.log('🔴 DADOS DO USUÁRIO:', userData);

            if (userData) {
                try {
                    const user = JSON.parse(userData);
                    console.log('🔴 USUÁRIO PARSEADO:', user);

                    if (user && user.matricula) {
                        const anoMatricula = parseInt(user.matricula.substring(0, 4));
                        console.log('🔴 ANO DA MATRÍCULA:', anoMatricula);

                        // Verificar se a matrícula está entre 2017 e 2022
                        if (anoMatricula >= 2017 && anoMatricula <= 2022) {
                            console.log('🔴 ALUNO ELEGÍVEL - Abrindo modal específico');
                            abrirModalIniciacaoCientifica(id);
                        } else {
                            console.log('🔴 ALUNO NÃO ELEGÍVEL - Redirecionando para cadastro padrão');
                            window.location.href = `cadastrar_atividade.php?id=${id}`;
                        }
                    } else {
                        console.log('🔴 MATRÍCULA NÃO ENCONTRADA - Redirecionando para cadastro padrão');
                        window.location.href = `cadastrar_atividade.php?id=${id}`;
                    }
                } catch (error) {
                    console.error('🔴 ERRO AO PARSEAR DADOS DO USUÁRIO:', error);
                    window.location.href = `cadastrar_atividade.php?id=${id}`;
                }
            } else {
                console.log('🔴 DADOS DO USUÁRIO NÃO ENCONTRADOS - Redirecionando para cadastro padrão');
                window.location.href = `cadastrar_atividade.php?id=${id}`;
            }
        }

        // Função para abrir modal de Iniciação Científica
        function abrirModalIniciacaoCientifica(id) {
            atividadeIniciacaoCientificaId = id;
            elementoAnteriorFocoIniciacaoCientifica = document.activeElement;

            // Buscar dados da atividade para obter horas máximas
            const atividade = todasAtividades.find(a => a.id === id);
            if (atividade) {
                horasMaximasIniciacaoCientifica = parseInt(atividade.horas_max);
            }

            const modal = document.getElementById('modalIniciacaoCientifica');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Aplicar limites por restante
            aplicarLimitesPesquisaProjeto(id);
            // Focar no primeiro campo
            setTimeout(() => {
                document.getElementById('nomeProjeto').focus();
            }, 100);
        }

        // Função para fechar modal de Iniciação Científica
        function fecharModalIniciacaoCientifica() {
            const modal = document.getElementById('modalIniciacaoCientifica');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';

            // Restaurar foco
            if (elementoAnteriorFocoIniciacaoCientifica) {
                elementoAnteriorFocoIniciacaoCientifica.focus();
            }

            // Limpar dados
            atividadeIniciacaoCientificaId = null;
            limparFormularioIniciacaoCientifica();
        }

        // Função para limpar formulário de Iniciação Científica
        function limparFormularioIniciacaoCientifica() {
            const form = document.getElementById('formIniciacaoCientifica');
            form.reset();

            // Limpar mensagens de erro
            const erros = form.querySelectorAll('[id$="-error"]');
            erros.forEach(erro => {
                if (erro.id.includes('Projeto')) {
                    erro.classList.add('hidden');
                    erro.textContent = '';
                }
            });

            // Resetar estilos dos campos
            const campos = form.querySelectorAll('input');
            campos.forEach(campo => {
                campo.classList.remove('border-red-500', 'border-green-500');
                campo.classList.add('border-gray-300');
            });

            // Ocultar info do arquivo
            document.getElementById('arquivo-info-projeto').classList.add('hidden');

            // Desabilitar botão confirmar
            document.getElementById('btnConfirmarProjeto').disabled = true;
        }

        // Função de validação para campos de Iniciação Científica
        function validarCampoIniciacaoCientifica(campo) {
            const elemento = document.getElementById(campo);
            const errorElement = document.getElementById(campo + '-error');
            let isValid = true;
            let errorMessage = '';

            // Limpar erro anterior
            errorElement.classList.add('hidden');
            elemento.classList.remove('border-red-500');

            switch (campo) {
                case 'nomeProjeto':
                    if (!elemento.value.trim()) {
                        errorMessage = 'Nome do projeto é obrigatório';
                        isValid = false;
                    } else if (elemento.value.trim().length < 3) {
                        errorMessage = 'Nome do projeto deve ter pelo menos 3 caracteres';
                        isValid = false;
                    }
                    break;

                case 'cargaHorariaProjeto':
                    const carga = parseInt(elemento.value);
                    if (!elemento.value) {
                        errorMessage = 'Carga horária é obrigatória';
                        isValid = false;
                    } else if (carga < 1) {
                        errorMessage = 'A carga horária deve ser um número positivo';
                        isValid = false;
                    } else if (horasMaximasIniciacaoCientifica && carga > horasMaximasIniciacaoCientifica) {
                        errorMessage = `A carga horária não pode exceder ${horasMaximasIniciacaoCientifica} horas`;
                        isValid = false;
                    }
                    break;

                case 'dataInicioProjeto':
                    if (!elemento.value) {
                        errorMessage = 'Data de início é obrigatória';
                        isValid = false;
                    } else {
                        const dataFim = document.getElementById('dataFimProjeto').value;
                        if (dataFim && new Date(elemento.value) >= new Date(dataFim)) {
                            errorMessage = 'Data de início deve ser anterior à data de fim';
                            isValid = false;
                        }
                    }
                    break;

                case 'dataFimProjeto':
                    if (!elemento.value) {
                        errorMessage = 'Data de fim é obrigatória';
                        isValid = false;
                    } else {
                        const dataInicio = document.getElementById('dataInicioProjeto').value;
                        if (dataInicio && new Date(elemento.value) <= new Date(dataInicio)) {
                            errorMessage = 'Data de fim deve ser posterior à data de início';
                            isValid = false;
                        }
                    }
                    break;



                case 'declaracaoProjeto':
                    if (!elemento.files || elemento.files.length === 0) {
                        errorMessage = 'Declaração/Certificado é obrigatório';
                        isValid = false;
                    } else {
                        const arquivo = elemento.files[0];
                        const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                        const tamanhoMaximo = 5 * 1024 * 1024; // 5MB

                        if (!tiposPermitidos.includes(arquivo.type)) {
                            errorMessage = 'Formato de arquivo não permitido. Use PDF, JPG ou PNG';
                            isValid = false;
                        } else if (arquivo.size > tamanhoMaximo) {
                            errorMessage = 'Arquivo muito grande. Tamanho máximo: 5MB';
                            isValid = false;
                        } else {
                            // Mostrar informações do arquivo
                            document.getElementById('arquivo-nome-projeto').textContent = arquivo.name;
                            document.getElementById('arquivo-tamanho-projeto').textContent =
                                formatarTamanhoArquivo(arquivo.size);
                            document.getElementById('arquivo-info-projeto').classList.remove('hidden');
                        }
                    }
                    break;
            }

            if (!isValid) {
                errorElement.textContent = errorMessage;
                errorElement.classList.remove('hidden');
                elemento.classList.add('border-red-500');
            } else {
                elemento.classList.add('border-green-500');
            }

            // Verificar se todos os campos estão válidos
            verificarFormularioIniciacaoCientifica();

            return isValid;
        }

        // Função para verificar se o formulário de Iniciação Científica é válido
        function verificarFormularioIniciacaoCientifica() {
            const campos = ['nomeProjeto', 'cargaHorariaProjeto', 'declaracaoProjeto'];
            let todosValidos = true;

            campos.forEach(campo => {
                const elemento = document.getElementById(campo);
                if (campo === 'declaracaoProjeto') {
                    // Para arquivo, verificar se foi selecionado
                    if (!elemento.files || elemento.files.length === 0 || elemento.classList.contains('border-red-500')) {
                        todosValidos = false;
                    }
                } else {
                    // Para outros campos, verificar se têm valor e não têm erro
                    if (!elemento.value || elemento.classList.contains('border-red-500')) {
                        todosValidos = false;
                    }
                }
            });

            document.getElementById('btnConfirmarProjeto').disabled = !todosValidos;
        }

        // Função para confirmar Iniciação Científica
        function confirmarIniciacaoCientifica() {
            // Validar todos os campos antes de submeter
            const campos = ['nomeProjeto', 'cargaHorariaProjeto', 'declaracaoProjeto'];
            let todosValidos = true;

            campos.forEach(campo => {
                if (!validarCampoIniciacaoCientifica(campo)) {
                    todosValidos = false;
                }
            });

            if (todosValidos) {
                // Validar se atividadeIniciacaoCientificaId está definido
                if (!atividadeIniciacaoCientificaId) {
                    alert('Erro: ID da atividade não encontrado. Tente novamente.');
                    return;
                }

                // Desabilitar botão para evitar duplo clique
                const btnConfirmar = document.getElementById('btnConfirmarProjeto');
                btnConfirmar.disabled = true;
                btnConfirmar.textContent = 'Cadastrando...';

                // Preparar dados do formulário
                const formData = new FormData();
                formData.append('atividades_por_resolucao_id', atividadeIniciacaoCientificaId);
                formData.append('titulo', document.getElementById('nomeProjeto').value.trim());
                const cargaVal = parseInt(document.getElementById('cargaHorariaProjeto').value);
                const rest = window.__ultimoRestantePesquisaProjeto || { restante: cargaVal };
                if (cargaVal > rest.restante) {
                    alert(`As horas informadas excedem o restante disponível (${rest.restante}h). ${gerarSugestoesPesquisa(rest.restante, 1, rest.restante) ? 'Sugestões: ' + gerarSugestoesPesquisa(rest.restante, 1, rest.restante) : ''}`);
                    btnConfirmar.disabled = false;
                    btnConfirmar.textContent = 'Confirmar';
                    return;
                }
                formData.append('ch_solicitada', cargaVal);
                formData.append('descricao', `Projeto de Iniciação Científica: ${document.getElementById('nomeProjeto').value.trim()}`);

                // Adicionar arquivo se selecionado
                const arquivo = document.getElementById('declaracaoProjeto').files[0];
                if (arquivo) {
                    formData.append('declaracao', arquivo);
                }

                // Enviar via AJAX
                fetch('../../backend/api/routes/cadastrar_atividades.php', {
                        method: 'POST',
                        headers: {
                            'X-API-Key': 'frontend-gerenciamento-acc-2025',
                            'Authorization': 'Bearer ' + localStorage.getItem('acc_jwt_token')
                        },
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response headers:', response.headers);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        return response.text().then(text => {
                            console.log('Response text:', text);
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('JSON parse error:', e);
                                throw new Error('Resposta inválida do servidor');
                            }
                        });
                    })
                    .then(async data => {
                        console.log('Parsed data:', data);
                        
                        if (data.success) {
                            // Mostrar mensagem de sucesso
                            alert('Projeto de Iniciação Científica cadastrado com sucesso!');

                            // Atualizar automaticamente a seção "Minhas Atividades"
                            await atualizarMinhasAtividades();

                            // Fechar modal
                            fecharModalIniciacaoCientifica();

                            // Recarregar lista de atividades
                            carregarAtividades();
                        } else {
                            alert('Erro ao cadastrar atividade: ' + (data.message || data.error || 'Erro desconhecido'));
                        }
                    })
                    .catch(error => {
                        console.error('Erro na requisição:', error);
                        alert('Erro ao cadastrar atividade. Tente novamente.');
                    })
                    .finally(() => {
                        // Reabilitar botão
                        btnConfirmar.disabled = false;
                        btnConfirmar.textContent = 'Confirmar';
                    });
            }
        }

        // Event listeners para os modais
        document.addEventListener('DOMContentLoaded', function() {
            // Event listeners para o modal de Iniciação Científica
            document.getElementById('modalIniciacaoCientifica').addEventListener('click', function(e) {
                if (e.target === this) {
                    fecharModalIniciacaoCientifica();
                }
            });

            // Event listeners para o modal de Publicação de Artigo
            document.getElementById('modalPublicacaoArtigo').addEventListener('click', function(e) {
                if (e.target === this) {
                    fecharModalPublicacaoArtigo();
                }
            });

            // Fechar modais com ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modalIniciacaoCientifica = document.getElementById('modalIniciacaoCientifica');
                    const modalPublicacaoArtigo = document.getElementById('modalPublicacaoArtigo');

                    if (!modalIniciacaoCientifica.classList.contains('hidden')) {
                        fecharModalIniciacaoCientifica();
                    } else if (!modalPublicacaoArtigo.classList.contains('hidden')) {
                        fecharModalPublicacaoArtigo();
                    }
                }
            });

            // Gerenciar foco no modal de Iniciação Científica
            document.getElementById('modalIniciacaoCientifica').addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    const focusableElements = this.querySelectorAll(
                        'input:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])'
                    );
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];

                    if (e.shiftKey) {
                        if (document.activeElement === firstElement) {
                            e.preventDefault();
                            lastElement.focus();
                        }
                    } else {
                        if (document.activeElement === lastElement) {
                            e.preventDefault();
                            firstElement.focus();
                        }
                    }
                }
            });

            // Gerenciar foco no modal de Publicação de Artigo
            document.getElementById('modalPublicacaoArtigo').addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    const focusableElements = this.querySelectorAll(
                        'input:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])'
                    );
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];

                    if (e.shiftKey) {
                        if (document.activeElement === firstElement) {
                            e.preventDefault();
                            lastElement.focus();
                        }
                    } else {
                        if (document.activeElement === lastElement) {
                            e.preventDefault();
                            firstElement.focus();
                        }
                    }
                }
            });
        });

        // Variáveis globais para os modais
        let atividadePublicacaoArtigoId = null;
        let horasMaximasPublicacaoArtigo = null;

        // Funções para o Modal de Publicação de Artigo
        function abrirModalPublicacaoArtigo(atividadeId) {
            console.log('🔴 FUNÇÃO CHAMADA - abrirModalPublicacaoArtigo, ID:', atividadeId);
            atividadePublicacaoArtigoId = atividadeId;

            // Buscar dados da atividade para obter horas máximas
            const atividade = todasAtividades.find(a => a.atividades_por_resolucao_id === atividadeId);
            if (atividade) {
                console.log('🔴 ATIVIDADE ENCONTRADA:', atividade.nome, 'Horas máximas:', atividade.horas_max);
                horasMaximasPublicacaoArtigo = parseInt(atividade.horas_max);
                // Atualizar o atributo max do campo de carga horária
                const campoCargaHoraria = document.getElementById('cargaHorariaArtigo');
                campoCargaHoraria.max = horasMaximasPublicacaoArtigo;

            }

            console.log('🔴 ABRINDO MODAL DE PUBLICAÇÃO DE ARTIGO');
            document.getElementById('modalPublicacaoArtigo').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Limpar formulário
            document.getElementById('formPublicacaoArtigo').reset();
            document.getElementById('btnConfirmarArtigo').disabled = true;

            // Limpar erros
            const campos = ['nomeArtigo', 'cargaHorariaArtigo', 'quantidadePublicacoes', 'declaracaoArtigo'];
            campos.forEach(campo => {
                const elemento = document.getElementById(campo);
                const errorElement = document.getElementById(campo + '-error');
                elemento.classList.remove('border-red-500', 'border-green-500');
                elemento.classList.add('border-gray-300');
                errorElement.classList.add('hidden');
            });

            // Ocultar informações do arquivo
            document.getElementById('arquivo-info-artigo').classList.add('hidden');

            // Focar no primeiro campo
            setTimeout(() => {
                document.getElementById('nomeArtigo').focus();
            }, 100);
        }

        function fecharModalPublicacaoArtigo() {
            console.log('🔴 FECHANDO MODAL DE PUBLICAÇÃO DE ARTIGO');
            document.getElementById('modalPublicacaoArtigo').classList.add('hidden');
            document.body.style.overflow = 'auto';
            atividadePublicacaoArtigoId = null;
        }

        // Função para calcular automaticamente a carga horária baseada na quantidade de publicações
        function calcularCargaHorariaArtigo() {
            const quantidadeInput = document.getElementById('quantidadePublicacoes');
            const cargaHorariaInput = document.getElementById('cargaHorariaArtigo');
            const quantidadeError = document.getElementById('quantidadePublicacoes-error');
            const infoDiv = document.getElementById('calculo-artigo-info');
            const warningDiv = document.getElementById('calculo-artigo-warning');
            const calculoDetalhes = document.getElementById('calculo-artigo-detalhes');

            const quantidade = parseInt(quantidadeInput.value) || 0;
            
            // Obter ano de matrícula do aluno
            const anoMatricula = obterAnoMatriculaAluno();
            
            // Determinar horas por publicação baseado no ano de matrícula
            let horasPorPublicacao;
            if (anoMatricula >= 2023) {
                horasPorPublicacao = 10; // Alunos 2023+: 10h por publicação
            } else {
                horasPorPublicacao = 20; // Alunos 2017-2022: 20h por publicação
            }
            
            let cargaCalculada = quantidade * horasPorPublicacao;

            // Aplicar limite máximo de 40h
            const rest = window.__ultimoRestantePesquisaArtigo || { restante: 40 };
            const cargaHoraria = Math.min(cargaCalculada, 40, rest.restante);

            // Atualizar o campo de carga horária
            cargaHorariaInput.value = cargaHoraria > 0 ? cargaHoraria : '';

            // Mostrar/ocultar informações do cálculo
            if (quantidade >= 1) {
                // Preparar texto do cálculo detalhado
                let detalhes = `${quantidade} publicação${quantidade > 1 ? 'ões' : ''} × ${horasPorPublicacao}h = ${cargaCalculada}h`;

                // Se houve limitação, adicionar informação
                if (cargaCalculada > 40 || cargaCalculada > rest.restante) {
                    const lim = Math.min(40, rest.restante);
                    detalhes += ` → limitado a ${lim}h`;
                    warningDiv.classList.remove('hidden');
                } else {
                    warningDiv.classList.add('hidden');
                }

                // Atualizar texto e mostrar informações
                calculoDetalhes.textContent = detalhes;
                infoDiv.classList.remove('hidden');

                // Validação visual - sempre válido se quantidade >= 1
                quantidadeError.classList.add('hidden');
                quantidadeInput.classList.remove('border-red-500');
                quantidadeInput.classList.add('border-green-500');
                cargaHorariaInput.classList.remove('border-red-500');
                cargaHorariaInput.classList.add('border-green-500');
            } else {
                // Ocultar informações quando não há quantidade válida
                infoDiv.classList.add('hidden');
                warningDiv.classList.add('hidden');

                quantidadeError.classList.add('hidden');
                quantidadeInput.classList.remove('border-red-500', 'border-green-500');
                quantidadeInput.classList.add('border-gray-300');
                cargaHorariaInput.classList.remove('border-red-500', 'border-green-500');
                cargaHorariaInput.classList.add('border-gray-300');
            }
        }

        // Função de validação para campos de Publicação de Artigo
        function validarCampoPublicacaoArtigo(campo) {
            const elemento = document.getElementById(campo);
            const errorElement = document.getElementById(campo + '-error');
            let isValid = true;
            let errorMessage = '';

            // Limpar erro anterior
            errorElement.classList.add('hidden');
            elemento.classList.remove('border-red-500');

            switch (campo) {
                case 'nomeArtigo':
                    if (!elemento.value.trim()) {
                        errorMessage = 'Título do artigo é obrigatório';
                        isValid = false;
                    } else if (elemento.value.trim().length < 5) {
                        errorMessage = 'Título do artigo deve ter pelo menos 5 caracteres';
                        isValid = false;
                    }
                    break;

                case 'cargaHorariaArtigo':
                    const carga = parseInt(elemento.value);
                    if (!elemento.value) {
                        errorMessage = 'Carga horária é obrigatória';
                        isValid = false;
                    } else if (carga < 20) {
                        errorMessage = 'A carga horária mínima é 20 horas (1 publicação)';
                        isValid = false;
                    } else if (carga > 40) {
                        errorMessage = 'A carga horária máxima é 40 horas';
                        isValid = false;
                    }
                    break;

                case 'quantidadePublicacoes':
                    const quantidade = parseInt(elemento.value);
                    if (!elemento.value) {
                        errorMessage = 'Quantidade de publicações é obrigatória';
                        isValid = false;
                    } else if (quantidade < 1) {
                        errorMessage = 'Quantidade deve ser pelo menos 1 publicação';
                        isValid = false;
                    }
                    break;



                case 'declaracaoArtigo':
                    if (!elemento.files || elemento.files.length === 0) {
                        errorMessage = 'Declaração/Certificado é obrigatório';
                        isValid = false;
                    } else {
                        const arquivo = elemento.files[0];
                        const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                        const tamanhoMaximo = 5 * 1024 * 1024; // 5MB

                        if (!tiposPermitidos.includes(arquivo.type)) {
                            errorMessage = 'Formato de arquivo não permitido. Use PDF, JPG ou PNG';
                            isValid = false;
                        } else if (arquivo.size > tamanhoMaximo) {
                            errorMessage = 'Arquivo muito grande. Tamanho máximo: 5MB';
                            isValid = false;
                        } else {
                            // Mostrar informações do arquivo
                            document.getElementById('arquivo-nome-artigo').textContent = arquivo.name;
                            document.getElementById('arquivo-tamanho-artigo').textContent =
                                formatarTamanhoArquivo(arquivo.size);
                            document.getElementById('arquivo-info-artigo').classList.remove('hidden');
                        }
                    }
                    break;
            }

            if (!isValid) {
                errorElement.textContent = errorMessage;
                errorElement.classList.remove('hidden');
                elemento.classList.add('border-red-500');
            } else {
                elemento.classList.add('border-green-500');
            }

            // Verificar se todos os campos estão válidos
            verificarFormularioPublicacaoArtigo();

            return isValid;
        }

        // Função para verificar se o formulário de Publicação de Artigo é válido
        function verificarFormularioPublicacaoArtigo() {
            const campos = ['nomeArtigo', 'cargaHorariaArtigo', 'quantidadePublicacoes', 'declaracaoArtigo'];
            let todosValidos = true;

            campos.forEach(campo => {
                const elemento = document.getElementById(campo);
                if (!elemento.value || elemento.classList.contains('border-red-500')) {
                    todosValidos = false;
                }
            });



            document.getElementById('btnConfirmarArtigo').disabled = !todosValidos;
        }

        // Função para confirmar Publicação de Artigo
        function confirmarPublicacaoArtigo() {
            if (!atividadePublicacaoArtigoId) {
                alert('Erro: ID da atividade não foi definido. Tente fechar e abrir o modal novamente.');
                return;
            }

            // Validar todos os campos antes de submeter
            const campos = ['nomeArtigo', 'cargaHorariaArtigo', 'quantidadePublicacoes', 'declaracaoArtigo'];
            let todosValidos = true;

            campos.forEach(campo => {
                if (!validarCampoPublicacaoArtigo(campo)) {
                    todosValidos = false;
                }
            });

            if (todosValidos) {
                // Desabilitar botão para evitar duplo clique
                const btnConfirmar = document.getElementById('btnConfirmarArtigo');
                btnConfirmar.disabled = true;
                btnConfirmar.textContent = 'Cadastrando...';

                // Obter valores dos campos
                const nomeArtigo = document.getElementById('nomeArtigo').value.trim();
                const quantidadePublicacoes = document.getElementById('quantidadePublicacoes').value;
                const cargaHoraria = document.getElementById('cargaHorariaArtigo').value;

                // Preparar dados do formulário para a nova rota
                const formData = new FormData();
                formData.append('atividades_por_resolucao_id', atividadePublicacaoArtigoId);
                formData.append('titulo', nomeArtigo); // Campo título do artigo vai para coluna titulo
                formData.append('ch_solicitada', cargaHoraria);
                formData.append('descricao', `Publicação de artigo - Quantidade de publicações: ${quantidadePublicacoes}`);

                // Adicionar arquivo se selecionado
                const arquivo = document.getElementById('declaracaoArtigo').files[0];
                if (arquivo) {
                    formData.append('declaracao', arquivo);
                }

                console.log('Dados sendo enviados para publicação de artigo:');
                console.log('atividades_por_resolucao_id:', atividadePublicacaoArtigoId);
                console.log('titulo (nome do artigo):', nomeArtigo);
                console.log('ch_solicitada:', cargaHoraria);
                console.log('descricao:', `Publicação de artigo - Quantidade de publicações: ${quantidadePublicacoes}`);

                // Enviar via AJAX para a nova rota
                fetch('../../backend/api/routes/cadastrar_atividades.php', {
                        method: 'POST',
                        headers: {
                            'X-API-Key': 'frontend-gerenciamento-acc-2025',
                            'Authorization': 'Bearer ' + localStorage.getItem('acc_jwt_token')
                        },
                        body: formData
                    })
                    .then(response => response.text().then(text => {
                        console.log('Resposta bruta do servidor:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Erro ao fazer parse do JSON:', e);
                            throw new Error('Resposta inválida do servidor: ' + text);
                        }
                    }))
                    .then(async data => {
                        console.log('Dados processados:', data);
                        if (data.success) {
                            // Mostrar mensagem de sucesso
                            alert('Atividade de pesquisa cadastrada com sucesso!');

                            // Atualizar automaticamente a seção "Minhas Atividades"
                            await atualizarMinhasAtividades();

                            // Fechar modal
                            fecharModalPublicacaoArtigo();

                            // Recarregar lista de atividades
                            carregarAtividades();
                        } else {
                            console.error('Erro retornado pelo backend:', data);
                            alert('Erro ao cadastrar atividade: ' + (data.error || 'Erro desconhecido'));
                        }
                    })
                    .catch(error => {
                        console.error('Erro na requisição:', error);
                        alert('Erro ao cadastrar atividade. Tente novamente.');
                    })
                    .finally(() => {
                        // Reabilitar botão
                        btnConfirmar.disabled = false;
                        btnConfirmar.textContent = 'Confirmar';
                    });
            }
        }

        // Função para atualizar a seção "Minhas Atividades"
        async function atualizarMinhasAtividades() {
            try {
                const response = await AuthClient.request('/api/atividades/aluno', {
                    method: 'GET'
                });

                if (response.success && response.data) {
                    // Aqui você pode atualizar a seção "Minhas Atividades" se ela existir na página
                    console.log('Atividades atualizadas:', response.data);
                    // Se houver uma função específica para atualizar a seção, chame-a aqui
                    // Por exemplo: atualizarSecaoMinhasAtividades(response.data);
                }
            } catch (error) {
                console.error('Erro ao atualizar Minhas Atividades:', error);
            }
        }



        // Carregar atividades ao inicializar a página
        carregarAtividades();
    </script>
</body>

</html>