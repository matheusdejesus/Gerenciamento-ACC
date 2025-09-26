<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Coordenador - SACC UFOPA</title>
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
                    <a href="configuracoes_coordenador.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Configurações da Conta
                    </a>
                </nav>
            </aside>
            <main class="lg:w-3/4 p-6 rounded-lg" style="background-color: #F6F8FA">
                <div class="mb-8">
                    <h2 class="text-3xl font-extralight mb-2" style="color: #0969DA">
                        Dashboard do Coordenador
                    </h2>
                    <p class="text-gray-600">Gerencie certificados e contabilize horas de ACC dos estudantes.</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                    <div class="p-4" style="background-color: #151B23">
                        <h3 class="text-white font-semibold">Certificados Pendentes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aluno</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curso</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atividade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaCertificadosPendentes" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="8" class="text-center text-gray-500 py-8">
                                        Carregando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4" style="background-color: #151B23">
                        <h3 class="text-white font-semibold">Histórico de Certificados Processados</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aluno</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curso</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atividade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaCertificadosProcessados" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="8" class="text-center text-gray-500 py-8">
                                        Carregando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div id="modalDetalhesCertificado" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-xl font-bold" style="color: #0969DA">Detalhes do Certificado</h3>
                    <button onclick="fecharModalDetalhes()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-700 mb-3">Informações do Aluno</h4>
                            <div class="space-y-2">
                                <p><strong>Nome:</strong> <span id="detalheAlunoNome">-</span></p>
                                <p><strong>Matrícula:</strong> <span id="detalheAlunoMatricula">-</span></p>
                                <p><strong>Curso:</strong> <span id="detalheAlunoCurso">-</span></p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-700 mb-3">Informações da Atividade</h4>
                            <div class="space-y-2">
                                <p><strong>Título:</strong> <span id="detalheAtividadeTitulo">-</span></p>
                                <p><strong>Horas Aprovadas:</strong> <span id="detalheAtividadeHoras">-</span></p>
                                <p><strong>Data de Envio:</strong> <span id="detalheDataEnvio">-</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 mb-3">Certificado</h4>
                        <div id="detalheCertificado" class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Certificado da Atividade</p>
                                        <p class="text-sm text-gray-500">Documento enviado pelo aluno</p>
                                    </div>
                                </div>
                                <button id="btnVisualizarCertificado" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Visualizar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Campo de observações - visível apenas para certificados pendentes -->
                    <div id="campoObservacoes" class="mb-6" style="display: none;">
                        <h4 class="font-semibold text-gray-700 mb-3">Observações</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <textarea 
                                id="observacoesCertificado" 
                                rows="3" 
                                class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Digite suas observações sobre o certificado..."
                            ></textarea>
                        </div>
                    </div>

                    <div id="botoesAcao" class="flex justify-end gap-3">
                        <button 
                            onclick="fecharModalDetalhes()" 
                            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition duration-200"
                        >
                            Cancelar
                        </button>
                        <button 
                            id="btnRejeitar"
                            onclick="rejeitarCertificado()" 
                            class="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700 transition duration-200"
                        >
                            Rejeitar
                        </button>
                        <button 
                            id="btnAprovar"
                            onclick="aprovarCertificado()" 
                            class="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700 transition duration-200"
                        >
                            Aprovar
                        </button>
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
        if (user && user.tipo !== 'coordenador') {
            AuthClient.logout();
        }
        
        if (user && user.nome) {
            document.getElementById('nomeUsuario').textContent = user.nome;
        }

        let certificadoAtual = null;

        // Carregar dados iniciais
        document.addEventListener('DOMContentLoaded', function() {
            carregarCertificadosPendentes();
            carregarCertificadosProcessados();
        });

        // Carregar certificados pendentes
        async function carregarCertificadosPendentes() {
            console.log('=== DEBUG: Iniciando carregamento de certificados pendentes ===');
            try {
                console.log('Fazendo requisição para: ../../backend/api/routes/avaliar_atividade.php?acao=certificados_pendentes');
                const response = await AuthClient.fetch('../../backend/api/routes/avaliar_atividade.php?acao=certificados_pendentes');
                console.log('Resposta completa da API:', response);
                
                const data = response.data;
                console.log('Dados extraídos da resposta:', data);
                
                if (data.success) {
                    const certificados = data.data || [];
                    console.log('Certificados encontrados:', certificados);
                    console.log('Total de certificados:', certificados.length);
                    
                    if (certificados.length === 0) {
                        console.log('Nenhum certificado pendente encontrado - exibindo mensagem');
                        document.getElementById('tabelaCertificadosPendentes').innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center text-gray-500 py-8">
                                    Nenhum certificado pendente
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    console.log('Processando certificados para exibição...');
                    document.getElementById('tabelaCertificadosPendentes').innerHTML = certificados.map((cert, index) => {
                        console.log(`Processando certificado ${index + 1}:`, cert);
                        const titulo = cert.titulo || 'N/A';
                        const atividade = cert.atividade_nome || 'N/A';
                        const categoria = cert.categoria_nome || 'N/A';
                        const certificadoPath = `../../backend/${cert.certificado_caminho}`;
                        
                        return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${cert.aluno_nome || 'N/A'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${cert.curso_nome || 'N/A'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${titulo}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${atividade}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${categoria}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${cert.horas_aprovadas || cert.carga_horaria_aprovada || 0}h
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${cert.data_envio ? new Date(cert.data_envio).toLocaleDateString('pt-BR') : 'N/A'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="verDetalhesCertificado(${JSON.stringify(cert).replace(/"/g, '&quot;')})" 
                                        class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition duration-200">
                                    Ver Detalhes
                                </button>
                            </td>
                        </tr>
                        `;
                    }).join('');
                    console.log('Certificados exibidos com sucesso na tabela');
                } else {
                    console.error('API retornou erro:', data.error);
                    console.error('Dados completos da resposta:', data);
                    document.getElementById('tabelaCertificadosPendentes').innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center text-red-500 py-8">
                                Erro ao carregar certificados pendentes: ${data.error || 'Erro desconhecido'}
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Erro na requisição de certificados pendentes:', error);
                console.error('Stack trace:', error.stack);
                document.getElementById('tabelaCertificadosPendentes').innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-red-500 py-8">
                            Erro de conexão: ${error.message}
                        </td>
                    </tr>
                `;
            }
        }

        // Carregar certificados processados
        async function carregarCertificadosProcessados() {
            console.log('=== DEBUG: Iniciando carregamento de certificados processados ===');
            try {
                console.log('Fazendo requisição para: /Gerenciamento-ACC/backend/api/routes/avaliar_atividade.php?acao=certificados_processados');
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/avaliar_atividade.php?acao=certificados_processados');
                console.log('Resposta completa da API (processados):', response);
                
                const data = response.data;
                console.log('Dados extraídos da resposta (processados):', data);
                
                if (data.success) {
                    const certificados = data.data || [];
                    console.log('Certificados processados encontrados:', certificados);
                    console.log('Total de certificados processados:', certificados.length);
                    
                    if (certificados.length === 0) {
                        console.log('Nenhum certificado processado encontrado - exibindo mensagem');
                        document.getElementById('tabelaCertificadosProcessados').innerHTML = `
                            <tr>
                            <td colspan="8" class="text-center text-gray-500 py-8">
                                Nenhum certificado processado
                            </td>
                        </tr>
                        `;
                        return;
                    }
                    
                    document.getElementById('tabelaCertificadosProcessados').innerHTML = certificados.map(cert => {
                        const titulo = cert.titulo || 'N/A';
                        const atividade = cert.atividade_nome || cert.titulo || 'N/A';
                        const certificadoPath = `../../backend/${cert.certificado_processado || cert.certificado_caminho}`;
                        const statusFormatted = cert.status === 'Aprovado' ? 'Aprovado' : cert.status === 'Rejeitado' ? 'Rejeitado' : cert.status;
                        
                        return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${cert.aluno_nome || 'N/A'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${cert.curso_nome || 'N/A'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${titulo}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${atividade}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${cert.horas_contabilizadas || cert.horas_aprovadas || 0}h
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusFormatted === 'Aprovado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${statusFormatted}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${cert.data_aprovacao ? new Date(cert.data_aprovacao).toLocaleDateString('pt-BR') : 'N/A'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="verDetalhesProcessado(${JSON.stringify(cert).replace(/"/g, '&quot;')})" 
                                        class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition duration-200">
                                    Ver Detalhes
                                </button>
                            </td>
                        </tr>
                        `;
                    }).join('');
                } else {
                    console.error('Erro ao carregar certificados processados:', data.error);
                    document.getElementById('tabelaCertificadosProcessados').innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center text-red-500 py-8">
                                Erro ao carregar certificados processados
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                document.getElementById('tabelaCertificadosProcessados').innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-red-500 py-8">
                            Erro de conexão
                        </td>
                    </tr>
                `;
            }
        }

        // Função para ver detalhes do certificado PENDENTE (com observações)
        function verDetalhesCertificado(certificado) {
            console.log('=== DEBUG: Abrindo detalhes do certificado pendente ===');
            console.log('Certificado completo:', certificado);
            console.log('ID do certificado:', certificado.id);
            console.log('Tipo do certificado:', certificado.tipo);
            console.log('Título:', certificado.titulo);
            console.log('Status:', certificado.status);
            
            certificadoAtual = certificado;
            
            // Preencher informações do aluno
            document.getElementById('detalheAlunoNome').textContent = certificado.aluno_nome || 'N/A';
            document.getElementById('detalheAlunoMatricula').textContent = certificado.aluno_matricula || 'N/A';
            document.getElementById('detalheAlunoCurso').textContent = certificado.curso_nome || 'N/A';
            
            // Preencher informações da atividade
            const tituloAtividade = certificado.atividade_nome || certificado.categoria_nome || certificado.titulo || 'N/A';
            document.getElementById('detalheAtividadeTitulo').textContent = tituloAtividade;
            document.getElementById('detalheAtividadeHoras').textContent = (certificado.horas_aprovadas || certificado.carga_horaria_aprovada || 0) + 'h';
            document.getElementById('detalheDataEnvio').textContent = certificado.data_envio ? 
                new Date(certificado.data_envio).toLocaleDateString('pt-BR') : 'N/A';
            
            // Configurar botão de visualizar certificado
            const btnVisualizarCertificado = document.getElementById('btnVisualizarCertificado');
            const caminhoCertificado = certificado.certificado_processado || certificado.certificado_caminho;
            if (caminhoCertificado) {
                btnVisualizarCertificado.onclick = () => {
                    const certificadoPath = `/Gerenciamento-ACC/backend/${caminhoCertificado}`;
                    window.open(certificadoPath, '_blank');
                };
            }
            
            // MOSTRAR campo de observações para certificados pendentes
            document.getElementById('campoObservacoes').style.display = 'block';
            document.getElementById('observacoesCertificado').value = '';
            
            // MOSTRAR botões de ação (Aprovar/Rejeitar)
            document.getElementById('botoesAcao').innerHTML = `
                <button 
                    onclick="fecharModalDetalhes()" 
                    class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition duration-200"
                >
                    Cancelar
                </button>
                <button 
                    onclick="rejeitarCertificado()" 
                    class="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700 transition duration-200"
                >
                    Rejeitar
                </button>
                <button 
                    onclick="aprovarCertificado()" 
                    class="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700 transition duration-200"
                >
                    Aprovar
                </button>
            `;
            
            // Mostrar modal
            document.getElementById('modalDetalhesCertificado').classList.remove('hidden');
        }

        // Função para fechar modal de detalhes
        function fecharModalDetalhes() {
            document.getElementById('modalDetalhesCertificado').classList.add('hidden');
            certificadoAtual = null;
        }

        // Função para aprovar certificado
        async function aprovarCertificado() {
            if (!certificadoAtual) {
                alert('Nenhum certificado selecionado');
                return;
            }

            console.log('=== DEBUG: Aprovando certificado ===');
            console.log('Certificado atual:', certificadoAtual);
            console.log('ID:', certificadoAtual.id);
            console.log('Tipo:', certificadoAtual.tipo);

            const observacoes = document.getElementById('observacoesCertificado').value || '';
            
            if (!confirm('Tem certeza que deseja aprovar este certificado?')) {
                return;
            }

            try {
                console.log('Enviando aprovação para certificado ID:', certificadoAtual.id);
                
                // Incluir o tipo da atividade nos dados enviados
                const formData = `acao=aprovar_certificado&atividade_id=${certificadoAtual.id}&tipo=${certificadoAtual.tipo || ''}${observacoes ? '&observacoes=' + encodeURIComponent(observacoes) : ''}`;
                
                console.log('Dados a serem enviados:', formData);

                // Corrigir o caminho do endpoint - usar caminho absoluto
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/avaliar_atividade.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData
                });

                console.log('Response status:', response.status);
                
                const result = await response.json();
                console.log('Response data:', result);

                if (result.success) {
                    alert('✅ Certificado aprovado com sucesso!');
                    fecharModalDetalhes();
                    carregarCertificadosPendentes();
                    carregarCertificadosProcessados();
                } else {
                    alert('❌ Erro ao aprovar certificado: ' + (result.error || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro completo:', error);
                console.error('Stack trace:', error.stack);
                
                // Melhorar mensagem de erro baseada no tipo
                let mensagemErro = 'Erro ao aprovar certificado';
                if (error.message.includes('fetch')) {
                    mensagemErro = 'Erro de conexão com o servidor. Verifique sua conexão.';
                } else if (error.message.includes('JSON')) {
                    mensagemErro = 'Erro na resposta do servidor. Tente novamente.';
                } else {
                    mensagemErro = error.message;
                }
                
                alert('❌ ' + mensagemErro);
            }
        }

        // Função para rejeitar certificado
        async function rejeitarCertificado() {
            if (!certificadoAtual) {
                alert('Nenhum certificado selecionado');
                return;
            }

            console.log('=== DEBUG: Rejeitando certificado ===');
            console.log('Certificado atual:', certificadoAtual);
            console.log('ID:', certificadoAtual.id);
            console.log('Tipo:', certificadoAtual.tipo);

            const observacoes = document.getElementById('observacoesCertificado').value;
            if (!observacoes.trim()) {
                alert('Por favor, informe o motivo da rejeição.');
                return;
            }
            
            if (!confirm('Tem certeza que deseja rejeitar este certificado?')) {
                return;
            }

            try {
                console.log('Enviando rejeição para certificado ID:', certificadoAtual.id);
                
                // Incluir o tipo da atividade nos dados enviados
                const formData = `acao=rejeitar_certificado&atividade_id=${certificadoAtual.id}&tipo=${certificadoAtual.tipo || ''}&observacoes=${encodeURIComponent(observacoes)}`;
                
                console.log('Dados a serem enviados:', formData);

                // Corrigir o caminho do endpoint - usar caminho absoluto
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/avaliar_atividade.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData
                });

                console.log('Response status:', response.status);
                
                const result = await response.json();
                console.log('Response data:', result);

                if (result.success) {
                    alert('✅ Certificado rejeitado com sucesso!');
                    fecharModalDetalhes();
                    carregarCertificadosPendentes();
                    carregarCertificadosProcessados();
                } else {
                    alert('❌ Erro ao rejeitar certificado: ' + (result.error || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro completo:', error);
                console.error('Stack trace:', error.stack);
                
                // Melhorar mensagem de erro baseada no tipo
                let mensagemErro = 'Erro ao rejeitar certificado';
                if (error.message.includes('fetch')) {
                    mensagemErro = 'Erro de conexão com o servidor. Verifique sua conexão.';
                } else if (error.message.includes('JSON')) {
                    mensagemErro = 'Erro na resposta do servidor. Tente novamente.';
                } else {
                    mensagemErro = error.message;
                }
                
                alert('❌ ' + mensagemErro);
            }
        }

        // Função para ver detalhes de certificado PROCESSADO (sem observações)
        function verDetalhesProcessado(certificado) {
            console.log('Abrindo detalhes do certificado processado:', certificado);
            
            // Preencher informações do aluno
            document.getElementById('detalheAlunoNome').textContent = certificado.aluno_nome || 'N/A';
            document.getElementById('detalheAlunoMatricula').textContent = certificado.aluno_matricula || 'N/A';
            document.getElementById('detalheAlunoCurso').textContent = certificado.curso_nome || 'N/A';
            
            // Preencher informações da atividade
            const tituloAtividade = certificado.atividade_nome || certificado.titulo || 'N/A';
            document.getElementById('detalheAtividadeTitulo').textContent = tituloAtividade;
            document.getElementById('detalheAtividadeHoras').textContent = (certificado.horas_contabilizadas || certificado.horas_aprovadas || 0) + 'h';
            document.getElementById('detalheDataEnvio').textContent = certificado.data_aprovacao ? 
                new Date(certificado.data_aprovacao).toLocaleDateString('pt-BR') : 'N/A';
            
            // Configurar botão de visualizar certificado
            const btnVisualizarCertificado = document.getElementById('btnVisualizarCertificado');
            const caminhoCertificado = certificado.certificado_processado || certificado.certificado_caminho;
            if (caminhoCertificado) {
                btnVisualizarCertificado.onclick = () => {
                    const certificadoPath = `/Gerenciamento-ACC/backend/${caminhoCertificado}`;
                    window.open(certificadoPath, '_blank');
                };
            }
            
            // OCULTAR campo de observações para certificados processados
            document.getElementById('campoObservacoes').style.display = 'none';
            
            // OCULTAR botões de ação para certificados processados (apenas botão Fechar)
            document.getElementById('botoesAcao').innerHTML = `
                <button 
                    onclick="fecharModalDetalhes()" 
                    class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition duration-200"
                >
                    Fechar
                </button>
            `;
            
            // Mostrar modal
            document.getElementById('modalDetalhesCertificado').classList.remove('hidden');
        }

        // Fechar modal ao clicar fora dele
        document.getElementById('modalDetalhesCertificado').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalDetalhes();
            }
        });
    </script>
</body>
</html>
