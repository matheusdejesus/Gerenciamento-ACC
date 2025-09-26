<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Aluno - SACC UFOPA</title>
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
                    <span id="nomeUsuario" class="text-white mr-4 font-extralight">Carregando...</span>
                    <button onclick="AuthClient.logout()" class="text-white hover:text-gray-200">Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex-grow pt-24 flex" style="background-color: #0D1117">
        <div class="container mx-auto flex flex-col lg:flex-row p-4">
            <aside class="lg:w-1/4 p-6 rounded-lg mb-4 lg:mb-0 mr-0 lg:mr-4" style="background-color: #F6F8FA">
                <nav class="space-y-2">
                    <a href="configuracoes_aluno.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Configurações da Conta
                    </a>
                </nav>
            </aside>
            <main class="lg:w-3/4 p-6 rounded-lg" style="background-color: #F6F8FA">
                <div class="mb-8">
                    <h2 class="text-3xl font-extralight mb-2" style="color: #0969DA">
                        Olá, <span id="nomeUsuarioMain">Carregando...</span>
                    </h2>
                    <p class="text-gray-600">Aqui estão suas Atividades ACC.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Horas Certificadas</h3>
                        <p id="horasValidadas" class="text-3xl font-bold" style="color: #1A7F37">-</p>
                        <p class="text-xs text-gray-500 mt-1">Atividades concluídas e validadas</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Horas Pendentes</h3>
                        <p id="horasPendentes" class="text-3xl font-bold" style="color: #B45309">120</p>
                        <p class="text-xs text-gray-500 mt-1">Aguardando conclusão ou avaliação</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4" style="background-color: #151B23">
                        <h3 class="text-xl font-bold text-white">Minhas Atividades</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead style="background-color: #F6F8FA">
    <tr>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Título</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Atividade</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Categoria</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Horas</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Status</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Ações</th>
    </tr>
