<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividades Extracurriculares - Sistema ACC</title>
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
                        <p class="text-gray-600 mt-2">Selecione uma atividade extracurricular para cadastrar</p>
                    </div>
                </div>
                

                
                <!-- Alerta de erro para atividades -->
                <div id="alertaAtividades" class="hidden mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-800">⚠️ Não foi possível carregar as atividades. Verifique a conexão com o banco de dados.</p>
                </div>
            </div>

            <!-- Seção de Busca removida -->
            <div class="hidden">
                <input id="campoBusca" />
                <select id="ordenacao"></select>
                <select id="direcao"></select>
                <button id="btnBuscar"></button>
                <button id="btnLimpar"></button>
            </div>

            <!-- Informações de Resultados -->
            <div id="infoResultados" class="mb-4 text-sm text-gray-600 hidden">
                <!-- Será preenchido dinamicamente -->
            </div>

            <!-- Container das atividades -->
            <div id="atividadesContainer" class="mb-8">
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto"></div>
                    <p class="text-gray-500 mt-4">Carregando atividades...</p>
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
                        <!-- Nome do Curso/Evento -->
                        <div id="campoCurso">
                            <label for="cursoNome" class="block text-sm font-medium text-gray-700 mb-2">
                                Curso/Evento *
                            </label>
                            <input type="text" id="cursoNome" name="cursoNome"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Digite o nome do curso ou evento" required>
                        </div>
                        
                        <!-- Campo Projeto (para PET) -->
                        <div id="campoProjeto" class="hidden">
                            <label for="projetoNome" class="block text-sm font-medium text-gray-700 mb-2">
                                Projeto *
                            </label>
                            <input type="text" id="projetoNome" name="projetoNome"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Digite o nome do projeto">
                        </div>
                        
                        <!-- Nome do Curso (apenas para atividades específicas de curso) -->
                        <div id="campoCursoEspecifico" class="hidden">
                            <label for="cursoEspecificoNome" class="block text-sm font-medium text-gray-700 mb-2">
                                Curso *
                            </label>
                            <input type="text" id="cursoEspecificoNome" name="cursoEspecificoNome"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Digite o nome do curso">
                        </div>
                        
                        <!-- Nome do Evento (apenas para atividades específicas) -->
                        <div id="campoEvento" class="hidden">
                            <label for="eventoNome" class="block text-sm font-medium text-gray-700 mb-2">
                                Evento *
                            </label>
                            <input type="text" id="eventoNome" name="eventoNome"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Digite o nome do evento">
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
                            <p class="text-xs text-gray-600 mt-1">Restante disponível: <span id="restanteHoras">--</span> horas</p>
                            <p class="text-xs font-medium mt-1 hidden" id="mensagemLimiteExtras" style="color:#DC2626"></p>
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
                } catch (e) {
                    console.error('❌ Erro ao parsear dados do usuário:', e);
                }
            }
            
            // Verificar se está logado usando AuthClient
            const isLoggedIn = AuthClient.isLoggedIn();
            console.log('✅ AuthClient.isLoggedIn():', isLoggedIn);
            
            if (!isLoggedIn) {
                console.log('❌ Usuário não autenticado, redirecionando para login');
                alert('Sua sessão expirou. Você será redirecionado para a página de login.');
                window.location.href = 'login.php';
                return false;
            }
            
            const user = AuthClient.getUser();
            console.log('👤 Dados do usuário via AuthClient:', user);
            
            if (!user || user.tipo !== 'aluno') {
                console.log('❌ Usuário não é aluno ou dados inválidos, fazendo logout');
                AuthClient.logout();
                return false;
            }
            
            console.log('✅ Autenticação válida para aluno:', user.nome || user.email);
            return true;
        }
        
        verificarAutenticacao();

        let todasAtividades = [];
        let dadosPaginacao = {};
        let filtrosAtuais = {
            busca: '',
            ordenacao: 'nome',
            direcao: 'ASC',
            pagina: 1,
            limite: 20
        };

        // Carregar atividades quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            carregarAtividades();
            
            // Event listeners para filtros
            document.getElementById('btnBuscar').addEventListener('click', aplicarFiltros);
            document.getElementById('btnLimpar').addEventListener('click', limparFiltros);
            
            // Event listeners para mudança de ordenação
            document.getElementById('ordenacao').addEventListener('change', aplicarFiltros);
            document.getElementById('direcao').addEventListener('change', aplicarFiltros);
            
            // Adicionar event listeners para busca em tempo real
            const campoBusca = document.getElementById('campoBusca');
            let timeoutBusca;
            
            campoBusca.addEventListener('input', function() {
                clearTimeout(timeoutBusca);
                timeoutBusca = setTimeout(() => {
                    filtrosAtuais.busca = this.value.trim();
                    filtrosAtuais.pagina = 1; // Reset para primeira página
                    carregarAtividades();
                }, 500); // Aguarda 500ms após parar de digitar
            });
        });

        // Carregar atividades com filtros e paginação
        async function carregarAtividades(tipo = 'extracurriculares') {
            try {
                console.log('🔍 === CARREGANDO ATIVIDADES ===');
                console.log('📊 Filtros atuais:', filtrosAtuais);
                
                // Verificar se AuthClient está disponível
                if (typeof AuthClient === 'undefined') {
                    console.error('❌ AuthClient não disponível para fazer requisição');
                    throw new Error('AuthClient não disponível');
                }
                
                // Verificar token antes da requisição
                const token = localStorage.getItem('acc_jwt_token');
                const apiKey = localStorage.getItem('acc_api_key');
                console.log('🎫 Token disponível para requisição:', !!token);
                console.log('🔑 API Key disponível para requisição:', !!apiKey);
                
                if (!token) {
                    console.error('❌ Token JWT não encontrado no localStorage');
                    throw new Error('Token JWT não encontrado');
                }
                
                if (!apiKey) {
                    console.error('❌ API Key não encontrada no localStorage');
                    throw new Error('API Key não encontrada');
                }
                
                // Construir URL com parâmetros de query
                const params = new URLSearchParams();
                if (filtrosAtuais.busca) params.append('busca', filtrosAtuais.busca);
                params.append('pagina', filtrosAtuais.pagina);
                params.append('limite', filtrosAtuais.limite);
                params.append('ordenacao', filtrosAtuais.ordenacao);
                params.append('direcao', filtrosAtuais.direcao);
                
                // Construir URL com parâmetros de query - usando a nova rota consolidada
                params.append('type', tipo); // Especificar tipo de atividade
                const url = `../../backend/api/routes/listar_atividades_disponiveis.php?${params.toString()}`;
                console.log('🌐 Fazendo requisição para:', url);
                
                const response = await AuthClient.fetch(url, {
                    method: 'GET'
                });
                
                console.log('📡 Status da resposta:', response.status);
                console.log('📡 Headers da resposta:', Object.fromEntries(response.headers.entries()));
                
                if (!response.ok) {
                    console.error('❌ Resposta não OK:', response.status, response.statusText);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('📊 Resposta da API:', data);
                
                if (data.success) {
                    console.log('✅ Total de atividades recebidas:', data.data?.atividades?.length || 0);
                    console.log('📊 Dados de paginação:', data.data?.paginacao);
                    
                    // Armazenar dados
                    const todasOriginais = data.data?.atividades || [];
                    todasAtividades = [...todasOriginais];
                    dadosPaginacao = data.data?.paginacao || {};

                    try {
                        const user = AuthClient.getUser() || {};
                        const anoMatricula = (user.matricula && typeof user.matricula === 'string') ? parseInt(user.matricula.substring(0,4)) : null;
                        const cursoNomeNorm = (user.curso_nome || '').toString().trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        const isBCC = (user.curso_id === 1) || cursoNomeNorm.includes('ciencia da computacao') || cursoNomeNorm.includes('bcc');
                        const isBSI = (user.curso_id === 2) || cursoNomeNorm.includes('sistemas de informacao') || cursoNomeNorm.includes('si') || cursoNomeNorm.includes('bsi');
                        const is2023Mais = !!(anoMatricula && anoMatricula >= 2023);

                        if (isBCC && is2023Mais) {
                            const RTA_EXTRACURRICULARES_BCC23 = 8;
                            const filtradasPorRta = todasOriginais.filter(a => a.resolucao_tipo_atividade_id === RTA_EXTRACURRICULARES_BCC23);
                            if (filtradasPorRta.length) {
                                todasAtividades = filtradasPorRta;
                            } else {
                                const nomesEsperados = [
                                    'Curso de extensão em áreas afins',
                                    'Curso de extensão na área específica',
                                    'Curso de língua estrangeira',
                                    'Seminários e eventos',
                                    'Seminários/eventos',
                                    'Missões nacionais e internacionais',
                                    'Eventos educação ambiental e diversidade cultural',
                                    'Eventos e ações relacionados à educação ambiental e diversidade cultural',
                                    'Membro efetivo e/ou assistente em eventos de extensão e profissionais',
                                    'PET – Programa de Educação Tutorial'
                                ];
                                const norm = s => s ? s.toString().trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'') : '';
                                const nomesNorm = nomesEsperados.map(e => norm(e));
                                const matchKeywords = n => (
                                    (n.includes('seminarios') && n.includes('eventos')) ||
                                    (n.includes('educacao') && n.includes('ambiental')) ||
                                    (n.includes('diversidade') && n.includes('cultural'))
                                );
                                todasAtividades = todasOriginais.filter(a => {
                                    const n = norm(a.nome);
                                    return nomesNorm.includes(n) || matchKeywords(n);
                                });
                            }
                            // Garantir inclusão explícita de "Seminários/eventos" caso venha com pequenas variações
                            const contemSeminariosEventos = todasAtividades.some(a => {
                                const n = (a.nome || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
                                return n.includes('seminarios') && (n.includes('eventos') || n.includes('evento'));
                            });
                            if (!contemSeminariosEventos) {
                                const candidato = todasOriginais.find(a => {
                                    const n = (a.nome || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
                                    return n.includes('seminarios') && (n.includes('eventos') || n.includes('evento'));
                                });
                                if (candidato) {
                                    todasAtividades.push(candidato);
                                } else {
                                    const porId = todasOriginais.find(a => a.atividade_complementar_id === 11);
                                    if (porId) {
                                        todasAtividades.push(porId);
                                    }
                                }
                            }
                            const ordem = {
                                'Curso de extensão em áreas afins': 1,
                                'Curso de extensão na área específica': 2,
                                'Curso de língua estrangeira': 3,
                                'Seminários e eventos': 4,
                                'Seminários/eventos': 4,
                                'Missões nacionais e internacionais': 5,
                                'Eventos e ações relacionados à educação ambiental e diversidade cultural': 6,
                                'Eventos educação ambiental e diversidade cultural': 6,
                                'Membro efetivo e/ou assistente em eventos de extensão e profissionais': 7,
                                'PET – Programa de Educação Tutorial': 8
                            };
                            todasAtividades.sort((a,b) => (ordem[a.nome]||99) - (ordem[b.nome]||99));
                        }
                        if (isBSI) {
                            const RTA_EXTRACURRICULARES_BSI18 = 12;
                            const baseSI = [
                                { acId: 25, nome: 'Curso de extensão em áreas afins', horas: 30, desc: 'Cursos de extensão relacionados à área' },
                                { acId: 26, nome: 'Curso de extensão na área específica', horas: 60, desc: 'Cursos de extensão específicos da área' },
                                { acId: 27, nome: 'Curso de língua estrangeira', horas: 75, desc: 'Cursos de idiomas' },
                                { acId: 28, nome: 'Seminários, simpósios, convenções, conferências, palestras, congressos, jornadas, fóruns, debates, visitas técnicas, viagens de estudos, workshops, programas de treinamento e eventos promovidos pela UFOPA e/ou outras IES', horas: 90, desc: 'Participação em eventos acadêmicos' },
                                { acId: 29, nome: 'Missões nacionais e internacionais', horas: 45, desc: 'Participação em missões acadêmicas' }
                            ];
                            const porRta = todasOriginais.filter(a => a.resolucao_tipo_atividade_id === RTA_EXTRACURRICULARES_BSI18);
                            let listaSI = porRta.length ? porRta : [];
                            if (!listaSI.length) {
                                const porIds = [];
                                for (const item of baseSI) {
                                    const encontrado = todasOriginais.find(a => a.atividade_complementar_id === item.acId);
                                    if (encontrado) {
                                        encontrado.carga_horaria_maxima = item.horas;
                                        encontrado.horas_max = item.horas;
                                        encontrado.descricao = encontrado.descricao || item.desc;
                                        encontrado.categoria = encontrado.categoria || 'Atividades extracurriculares';
                                        encontrado.resolucao_tipo_atividade_id = encontrado.resolucao_tipo_atividade_id || RTA_EXTRACURRICULARES_BSI18;
                                        porIds.push(encontrado);
                                    } else {
                                        porIds.push({
                                            id: item.acId,
                                            atividade_complementar_id: item.acId,
                                            nome: item.nome,
                                            categoria: 'Atividades extracurriculares',
                                            descricao: item.desc,
                                            carga_horaria_maxima: item.horas,
                                            horas_max: item.horas,
                                            resolucao_tipo_atividade_id: RTA_EXTRACURRICULARES_BSI18
                                        });
                                    }
                                }
                                listaSI = porIds;
                            }
                            const ordemSI = {
                                'Curso de extensão em áreas afins': 1,
                                'Curso de extensão na área específica': 2,
                                'Curso de língua estrangeira': 3,
                                'Seminários, simpósios, convenções, conferências, palestras, congressos, jornadas, fóruns, debates, visitas técnicas, viagens de estudos, workshops, programas de treinamento e eventos promovidos pela UFOPA e/ou outras IES': 4,
                                'Missões nacionais e internacionais': 5
                            };
                            listaSI.sort((a,b) => (ordemSI[a.nome]||99) - (ordemSI[b.nome]||99));
                            todasAtividades = listaSI;
                        }
                    } catch (regraErr) {
                        console.warn('Falha ao aplicar regra BCC 2023+:', regraErr);
                    }

                    // Fallback: se não veio nada por tipo=extracurriculares, buscar geral e filtrar por nome
                    if (!todasAtividades.length) {
                        console.warn('⚠️ Nenhuma atividade retornada para tipo=extracurriculares. Tentando fallback sem tipo...');
                        const paramsFallback = new URLSearchParams();
                        if (filtrosAtuais.busca) paramsFallback.append('busca', filtrosAtuais.busca);
                        paramsFallback.append('pagina', filtrosAtuais.pagina);
                        paramsFallback.append('limite', filtrosAtuais.limite);
                        paramsFallback.append('ordenacao', filtrosAtuais.ordenacao);
                        paramsFallback.append('direcao', filtrosAtuais.direcao);
                        const urlFallback = `../../backend/api/routes/listar_atividades_disponiveis.php?${paramsFallback.toString()}`;
                        console.log('🌐 Fallback requisição para:', urlFallback);
                        try {
                            const respFallback = await AuthClient.fetch(urlFallback, { method: 'GET' });
                            const dataFallback = await respFallback.json();
                            console.log('📊 Resposta Fallback:', dataFallback);
                            if (dataFallback.success) {
                                const todas = dataFallback.data?.atividades || [];
                                // Filtrar por categorias que contenham 'extracurricular' ou 'extens' (extensão)
                                todasAtividades = todas.filter(a => {
                                    const cat = (a.categoria || a.tipo || '').toLowerCase();
                                    return cat.includes('extracurricular') || cat.includes('extens');
                                });
                                // Ajustar paginação básica para refletir filtro
                                dadosPaginacao = {
                                    pagina_atual: 1,
                                    total_paginas: 1,
                                    total_registros: todasAtividades.length,
                                    limite: todasAtividades.length,
                                    tem_proxima: false,
                                    tem_anterior: false
                                };
                                console.log('✅ Fallback encontrou atividades:', todasAtividades.length);
                            }
                        } catch (fbErr) {
                            console.error('Erro no fallback:', fbErr);
                        }
                    }

                    // Renderizar atividades e controles
                    renderizarAtividades();
                    renderizarInfoResultados();
                    renderizarPaginacao();
                    try {
                        const bloqueio = await verificarBloqueioCategoria('acc');
                        if (bloqueio.completo) desabilitarSelecaoCategoria('acc');
                    } catch (e) { console.warn('Falha ao verificar bloqueio de categoria ACC:', e); }
                    
                    document.getElementById('alertaAtividades').classList.add('hidden');
                } else {
                    console.error('❌ Erro na resposta da API:', data.message || 'Erro desconhecido');
                    document.getElementById('alertaAtividades').classList.remove('hidden');
                    
                    // Limpar dados em caso de erro
                    todasAtividades = [];
                    dadosPaginacao = {};
                    renderizarAtividades();
                }
            } catch (e) {
                console.error('💥 Erro ao carregar atividades:', e);
                document.getElementById('alertaAtividades').classList.remove('hidden');
            }
        }

        // Funções para aplicar e limpar filtros
        function aplicarFiltros() {
            filtrosAtuais.ordenacao = document.getElementById('ordenacao').value;
            filtrosAtuais.direcao = document.getElementById('direcao').value;
            filtrosAtuais.busca = document.getElementById('campoBusca').value.trim();
            filtrosAtuais.pagina = 1; // Reset para primeira página
            
            carregarAtividades();
        }

        function limparFiltros() {
            document.getElementById('campoBusca').value = '';
            document.getElementById('ordenacao').value = 'nome';
            document.getElementById('direcao').value = 'ASC';
            
            filtrosAtuais = {
                busca: '',
                ordenacao: 'nome',
                direcao: 'ASC',
                pagina: 1,
                limite: 20
            };
            
            carregarAtividades();
        }

        // Função para mudar página
        function mudarPagina(novaPagina) {
            if (novaPagina >= 1 && novaPagina <= dadosPaginacao.total_paginas) {
                filtrosAtuais.pagina = novaPagina;
                carregarAtividades();
            }
        }

        // Renderizar informações dos resultados
        function renderizarInfoResultados() {
            const infoDiv = document.getElementById('infoResultados');
            
            if (dadosPaginacao.total_registros > 0) {
                const inicio = ((dadosPaginacao.pagina_atual - 1) * dadosPaginacao.limite) + 1;
                const fim = Math.min(dadosPaginacao.pagina_atual * dadosPaginacao.limite, dadosPaginacao.total_registros);
                
                infoDiv.innerHTML = `
                    Mostrando ${inicio}-${fim} de ${dadosPaginacao.total_registros} atividades
                    ${filtrosAtuais.busca ? `(filtrado por: "${filtrosAtuais.busca}")` : ''}
                `;
                infoDiv.classList.remove('hidden');
            } else {
                infoDiv.classList.add('hidden');
            }
        }

        // Renderizar controles de paginação
        function renderizarPaginacao() {
            const paginacaoDiv = document.getElementById('paginacao');
            
            if (!dadosPaginacao.total_paginas || dadosPaginacao.total_paginas <= 1) {
                paginacaoDiv.classList.add('hidden');
                return;
            }
            
            let html = '';
            
            // Botão anterior
            if (dadosPaginacao.tem_anterior) {
                html += `<button onclick="mudarPagina(${dadosPaginacao.pagina_atual - 1})" 
                                class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                            Anterior
                         </button>`;
            }
            
            // Números das páginas
            const paginaAtual = dadosPaginacao.pagina_atual;
            const totalPaginas = dadosPaginacao.total_paginas;
            
            let inicioRange = Math.max(1, paginaAtual - 2);
            let fimRange = Math.min(totalPaginas, paginaAtual + 2);
            
            if (inicioRange > 1) {
                html += `<button onclick="mudarPagina(1)" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">1</button>`;
                if (inicioRange > 2) {
                    html += `<span class="px-2 text-gray-500">...</span>`;
                }
            }
            
            for (let i = inicioRange; i <= fimRange; i++) {
                const isAtual = i === paginaAtual;
                html += `<button onclick="mudarPagina(${i})" 
                                class="px-3 py-2 text-sm border rounded-lg ${isAtual ? 'bg-purple-600 text-white border-purple-600' : 'border-gray-300 hover:bg-gray-50'}">
                            ${i}
                         </button>`;
            }
            
            if (fimRange < totalPaginas) {
                if (fimRange < totalPaginas - 1) {
                    html += `<span class="px-2 text-gray-500">...</span>`;
                }
                html += `<button onclick="mudarPagina(${totalPaginas})" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">${totalPaginas}</button>`;
            }
            
            // Botão próximo
            if (dadosPaginacao.tem_proxima) {
                html += `<button onclick="mudarPagina(${dadosPaginacao.pagina_atual + 1})" 
                                class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                            Próximo
                         </button>`;
            }
            
            paginacaoDiv.innerHTML = html;
            paginacaoDiv.classList.remove('hidden');
        }

        function renderizarAtividades() {
            const container = document.getElementById('atividadesContainer');
            if (!todasAtividades.length) {
                container.innerHTML = `<div class="text-center py-12">
                    <div class="text-6xl mb-4">🎓</div>
                    <p class="text-gray-500 text-lg mb-2">Nenhuma atividade de extensão encontrada.</p>
                    <p class="text-gray-400 text-sm">
                        ${filtrosAtuais.busca ? 'Tente ajustar os filtros de busca.' : 'Entre em contato com a coordenação para mais informações.'}
                    </p>
                </div>`;
                return;
            }
            
            container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ${todasAtividades.map(atividade => `
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-4" style="background-color: #8B5CF6">
                            <h3 class="text-lg font-bold text-white">${atividade.nome}</h3>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 mt-2">
                                ${atividade.categoria && (atividade.categoria.toLowerCase().includes('extracurricular') || atividade.categoria.toLowerCase().includes('extens')) ? 'Atividades Extracurriculares' : (atividade.categoria || 'Atividades Extracurriculares')}
                            </span>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 text-sm mb-4">${atividade.descricao}</p>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #8B5CF6">Tipo:</span>
                                    <span class="text-gray-600">${atividade.categoria && (atividade.categoria.toLowerCase().includes('extracurricular') || atividade.categoria.toLowerCase().includes('extens')) ? 'Atividades Extracurriculares' : (atividade.categoria || atividade.tipo)}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #8B5CF6">Horas Máximas:</span>
                                    <span class="text-gray-600">${atividade.carga_horaria_maxima || atividade.horas_max}h</span>
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
                        <span class="ml-2">${atividade.categoria && (atividade.categoria.toLowerCase().includes('extracurricular') || atividade.categoria.toLowerCase().includes('extens')) ? 'Atividades Extracurriculares' : (atividade.categoria || 'Atividades Extracurriculares')}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #8B5CF6">Tipo:</span>
                        <span class="ml-2">${atividade.categoria && (atividade.categoria.toLowerCase().includes('extracurricular') || atividade.categoria.toLowerCase().includes('extens')) ? 'Atividades Extracurriculares' : (atividade.categoria || atividade.tipo)}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #8B5CF6">Horas Máximas:</span>
                        <span class="ml-2">${atividade.carga_horaria_maxima || atividade.horas_max} horas</span>
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
                    <p><strong>Tipo:</strong> ${atividadeSelecionada.categoria || atividadeSelecionada.tipo}</p>
                    <p><strong>Horas Máximas:</strong> ${atividadeSelecionada.carga_horaria_maxima || atividadeSelecionada.horas_max}h</p>
                </div>
            `;
            
            // Configurar limite máximo de horas
            const inputHoras = document.getElementById('horasRealizadas');
            const spanMaxHoras = document.getElementById('maxHoras');
            
            // Usar limite padrão para todas as atividades
            verificarLimiteHoras(atividadeSelecionada, inputHoras, spanMaxHoras);
            
            // Detectar atividades específicas
            const isPET = atividadeSelecionada.nome.toLowerCase().includes('pet – programa de educação tutorial');
            const isMissoes = atividadeSelecionada.nome.toLowerCase().includes('missões nacionais e internacionais');
            
            // Detectar se é uma das atividades específicas que precisam do campo "Evento"
            const atividadesComEvento = [
                'Eventos e ações relacionados à educação ambiental e diversidade cultural',
                'Membro efetivo e/ou assistente em eventos de extensão e profissionais'
            ];
            
            // Detectar se é uma das atividades específicas que precisam do campo "Curso"
            const atividadesComCurso = [
                'Curso de extensão em áreas afins',
                'Curso de extensão na área específica',
                'Curso de língua estrangeira'
            ];
            
            const precisaEvento = atividadesComEvento.some(nomeAtividade => 
                atividadeSelecionada.nome.toLowerCase().includes(nomeAtividade.toLowerCase())
            );
            
            const precisaCurso = atividadesComCurso.some(nomeAtividade => 
                atividadeSelecionada.nome.toLowerCase().includes(nomeAtividade.toLowerCase())
            );
            
            const campoCurso = document.getElementById('campoCurso');
            const inputCurso = document.getElementById('cursoNome');
            const campoProjeto = document.getElementById('campoProjeto');
            const inputProjeto = document.getElementById('projetoNome');
            const campoCursoEspecifico = document.getElementById('campoCursoEspecifico');
            const inputCursoEspecifico = document.getElementById('cursoEspecificoNome');
            const campoEvento = document.getElementById('campoEvento');
            const inputEvento = document.getElementById('eventoNome');
            
            // Ocultar todos os campos primeiro
            campoCurso.classList.add('hidden');
            inputCurso.required = false;
            inputCurso.value = '';
            
            campoProjeto.classList.add('hidden');
            inputProjeto.required = false;
            inputProjeto.value = '';
            
            campoCursoEspecifico.classList.add('hidden');
            inputCursoEspecifico.required = false;
            inputCursoEspecifico.value = '';
            
            campoEvento.classList.add('hidden');
            inputEvento.required = false;
            inputEvento.value = '';
            
            if (isPET) {
                // Para PET: mostrar apenas campo projeto
                campoProjeto.classList.remove('hidden');
                inputProjeto.required = true;
                inputProjeto.value = '';
            } else if (isMissoes) {
                // Para Missões: não mostrar nenhum campo de curso/evento/projeto
                // Todos os campos já foram ocultados acima
            } else if (precisaCurso) {
                // Para atividades específicas de curso: mostrar campo curso específico
                campoCursoEspecifico.classList.remove('hidden');
                inputCursoEspecifico.required = true;
                inputCursoEspecifico.value = '';
            } else if (precisaEvento) {
                // Para atividades específicas de evento: mostrar campo evento
                campoEvento.classList.remove('hidden');
                inputEvento.required = true;
                inputEvento.value = '';
            } else {
                // Para outras atividades: mostrar campo curso/evento padrão
                campoCurso.classList.remove('hidden');
                inputCurso.required = true;
                inputCurso.value = '';
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
            const declaracao = document.getElementById('declaracao').files[0];
            const cursoNome = document.getElementById('cursoNome').value.trim();
            const projetoNome = document.getElementById('projetoNome').value.trim();
            const cursoEspecificoNome = document.getElementById('cursoEspecificoNome').value.trim();
            const eventoNome = document.getElementById('eventoNome').value.trim();
            
            // Verificar qual tipo de campo está sendo usado
            const campoProjeto = document.getElementById('campoProjeto');
            const campoEvento = document.getElementById('campoEvento');
            const campoCursoEspecifico = document.getElementById('campoCursoEspecifico');
            const campoCurso = document.getElementById('campoCurso');
            
            const precisaProjeto = !campoProjeto.classList.contains('hidden');
            const precisaEvento = !campoEvento.classList.contains('hidden');
            const precisaCursoEspecifico = !campoCursoEspecifico.classList.contains('hidden');
            const precisaCurso = !campoCurso.classList.contains('hidden');
            
            // Detectar se é Missões (não precisa de campo obrigatório adicional)
            const isMissoes = atividadeSelecionada.nome.toLowerCase().includes('missões nacionais e internacionais');
            
            // Validar campos obrigatórios baseado no tipo de atividade
            let campoObrigatorioFaltando = false;
            let mensagemErro = 'Por favor, preencha todos os campos obrigatórios.';
            
            if (!horasRealizadas || !declaracao) {
                campoObrigatorioFaltando = true;
            } else if (precisaProjeto && !projetoNome) {
                campoObrigatorioFaltando = true;
                mensagemErro = 'Por favor, preencha o campo Projeto.';
            } else if (precisaCursoEspecifico && !cursoEspecificoNome) {
                campoObrigatorioFaltando = true;
                mensagemErro = 'Por favor, preencha o campo Curso.';
            } else if (precisaEvento && !eventoNome) {
                campoObrigatorioFaltando = true;
                mensagemErro = 'Por favor, preencha o campo Evento.';
            } else if (precisaCurso && !cursoNome) {
                campoObrigatorioFaltando = true;
                mensagemErro = 'Por favor, preencha o campo Curso/Evento.';
            }
            // Para Missões, não há campo adicional obrigatório
            
            if (campoObrigatorioFaltando) {
                alert(mensagemErro);
                return;
            }
            
            // Validação de datas removida pois os campos foram removidos do formulário
            
            // Validar horas
            if (parseInt(horasRealizadas) > parseInt(atividadeSelecionada.horas_max)) {
                alert(`As horas realizadas não podem exceder ${atividadeSelecionada.horas_max} horas.`);
                return;
            }
            
            // Obter dados do usuário logado
            const usuario = AuthClient.getUser();
            if (!usuario || !usuario.id) {
                alert('Erro: Dados do usuário não encontrados.');
                return;
            }
            
            // Criar título baseado no tipo de atividade e campo específico
            let titulo = '';
            if (precisaProjeto && projetoNome) {
                titulo = `${atividadeSelecionada.nome} - Projeto: ${projetoNome}`;
            } else if (precisaCursoEspecifico && cursoEspecificoNome) {
                titulo = `${atividadeSelecionada.nome} - Curso: ${cursoEspecificoNome}`;
            } else if (precisaEvento && eventoNome) {
                titulo = `${atividadeSelecionada.nome} - Evento: ${eventoNome}`;
            } else if (precisaCurso && cursoNome) {
                titulo = `${atividadeSelecionada.nome} - ${cursoNome}`;
            } else {
                titulo = atividadeSelecionada.nome;
            }
            
            // Preparar dados para envio - usando estrutura JSON para a nova API
            const dadosAtividade = {
                aluno_id: usuario.id,
                // Usar o identificador correto da atividade (id retornado pela API)
                atividades_por_resolucao_id: atividadeSelecionada.id,
                titulo: titulo,
                descricao: document.getElementById('observacoes').value || null,
                ch_solicitada: parseInt(horasRealizadas)
            };
            
            // Preparar FormData para incluir o arquivo
            const formData = new FormData();
            
            // Adicionar dados JSON como string
            formData.append('data', JSON.stringify(dadosAtividade));
            
            // Adicionar arquivo de declaração
            formData.append('declaracao', declaracao);
            
            // Desabilitar botão de envio
            const btnSubmit = e.target.querySelector('button[type="submit"]');
            const textoOriginal = btnSubmit.textContent;
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Enviando...';
            
            try {
                console.log('=== DEBUG REQUISIÇÃO ===');
                console.log('Enviando dados:', dadosAtividade);
                console.log('URL:', '../../backend/api/routes/cadastrar_atividades.php');
                console.log('Token disponível:', AuthClient.getToken());
                console.log('Usuário logado:', AuthClient.getUser());
                
                // Log detalhado do FormData
                console.log('=== CONTEÚDO DO FORMDATA ===');
                for (let [key, value] of formData.entries()) {
                    if (value instanceof File) {
                        console.log(`${key}: [File] ${value.name} (${value.size} bytes, ${value.type})`);
                    } else {
                        console.log(`${key}: ${value}`);
                    }
                }
                
                // Verificar se o usuário está logado
                if (!AuthClient.isLoggedIn()) {
                    alert('Você precisa estar logado para cadastrar uma atividade.');
                    window.location.href = 'login.php';
                    return;
                }
                
                // Log dos headers que serão enviados
                console.log('=== HEADERS DA REQUISIÇÃO ===');
                const headers = AuthClient.getHeaders();
                console.log('Headers:', headers);
                
                console.log('=== INICIANDO REQUISIÇÃO ===');
                const response = await AuthClient.fetch('../../backend/api/routes/cadastrar_atividades.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('=== RESPOSTA RECEBIDA ===');
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                console.log('Response data:', response.data);
                
                // Não precisamos chamar response.json() novamente, pois o AuthClient.fetch já retorna os dados processados
                const result = response.data;
                
                if (result && result.success) {
                    alert('Atividade cadastrada com sucesso!');
                    
                    fecharModalSelecao();
                    // Redirecionar para página de atividades do aluno
                    window.location.href = 'home_aluno.php';
                } else {
                    alert('Erro ao enviar dados: ' + (result?.error || result?.message || 'Erro desconhecido'));
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = textoOriginal;
                }
            } catch (error) {
                console.error('Erro completo ao enviar atividade:', error);
                alert('Erro ao enviar dados: ' + (error.message || 'Erro desconhecido'));
                btnSubmit.disabled = false;
                btnSubmit.textContent = textoOriginal;
            }
        });
        
        // Removida validação que definia data mínima do campo dataFim
        
        // Removida validação que impedia data fim anterior à data início no evento change



        // Função simplificada para verificar limite de horas
        async function obterRestantePorAtividade(aprId) {
            try {
                const resp = await AuthClient.fetch('../../backend/api/routes/listar_atividades_disponiveis.php?acao=enviadas&limite=200');
                const json = await resp.json();
                const lista = json?.data?.atividades || [];
                const relevantes = lista.filter(a => parseInt(a.atividades_por_resolucao_id) === parseInt(aprId) && ['aprovado','aprovada'].includes(String(a.status).toLowerCase()));
                const soma = relevantes.reduce((acc, a) => acc + (parseInt(a.ch_atribuida||0)||0), 0);
                const max = (atividadeSelecionada.carga_horaria_maxima || atividadeSelecionada.horas_max);
                const restante = Math.max(0, max - soma);
                return { restante, max };
            } catch (e) {
                return { restante: (atividadeSelecionada.carga_horaria_maxima || atividadeSelecionada.horas_max), max: (atividadeSelecionada.carga_horaria_maxima || atividadeSelecionada.horas_max) };
            }
        }

        

        async function verificarLimiteHoras(atividade, inputHoras, spanMaxHoras) {
            const infoDiv = document.getElementById('infoAtividadeSelecionada');
            infoDiv.innerHTML = `
                <div class="space-y-1">
                    <p><strong>Nome:</strong> ${atividade.nome}</p>
                    <p><strong>Categoria:</strong> ${atividade.categoria}</p>
                    <p><strong>Tipo:</strong> ${atividade.tipo}</p>
                    <p><strong>Horas Máximas:</strong> ${atividade.horas_max}h</p>
                </div>
            `;
            const dados = await obterRestantePorAtividade(atividade.id);
            let restanteTotal = null;
            try { const t = await verificarBloqueioCategoria('acc'); restanteTotal = t.lim - t.atual; } catch (e) { restanteTotal = null; }
            inputHoras.max = restanteTotal !== null ? Math.min(dados.restante, Math.max(0, restanteTotal)) : dados.restante;
            inputHoras.min = dados.restante === 0 ? 0 : 1;
            spanMaxHoras.textContent = dados.restante;
            const restanteEl = document.getElementById('restanteHoras');
            restanteEl.textContent = dados.restante;

            const msg = document.getElementById('mensagemLimiteExtras');
            const submitBtn = document.querySelector('#formSelecaoAtividade button[type="submit"]');
            const bloqueadoTotal = (restanteTotal !== null && Math.max(0, restanteTotal) === 0);
            if (dados.restante === 0 || bloqueadoTotal) {
                msg.textContent = 'Você atingiu o limite de horas para esta atividade.';
                msg.classList.remove('hidden');
                inputHoras.value = '';
                inputHoras.disabled = true;
                if (submitBtn) submitBtn.disabled = true;
            } else {
                msg.classList.add('hidden');
                inputHoras.disabled = false;
                if (submitBtn) submitBtn.disabled = false;
            }
        }

        async function verificarBloqueioCategoria(slug) {
            const resp = await AuthClient.fetch('../../backend/api/routes/calcular_horas_categorias.php', { method: 'POST' });
            const json = await resp.json();
            const categorias = json?.data?.categorias || {}; const limites = json?.data?.limites || {};
            const atual = categorias[slug] || 0; const lim = limites[slug] || 0;
            return { completo: lim > 0 && atual >= lim, atual, lim };
        }

        function desabilitarSelecaoCategoria(slug) {
            return;
        }

        // Validação simples no campo de horas
        document.addEventListener('DOMContentLoaded', function() {
            const inputHoras = document.getElementById('horasRealizadas');
            
            inputHoras.addEventListener('input', function() {
                const valorDigitado = parseInt(this.value) || 0;
                const maxPermitido = parseInt(this.max) || 0;
                
                // Limitar o valor ao máximo permitido
                if (valorDigitado > maxPermitido) {
                    this.value = maxPermitido;
                    
                    // Mostrar mensagem de erro
                    const mensagemErro = document.getElementById('mensagemErroHoras') || document.createElement('div');
                    mensagemErro.id = 'mensagemErroHoras';
                    mensagemErro.className = 'mt-1 text-sm text-red-600';
                    mensagemErro.textContent = `Máximo permitido: ${maxPermitido}h`;
                    
                    if (!document.getElementById('mensagemErroHoras')) {
                        this.parentNode.appendChild(mensagemErro);
                    }
                    
                    // Remover mensagem após 3 segundos
                    setTimeout(() => {
                        if (mensagemErro.parentNode) {
                            mensagemErro.parentNode.removeChild(mensagemErro);
                        }
                    }, 3000);
                } else {
                    // Remover mensagem de erro se existir
                    const mensagemErro = document.getElementById('mensagemErroHoras');
                    if (mensagemErro) {
                        mensagemErro.parentNode.removeChild(mensagemErro);
                    }
                }
            });
        });

        // Inicializar página - carregar diretamente as atividades extracurriculares
        carregarAtividades('extracurriculares');
    </script>
</body>
</html>
