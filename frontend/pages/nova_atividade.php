<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Atividade - ACC Discente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wght@0,200..900;1,200..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!-- Carregar AuthClient PRIMEIRO, antes de qualquer outro script -->
    <script src="../assets/js/auth.js"></script>

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
                    <a href="home_aluno.php" class="text-white hover:text-gray-200">Voltar</a>
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
                </nav>
            </aside>
            <main class="lg:w-3/4 p-6 rounded-lg" style="background-color: #F6F8FA">
                <div class="mb-8">
                    <h2 class="text-3xl font-extralight mb-2" style="color: #0969DA">
                        Escolher Categoria de Atividade
                    </h2>
                    <p class="text-gray-600">Selecione uma categoria para ver as atividades disponíveis</p>
                    <div id="alertaCategorias" class="mt-4 hidden p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-yellow-800">⚠️ Não foi possível carregar as categorias. Verifique a conexão com o banco de dados.</p>
                    </div>
                </div>
                <div id="categoriasContainer"></div>

                <div class="mt-8 p-6 rounded-lg" style="background-color: #E6F3FF; border-left: 4px solid #0969DA">
                    <h4 class="font-bold mb-2" style="color: #0969DA">Informações Importantes</h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>• Você pode se cadastrar em múltiplas atividades</li>
                        <li>• Após selecionar, você precisará enviar comprovantes</li>
                        <li>• As atividades são organizadas por categoria</li>
                        <li>• Verifique os requisitos antes de se inscrever</li>
                    </ul>
                </div>
            </main>
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

    <script>
        // Verificar autenticação JWT
        function verificarAutenticacao() {
            if (!AuthClient.isLoggedIn()) {
                window.location.href = 'login.php';
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

        // Carregar categorias via JWT
        let todasCategorias = [];
        let isAlunoAntigo = false;
        let isBSI = false;
        let isBCC = false;
        let deveMostrarPET = false;
        let tentativasCarregamento = 0;
        const MAX_TENTATIVAS = 3;

        // Verificar se o aluno tem matrícula entre 2017 e 2022
        function verificarAlunoAntigo() {
            const user = AuthClient.getUser();
            if (user) {
                // Detectar ano de matrícula
                let anoMatricula = null;
                if (user.matricula && typeof user.matricula === 'string') {
                    anoMatricula = parseInt(user.matricula.substring(0, 4));
                }

                // Detectar curso BSI: curso_id === 2 ou nome contém "Sistemas de Informação"
                isBSI = (user.curso_id === 2) || (user.curso_nome && user.curso_nome.toLowerCase().includes('sistemas de informação'));

                // Detectar curso BCC
                const cursoNomeNorm = (user.curso_nome || '').toString().trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                isBCC = (user.curso_id === 1) || cursoNomeNorm.includes('ciencia da computacao') || cursoNomeNorm.includes('bcc');

                // Regra de aluno antigo já existente
                if (anoMatricula) {
                    isAlunoAntigo = anoMatricula >= 2017 && anoMatricula <= 2022;
                }

                // Regra PET: somente para BSI com matrícula a partir de 2018
                deveMostrarPET = !!(isBSI && anoMatricula && anoMatricula >= 2018);

                console.log('🎓 Aluno antigo:', isAlunoAntigo, '| Ano:', anoMatricula, '| isBSI:', isBSI, '| deveMostrarPET:', deveMostrarPET);
            }
        }

        // Função para normalizar nomes de categorias (remove acentos e caracteres especiais)
        function normalizarCategoria(nome) {
            if (!nome) return '';
            let s = nome.toString().trim().toLowerCase();
            s = s.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            s = s.replace(/ß/g, 'ss');
            s = s.replace(/[^a-z0-9\s]/g, ' ');
            s = s.replace(/\s+/g, ' ').trim();
            return s;
        }

        // Função para verificar se é categoria estágio (corrigindo problemas de encoding)
        function ehEstagioCategoria(nome) {
            if (!nome) return false;
            const n = normalizarCategoria(nome);
            if (n === 'estagio') return true;
            if (/^est.*gio$/.test(n)) return true;
            const raw = nome.toString().toLowerCase();
            if (raw.includes('estßgio')) return true;
            return false;
        }

        function obterConfigCategoria(nomeOriginal) {
            const categoriaConfig = {
                'Ensino': { cor: '#1A7F37', icone: '📚', titulo: 'Ensino' },
                'Pesquisa': { cor: '#0969DA', icone: '🔬', titulo: 'Pesquisa' },
                'Atividades extracurriculares': { cor: '#8B5CF6', icone: '🎓', titulo: 'Atividades extracurriculares' },
                'Atividades Extracurriculares': { cor: '#8B5CF6', icone: '🎓', titulo: 'Atividades Extracurriculares' },
                'Estágio': { cor: '#F59E0B', icone: '💼', titulo: 'Estágio' },
                'Ação Social': { cor: '#DC2626', icone: '🤝', titulo: 'Ação Social' },
                'Atividades sociais e comunitárias': { cor: '#DC2626', icone: '🤝', titulo: 'Atividades sociais e comunitárias' },
                'PET': { cor: '#1E3A8A', icone: '📋', titulo: 'PET' }
            };

            if (ehEstagioCategoria(nomeOriginal)) {
                return categoriaConfig['Estágio'];
            }

            if (categoriaConfig[nomeOriginal]) {
                return categoriaConfig[nomeOriginal];
            }

            return { cor: '#6B7280', icone: '📋', titulo: nomeOriginal };
        }

        // Função para aguardar um tempo específico
        function aguardar(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        // Verificar se AuthClient está disponível e carregado
        async function verificarAuthClientDisponivel() {
            let tentativas = 0;
            const maxTentativas = 10;

            while (tentativas < maxTentativas) {
                if (typeof AuthClient !== 'undefined' && AuthClient.isLoggedIn()) {
                    console.log('✅ AuthClient disponível e usuário logado');
                    return true;
                }

                console.log(`⏳ Aguardando AuthClient... Tentativa ${tentativas + 1}/${maxTentativas}`);
                await aguardar(500); // Aguardar 500ms
                tentativas++;
            }

            console.error('❌ AuthClient não disponível após múltiplas tentativas');
            return false;
        }

        // Verificar conectividade com o servidor
        async function verificarConectividade() {
            try {
                console.log('🌐 Verificando conectividade com o servidor...');
                const response = await fetch('../../backend/api/routes/listar_categorias.php', {
                    method: 'HEAD',
                    headers: {
                        'X-API-Key': AuthClient.getApiKey() || 'frontend-gerenciamento-acc-2025'
                    }
                });

                console.log('📡 Status de conectividade:', response.status);
                return response.status < 500; // Aceitar até erros 4xx, mas não 5xx
            } catch (error) {
                console.error('❌ Erro de conectividade:', error);
                return false;
            }
        }

        async function carregarCategorias() {
            try {
                tentativasCarregamento++;
                console.log(`🔄 Iniciando carregamento de categorias - Tentativa ${tentativasCarregamento}/${MAX_TENTATIVAS}`);

                // Verificar se AuthClient está disponível
                const authDisponivel = await verificarAuthClientDisponivel();
                if (!authDisponivel) {
                    throw new Error('AuthClient não disponível - redirecionando para login');
                }

                // Verificar conectividade
                const conectividade = await verificarConectividade();
                if (!conectividade) {
                    throw new Error('Servidor não disponível');
                }

                // Verificar tokens antes da requisição
                const token = AuthClient.getToken();
                const apiKey = AuthClient.getApiKey();
                const user = AuthClient.getUser();

                console.log('🎫 Token disponível:', !!token);
                console.log('🔑 API Key disponível:', !!apiKey);
                console.log('👤 Usuário disponível:', !!user);

                if (!token) {
                    console.error('❌ Token JWT não encontrado - fazendo logout');
                    AuthClient.logout();
                    return;
                }

                if (!apiKey) {
                    console.error('❌ API Key não encontrada - usando padrão');
                    localStorage.setItem('acc_api_key', 'frontend-gerenciamento-acc-2025');
                }

                console.log('🌐 Fazendo requisição para categorias...');

                const response = await AuthClient.fetch('../../backend/api/routes/listar_categorias.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                console.log('📡 Status da resposta:', response.status);

                if (!response.ok) {
                    console.error('❌ Resposta não OK:', response.status, response.statusText);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                console.log('📊 Resposta da API:', data);

                if (data.success && data.data) {
                    // Aplicar regras de negócio para filtrar categorias
                    // IMPORTANTE: NÃO filtrar estágio - todos os alunos devem ver esta categoria
                    let categoriasFiltradas = data.data;

                    todasCategorias = categoriasFiltradas;
                    console.log('✅ Categorias carregadas com sucesso:', todasCategorias.length);
                    renderizarCategorias();
                    document.getElementById('alertaCategorias').classList.add('hidden');
                    tentativasCarregamento = 0; // Reset contador em caso de sucesso
                } else {
                    console.error('❌ Erro na resposta da API:', data.error || 'Erro desconhecido');
                    throw new Error(data.error || 'Erro ao carregar categorias');
                }

            } catch (error) {
                console.error('💥 Erro ao carregar categorias:', error);

                // Mostrar alerta de erro
                const alertaElement = document.getElementById('alertaCategorias');
                alertaElement.classList.remove('hidden');
                alertaElement.innerHTML = `
                    <p class="text-yellow-800">⚠️ ${error.message}</p>
                    ${tentativasCarregamento < MAX_TENTATIVAS ? 
                        `<p class="text-yellow-600 text-sm mt-2">Tentando novamente em 3 segundos... (${tentativasCarregamento}/${MAX_TENTATIVAS})</p>` : 
                        '<p class="text-red-600 text-sm mt-2">Máximo de tentativas atingido. Recarregue a página ou verifique sua conexão.</p>'
                    }
                `;

                // Retry automático se não atingiu o máximo de tentativas
                if (tentativasCarregamento < MAX_TENTATIVAS) {
                    console.log(`🔄 Tentando novamente em 3 segundos... (${tentativasCarregamento}/${MAX_TENTATIVAS})`);
                    setTimeout(() => {
                        carregarCategorias();
                    }, 3000);
                } else {
                    console.error('❌ Máximo de tentativas atingido para carregamento de categorias');
                }
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
                'Ensino': {
                    cor: '#1A7F37',
                    icone: '📚',
                    titulo: 'Ensino'
                },
                'Pesquisa': {
                    cor: '#0969DA',
                    icone: '🔬',
                    titulo: 'Pesquisa'
                },
                'Atividades extracurriculares': {
                    cor: '#8B5CF6',
                    icone: '🎓',
                    titulo: 'Atividades extracurriculares'
                },
                'Atividades Extracurriculares': {
                    cor: '#8B5CF6',
                    icone: '🎓',
                    titulo: 'Atividades Extracurriculares'
                },
                'Estágio': {
                    cor: '#F59E0B',
                    icone: '💼',
                    titulo: 'Estágio'
                },
                'Ação Social': {
                    cor: '#DC2626',
                    icone: '🤝',
                    titulo: 'Ação Social'
                },
                'Atividades sociais e comunitárias': {
                    cor: '#DC2626',
                    icone: '🤝',
                    titulo: 'Atividades sociais e comunitárias'
                },
                'PET': {
                    cor: '#1E3A8A',
                    icone: '📋',
                    titulo: 'PET'
                }
            };

            // Se for aluno antigo (2017-2022), mostrar interface especial
            if (isAlunoAntigo) {
                const categoriasEspeciais = [{
                        nome: 'Atividades extracurriculares',
                        config: categoriaConfig['Atividades extracurriculares']
                    },
                    {
                        nome: 'Ensino',
                        config: categoriaConfig['Ensino']
                    },
                    {
                        nome: 'Estágio',
                        config: categoriaConfig['Estágio']
                    },
                    {
                        nome: 'Pesquisa',
                        config: categoriaConfig['Pesquisa']
                    },
                    {
                        nome: 'Atividades sociais e comunitárias',
                        config: categoriaConfig['Atividades sociais e comunitárias']
                    }
                ];

                // Incluir PET somente quando permitido pela regra
                if (deveMostrarPET) {
                    categoriasEspeciais.push({
                        nome: 'PET',
                        config: categoriaConfig['PET']
                    });
                }

                container.innerHTML = `
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">Escolher Categoria de Atividade</h3>
                        <p class="text-blue-700 text-sm">Selecione uma categoria para ver as atividades disponíveis</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        ${categoriasEspeciais.map(item => `
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-lg transition-all duration-300 cursor-pointer transform hover:scale-105"
                                 onclick="selecionarCategoria('${item.nome}')">
                                <div class="p-6 text-center" style="${item.nome === 'PET' ? 'background-color: ' + item.config.cor : 'background: linear-gradient(135deg, ' + item.config.cor + ', ' + item.config.cor + 'dd)'}">
                                    <div class="text-4xl mb-3">${item.config.icone}</div>
                                    <h3 class="text-xl font-bold text-white">${item.config.titulo}</h3>
                                </div>
                                <div class="p-4 text-center">
                                    <p class="text-gray-600 text-sm mb-4">Clique para ver as atividades disponíveis nesta categoria</p>
                                    <div class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition duration-200"
                                         style="background-color: ${item.config.cor}20; color: ${item.config.cor}">
                                        Ver Atividades
                                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else {
                // Interface normal para alunos de 2023 em diante - FILTRAR categorias sociais e PET conforme regra
                const categoriasFiltradas = todasCategorias.filter(categoria => {
                    const nome = categoria.nome.toLowerCase();
                    const ehSocial = (nome.includes('ação social') || nome.includes('social e comunitária') || nome.includes('atividades sociais'));
                    const ehPET = nome === 'pet';
                    if (ehSocial) return false;
                    if (ehPET && !deveMostrarPET) return false;
                    return true;
                });

                container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    ${categoriasFiltradas.map(categoria => {
                        const config = obterConfigCategoria(categoria.nome);
                        return `
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-lg transition-all duration-300 cursor-pointer transform hover:scale-105"
                                 onclick="selecionarCategoria('${categoria.nome}', ${categoria.id})">
                                <div class="p-6 text-center" style="${config.titulo === 'PET' ? 'background-color: ' + config.cor : 'background: linear-gradient(135deg, ' + config.cor + ', ' + config.cor + 'dd)'}">
                                    <div class="text-4xl mb-3">${config.icone}</div>
                                    <h3 class="text-xl font-bold text-white">${config.titulo}</h3>
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
        }

        function selecionarCategoria(nomeCategoria, idCategoria) {
            const idMap = {
                1: 'atividades_ensino.php',
                2: 'atividades_pesquisa.php',
                3: 'atividades_extracurriculares.php',
                4: 'atividades_estagio.php',
                5: 'atividades_acao_social.php',
                6: 'pet.php'
            };
            let pagina = undefined;
            if (typeof idCategoria === 'number' && idMap[idCategoria]) {
                pagina = idMap[idCategoria];
            }
            if (!pagina) {
                const slug = normalizarCategoria(nomeCategoria);
                const mapa = {
                    'ensino': 'atividades_ensino.php',
                    'pesquisa': 'atividades_pesquisa.php',
                    'atividades extracurriculares': 'atividades_extracurriculares.php',
                    'extracurriculares': 'atividades_extracurriculares.php',
                    'extensao': 'atividades_extracurriculares.php',
                    'estagio': 'atividades_estagio.php',
                    'acao social': 'atividades_acao_social.php',
                    'atividades sociais e comunitarias': 'atividades_acao_social.php',
                    'pet': 'pet.php'
                };
                pagina = mapa[slug];
                if (!pagina) {
                    if (/^est.*gio$/.test(slug) || (slug.includes('est') && slug.includes('gio'))) pagina = 'atividades_estagio.php';
                    else if (slug.includes('pesq')) pagina = 'atividades_pesquisa.php';
                    else if (slug.includes('ensin')) pagina = 'atividades_ensino.php';
                    else if (slug.includes('extrac') || slug.includes('extens')) pagina = 'atividades_extracurriculares.php';
                    else if (slug.includes('social') || slug.includes('comunit')) pagina = 'atividades_acao_social.php';
                }
            }
            if (pagina) {
                window.location.href = pagina;
            } else {
                alert('Página não encontrada para esta categoria.');
            }
        }

        // Inicializar página com verificação de dependências
        document.addEventListener('DOMContentLoaded', async function() {
            console.log('🚀 DOM carregado - iniciando verificações...');

            // Verificar se AuthClient está disponível
            if (typeof AuthClient === 'undefined') {
                console.error('❌ AuthClient não carregado - recarregando página');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
                return;
            }

            // Verificar autenticação
            if (!AuthClient.isLoggedIn()) {
                console.log('❌ Usuário não autenticado - redirecionando para login');
                window.location.href = 'login.php';
                return;
            }

            console.log('✅ Dependências verificadas - iniciando carregamento');
            verificarAlunoAntigo();

            // Aguardar um pouco para garantir que tudo está carregado
            setTimeout(() => {
                carregarCategorias();
            }, 100);
        });
    </script>
</body>

</html>
