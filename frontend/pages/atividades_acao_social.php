<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividades de Ação Social - Sistema ACC</title>
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
                    <div class="text-4xl mr-4">🤝</div>
                    <div>
                        <h2 class="text-3xl font-bold" style="color: #DC2626">Atividades de Ação Social</h2>
                        <p class="text-gray-600 mt-2">Selecione uma atividade de ação social para cadastrar</p>
                    </div>
                </div>
                


            </div>

            <!-- Lista de Atividades -->
            <div id="atividadesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Atividades serão carregadas dinamicamente -->
            </div>

            <!-- Mensagem quando não há atividades -->
            <div id="mensagemVazia" class="text-center py-12 hidden">
                <div class="text-6xl mb-4">🤝</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma atividade disponível</h3>
                <p class="text-gray-500">No momento não há atividades de ação social cadastradas no sistema.</p>
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div id="modalDetalhes" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-xl font-bold" style="color: #DC2626">Detalhes da Atividade</h3>
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
                    <button onclick="abrirModalSelecao()" class="px-4 py-2 text-white rounded-lg hover:opacity-90 transition duration-200" style="background-color: #DC2626">
                        Selecionar Atividade
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cadastro -->
    <div id="modalSelecao" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 overflow-y-auto z-50">
        <div class="relative w-full max-w-2xl bg-white rounded-lg shadow-xl max-h-[90vh] overflow-y-auto mx-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold" style="color: #DC2626">Cadastrar Atividade de Ação Social</h3>
                    <button onclick="fecharModalSelecao()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="formCadastro" class="space-y-4">
                    <input type="hidden" id="atividadeId" name="atividade_id">
                    <input type="hidden" id="categoriaId" name="categoria_id" value="5">

                    <!-- Nome do Projeto/Ação -->
                    <div>
                        <label for="nomeProjeto" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Projeto/Ação Social *
                        </label>
                        <input type="text" id="nomeProjeto" name="nome_projeto" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="Ex: Projeto Alimentar Solidário" required>
                    </div>

                    <!-- Instituição/Organização -->
                    <div>
                        <label for="instituicao" class="block text-sm font-medium text-gray-700 mb-2">
                            Instituição/Organização *
                        </label>
                        <input type="text" id="instituicao" name="instituicao" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="Ex: ONG Esperança, Igreja São José" required>
                    </div>
                    
                    <!-- Carga Horária -->
                    <div>
                        <label for="cargaHoraria" class="block text-sm font-medium text-gray-700 mb-2">
                            Carga Horária Total (horas) *
                        </label>
                        <input type="number" id="cargaHoraria" name="carga_horaria" min="1" max="200"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="Ex: 40" required>
                        <div id="cargaHoraria-error" class="text-red-500 text-sm mt-1 hidden" role="alert">
                            A carga horária não pode exceder 30 horas
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Restante disponível: <span id="restanteAcaoSocial">--</span> horas</p>
                        <p class="text-xs text-red-700 mt-1" id="sugestoesAcaoSocial">Sugestões: --</p>
                        <p class="text-xs font-medium mt-1 hidden" id="mensagemLimiteAcaoSocial" style="color:#DC2626"></p>
                    </div>

                    <!-- Comprovante -->
                    <div>
                        <label for="declaracao" class="block text-sm font-medium text-gray-700 mb-2">
                            Comprovante/Declaração *
                        </label>
                        <input type="file" id="declaracao" name="declaracao" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                        <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, JPEG, PNG (máx. 5MB)</p>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="fecharModalSelecao()"
                                class="flex-1 px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition duration-200"
                                style="background-color: #DC2626">
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
        function normalizarCategoria(nome) {
            if (!nome) return '';
            let s = nome.toString().trim().toLowerCase();
            s = s.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            s = s.replace(/ß/g, 'ss');
            s = s.replace(/[^a-z0-9\s]/g, ' ');
            s = s.replace(/\s+/g, ' ').trim();
            return s;
        }

        function tituloCanonicoAcaoSocial(nome) {
            const raw = (nome || '').toString().toLowerCase();
            const norm = normalizarCategoria(nome || '');
            if (raw.includes('comunitßr')) return 'Atividades sociais e comunitárias';
            if (norm === 'atividades sociais e comunitarias') return 'Atividades sociais e comunitárias';
            if (!nome) return 'Ação Social';
            return nome;
        }
        // Verificar autenticação ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            if (!AuthClient.isLoggedIn()) {
                alert('Você precisa estar logado para acessar esta página.');
                window.location.href = 'login.php';
                return;
            }

            // Verificar se é aluno com matrícula entre 2017-2022
            const user = AuthClient.getUser();
            if (!user || user.tipo !== 'aluno') {
                alert('Acesso restrito a alunos.');
                window.location.href = 'home_aluno.php';
                return;
            }

            // Verificar se a matrícula está entre 2017 e 2022
            if (user && user.matricula) {
                const anoMatricula = parseInt(user.matricula.substring(0, 4));
                if (anoMatricula < 2017 || anoMatricula > 2022) {
                    alert('Atividades de Ação Social estão disponíveis apenas para alunos com matrícula entre 2017 e 2022.');
                    window.location.href = 'home_aluno.php';
                    return;
                }
            } else {
                alert('Não foi possível verificar sua matrícula. Entre em contato com o suporte.');
                window.location.href = 'home_aluno.php';
                return;
            }

            // Atividades de ação social disponíveis apenas para alunos 2017-2022
            carregarAtividades();
        });

        let atividadeSelecionada = null;
        let atividadeAcaoSocial = null;

        async function carregarAtividades() {
            const container = document.getElementById('atividadesContainer');
            const mensagemVazia = document.getElementById('mensagemVazia');

            try {
                console.log('Carregando atividades de ação social...');
                
                // Buscar atividades de Ação Social da nova API usando AuthClient.fetch()
                const response = await AuthClient.fetch('../../backend/api/routes/listar_atividades_disponiveis.php?type=acao_social', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    console.error('Erro na resposta da API:', response.status, response.statusText);
                    throw new Error(`Erro ao buscar atividades: ${response.status} ${response.statusText}`);
                }

                const data = await response.json();
                console.log('Dados recebidos da API:', data);
                
                if (!data.success || !data.data || !data.data.atividades || data.data.atividades.length === 0) {
                    console.log('Nenhuma atividade encontrada ou erro na resposta');
                    container.classList.add('hidden');
                    mensagemVazia.classList.remove('hidden');
                    return;
                }

                // Exibir todas as atividades disponíveis
                let cardsHTML = '';
                data.data.atividades.forEach((atividade, index) => {
                    cardsHTML += `
                        <div class="atividade-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-lg transition-all duration-300">
                            <div class="p-4" style="background-color: #DC2626">
                                <h3 class="text-lg font-bold text-white">${atividade.nome}</h3>
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 mt-2">
                                    ${tituloCanonicoAcaoSocial(atividade.categoria)}
                                </span>
                            </div>
                            <div class="p-4">
                                <p class="text-gray-600 text-sm mb-4">${atividade.observacoes || atividade.descricao || 'Atividade de ação social voltada para projetos comunitários e voluntariado.'}</p>
                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="font-medium" style="color: #DC2626">Horas Máximas:</span>
                                        <span class="text-gray-600">${atividade.carga_horaria_maxima}h</span>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="verDetalhes(${index})"
                                            class="flex-1 px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200"
                                            style="color: #DC2626">
                                        Ver Detalhes
                                    </button>
                                    <button onclick="selecionarAtividade(${index})"
                                            class="flex-1 px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition duration-200"
                                            style="background-color: #DC2626">
                                        Cadastrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = `<div class="atividades-grid">${cardsHTML}</div>`;
                
                // Armazenar todas as atividades para uso posterior
                window.atividadesDisponiveis = data.data.atividades;
                
            } catch (error) {
                console.error('Erro ao carregar atividades:', error);
                container.classList.add('hidden');
                mensagemVazia.classList.remove('hidden');
            }
        }

        function verDetalhes(index) {
            if (!window.atividadesDisponiveis || !window.atividadesDisponiveis[index]) return;
            
            const atividade = window.atividadesDisponiveis[index];

            document.getElementById('conteudoDetalhes').innerHTML = `
                <div class="p-6">
                    <h4 class="text-lg font-semibold mb-4" style="color: #DC2626">${atividade.nome}</h4>
                    <div class="space-y-4">
                        <div>
                            <span class="font-medium text-gray-700">Descrição:</span>
                            <p class="mt-1 text-gray-600">${atividade.observacoes || atividade.descricao || 'Atividade de ação social voltada para projetos comunitários, voluntariado e ações que beneficiem a sociedade.'}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Carga Horária Máxima:</span>
                            <span class="ml-2 text-gray-600">${atividade.carga_horaria_maxima} horas</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Categoria:</span>
                            <span class="ml-2 text-gray-600">${tituloCanonicoAcaoSocial(atividade.categoria)}</span>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h5 class="font-medium text-yellow-800 mb-2">Documentos Necessários:</h5>
                            <ul class="text-sm text-yellow-700 space-y-1">
                                <li>• Declaração da instituição/organização</li>
                                <li>• Comprovante de participação</li>
                                <li>• Relatório das atividades realizadas (se aplicável)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;
            
            atividadeSelecionada = atividade;
            document.getElementById('modalDetalhes').classList.remove('hidden');
        }

        function selecionarAtividade(index) {
            if (!window.atividadesDisponiveis || !window.atividadesDisponiveis[index]) return;
            
            const atividade = window.atividadesDisponiveis[index];
            atividadeSelecionada = atividade;
            document.getElementById('atividadeId').value = atividade.id;
            document.getElementById('modalSelecao').classList.remove('hidden');
            aplicarLimitesAcaoSocial(atividade.id);
            (async function(){
                try {
                    const resp = await AuthClient.fetch('../../backend/api/routes/calcular_horas_categorias.php', { method: 'POST' });
                    const json = await resp.json();
                    const categorias = json?.data?.categorias || {}; const limites = json?.data?.limites || {};
                    const atual = categorias['acao_social'] || 0; const lim = limites['acao_social'] || 0;
                    if (lim > 0 && atual >= lim) {
                        const msg = document.getElementById('mensagemLimiteAcaoSocial');
                        if (msg) { msg.textContent = 'Você atingiu o limite de horas da categoria Ação Social. Novos cadastros não são permitidos.'; msg.classList.remove('hidden'); }
                        const submitBtn = document.querySelector('#formCadastro button[type="submit"]');
                        const input = document.getElementById('cargaHoraria');
                        if (submitBtn) submitBtn.disabled = true;
                        if (input) { input.disabled = true; input.min = 0; input.max = 0; }
                    }
                } catch(e) {}
            })();
        }

        function abrirModalSelecao() {
            if (!atividadeSelecionada) return;
            
            document.getElementById('modalDetalhes').classList.add('hidden');
            document.getElementById('atividadeId').value = atividadeSelecionada.id;
            document.getElementById('modalSelecao').classList.remove('hidden');
            aplicarLimitesAcaoSocial(atividadeSelecionada.id);
        }

        function fecharModal() {
            document.getElementById('modalDetalhes').classList.add('hidden');
            atividadeSelecionada = null;
        }

        function fecharModalSelecao() {
            document.getElementById('modalSelecao').classList.add('hidden');
            document.getElementById('formCadastro').reset();
        }

        // Submissão do formulário
        document.getElementById('formCadastro').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            try {
                // Desabilitar botão e mostrar loading
                submitButton.disabled = true;
                submitButton.textContent = 'Enviando...';
                
                const formData = new FormData(this);
                const user = AuthClient.getUser();
                
                // Validar campos obrigatórios
                const nomeProjeto = formData.get('nome_projeto');
                const instituicao = formData.get('instituicao');
                const cargaHoraria = formData.get('carga_horaria');
                const declaracao = formData.get('declaracao');
                
                if (!nomeProjeto || !instituicao || !cargaHoraria) {
                    throw new Error('Por favor, preencha todos os campos obrigatórios.');
                }
                
                if (!declaracao || declaracao.size === 0) {
                    throw new Error('Por favor, anexe o comprovante/declaração.');
                }
                
                // Validar carga horária
                const horas = parseInt(cargaHoraria);
                if (horas < 1 || horas > 200) {
                    throw new Error('A carga horária deve estar entre 1 e 200 horas.');
                }
                
                // Validar contra restante disponível
                const rest = await obterRestanteAcaoSocial(document.getElementById('atividadeId').value);
                if (horas > rest.restante) {
                    alert(`As horas informadas excedem o restante disponível (${rest.restante}h). ${gerarSugestoesAcaoSocial(rest.restante, 1, rest.restante) ? 'Sugestões: ' + gerarSugestoesAcaoSocial(rest.restante, 1, rest.restante) : ''}`);
                    return;
                }

                // Preparar dados para a API cadastrar_atividades.php
                const apiFormData = new FormData();
                
                // Mapear campos do formulário para os campos esperados pela API
                apiFormData.append('atividades_por_resolucao_id', document.getElementById('atividadeId').value);
                apiFormData.append('titulo', nomeProjeto);
                
                // Usar apenas o nome da instituição como descrição
                apiFormData.append('descricao', instituicao);
                
                apiFormData.append('ch_solicitada', cargaHoraria);
                
                // Adicionar arquivo se existir
                if (declaracao && declaracao.size > 0) {
                    apiFormData.append('declaracao', declaracao);
                }
                
                // Enviar para o endpoint correto
                const response = await AuthClient.fetch('../../backend/api/routes/cadastrar_atividades.php', {
                    method: 'POST',
                    body: apiFormData
                });
                
                // AuthClient.fetch já processa o JSON, então podemos acessar diretamente
                const result = response.data || await response.json();
                console.log('Response data:', result);
                
                if (!response.ok || !result.success) {
                    throw new Error(result.error || 'Erro ao cadastrar atividade.');
                }
                
                // Sucesso
                alert('✅ Atividade de Ação Social cadastrada com sucesso!\n\nSua solicitação foi enviada para avaliação.');
                fecharModalSelecao();
                
                // Redirecionar para a página do aluno
                setTimeout(() => {
                    window.location.href = 'home_aluno.php';
                }, 1000);
                
            } catch (error) {
                console.error('Erro ao cadastrar atividade:', error);
                alert('❌ ' + error.message);
            } finally {
                // Reabilitar botão
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });

        // Validação de carga horária em tempo real
        document.getElementById('cargaHoraria').addEventListener('input', function() {
            const valor = parseInt(this.value);
            const errorDiv = document.getElementById('cargaHoraria-error');
            
            // Validação para mais de 30 horas
            if (valor > 30) {
                this.classList.add('border-red-500');
                this.classList.remove('border-gray-300');
                errorDiv.classList.remove('hidden');
            } else {
                this.classList.remove('border-red-500');
                this.classList.add('border-gray-300');
                errorDiv.classList.add('hidden');
            }
            
            // Validação geral (1-200 horas)
            if (valor && (valor < 1 || valor > 200)) {
                this.setCustomValidity('A carga horária deve estar entre 1 e 200 horas.');
            } else {
                this.setCustomValidity('');
            }

            // Limitar ao restante disponível
            const maxPermitido = parseInt(this.max)||0;
            if (maxPermitido && valor > maxPermitido) {
                this.value = maxPermitido;
            }
        });

        async function obterRestanteAcaoSocial(aprId) {
            try {
                const resp = await AuthClient.fetch('../../backend/api/routes/listar_atividades_disponiveis.php?acao=enviadas&limite=200');
                const json = await resp.json();
                const lista = json?.data?.atividades || [];
                const relevantes = lista.filter(a => parseInt(a.atividades_por_resolucao_id) === parseInt(aprId) && ['aprovado','aprovada'].includes(String(a.status).toLowerCase()));
                const soma = relevantes.reduce((acc, a) => acc + (parseInt(a.ch_atribuida||0)||0), 0);
                const max = (atividadeSelecionada?.carga_horaria_maxima || atividadeSelecionada?.horas_max || 0);
                const restante = Math.max(0, max - soma);
                return { restante, max };
            } catch (e) {
                const max = (atividadeSelecionada?.carga_horaria_maxima || atividadeSelecionada?.horas_max || 0);
                return { restante: max, max };
            }
        }

        function gerarSugestoesAcaoSocial(restante, minCadastro, maxCadastro) {
            const r = parseInt(restante)||0; const min = Math.max(1, parseInt(minCadastro)||1); const max = Math.max(min, parseInt(maxCadastro)||min);
            const sugs = [];
            if (r <= 0) return '';
            if (r % max === 0) sugs.push(`${r/max}x${max}h`);
            if (r % min === 0) sugs.push(`${r/min}x${min}h`);
            for (let k = Math.floor(r/max); k >= 1 && sugs.length < 3; k--) {
                const left = r - k*max;
                if (left >= 0 && left % min === 0) sugs.push(`${k}x${max}h + ${Math.floor(left/min)}x${min}h`);
            }
            if (!sugs.length) sugs.push(`${r}h`);
            return sugs.join(', ');
        }

        async function aplicarLimitesAcaoSocial(aprId) {
            const dados = await obterRestanteAcaoSocial(aprId);
            let restanteTotal = null;
            try { const t = await verificarBloqueioCategoria('acao_social'); restanteTotal = t.lim - t.atual; } catch (e) { restanteTotal = null; }
            const input = document.getElementById('cargaHoraria');
            input.max = restanteTotal !== null ? Math.min(dados.restante, Math.max(0, restanteTotal)) : dados.restante;
            input.min = dados.restante === 0 ? 0 : 1;
            const restanteEl = document.getElementById('restanteAcaoSocial');
            restanteEl.textContent = dados.restante;
            const sugEl = document.getElementById('sugestoesAcaoSocial');
            sugEl.textContent = `Sugestões: ${gerarSugestoesAcaoSocial(dados.restante, 1, dados.restante) || '--'}`;
            const msg = document.getElementById('mensagemLimiteAcaoSocial');
            const submitBtn = document.querySelector('#formCadastro button[type="submit"]');
            const bloqueadoTotal = (restanteTotal !== null && Math.max(0, restanteTotal) === 0);
            if (dados.restante === 0 || bloqueadoTotal) {
                msg.textContent = 'Você atingiu o limite de horas para esta atividade.';
                msg.classList.remove('hidden');
                input.value = '';
                input.disabled = true;
                if (submitBtn) submitBtn.disabled = true;
            } else {
                msg.classList.add('hidden');
                input.disabled = false;
                if (submitBtn) submitBtn.disabled = false;
            }
        }


    </script>
</body>
</html>
