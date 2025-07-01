<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Orientador - SACC UFOPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wght@0,200..900;1,200..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        .bg-pattern {
            background-color: #0D1117;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23151B23' fill-opacity='0.3'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
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
                    <a href="#" class="block p-3 rounded bg-gray-200 text-[#0969DA] font-medium">
                        Início
                    </a>
                    <a href="configuracoes_orientador.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Configurações
                    </a>
                </nav>
            </aside>
            
            <main class="lg:w-3/4 p-6 rounded-lg" style="background-color: #F6F8FA">
                <div class="mb-8">
                    <h2 class="text-3xl font-extralight mb-2" style="color: #0969DA">
                        Olá, <span id="nomeUsuarioMain">Carregando...</span>
                    </h2>
                    <p class="text-gray-600">Gerencie as solicitações de ACC dos seus orientandos.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Pendentes</h3>
                        <p class="text-3xl font-bold" style="color: #B45309" id="countPendentes">0</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Aprovadas</h3>
                        <p class="text-3xl font-bold" style="color: #1A7F37" id="countAprovadas">0</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Rejeitadas</h3>
                        <p class="text-3xl font-bold" style="color: #DA1A3A" id="countRejeitadas">0</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Total de Horas Aprovadas</h3>
                        <p class="text-3xl font-bold" style="color: #1A7F37" id="totalHorasAprovadas">0</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                    <div class="p-4" style="background-color: #151B23">
                        <h3 class="text-xl font-bold text-white">Atividades Pendentes de Avaliação</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full" id="tabelaAtividadesPendentes">
                            <thead style="background-color: #F6F8FA">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Aluno</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Atividade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Horas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                        <div id="mensagemVaziaPendentes" class="p-8 text-center text-gray-500 hidden">
                            <p class="text-lg">Não há atividades pendentes no momento.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4" style="background-color: #151B23">
                        <h3 class="text-xl font-bold text-white">Atividades Avaliadas Recentemente</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full" id="tabelaAtividadesAvaliadas">
                            <thead style="background-color: #F6F8FA">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Aluno</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Atividade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Horas Sol./Apr.</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Data Avaliação</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                        <div id="mensagemVaziaAvaliadas" class="p-8 text-center text-gray-500 hidden">
                            <p class="text-lg">Nenhuma atividade avaliada ainda.</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div id="modalDetalhes" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-xl font-bold" style="color: #0969DA">Detalhes da Atividade</h3>
                    <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold mb-3" style="color: #0969DA">Dados do Estudante</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="font-medium text-gray-700">Nome:</span>
                                    <span id="detalhesNomeEstudante" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Matrícula:</span>
                                    <span id="detalhesMatricula" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">E-mail:</span>
                                    <span id="detalhesEmailEstudante" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Curso:</span>
                                    <span id="detalhesCurso" class="ml-2"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold mb-3" style="color: #0969DA">Dados da Atividade</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <span class="font-medium text-gray-700">Título:</span>
                                    <span id="detalhesTitulo" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Tipo:</span>
                                    <span id="detalhesTipo" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Data de Início:</span>
                                    <span id="detalhesDataInicio" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Data de Término:</span>
                                    <span id="detalhesDataTermino" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Data de Submissão:</span>
                                    <span id="detalhesDataSubmissao" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Horas Solicitadas:</span>
                                    <span id="detalhesHoras" class="ml-2"></span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <span class="font-medium text-gray-700">Descrição:</span>
                                <p id="detalhesDescricao" class="mt-2 text-gray-600"></p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold mb-3" style="color: #0969DA">Documentos Anexados</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div id="detalhesDocumentos" class="space-y-2">
                            </div>
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
        if (user.tipo !== 'orientador') {
            AuthClient.logout();
        }
        
        // Atualizar nome do usuário na interface
        if (user && user.nome) {
            document.getElementById('nomeUsuario').textContent = user.nome;
            document.getElementById('nomeUsuarioMain').textContent = user.nome;
        }

        let atividadesPendentes = [];
        let atividadesAvaliadas = [];

        // Carregar atividades pendentes usando JWT
        async function carregarAtividadesPendentes() {
            try {
                const response = await AuthClient.fetch('/Gerenciamento-de-ACC/backend/api/routes/atividades_pendentes.php');
                const data = await response.json();
                
                if (data.success) {
                    atividadesPendentes = (data.data || []).map(atividade => ({
                        id: atividade.id,
                        estudante: {
                            nome: atividade.nome_aluno || atividade.aluno_nome,
                            matricula: atividade.aluno_matricula || atividade.matricula || 'N/A',
                            email: atividade.aluno_email || atividade.email || 'N/A',
                            curso: atividade.curso_nome || 'N/A'
                        },
                        titulo: atividade.titulo_atividade || atividade.titulo,
                        descricao: atividade.descricao || '',
                        dataInicio: atividade.data_inicio ? new Date(atividade.data_inicio + 'T00:00:00').toLocaleDateString('pt-BR') : 'N/A',
                        dataTermino: atividade.data_fim ? new Date(atividade.data_fim + 'T00:00:00').toLocaleDateString('pt-BR') : 'N/A',
                        dataSubmissao: new Date(atividade.data_submissao).toLocaleDateString('pt-BR'),
                        horasSolicitadas: atividade.carga_horaria_solicitada,
                        tipo: 'Atividade Complementar',
                        temDeclaracao: atividade.tem_declaracao === true || atividade.tem_declaracao === 1 || atividade.tem_declaracao === '1' || atividade.tem_declaracao === 'true'
                    }));
                    
                    atualizarTabelaAtividades();
                    atualizarEstatisticas();
                } else {
                    console.error('Erro ao carregar atividades:', data.error);
                    exibirMensagemVazia('pendentes', 'Erro ao carregar atividades pendentes');
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                exibirMensagemVazia('pendentes', 'Erro de conexão ao carregar atividades');
            }
        }

        // Carregar atividades avaliadas usando JWT
        async function carregarAtividadesAvaliadas() {
            try {
                const response = await AuthClient.fetch('/Gerenciamento-de-ACC/backend/api/routes/avaliar_atividade.php');
                const data = await response.json();
                
                if (data.success) {
                    atividadesAvaliadas = (data.data || []).map(atividade => ({
                        id: atividade.id,
                        estudante: {
                            nome: atividade.aluno_nome,
                            matricula: atividade.aluno_matricula || 'N/A',
                            curso: atividade.curso_nome || 'N/A',
                            email: atividade.aluno_email || 'N/A'
                        },
                        titulo: atividade.titulo,
                        descricao: atividade.descricao || '',
                        dataInicio: atividade.data_inicio ? new Date(atividade.data_inicio + 'T00:00:00').toLocaleDateString('pt-BR') : 'N/A',
                        dataTermino: atividade.data_fim ? new Date(atividade.data_fim + 'T00:00:00').toLocaleDateString('pt-BR') : 'N/A',
                        dataSubmissao: new Date(atividade.data_submissao).toLocaleDateString('pt-BR'),
                        dataAvaliacao: atividade.data_avaliacao ? new Date(atividade.data_avaliacao).toLocaleDateString('pt-BR') : 'Não avaliado',
                        horasSolicitadas: atividade.carga_horaria_solicitada,
                        horasAprovadas: atividade.carga_horaria_aprovada,
                        status: atividade.status,
                        parecer: atividade.observacoes_Analise,
                        tipo: 'Atividade Complementar',
                        temDeclaracao: atividade.tem_declaracao === true || atividade.tem_declaracao === 1 || atividade.tem_declaracao === '1' || atividade.tem_declaracao === 'true'
                    }));
                    
                    atualizarTabelaAtividadesAvaliadas();
                    atualizarEstatisticas();
                } else {
                    console.error('Erro ao carregar atividades avaliadas:', data.error);
                    exibirMensagemVazia('avaliadas', 'Erro ao carregar atividades avaliadas');
                }
            } catch (error) {
                console.error('Erro na requisição de atividades avaliadas:', error);
                exibirMensagemVazia('avaliadas', 'Erro de conexão ao carregar atividades');
            }
        }

        // Atualizar tabela de atividades pendentes
        function atualizarTabelaAtividades() {
            const tbody = document.querySelector('#tabelaAtividadesPendentes tbody');
            const mensagemVazia = document.getElementById('mensagemVaziaPendentes');
            
            if (!tbody) {
                console.error('Tabela de atividades pendentes não encontrada');
                return;
            }
            
            if (atividadesPendentes.length === 0) {
                tbody.innerHTML = '';
                mensagemVazia.classList.remove('hidden');
                return;
            }
            
            mensagemVazia.classList.add('hidden');
            tbody.innerHTML = '';
            
            atividadesPendentes.forEach(atividade => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${atividade.estudante.nome}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${atividade.titulo}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${atividade.horasSolicitadas}h
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${atividade.dataSubmissao}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="avaliarAtividade(${atividade.id})" 
                                class="text-white px-3 py-1 rounded mr-2 hover:opacity-80" 
                                style="background-color: #0969DA">
                            Avaliar
                        </button>
                        <button onclick="verDetalhes(${atividade.id})" 
                                class="text-[#0969DA] hover:text-[#061B53]">
                            Ver Detalhes
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Atualizar tabela de atividades avaliadas
        function atualizarTabelaAtividadesAvaliadas() {
            const tbody = document.querySelector('#tabelaAtividadesAvaliadas tbody');
            const mensagemVazia = document.getElementById('mensagemVaziaAvaliadas');
            
            if (!tbody) {
                console.error('Tabela de atividades avaliadas não encontrada');
                return;
            }
            
            if (atividadesAvaliadas.length === 0) {
                tbody.innerHTML = '';
                mensagemVazia.classList.remove('hidden');
                return;
            }
            
            mensagemVazia.classList.add('hidden');
            tbody.innerHTML = '';
            
            atividadesAvaliadas.forEach(atividade => {
                const statusClass = atividade.status === 'Aprovada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                
                // Opção de enviar certificado apenas para atividades aprovadas
                const opcaoCertificado = atividade.status === 'Aprovada' ? `
                    <button onclick="enviarCertificado(${atividade.id})" 
                            class="text-purple-600 hover:text-purple-900 disabled:opacity-50 disabled:cursor-not-allowed" 
                            disabled
                            title="Funcionalidade em desenvolvimento">
                        Enviar Certificado
                    </button>
                ` : '';
                
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${atividade.estudante.nome}</div>
                        <div class="text-sm text-gray-500">${atividade.estudante.matricula}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">${atividade.titulo}</div>
                        <div class="text-sm text-gray-500">${atividade.tipo}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${atividade.horasSolicitadas}h / ${atividade.horasAprovadas || 0}h
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${atividade.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${atividade.dataAvaliacao}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                        <div class="flex flex-col items-start space-y-2">
                            <button onclick="verDetalhes(${atividade.id})" class="text-blue-600 hover:text-blue-900">
                                Ver Detalhes
                            </button>
                            ${opcaoCertificado}
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Exibir mensagem vazia
        function exibirMensagemVazia(tipo, mensagem) {
            const tbody = document.querySelector(`#tabelaAtividades${tipo === 'pendentes' ? 'Pendentes' : 'Avaliadas'} tbody`);
            if (tbody) {
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
        }

        //Atualizar estatísticas
        function atualizarEstatisticas() {
            document.getElementById('countPendentes').textContent = atividadesPendentes.length;
            
            const aprovadas = atividadesAvaliadas.filter(a => a.status === 'Aprovada');
            const rejeitadas = atividadesAvaliadas.filter(a => a.status === 'Rejeitada');
            const totalHoras = aprovadas.reduce((total, a) => total + (a.horasAprovadas || 0), 0);
            
            document.getElementById('countAprovadas').textContent = aprovadas.length;
            document.getElementById('countRejeitadas').textContent = rejeitadas.length;
            document.getElementById('totalHorasAprovadas').textContent = totalHoras;
        }

        // Carregar todos os dados
        async function carregarTodosOsDados() {
            console.log('Carregando todos os dados...');
            try {
                await Promise.all([
                    carregarAtividadesPendentes(),
                    carregarAtividadesAvaliadas()
                ]);
                console.log('Dados carregados com sucesso');
            } catch (error) {
                console.error('Erro ao carregar dados:', error);
            }
        }

        // Avaliar atividade
        function avaliarAtividade(id) {
            console.log('Avaliar atividade ID:', id);
            const atividade = atividadesPendentes.find(a => a.id === id);
            if (!atividade) {
                alert('Atividade não encontrada');
                return;
            }

            // Remover qualquer modal existente
            const modalExistente = document.querySelector('.modal-avaliacao');
            if (modalExistente) {
                modalExistente.remove();
            }

            // Criar modal de avaliação
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 modal-avaliacao';
            modal.innerHTML = `
                <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold" style="color: #0969DA">Avaliar Atividade</h3>
                        <button onclick="fecharModalAvaliacao()" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mb-4 bg-gray-50 p-4 rounded-lg">
                        <p><strong>Estudante:</strong> ${atividade.estudante.nome}</p>
                        <p><strong>Atividade:</strong> ${atividade.titulo}</p>
                        <p><strong>Horas Solicitadas:</strong> ${atividade.horasSolicitadas}h</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Horas Aprovadas</label>
                        <input type="number" id="horasAprovadasModal" min="0" max="${atividade.horasSolicitadas}" 
                               value="${atividade.horasSolicitadas}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Parecer/Observações *</label>
                        <textarea id="parecerModal" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Digite seu parecer sobre a atividade..."></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button onclick="fecharModalAvaliacao()" 
                                class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancelar
                        </button>
                        <button onclick="rejeitarAtividadeModal(${id})" 
                                class="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700">
                            Rejeitar
                        </button>
                        <button onclick="aprovarAtividadeModal(${id})" 
                                class="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700">
                            Aprovar
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        }

        //Fechar modal de avaliação
        function fecharModalAvaliacao() {
            const modal = document.querySelector('.modal-avaliacao');
            if (modal) {
                modal.remove();
            }
        }

        //Aprovar atividade
        async function aprovarAtividadeModal(id) {
            const modal = document.querySelector('.modal-avaliacao');
            if (!modal) return;

            const horas = parseInt(modal.querySelector('#horasAprovadasModal').value);
            const parecer = modal.querySelector('#parecerModal').value.trim();
            
            if (!parecer) {
                alert('Por favor, digite um parecer para a atividade.');
                return;
            }

            try {
                const response = await AuthClient.fetch('/Gerenciamento-de-ACC/backend/api/routes/avaliar_atividade.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        atividade_id: id,
                        status: 'Aprovada',
                        carga_horaria_aprovada: horas,
                        observacoes_analise: parecer
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(`✅ Atividade aprovada com sucesso! ${horas} horas aprovadas.`);
                    fecharModalAvaliacao();
                    await carregarTodosOsDados();
                } else {
                    alert('❌ Erro: ' + (data.error || 'Erro desconhecido'));
                }
                
            } catch (error) {
                console.error('Erro ao aprovar atividade:', error);
                alert('❌ Erro ao avaliar atividade. Tente novamente.');
            }
        }

        //Rejeitar atividade
        async function rejeitarAtividadeModal(id) {
            const modal = document.querySelector('.modal-avaliacao');
            if (!modal) return;

            const parecer = modal.querySelector('#parecerModal').value.trim();
            
            if (!parecer) {
                alert('Por favor, digite um parecer explicando o motivo da rejeição.');
                return;
            }
            
            if (confirm('Confirma a rejeição desta atividade?')) {
                try {
                    const response = await AuthClient.fetch('/Gerenciamento-de-ACC/backend/api/routes/avaliar_atividade.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            atividade_id: id,
                            status: 'Rejeitada',
                            carga_horaria_aprovada: 0,
                            observacoes_analise: parecer
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('❌ Atividade rejeitada.');
                        fecharModalAvaliacao();
                        await carregarTodosOsDados();
                    } else {
                        alert('❌ Erro: ' + (data.error || 'Erro desconhecido'));
                    }
                    
                } catch (error) {
                    console.error('Erro ao rejeitar atividade:', error);
                    alert('❌ Erro ao avaliar atividade. Tente novamente.');
                }
            }
        }

        //Ver detalhes
        function verDetalhes(id) {
            let atividade = atividadesPendentes.find(a => a.id === id);
            if (!atividade) {
                atividade = atividadesAvaliadas.find(a => a.id === id);
            }
            
            if (!atividade) {
                alert('Atividade não encontrada');
                return;
            }
            
            // Preencher modal de detalhes
            document.getElementById('detalhesNomeEstudante').textContent = atividade.estudante.nome;
            document.getElementById('detalhesMatricula').textContent = atividade.estudante.matricula || 'N/A';
            document.getElementById('detalhesEmailEstudante').textContent = atividade.estudante.email || 'N/A';
            document.getElementById('detalhesCurso').textContent = atividade.estudante.curso || 'N/A';
            document.getElementById('detalhesTitulo').textContent = atividade.titulo;
            document.getElementById('detalhesTipo').textContent = atividade.tipo;
            document.getElementById('detalhesDataInicio').textContent = atividade.dataInicio;
            document.getElementById('detalhesDataTermino').textContent = atividade.dataTermino;
            document.getElementById('detalhesDataSubmissao').textContent = atividade.dataSubmissao;
            document.getElementById('detalhesHoras').textContent = atividade.horasSolicitadas + 'h';
            document.getElementById('detalhesDescricao').textContent = atividade.descricao || 'Nenhuma descrição fornecida';
            
            // Preencher documentos
            const containerDocumentos = document.getElementById('detalhesDocumentos');
            containerDocumentos.innerHTML = '';

            if (atividade.temDeclaracao) {
                containerDocumentos.innerHTML = `
                    <div class="flex items-center justify-between p-3 bg-white border rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Declaração da Atividade</p>
                                <p class="text-sm text-gray-500">Documento enviado pelo aluno</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="visualizarDeclaracao(${id})" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Visualizar
                            </button>
                            <button onclick="baixarDeclaracao(${id})" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                Baixar
                            </button>
                        </div>
                    </div>
                `;
            } else {
                containerDocumentos.innerHTML = `
                    <div class="text-center py-4 text-gray-500">
                        <p>Nenhum documento anexado</p>
                    </div>
                `;
            }
            
            document.getElementById('modalDetalhes').classList.remove('hidden');
        }

        //Fechar modal de detalhes
        function fecharModal() {
            document.getElementById('modalDetalhes').classList.add('hidden');
        }

        //Visualizar declaração em nova aba
        function visualizarDeclaracao(id) {
            window.open(`/Gerenciamento-de-ACC/backend/api/routes/avaliar_atividade.php?download=declaracao&id=${id}`, '_blank');
        }

        //Visualizar/baixar declaração
        function baixarDeclaracao(id) {
            window.open(`/Gerenciamento-de-ACC/backend/api/routes/avaliar_atividade.php?download=declaracao&id=${id}`, '_blank');
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM carregado, iniciando carregamento de dados...');
            carregarTodosOsDados();
        });
    </script>
</body>
</html>