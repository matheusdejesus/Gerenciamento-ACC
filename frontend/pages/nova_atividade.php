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

        // Carregar categorias via JWT
        let todasCategorias = [];

        async function carregarCategorias() {
            try {
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/listar_categorias.php', {
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
            // Redirecionar para a página específica da categoria
            const paginasCategoria = {
                'Ensino': 'atividades_ensino.php',
                'Pesquisa': 'atividades_pesquisa.php',
                'Atividades extracurriculares': 'atividades_extracurriculares.php',
                'Estágio': 'atividades_estagio.php'
            };
            
            const pagina = paginasCategoria[nomeCategoria];
            if (pagina) {
                window.location.href = pagina;
            } else {
                alert('Página não encontrada para esta categoria.');
            }
        }

        carregarCategorias();
    </script>
</body>
</html>