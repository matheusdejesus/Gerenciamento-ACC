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
                    <a href="#" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Minhas Matrículas
                    </a>
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Horas Certificadas</h3>
                        <p id="horasValidadas" class="text-3xl font-bold" style="color: #1A7F37">-</p>
                        <p class="text-xs text-gray-500 mt-1">Atividades concluídas e validadas</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Horas Pendentes</h3>
                        <p id="horasPendentes" class="text-3xl font-bold" style="color: #B45309">-</p>
                        <p class="text-xs text-gray-500 mt-1">Aguardando conclusão ou avaliação</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Atividades em Andamento</h3>
                        <p id="atividadesAndamento" class="text-3xl font-bold" style="color: #0969DA">-</p>
                        <p class="text-xs text-gray-500 mt-1">Total de atividades ativas</p>
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
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Categoria</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Horas</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Status</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Ações</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Certificados</th>
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
        if (user.tipo !== 'aluno') {
            AuthClient.logout();
        }
        
        // Atualizar nome do usuário na interface
        if (user && user.nome) {
            document.getElementById('nomeUsuario').textContent = user.nome;
            document.getElementById('nomeUsuarioMain').textContent = user.nome;
        }

        let minhasAtividades = [];

        // Função para carregar atividades
        async function carregarMinhasAtividades() {
            try {
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/minhas_atividades.php');
                const data = await response.json();
                
                if (data.success) {
                    minhasAtividades = data.data || [];
                    atualizarTabelaAtividades();
                    atualizarEstatisticas();
                } else {
                    console.error('Erro ao carregar atividades:', data.error);
                    exibirMensagemErro('Erro ao carregar suas atividades');
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                exibirMensagemErro('Erro de conexão ao carregar atividades');
            }
        }
        
        // Função para obter status mais detalhado
        function getStatusDetalhado(atividade) {
            if (atividade.status === 'Pendente') {
                return { class: 'bg-yellow-100 text-yellow-800', text: 'Aguardando Avaliação' };
            }

            if (atividade.status === 'Aprovada') {
                // Verificar se o certificado foi aprovado pelo coordenador
                if (atividade.observacoes_Analise && 
                    atividade.observacoes_Analise.includes('[CERTIFICADO APROVADO PELO COORDENADOR')) {
                    return { class: 'bg-green-100 text-green-800', text: 'Concluída' };
                }
                return { class: 'bg-blue-100 text-blue-800', text: 'Em Andamento' };
            }

            if (atividade.status === 'Rejeitada') {
                return { class: 'bg-red-100 text-red-800', text: 'Rejeitada' };
            }

            return { class: 'bg-gray-100 text-gray-800', text: atividade.status || 'N/A' };
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
                let horasDisplay = `${atividade.carga_horaria_solicitada}h solicitadas`;
                if (atividade.carga_horaria_aprovada !== null && atividade.carga_horaria_aprovada !== undefined) {
                    if (atividade.status === 'Aprovada') {
                        horasDisplay = `${atividade.carga_horaria_aprovada}h`;
                        if (atividade.carga_horaria_aprovada != atividade.carga_horaria_solicitada) {
                            horasDisplay += ` (de ${atividade.carga_horaria_solicitada}h)`;
                        }
                    } else if (atividade.status === 'Rejeitada') {
                        horasDisplay = `0h aprovadas (de ${atividade.carga_horaria_solicitada}h)`;
                    }
                }

                // Botão de certificado: habilita se certificado_caminho existir
                let certificadoBtn;
                const hoje = new Date();
                let dataFim = atividade.data_fim ? new Date(atividade.data_fim + 'T00:00:00') : null;
                const concluida = atividade.status === 'Aprovada' && dataFim && dataFim <= hoje;

                if (atividade.certificado_caminho) {
                    certificadoBtn = `
                        <button class="bg-green-100 text-green-700 px-3 py-1 rounded hover:bg-green-200 transition"
                            onclick="verCertificado('${atividade.certificado_caminho}')">
                            Ver Certificado
                        </button>
                    `;
                } else if (concluida) {
                    certificadoBtn = `
                        <button class="bg-blue-100 text-blue-700 px-3 py-1 rounded hover:bg-blue-200 transition"
                            onclick="solicitarCertificado(${atividade.id})">
                            Solicitar Certificado
                        </button>
                    `;
                } else {
                    certificadoBtn = `
                        <button class="bg-gray-200 text-gray-400 px-3 py-1 rounded cursor-not-allowed" disabled>
                            Certificados
                        </button>
                    `;
                }

                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${atividade.titulo}
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            ${certificadoBtn}
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
            // Horas certificadas: atividades aprovadas e com certificado aprovado pelo coordenador
            const horasValidadas = minhasAtividades
                .filter(a => a.status === 'Aprovada' && 
                            a.observacoes_Analise && 
                            a.observacoes_Analise.includes('[CERTIFICADO APROVADO PELO COORDENADOR'))
                .reduce((total, a) => total + (a.carga_horaria_aprovada || 0), 0);

            // Horas pendentes: atividades não rejeitadas e ainda sem certificado aprovado pelo coordenador
            const horasPendentes = minhasAtividades
                .filter(a => a.status !== 'Rejeitada' && 
                            (!a.observacoes_Analise || 
                             !a.observacoes_Analise.includes('[CERTIFICADO APROVADO PELO COORDENADOR')))
                .reduce((total, a) => total + (a.carga_horaria_solicitada || 0), 0);

            // Atividades em andamento: aprovadas mas ainda sem aprovação do coordenador
            const atividadesAndamento = minhasAtividades
                .filter(a => a.status === 'Aprovada' && 
                            (!a.observacoes_Analise || 
                             !a.observacoes_Analise.includes('[CERTIFICADO APROVADO PELO COORDENADOR')))
                .length;

            document.getElementById('horasValidadas').textContent = horasValidadas;
            document.getElementById('horasPendentes').textContent = horasPendentes;
            document.getElementById('atividadesAndamento').textContent = atividadesAndamento;
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
            document.getElementById('detalheOrientador').textContent = 
                atividade.orientador_nome || 'Não definido';

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
                    <td colspan="5" class="px-6 py-8 text-center text-red-600">
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
