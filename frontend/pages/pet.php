<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividades PET - Sistema ACC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../assets/js/auth.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        // Verificar autenticação ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔐 === VERIFICAÇÃO DE AUTENTICAÇÃO (DOMContentLoaded) ===');
            console.log('📍 Página atual:', window.location.pathname);
            console.log('🕐 Timestamp:', new Date().toISOString());
            
            // Verificar se AuthClient está disponível
            if (typeof AuthClient === 'undefined') {
                console.error('❌ AuthClient não está disponível no DOMContentLoaded!');
                alert('Erro: Sistema de autenticação não carregado.');
                window.location.href = 'login.php';
                return;
            }
            console.log('✅ AuthClient está disponível');
            
            // Verificar localStorage diretamente
            const token = localStorage.getItem('acc_jwt_token');
            const apiKey = localStorage.getItem('acc_api_key');
            const userData = localStorage.getItem('acc_user_data');
            
            console.log('🎫 Token presente:', !!token);
            console.log('🔑 API Key presente:', !!apiKey);
            console.log('👤 User Data presente:', !!userData);
            
            if (token) {
                console.log('🎫 Token (primeiros 50 chars):', token.substring(0, 50) + '...');
                try {
                    const payload = JSON.parse(atob(token.split('.')[1]));
                    const now = Math.floor(Date.now() / 1000);
                    console.log('⏰ Token expira em:', new Date(payload.exp * 1000));
                    console.log('⏰ Hora atual:', new Date());
                    console.log('⏰ Token válido:', payload.exp > now);
                } catch (e) {
                    console.error('❌ Erro ao decodificar token:', e);
                }
            }
            
            if (userData) {
                try {
                    const user = JSON.parse(userData);
                    console.log('👤 Tipo de usuário:', user.tipo);
                    console.log('👤 ID do usuário:', user.id);
                    console.log('👤 Nome do usuário:', user.nome);
                } catch (e) {
                    console.error('❌ Erro ao parsear dados do usuário:', e);
                }
            }
            
            // Verificar AuthClient methods
            console.log('🔍 AuthClient.getToken():', AuthClient.getToken());
            console.log('🔍 AuthClient.getUser():', AuthClient.getUser());
            console.log('🔍 AuthClient.isLoggedIn():', AuthClient.isLoggedIn());
            
            const isLoggedIn = AuthClient.isLoggedIn();
            
            if (!isLoggedIn) {
                console.log('❌ Usuário não autenticado no DOMContentLoaded, redirecionando para login');
                alert('Sua sessão expirou. Você será redirecionado para a página de login.');
                window.location.href = 'login.php';
                return;
            }
            
            const user = AuthClient.getUser();
            if (!user || user.tipo !== 'aluno') {
                console.log('❌ Usuário não é aluno ou dados inválidos, fazendo logout');
                AuthClient.logout();
                return;
            }
            
            console.log('✅ Autenticação válida no DOMContentLoaded para aluno:', user.nome || user.email);
        });
    </script>