</thead>
                            <tbody id="tabelaAtividades" class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                        <div id="mensagemVazia" class="p-8 text-center text-gray-500 hidden">
                            <p class="text-lg">Você ainda não possui atividades cadastradas.</p>
                            <p class="text-sm mt-2">Clique em "Nova Atividade" para começar.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="nova_atividade.php" class="p-4 rounded-lg text-center transition duration-200 text-white" style="background-color: #1A7F37">
                        <h4 class="font-bold mb-2">Nova Atividade</h4>
                        <p class="text-sm">Cadastrar em uma nova atividade</p>
                    </a>
                    <a href="enviar_comprovante.php" class="p-4 rounded-lg text-center transition duration-200" style="background-color: #0969DA">
                        <h4 class="font-bold mb-2 text-white">Enviar Comprovante</h4>
                        <p class="text-sm text-white">Fazer upload de comprovantes</p>
                    </a>
                </div>
            </main>
        </div>
    </div>
    <div id="modalDetalhesAtividade" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-xl font-bold" style="color: #0969DA">Detalhes da Atividade</h3>
                    <button onclick="fecharModalDetalhes()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold mb-3" style="color: #0969DA">Informações da Atividade</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <span class="font-medium text-gray-700">Título:</span>
                                    <span id="detalheTitulo" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Categoria:</span>
                                    <span id="detalheCategoria" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Status:</span>
                                    <span id="detalheStatus" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Horas Solicitadas:</span>
                                    <span id="detalheHorasSolicitadas" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Horas Aprovadas:</span>
                                    <span id="detalheHorasAprovadas" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Data de Submissão:</span>
                                    <span id="detalheDataSubmissao" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Período:</span>
                                    <span id="detalhePeriodo" class="ml-2"></span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <span class="font-medium text-gray-700">Descrição:</span>
                                <p id="detalheDescricao" class="mt-2 text-gray-600"></p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold mb-3" style="color: #0969DA">Orientador</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <span id="detalheOrientador" class="text-gray-700"></span>
                        </div>
                    </div>
                    <div id="documentosAnexadosContainer" class="mb-6">
                        <h4 class="text-lg font-semibold mb-3" style="color: #0969DA">Documentos Anexados</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div id="documentoDeclaracaoAluno"></div>
                        </div>
                    </div>
                    <div id="parecerContainer" class="mb-6 hidden">
                        <h4 class="text-lg font-semibold mb-3" style="color: #0969DA">Parecer da Avaliação</h4>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p id="detalheParecer" class="text-gray-700"></p>
                        </div>
                    </div>
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
        if (!AuthClient.isLoggedIn()) {
            window.location.href = 'login.php';
        }
        
        const user = AuthClient.getUser();
        if (!user || user.tipo !== 'aluno') {
            AuthClient.logout();
        }
        
        // Atualizar nome do usuário na interface
        if (user && user.nome) {
            document.getElementById('nomeUsuario').textContent = user.nome;
            document.getElementById('nomeUsuarioMain').textContent = user.nome;
        }

        let minhasAtividades = [];

        // Função para carregar atividades de todas as categorias
        async function carregarMinhasAtividades() {
            try {
                console.log('Iniciando carregamento de atividades...');
                
                // Obter ID do usuário logado
                const usuario = AuthClient.getUser();
                const alunoId = usuario ? usuario.id : null;
                
                if (!alunoId) {
                    console.error('Usuário não logado ou ID não encontrado');
                    exibirMensagemErro('Erro de autenticação');
                    return;
                }

                // Fazer requisições paralelas para todas as categorias
                const promises = [
                    // Atividades ACC (extracurriculares) - CORRIGIDO: usar rota específica
                    AuthClient.fetch(`/Gerenciamento-ACC/backend/api/routes/atividade_complementar_acc.php?aluno_id=${alunoId}`)
                        .then(response => response.json())
                        .catch(error => {
                            console.warn('Erro ao carregar atividades ACC:', error);
                            return { success: false, data: [] };
                        }),
                    
                    // Atividades de Ensino
                    AuthClient.fetch(`/Gerenciamento-ACC/backend/api/routes/atividade_complementar_ensino.php?aluno_id=${alunoId}`)
                        .then(response => response.json())
                        .catch(error => {
                            console.warn('Erro ao carregar atividades de Ensino:', error);
                            return { success: false, data: [] };
                        }),
                    
                    // Atividades de Estágio
                    AuthClient.fetch(`/Gerenciamento-ACC/backend/api/routes/atividades_estagio.php?aluno_id=${alunoId}`)
                        .then(response => response.json())
                        .catch(error => {
                            console.warn('Erro ao carregar atividades de Estágio:', error);
                            return { success: false, data: [] };
                        }),
                    
                    // Atividades de Pesquisa
                    AuthClient.fetch(`/Gerenciamento-ACC/backend/api/routes/atividade_complementar_pesquisa.php?aluno_id=${alunoId}`)
                        .then(response => response.json())
                        .catch(error => {
                            console.warn('Erro ao carregar atividades de Pesquisa:', error);
                            return { success: false, data: [] };
                        })
                ];

                const resultados = await Promise.all(promises);
                console.log('Resultados das requisições:', resultados);

                // Consolidar todas as atividades
                let todasAtividades = [];
                
                resultados.forEach((resultado, index) => {
                    if (resultado.success && resultado.data) {
                        const categoria = ['ACC', 'Ensino', 'Estágio', 'Pesquisa'][index];
                        console.log(`Atividades de ${categoria}:`, resultado.data);
                        
                        // Normalizar dados para cada categoria
                        const atividadesNormalizadas = resultado.data.map(atividade => {
                            let titulo, cargaHoraria;
                            
                            // Mapear campos específicos por categoria
                            if (categoria === 'ACC') {
                                titulo = atividade.curso_evento_nome || 'Sem título';
                                cargaHoraria = atividade.horas_realizadas || 0;
                            } else if (categoria === 'Pesquisa') {
                                // Para pesquisa, usar o título específico da atividade (nome do evento, projeto ou artigo)
                                titulo = atividade.titulo_atividade || atividade.nome_evento || atividade.nome_projeto || atividade.nome_artigo || 'Sem título';
                                cargaHoraria = atividade.horas_realizadas || 0;
                            } else {
                                titulo = atividade.titulo || atividade.nome_disciplina || atividade.empresa || atividade.tema || 'Sem título';
                                cargaHoraria = atividade.carga_horaria_solicitada || atividade.carga_horaria || atividade.horas || atividade.horas_realizadas || 0;
                            }
                            
                            return {
                                ...atividade,
                                categoria_origem: categoria,
                                // Garantir campos obrigatórios
                                titulo: titulo,
                                categoria_nome: atividade.categoria_nome || categoria,
                                atividade_titulo: atividade.atividade_titulo || titulo, // Usar o título da atividade disponível
                                carga_horaria_solicitada: cargaHoraria,
                                carga_horaria_aprovada: atividade.carga_horaria_aprovada || atividade.horas_aprovadas || null,
                                status: atividade.status || 'Pendente',
                                data_submissao: atividade.data_submissao || atividade.data_inicio || new Date().toISOString().split('T')[0]
                            };
                        });
                        
                        todasAtividades = todasAtividades.concat(atividadesNormalizadas);
                    }
                });

                // Ordenar por data de submissão (mais recente primeiro)
                todasAtividades.sort((a, b) => {
                    const dataA = new Date(a.data_submissao);
                    const dataB = new Date(b.data_submissao);
                    return dataB - dataA;
                });

                console.log('Total de atividades carregadas:', todasAtividades.length);
                console.log('Atividades consolidadas:', todasAtividades);

                minhasAtividades = todasAtividades;
                atualizarTabelaAtividades();
                atualizarEstatisticas();

            } catch (error) {
                console.error('Erro geral no carregamento:', error);
                exibirMensagemErro('Erro de conexão ao carregar atividades');
            }
        }
        
        // Função para obter status mais detalhado
        function getStatusDetalhado(atividade) {
            // Verificar status do banco de dados primeiro
            if (atividade.status === 'aprovado') {
                return { class: 'bg-green-100 text-green-800', text: 'Aprovado' };
            }

            if (atividade.status === 'rejeitado') {
                return { class: 'bg-red-100 text-red-800', text: 'Rejeitado' };
            }

            if (atividade.status === 'Aguardando avaliação') {
                return { class: 'bg-yellow-100 text-yellow-800', text: 'Aguardando Avaliação' };
            }

            // Fallback para status antigos baseados em observações
            if (atividade.status === 'Aprovada') {
                if (atividade.observacoes_Analise && 
                    atividade.observacoes_Analise.includes('[CERTIFICADO APROVADO PELO COORDENADOR')) {
                    return { class: 'bg-green-100 text-green-800', text: 'Aprovado' };
                }
                return { class: 'bg-blue-100 text-blue-800', text: 'Em Andamento' };
            }

            if (atividade.status === 'Rejeitada') {
                return { class: 'bg-red-100 text-red-800', text: 'Rejeitado' };
            }

            return { class: 'bg-gray-100 text-gray-800', text: atividade.status || 'Aguardando Avaliação' };
        }

        // Função para exibir as atividades na tabela
        function atualizarTabelaAtividades() {
            const tbody = document.getElementById('tabelaAtividades');
            const mensagemVazia = document.getElementById('mensagemVazia');

            if (minhasAtividades.length === 0) {
                tbody.innerHTML = '';
                mensagemVazia.classList.remove('hidden');
                return;
            }

            mensagemVazia.classList.add('hidden');
            
            tbody.innerHTML = minhasAtividades.map(atividade => {
                const statusDetalhado = getStatusDetalhado(atividade);

                // Formatação das horas
                let horasDisplay = `${atividade.carga_horaria_solicitada}h`;
                if (atividade.carga_horaria_aprovada !== null && atividade.carga_horaria_aprovada !== undefined) {
                    if (atividade.status === 'Aprovada') {
                        horasDisplay = `${atividade.carga_horaria_aprovada}h`;
                        if (atividade.carga_horaria_aprovada != atividade.carga_horaria_solicitada) {
                            horasDisplay += ` (de ${atividade.carga_horaria_solicitada}h)`;
                        }
                    } else if (atividade.status === 'Rejeitada') {
                        horasDisplay = `0h (de ${atividade.carga_horaria_solicitada}h)`;
                    }
                }



                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="font-medium">${atividade.titulo}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="font-medium">${atividade.atividade_titulo || 'N/A'}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${atividade.categoria_nome || 'N/A'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${horasDisplay}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusDetalhado.class}">
                                ${statusDetalhado.text}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="verDetalhesAtividade(${atividade.id})" 
                                    class="text-[#0969DA] hover:text-[#061B53]">
                                Ver Detalhes
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Função para ver o certificado
        function verCertificado(caminho) {
            window.open('/Gerenciamento-ACC/backend/' + caminho, '_blank');
        }

        // Função para atualizar as estatísticas
        function atualizarEstatisticas() {
            // Calcular horas certificadas (aprovadas pelo coordenador)
            const horasValidadas = minhasAtividades
                .filter(a => {
                    // Verificar se foi aprovado pelo coordenador (certificado aprovado)
                    return a.status === 'aprovado' || 
                           (a.observacoes_Analise && 
                            a.observacoes_Analise.includes('[CERTIFICADO APROVADO PELO COORDENADOR')) ||
                           (a.observacoes_avaliacao && 
                            a.observacoes_avaliacao.includes('[CERTIFICADO APROVADO PELO COORDENADOR'));
                })
                .reduce((total, a) => total + (a.carga_horaria_aprovada || 0), 0);

            // Calcular horas pendentes (total necessário - horas certificadas)
            const totalHorasNecessarias = 120; // Total de horas ACC necessárias
            const horasPendentes = Math.max(0, totalHorasNecessarias - horasValidadas);

            document.getElementById('horasValidadas').textContent = horasValidadas;
            document.getElementById('horasPendentes').textContent = horasPendentes;
        }

        // Função para exibir detalhes da atividade
        function verDetalhesAtividade(id) {
            const atividade = minhasAtividades.find(a => a.id === id);
            
            if (!atividade) {
                alert('Atividade não encontrada!');
                return;
            }

            // Preencher os dados no modal
            document.getElementById('detalheTitulo').textContent = atividade.titulo;
            document.getElementById('detalheCategoria').textContent = atividade.categoria_nome || 'N/A';
            document.getElementById('detalheHorasSolicitadas').textContent = atividade.carga_horaria_solicitada + 'h';
            document.getElementById('detalheHorasAprovadas').textContent = 
                atividade.carga_horaria_aprovada ? atividade.carga_horaria_aprovada + 'h' : 'Aguardando avaliação';
            document.getElementById('detalheDataSubmissao').textContent = 
                formatarData(atividade.data_submissao);
            
            document.getElementById('detalhePeriodo').textContent = 
                `${formatarData(atividade.data_inicio)} a ${formatarData(atividade.data_fim)}`;
            
            document.getElementById('detalheDescricao').textContent = atividade.descricao || 'Sem descrição';
            document.getElementById('detalheOrientador').textContent = atividade.orientador_nome || 'Não definido';

            // Status com informação mais detalhada
            const statusElement = document.getElementById('detalheStatus');
            const statusDetalhado = getStatusDetalhado(atividade);
            statusElement.className = `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusDetalhado.class}`;
            statusElement.textContent = statusDetalhado.text;

            // Mostrar/ocultar parecer baseado na existência
            const parecerContainer = document.getElementById('parecerContainer');
            if (atividade.observacoes_Analise) {
                document.getElementById('detalheParecer').textContent = atividade.observacoes_Analise;
                parecerContainer.classList.remove('hidden');
            } else {
                parecerContainer.classList.add('hidden');
            }

            // Documentos anexados
            const docContainer = document.getElementById('documentoDeclaracaoAluno');
            if (atividade.tem_declaracao === true || atividade.tem_declaracao === '1' || atividade.tem_declaracao === 1 || atividade.tem_declaracao === 'true') {
                docContainer.innerHTML = `
                    <div class="flex items-center justify-between p-3 bg-white border rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Declaração da Atividade</p>
                            </div>
                        </div>
                       <button onclick="visualizarDeclaracao('${atividade.declaracao_caminho}')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Visualizar
                        </button>
                    </div>
                `;
            } else {
                docContainer.innerHTML = `<div class="text-center py-4 text-gray-500">Nenhum documento anexado</div>`;
            }

            // Mostrar modal
            document.getElementById('modalDetalhesAtividade').classList.remove('hidden');
        }

      function visualizarDeclaracao(caminho) {
            window.open('/Gerenciamento-ACC/backend/' + caminho, '_blank');
        }


        // Função para formatar data
        function formatarData(data) {
            if (!data) return 'N/A';
            
            const dataObj = new Date(data);
            return dataObj.toLocaleDateString('pt-BR');
        }

        // Função para fechar modal
        function fecharModalDetalhes() {
            document.getElementById('modalDetalhesAtividade').classList.add('hidden');
        }

        // Função para exibir mensagem de erro
        function exibirMensagemErro(mensagem) {
            const tbody = document.getElementById('tabelaAtividades');
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-red-600">
                        <div class="flex flex-col items-center">
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p>${mensagem}</p>
                        </div>
                    </td>
                </tr>
            `;
        }

        document.getElementById('modalDetalhesAtividade').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalDetalhes();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            carregarMinhasAtividades();
        });

        function solicitarCertificado(atividadeId) {
            alert('Solicitação de certificado enviada para a secretaria/coordenador. (Funcionalidade a ser implementada)');
        }
    </script>
</body>
</html>
