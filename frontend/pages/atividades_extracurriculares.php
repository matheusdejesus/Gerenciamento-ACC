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
                
                <!-- Alerta de erro para categorias -->
                <div id="alertaCategorias" class="hidden mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-800">⚠️ Não foi possível carregar as categorias. Verifique a conexão com o banco de dados.</p>
                </div>
                
                <!-- Alerta de erro para atividades -->
                <div id="alertaAtividades" class="hidden mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-800">⚠️ Não foi possível carregar as atividades. Verifique a conexão com o banco de dados.</p>
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
                            
                            <!-- Contador de horas para Curso de extensão em áreas afins -->
                            <div id="contadorHorasCursoExtensao" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg hidden">
                                <div class="text-sm text-blue-800">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-medium">Horas já cadastradas:</span>
                                        <span id="horasJaCadastradas" class="font-semibold">0h</span>
                                    </div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-medium">Horas disponíveis:</span>
                                        <span id="horasDisponiveis" class="font-semibold text-green-600">0h</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium">Limite máximo:</span>
                                        <span id="limiteMaximo" class="font-semibold">0h</span>
                                    </div>
                                </div>
                            </div>
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

        // Variáveis globais
        let todasCategorias = [];
        let todasAtividades = [];
        let categoriaAtual = null;

        // Carregar categorias via JWT
        async function carregarCategorias() {
            try {
                const response = await AuthClient.fetch('../../backend/api/routes/listar_categorias.php', {
                    method: 'POST'
                });
                const data = await response.json();
                if (data.success) {
                    todasCategorias = data.data || [];
                    renderizarCategorias();
                    document.getElementById('alertaCategorias').classList.add('hidden');
                } else {
                    document.getElementById('alertaCategorias').classList.remove('hidden');
                }
            } catch (e) {
                document.getElementById('alertaCategorias').classList.remove('hidden');
            }
        }

        function renderizarCategorias() {
            const container = document.getElementById('categoriasContainer');
            if (!todasCategorias.length) {
                container.innerHTML = `<div class="text-center py-12">
                    <p class="text-gray-500 text-lg">Nenhuma categoria encontrada.</p>
                </div>`;
                return;
            }

            // Definir cores e ícones para cada categoria
            const categoriaConfig = {
                'Ensino': { cor: '#1A7F37', icone: '📚' },
                'Pesquisa': { cor: '#0969DA', icone: '🔬' },
                'Atividades extracurriculares': { cor: '#8B5CF6', icone: '🎓' },
                'Atividades Extracurriculares': { cor: '#8B5CF6', icone: '🎓' },
                'Estágio': { cor: '#F59E0B', icone: '💼' }
            };

            container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                ${todasCategorias.map(categoria => {
                    const config = categoriaConfig[categoria.nome] || { cor: '#6B7280', icone: '📋' };
                    return `
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-lg transition-all duration-300 cursor-pointer transform hover:scale-105"
                             onclick="selecionarCategoria('${categoria.nome}')">
                            <div class="p-6 text-center" style="background: linear-gradient(135deg, ${config.cor}, ${config.cor}dd)">
                                <div class="text-4xl mb-3">${config.icone}</div>
                                <h3 class="text-xl font-bold text-white">${categoria.nome}</h3>
                            </div>
                            <div class="p-4 text-center">
                                <p class="text-gray-600 text-sm mb-4">Clique para ver as atividades disponíveis nesta categoria</p>
                                <div class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition duration-200"
                                     style="background-color: ${config.cor}20; color: ${config.cor}">
                                    Ver Atividades
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>`;
        }

        function selecionarCategoria(nomeCategoria) {
            categoriaAtual = nomeCategoria;
            
            // Ocultar categorias e mostrar atividades
            document.getElementById('categoriasContainer').classList.add('hidden');
            document.getElementById('atividadesContainer').classList.remove('hidden');
            
            // Atualizar título
            const titulo = document.querySelector('h2');
            titulo.textContent = `Atividades - ${nomeCategoria}`;
            
            // Adicionar botão voltar
            const cabecalho = document.querySelector('.flex.items-center.mb-4');
            if (!document.getElementById('btnVoltar')) {
                const btnVoltar = document.createElement('button');
                btnVoltar.id = 'btnVoltar';
                btnVoltar.className = 'ml-4 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200';
                btnVoltar.textContent = '← Voltar às Categorias';
                btnVoltar.onclick = voltarCategorias;
                cabecalho.appendChild(btnVoltar);
            }
            
            // Carregar atividades da categoria
            carregarAtividades(nomeCategoria);
        }

        function voltarCategorias() {
            // Mostrar categorias e ocultar atividades
            document.getElementById('categoriasContainer').classList.remove('hidden');
            document.getElementById('atividadesContainer').classList.add('hidden');
            
            // Restaurar título
            const titulo = document.querySelector('h2');
            titulo.textContent = 'Escolher Categoria de Atividade';
            
            // Remover botão voltar
            const btnVoltar = document.getElementById('btnVoltar');
            if (btnVoltar) {
                btnVoltar.remove();
            }
            
            categoriaAtual = null;
        }

        // Carregar atividades de uma categoria específica via JWT
        async function carregarAtividades(categoria) {
            try {
                console.log('🔍 === CARREGANDO ATIVIDADES ===');
                console.log('📂 Categoria solicitada:', categoria);
                
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
                
                console.log('🌐 Fazendo requisição para: ../../backend/api/routes/listar_atividades.php');
                
                const response = await AuthClient.fetch('../../backend/api/routes/listar_atividades.php', {
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
                    console.log('✅ Total de atividades recebidas:', data.data?.length || 0);
                    
                    // Log de todas as categorias disponíveis
                    const categoriasDisponiveis = [...new Set((data.data || []).map(a => a.categoria))];
                    console.log('📋 Categorias disponíveis no banco:', categoriasDisponiveis);
                    
                    // Filtrar atividades da categoria selecionada - melhorar o filtro
                    todasAtividades = (data.data || []).filter(atividade => {
                        if (!atividade.categoria) return false;
                        
                        const categoriaAtividade = atividade.categoria.toLowerCase().trim();
                        const categoriaBusca = categoria.toLowerCase().trim();
                        
                        // Verificar correspondência exata ou parcial
                        const match = categoriaAtividade === categoriaBusca || 
                                     categoriaAtividade.includes(categoriaBusca) ||
                                     categoriaBusca.includes(categoriaAtividade);
                        
                        if (match) {
                            console.log(`✅ Atividade encontrada: "${atividade.nome}" - Categoria: "${atividade.categoria}"`);
                        }
                        
                        return match;
                    });
                    
                    console.log('🎯 Atividades filtradas para categoria "' + categoria + '":', todasAtividades.length);
                    console.log('📝 Atividades encontradas:', todasAtividades.map(a => a.nome));
                    
                    renderizarAtividades();
                    document.getElementById('alertaAtividades').classList.add('hidden');
                } else {
                    console.error('❌ Erro na resposta da API:', data.error || 'Erro desconhecido');
                    document.getElementById('alertaAtividades').classList.remove('hidden');
                }
            } catch (e) {
                console.error('💥 Erro ao carregar atividades:', e);
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
            
            // Verificar se é "Curso de extensão em áreas afins" para aplicar validação específica
            const isCursoExtensaoAreasAfins = atividadeSelecionada.nome.toLowerCase().includes('curso de extensão em áreas afins');
            
            if (isCursoExtensaoAreasAfins) {
                // Para "Curso de extensão em áreas afins", verificar limite baseado no ano da matrícula
                verificarLimiteCursoExtensaoAreasAfins(atividadeSelecionada, inputHoras, spanMaxHoras);
            } else {
                // Para outras atividades, usar o limite padrão
                inputHoras.max = atividadeSelecionada.horas_max;
                spanMaxHoras.textContent = atividadeSelecionada.horas_max;
                
                // Esconder contador de horas para outras atividades
                const contadorDiv = document.getElementById('contadorHorasCursoExtensao');
                contadorDiv.classList.add('hidden');
            }
            
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
            const dataInicio = document.getElementById('dataInicio').value;
            const dataFim = document.getElementById('dataFim').value;
            const local = document.getElementById('local').value;
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
            
            if (!horasRealizadas || !dataInicio || !dataFim || !local || !declaracao) {
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
            
            // Enviar o campo apropriado baseado no tipo de atividade
            if (precisaProjeto) {
                formData.append('projeto_nome', projetoNome);
            } else if (precisaCursoEspecifico) {
                formData.append('curso_nome', cursoEspecificoNome);
            } else if (precisaEvento) {
                formData.append('evento_nome', eventoNome);
            } else if (precisaCurso) {
                formData.append('curso_nome', cursoNome);
            }
            // Para Missões, não enviamos campo adicional
            
            formData.append('declaracao', declaracao);
            
            // Desabilitar botão de envio
            const btnSubmit = e.target.querySelector('button[type="submit"]');
            const textoOriginal = btnSubmit.textContent;
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Enviando...';
            
            try {
                console.log('=== DEBUG REQUISIÇÃO ===');
                console.log('Enviando dados:', formData);
                console.log('URL:', '../../backend/api/routes/atividade_complementar_acc.php');
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
                const response = await AuthClient.fetch('../../backend/api/routes/atividade_complementar_acc.php', {
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
                    
                    // Atualizar automaticamente a seção "Minhas Atividades"
                    await atualizarMinhasAtividades();
                    
                    fecharModalSelecao();
                    // Redirecionar para página de atividades do aluno
                    window.location.href = 'home_aluno.php';
                } else {
                    console.error('Erro na resposta:', result);
                    alert('Erro ao cadastrar atividade: ' + (result?.message || result?.error || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('=== ERRO CAPTURADO ===');
                console.error('Tipo do erro:', error.constructor.name);
                console.error('Mensagem do erro:', error.message);
                console.error('Stack trace:', error.stack);
                console.error('Erro completo:', error);
                
                // Tentar extrair mais informações do erro
                if (error.response) {
                    console.error('Response do erro:', error.response);
                }
                
                alert('Erro ao enviar dados: ' + error.message + '\nVerifique o console para mais detalhes.');
            } finally {
                // Reabilitar botão
                btnSubmit.disabled = false;
                btnSubmit.textContent = textoOriginal;
            }
        });
        
        // Removida validação que definia data mínima do campo dataFim
        
        // Removida validação que impedia data fim anterior à data início no evento change

        // Função para atualizar a seção "Minhas Atividades"
        async function atualizarMinhasAtividades() {
            try {
                const response = await AuthClient.request('../../backend/api/routes/listar_atividades_aluno.php', {
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

        // Função para verificar limite específico para "Curso de extensão em áreas afins"
        async function verificarLimiteCursoExtensaoAreasAfins(atividade, inputHoras, spanMaxHoras) {
            try {
                console.log('🔍 Verificando limite para Curso de extensão em áreas afins');
                
                // Buscar dados do usuário para determinar o limite baseado no ano da matrícula
                const user = AuthClient.getUser();
                if (!user || !user.matricula) {
                    console.error('Dados do usuário não encontrados');
                    // Usar limite padrão se não conseguir determinar
                    inputHoras.max = atividade.horas_max;
                    spanMaxHoras.textContent = atividade.horas_max;
                    return;
                }
                
                const matricula = user.matricula;
                const anoMatricula = parseInt(matricula.substring(0, 4));
                
                // Definir limite baseado no ano da matrícula
                const limiteHoras = (anoMatricula >= 2023) ? 10 : 20;
                
                console.log(`📅 Matrícula: ${matricula}, Ano: ${anoMatricula}, Limite: ${limiteHoras}h`);
                
                // Buscar horas já cadastradas desta atividade específica
                const response = await AuthClient.request('../../backend/api/routes/verificar_horas_curso_extensao.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        atividade_disponivel_id: atividade.id
                    })
                });
                
                let horasJaCadastradas = 0;
                if (response.success && response.data) {
                    horasJaCadastradas = response.data.horas_ja_cadastradas || 0;
                }
                
                console.log(`⏰ Horas já cadastradas: ${horasJaCadastradas}h de ${limiteHoras}h`);
                
                // Calcular horas restantes
                const horasRestantes = Math.max(0, limiteHoras - horasJaCadastradas);
                
                // Atualizar interface
                inputHoras.max = horasRestantes;
                spanMaxHoras.textContent = horasRestantes;
                
                // Mostrar contador de horas para Curso de extensão em áreas afins
                const contadorDiv = document.getElementById('contadorHorasCursoExtensao');
                contadorDiv.classList.remove('hidden');
                
                // Atualizar valores do contador
                document.getElementById('horasJaCadastradas').textContent = `${horasJaCadastradas}h`;
                document.getElementById('horasDisponiveis').textContent = `${horasRestantes}h`;
                document.getElementById('limiteMaximo').textContent = `${limiteHoras}h`;
                
                // Atualizar cor das horas disponíveis
                const horasDisponiveisSpan = document.getElementById('horasDisponiveis');
                horasDisponiveisSpan.className = horasRestantes > 0 ? 'font-semibold text-green-600' : 'font-semibold text-red-600';
                
                // Atualizar informações da atividade selecionada
                const infoDiv = document.getElementById('infoAtividadeSelecionada');
                infoDiv.innerHTML = `
                    <div class="space-y-1">
                        <p><strong>Nome:</strong> ${atividade.nome}</p>
                        <p><strong>Categoria:</strong> ${atividade.categoria}</p>
                        <p><strong>Tipo:</strong> ${atividade.tipo}</p>
                        <p><strong>Limite Total:</strong> ${limiteHoras}h</p>
                        <p><strong>Horas Já Cadastradas:</strong> ${horasJaCadastradas}h</p>
                        <p><strong>Horas Disponíveis:</strong> <span class="font-semibold ${horasRestantes > 0 ? 'text-green-600' : 'text-red-600'}">${horasRestantes}h</span></p>
                    </div>
                `;
                
                // Se não há horas restantes, mostrar aviso
                if (horasRestantes === 0) {
                    alert(`Limite máximo de ${limiteHoras}h atingido para esta atividade. Você não pode cadastrar mais horas.`);
                    fecharModalSelecao();
                    return;
                }
                
                // Se há poucas horas restantes, mostrar aviso
                if (horasRestantes < limiteHoras) {
                    const mensagem = `Atenção: Você já possui ${horasJaCadastradas}h cadastradas desta atividade. ` +
                                   `Você pode cadastrar no máximo ${horasRestantes}h adicionais (limite total: ${limiteHoras}h).`;
                    
                    // Mostrar aviso visual na interface
                    const avisoDiv = document.createElement('div');
                    avisoDiv.className = 'mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg';
                    avisoDiv.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">${mensagem}</p>
                            </div>
                        </div>
                    `;
                    
                    // Inserir aviso após as informações da atividade
                    infoDiv.parentNode.insertBefore(avisoDiv, infoDiv.nextSibling);
                }
                
            } catch (error) {
                console.error('Erro ao verificar limite:', error);
                // Em caso de erro, usar limite padrão
                inputHoras.max = atividade.horas_max;
                spanMaxHoras.textContent = atividade.horas_max;
            }
        }

        // Adicionar validação em tempo real no campo de horas
        document.addEventListener('DOMContentLoaded', function() {
            const inputHoras = document.getElementById('horasRealizadas');
            
            inputHoras.addEventListener('input', function() {
                // Verificar se é "Curso de extensão em áreas afins"
                if (atividadeSelecionada && atividadeSelecionada.nome.toLowerCase().includes('curso de extensão em áreas afins')) {
                    const valorDigitado = parseInt(this.value) || 0;
                    const maxPermitido = parseInt(this.max) || 0;
                    
                    // Atualizar contador em tempo real
                    const horasJaCadastradas = parseInt(document.getElementById('horasJaCadastradas').textContent) || 0;
                    const limiteMaximo = parseInt(document.getElementById('limiteMaximo').textContent) || 0;
                    const horasDisponiveis = Math.max(0, limiteMaximo - horasJaCadastradas);
                    
                    // Limitar o valor ao máximo permitido
                    if (valorDigitado > maxPermitido) {
                        this.value = maxPermitido;
                        
                        // Mostrar mensagem de erro
                        const mensagemErro = document.getElementById('mensagemErroHoras') || document.createElement('div');
                        mensagemErro.id = 'mensagemErroHoras';
                        mensagemErro.className = 'mt-1 text-sm text-red-600';
                        mensagemErro.textContent = `Máximo permitido: ${maxPermitido}h (você já possui ${horasJaCadastradas}h cadastradas)`;
                        
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
                }
            });
        });

        // Inicializar página - carregar diretamente as atividades extracurriculares
        carregarAtividades('extracurriculares');
    </script>
</body>
</html>