</head>
<body class="bg-white" style="background-color:#FFFFFF">
    <!-- Navegação -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold" style="color: #1e3a8a">Sistema ACC</h1>
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
                <div class="mb-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-block text-3xl" style="color:#1E3A8A">📋</span>
                        <h2 class="text-3xl font-bold" style="color: #1e3a8a">Atividades PET</h2>
                    </div>
                    <p class="text-gray-600 mt-2">Programa de Educação Tutorial - Selecione uma atividade PET para cadastrar</p>
                </div>
                
                <!-- Alerta de erro para atividades -->
                <div id="alertaAtividades" class="hidden mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-800">⚠️ Não foi possível carregar as atividades PET. Verifique a conexão com o banco de dados.</p>
                </div>
            </div>

            <!-- Badge removido -->

            <!-- Informações de Resultados -->
            <div id="infoResultados" class="mb-4 text-sm text-gray-600 hidden">
                <!-- Será preenchido dinamicamente -->
            </div>

            <!-- Container das atividades PET -->
            <div id="atividadesContainer" class="mb-8">
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 mx-auto" style="border-color:#1e3a8a"></div>
                    <p class="text-gray-500 mt-4">Carregando atividades PET...</p>
                </div>
            </div>

            <!-- Paginação -->
            <div id="paginacao" class="flex justify-center items-center space-x-2 mt-6 hidden">
                <!-- Será preenchido dinamicamente -->
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div id="modalDetalhes" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Detalhes da Atividade PET</h3>
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
                    <button onclick="abrirModalSelecao()" class="px-4 py-2 text-white rounded-lg hover:opacity-90 transition duration-200" style="background-color: #1e3a8a">
                        Selecionar Atividade PET
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
                    <h3 class="text-xl font-bold text-gray-900" style="color: #1e3a8a">Cadastrar Atividade PET</h3>
                    <button onclick="fecharModalSelecao()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="formSelecaoAtividade" class="space-y-6">
                    <!-- Informações da Atividade PET Selecionada -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h4 class="font-semibold text-blue-800 mb-2">Atividade PET Selecionada:</h4>
                        <div id="infoAtividadeSelecionada" class="text-sm text-blue-700">
                            <!-- Será preenchido dinamicamente -->
                        </div>
                    </div>

                    <!-- Campos do Formulário -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nome do Projeto PET -->
                        <div id="campoProjeto">
                            <label for="projetoNome" class="block text-sm font-medium text-gray-700 mb-2">
                                Projeto PET *
                            </label>
                            <input type="text" id="projetoNome" name="projetoNome"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                                   placeholder="Digite o nome do projeto PET" required>
                        </div>
                        
                        <!-- Horas Realizadas -->
                        <div>
                            <label for="horasRealizadas" class="block text-sm font-medium text-gray-700 mb-2">
                                Horas Realizadas *
                            </label>
                            <input type="number" id="horasRealizadas" name="horasRealizadas" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                                   placeholder="Ex: 10" min="1" max="" required>
                            <p class="text-xs text-gray-500 mt-1">Máximo: <span id="maxHoras">--</span> horas</p>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div>
                        <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">
                            Observações
                        </label>
                        <textarea id="observacoes" name="observacoes" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                                  placeholder="Descreva detalhes adicionais sobre a atividade PET realizada..."></textarea>
                    </div>

                    <!-- Upload de Declaração -->
                    <div>
                        <label for="declaracao" class="block text-sm font-medium text-gray-700 mb-2">
                            Declaração/Certificado PET *
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                            <input type="file" id="declaracao" name="declaracao" accept=".pdf,.jpg,.jpeg,.png" 
                                   class="hidden" onchange="mostrarArquivoSelecionado(this)" required>
                            <label for="declaracao" class="cursor-pointer">
                                <div class="text-gray-400 mb-2">
                                    <svg class="mx-auto h-12 w-12" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium text-blue-700">Clique para enviar</span> ou arraste o arquivo
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
                                style="background-color: #1e3a8a">
                            Cadastrar Atividade PET
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
        // Variáveis globais
        let atividadesPET = [];
        let atividadeSelecionada = null;
        let paginaAtual = 1;
        const limitePorPagina = 12;

        // Verificar autenticação
        function verificarAutenticacao() {
            console.log('🔐 === VERIFICAÇÃO DE AUTENTICAÇÃO ===');
            console.log('📍 Página atual:', window.location.pathname);
            
            // Verificar se AuthClient está disponível
            if (typeof AuthClient === 'undefined') {
                console.error('❌ AuthClient não está disponível!');
                window.location.href = 'login.php';
                return false;
            }
            
            // Verificar token JWT
            const token = localStorage.getItem('acc_jwt_token');
            console.log('🎫 Token JWT presente:', !!token);
            if (token) {
                console.log('🎫 Token JWT (primeiros 50 chars):', token.substring(0, 50) + '...');
                
                // Verificar se o token não está expirado
                try {
                    const payload = JSON.parse(atob(token.split('.')[1]));
                    const now = Math.floor(Date.now() / 1000);
                    console.log('⏰ Token expira em:', new Date(payload.exp * 1000));
                    console.log('⏰ Hora atual:', new Date());
                    console.log('⏰ Token válido:', payload.exp > now);
                } catch (e) {
                    console.error('❌ Erro ao decodificar token:', e);
                }
            }
            
            // Verificar API Key
            const apiKey = localStorage.getItem('acc_api_key');
            console.log('🔑 API Key presente:', !!apiKey);
            if (apiKey) {
                console.log('🔑 API Key (primeiros 20 chars):', apiKey.substring(0, 20) + '...');
            }
            
            // Verificar dados do usuário
            const userData = localStorage.getItem('acc_user_data');
            console.log('👤 Dados do usuário presentes:', !!userData);
            if (userData) {
                try {
                    const user = JSON.parse(userData);
                    console.log('👤 Tipo de usuário:', user.tipo);
                    console.log('👤 ID do usuário:', user.id);
                    console.log('👤 Nome do usuário:', user.nome);
                    console.log('👤 Curso do usuário:', user.curso);
                } catch (e) {
                    console.error('❌ Erro ao parsear dados do usuário:', e);
                }
            }
            
            // Verificar login
            const isLoggedIn = AuthClient.isLoggedIn();
            console.log('✅ Usuário autenticado:', isLoggedIn);
            
            if (!isLoggedIn) {
                console.log('❌ Usuário não autenticado, redirecionando para login');
                window.location.href = 'login.php';
                return false;
            }
            
            const user = AuthClient.getUser();
            if (!user || user.tipo !== 'aluno') {
                console.log('❌ Usuário não é aluno ou dados inválidos, fazendo logout');
                AuthClient.logout();
                return false;
            }
            
            // Verificar se é aluno de BSI
            if (user.curso && !user.curso.toLowerCase().includes('sistemas')) {
                console.log('⚠️ Usuário não é do curso de Sistemas de Informação');
                // Ainda assim permite o acesso, mas mostra um alerta
                setTimeout(() => {
                    alert('Esta página é específica para atividades PET do curso de Sistemas de Informação.');
                }, 1000);
            }
            
            console.log('✅ Autenticação válida para aluno:', user.nome || user.email);
            return true;
        }

        // Buscar atividades PET
        async function buscarAtividadesPET(pagina = 1, busca = '', ordenacao = 'nome', direcao = 'ASC') {
            try {
                console.log('🔍 Buscando atividades PET...');
                console.log('📄 Página:', pagina);
                console.log('🔍 Busca:', busca);
                console.log('📊 Ordenação:', ordenacao, direcao);
                
                // Verificar autenticação
                if (!verificarAutenticacao()) {
                    return;
                }
                
                // Obter token e API key
                const token = AuthClient.getToken();
                const apiKey = localStorage.getItem('acc_api_key');
                const user = AuthClient.getUser();
                
                if (!token || !apiKey) {
                    console.error('❌ Token ou API Key não encontrados');
                    throw new Error('Token ou API Key não encontrados');
                }
                
                // Construir URL da API (rota correta sob Gerenciamento-ACC)
                const url = new URL('/Gerenciamento-ACC/backend/api/routes/listar_atividades_disponiveis.php', window.location.origin);
                
                // Adicionar parâmetros (listar tipo PET)
                url.searchParams.append('tipo', 'pet');
                url.searchParams.append('pagina', pagina);
                url.searchParams.append('limite', limitePorPagina);
                url.searchParams.append('ordenacao', ordenacao);
                url.searchParams.append('direcao', direcao);
                url.searchParams.append('busca', busca);
                
                console.log('🌐 URL da requisição:', url.toString());
                
                // Fazer requisição
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'X-API-Key': apiKey,
                        'Content-Type': 'application/json'
                    }
                });
                
                console.log('📊 Status da resposta:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('📦 Dados recebidos:', data);
                
                if (data.success) {
                    atividadesPET = Array.isArray(data.data?.atividades) ? data.data.atividades : [];

                    try {
                        const user = AuthClient.getUser() || {};
                        const anoMatricula = (user.matricula && typeof user.matricula === 'string') ? parseInt(user.matricula.substring(0,4)) : null;
                        const cursoNomeNorm = (user.curso_nome || '').toString().trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        const isBSI = (user.curso_id === 2) || cursoNomeNorm.includes('sistemas de informacao') || cursoNomeNorm.includes('si') || cursoNomeNorm.includes('bsi');
                        const isBCC = (user.curso_id === 1) || cursoNomeNorm.includes('ciencia da computacao') || cursoNomeNorm.includes('bcc');

                        if ((!atividadesPET || atividadesPET.length === 0) && (isBSI || isBCC)) {
                            const base = [];
                            if (isBSI) {
                                base.push({
                                    id: 32,
                                    atividade_complementar_id: 32,
                                    nome: 'PET – Programa de Educação Tutorial',
                                    categoria: 'PET',
                                    tipo: 'PET',
                                    descricao: 'Participação no programa PET',
                                    carga_horaria_maxima: 90,
                                    horas_max: 90,
                                    resolucao_tipo_atividade_id: 14
                                });
                            } else if (isBCC && anoMatricula && anoMatricula >= 2023) {
                                base.push({
                                    id: 15,
                                    atividade_complementar_id: 15,
                                    nome: 'PET – Programa de Educação Tutorial',
                                    categoria: 'PET',
                                    tipo: 'PET',
                                    descricao: 'Participação no programa PET',
                                    carga_horaria_maxima: 40,
                                    horas_max: 40,
                                    resolucao_tipo_atividade_id: 8
                                });
                            }
                            if (base.length) atividadesPET = base;
                        }
                    } catch (regraErr) {
                        console.warn('Falha ao aplicar fallback PET:', regraErr);
                    }

                    const total = Number.isFinite(Number(data.data?.total)) ? Number(data.data.total) : atividadesPET.length;
                    console.log('🐾 Atividades PET encontradas:', atividadesPET.length);
                    exibirAtividadesPET(atividadesPET, total, pagina);
                } else {
                    throw new Error(data.message || 'Erro ao buscar atividades PET');
                }
                
            } catch (error) {
                console.error('❌ Erro ao buscar atividades PET:', error);
                exibirErro('Erro ao carregar atividades PET: ' + error.message);
            }
        }

        // Exibir atividades PET em cards
        function exibirAtividadesPET(atividades, total, pagina) {
            const container = document.getElementById('atividadesContainer');
            const infoResultados = document.getElementById('infoResultados');
            const paginacao = document.getElementById('paginacao');
            
            if (!atividades || atividades.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🐾</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma atividade PET encontrada</h3>
                        <p class="text-gray-500">Tente ajustar os filtros de busca ou entre em contato com o coordenador.</p>
                    </div>
                `;
                infoResultados.classList.add('hidden');
                paginacao.classList.add('hidden');
                return;
            }
            
            // Atualizar informações de resultados com fallback seguro
            const safeTotal = Number.isFinite(Number(total)) ? Number(total) : atividades.length;
            const inicio = (pagina - 1) * limitePorPagina + 1;
            const fim = Math.min(pagina * limitePorPagina, safeTotal);
            infoResultados.innerHTML = `Mostrando ${inicio}-${fim} de ${safeTotal} atividades PET`;
            infoResultados.classList.remove('hidden');
            
            // Criar cards de atividades PET
            let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
            
            atividades.forEach(atividade => {
                html += `
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                        <!-- Cabeçalho do card em azul escuro -->
                        <div class="rounded-t-lg px-4 py-4" style="background-color:#1e3a8a;">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start">
                                    
                                    <div>
                                        <h3 class="text-xl" style="color:#ffffff;font-family:system-ui;font-weight:700;">${atividade.nome}</h3>
                                        <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs" 
                                              style="font-family:system-ui;font-weight:600;background-color:#DBEAFE;color:#1E3A8A;">
                                            ${atividade.tipo || atividade.categoria || 'PET'}
                                        </span>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <p class="text-gray-700 text-sm mb-4">${atividade.descricao || 'Atividade do Programa de Educação Tutorial'}</p>
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="font-medium" style="color:#1e3a8a;">Carga Horária Máxima:</span>
                                    <span class="font-medium text-gray-900" style="font-family:system-ui;font-weight:400;">${atividade.carga_horaria_maxima}h</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium" style="color:#1e3a8a;">Tipo:</span>
                                    <span class="font-medium text-gray-900" style="font-family:system-ui;font-weight:400;">${atividade.tipo || atividade.categoria || 'PET'}</span>
                            </div>
                            </div>
                            
                            <div class="mt-4 flex gap-2">
                                <button onclick="verDetalhesPET(${atividade.id})" 
                                        class="w-1/2 px-4 py-2 text-sm bg-white rounded-lg transition duration-200"
                                        style="border:1px solid #1e3a8a;color:#1e3a8a;">
                                    Ver Detalhes
                                </button>
                                <button onclick="selecionarAtividadePET(${atividade.id})" 
                                        class="w-1/2 px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition duration-200" 
                                        style="background-color:#1e3a8a;">
                                    Selecionar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
            
            // Atualizar paginação
            atualizarPaginacao(total, pagina);
        }

        // Atualizar paginação
        function atualizarPaginacao(total, paginaAtual) {
            const paginacao = document.getElementById('paginacao');
            const totalPaginas = Math.ceil(total / limitePorPagina);
            
            if (totalPaginas <= 1) {
                paginacao.classList.add('hidden');
                return;
            }
            
            paginacao.classList.remove('hidden');
            
            let html = '';
            
            // Botão anterior
            if (paginaAtual > 1) {
                html += `
                    <button onclick="mudarPagina(${paginaAtual - 1})" 
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                        Anterior
                    </button>
                `;
            }
            
            // Números das páginas
            for (let i = 1; i <= totalPaginas; i++) {
                if (i === paginaAtual) {
                    html += `
                        <button class="px-3 py-2 text-sm text-white rounded-lg" style="background-color: #1e3a8a">
                            ${i}
                        </button>
                    `;
                } else {
                    html += `
                        <button onclick="mudarPagina(${i})" 
                                class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                            ${i}
                        </button>
                    `;
                }
            }
            
            // Botão próximo
            if (paginaAtual < totalPaginas) {
                html += `
                    <button onclick="mudarPagina(${paginaAtual + 1})" 
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                        Próximo
                    </button>
                `;
            }
            
            paginacao.innerHTML = html;
        }

        // Mudar página
        function mudarPagina(novaPagina) {
            paginaAtual = novaPagina;
            buscarAtividadesPET(novaPagina);
        }

        // Ver detalhes da atividade PET
        function verDetalhesPET(id) {
            const atividade = atividadesPET.find(a => a.id === id);
            if (!atividade) return;
            
            atividadeSelecionada = atividade;
            
            const modal = document.getElementById('modalDetalhes');
            const conteudo = document.getElementById('conteudoDetalhes');
            
            conteudo.innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-start mb-4">
                        <div class="text-3xl mr-4">📒</div>
                        <div>
                            <h4 class="text-2xl text-gray-900" style="font-family:system-ui;font-weight:700;">${atividade.nome}</h4>
                            <span class="mt-1 text-sm font-semibold" style="color:#1E3A8A;">
                                ${atividade.tipo || atividade.categoria || 'PET'}
                            </span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h5 class="font-medium text-gray-900 mb-2">Descrição</h5>
                        <p class="text-gray-700">${atividade.descricao || 'Atividade do Programa de Educação Tutorial (PET)'}</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h5 class="font-medium text-blue-900 mb-1">Carga Horária Máxima</h5>
                            <p class="text-2xl font-bold text-blue-700">${atividade.carga_horaria_maxima}h</p>
                        </div>
                        
                        
                    </div>
                    
                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                        <h5 class="font-medium text-yellow-800 mb-2">⚠️ Importante</h5>
                        <p class="text-yellow-700 text-sm">
                            Esta atividade é específica para o Programa de Educação Tutorial (PET). 
                            Certifique-se de que você participou efetivamente das atividades do PET antes de cadastrar.
                        </p>
                    </div>
                </div>
            `;
            
            modal.classList.remove('hidden');
        }

        // Selecionar atividade PET
        function selecionarAtividadePET(id) {
            const atividade = atividadesPET.find(a => a.id === id);
            if (!atividade) return;
            
            atividadeSelecionada = atividade;
            abrirModalSelecao();
        }

        // Abrir modal de seleção
        function abrirModalSelecao() {
            if (!atividadeSelecionada) return;
            
            const modal = document.getElementById('modalSelecao');
            const infoAtividade = document.getElementById('infoAtividadeSelecionada');
            const maxHoras = document.getElementById('maxHoras');
            
            infoAtividade.innerHTML = `
                <div class="flex items-start">
                    <div class="text-2xl mr-3">📒</div>
                    <div>
                        <strong style="font-family:system-ui;font-weight:700;">${atividadeSelecionada.nome}</strong><br>
                        <span class="mt-1 text-sm font-semibold" style="color:#1E3A8A;">
                            ${atividadeSelecionada.tipo || atividadeSelecionada.categoria || 'PET'}
                        </span><br>
                        <small class="text-blue-700">Carga horária máxima: ${atividadeSelecionada.carga_horaria_maxima}h</small>
                    </div>
                </div>
            `;
            
            maxHoras.textContent = atividadeSelecionada.carga_horaria_maxima;
            document.getElementById('horasRealizadas').max = atividadeSelecionada.carga_horaria_maxima;
            
            modal.classList.remove('hidden');
        }

        // Fechar modal de seleção
        function fecharModalSelecao() {
            document.getElementById('modalSelecao').classList.add('hidden');
            document.getElementById('formSelecaoAtividade').reset();
            document.getElementById('arquivoSelecionado').classList.add('hidden');
        }

        // Fechar modal de detalhes
        function fecharModal() {
            document.getElementById('modalDetalhes').classList.add('hidden');
        }

        // Mostrar arquivo selecionado
        function mostrarArquivoSelecionado(input) {
            const divArquivo = document.getElementById('arquivoSelecionado');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                divArquivo.innerHTML = `
                    <strong>Arquivo selecionado:</strong> ${file.name}<br>
                    <small>Tamanho: ${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                `;
                divArquivo.classList.remove('hidden');
            } else {
                divArquivo.classList.add('hidden');
            }
        }

        // Exibir erro
        function exibirErro(mensagem) {
            const container = document.getElementById('atividadesContainer');
            const alerta = document.getElementById('alertaAtividades');
            
            container.innerHTML = `
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">😔</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Erro ao carregar atividades PET</h3>
                    <p class="text-gray-500">${mensagem}</p>
                </div>
            `;
            
            alerta.classList.remove('hidden');
            alerta.innerHTML = `<p class="text-yellow-800">⚠️ ${mensagem}</p>`;
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar autenticação e carregar atividades
            if (verificarAutenticacao()) {
                buscarAtividadesPET();
            }
            
            // Botão buscar
            // Busca removida: listeners desativados
            
            // Formulário de seleção
            document.getElementById('formSelecaoAtividade').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if (!atividadeSelecionada) {
                    alert('Nenhuma atividade PET selecionada');
                    return;
                }
                
                const formData = new FormData(this);
                // Mapear campos exigidos pelo controller de cadastro
                formData.append('atividades_por_resolucao_id', atividadeSelecionada.id);
                formData.append('titulo', document.getElementById('projetoNome').value || atividadeSelecionada.nome || 'Atividade PET');
                formData.append('descricao', document.getElementById('observacoes').value || '');
                formData.append('ch_solicitada', document.getElementById('horasRealizadas').value);
                
                try {
                    const token = AuthClient.getToken();
                    const apiKey = localStorage.getItem('acc_api_key');
                    
                    // Usar rota consolidada que aciona CadastrarAtividadesController
                    const response = await fetch('http://localhost/Gerenciamento-ACC/backend/api/routes/cadastrar_atividades.php', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'X-API-Key': apiKey
                        },
                        body: formData
                    });
                    
                    // Fazer parse robusto do corpo para lidar com possíveis BOMs
                    const text = await response.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (err) {
                        console.error('❌ Erro ao parsear resposta JSON:', err, text);
                        throw new Error('Resposta inválida do servidor');
                    }
                    
                    if (data.success) {
                        alert('Atividade PET cadastrada com sucesso!');
                        fecharModalSelecao();
                        buscarAtividadesPET();
                    } else {
                        alert('Erro ao cadastrar atividade PET: ' + data.message);
                    }
                } catch (error) {
                    console.error('❌ Erro ao cadastrar atividade PET:', error);
                    alert('Erro ao cadastrar atividade PET: ' + error.message);
                }
            });
        });

        // Fechar modais ao clicar fora
        window.addEventListener('click', function(e) {
            const modalDetalhes = document.getElementById('modalDetalhes');
            const modalSelecao = document.getElementById('modalSelecao');
            
            if (e.target === modalDetalhes) {
                fecharModal();
            }
            if (e.target === modalSelecao) {
                fecharModalSelecao();
            }
        });
    </script>
</body>
</html>