<?php
session_start();
if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'aluno') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Aluno - ACC Discente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wght@0,200..900;1,200..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="bg-pattern font-montserrat min-h-screen flex flex-col">
    <nav class="bg-white shadow-lg fixed top-0 w-full z-50" style="background-color: #151B23">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-regular text-white">SACC</span>
                </div>
                <div class="flex items-center">
                    <a href="login.php?logout=1" class="text-white hover:text-gray-200">Logout</a>
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
                        Olá, <?= htmlspecialchars($_SESSION['usuario']['nome']) ?>
                    </h2>
                    <p class="text-gray-600">Aqui estão suas Atividades ACC.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Total de Horas Validadas</h3>
                        <p class="text-3xl font-bold" style="color: #1A7F37">0</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Horas Pendentes</h3>
                        <p class="text-3xl font-bold" style="color: #1A7F37">0</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Atividades em Andamento</h3>
                        <p class="text-3xl font-bold" style="color: #1A7F37">2</p>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Atividades</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Atividade 1</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Em Andamento
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="verDetalhesAtividade(1)" class="text-[#0969DA] hover:text-[#061B53]">Ver Detalhes</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Atividade 2</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Concluído
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="verDetalhesAtividade(2)" class="text-blue-600 hover:text-blue-900">Ver Detalhes</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                                    <span class="font-medium text-gray-700">Tipo:</span>
                                    <span id="detalheTipo" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Status:</span>
                                    <span id="detalheStatus" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Horas Solicitadas:</span>
                                    <span id="detalheHoras" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Data de Submissão:</span>
                                    <span id="detalheDataSubmissao" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Data de Avaliação:</span>
                                    <span id="detalheDataAvaliacao" class="ml-2"></span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <span class="font-medium text-gray-700">Descrição:</span>
                                <p id="detalheDescricao" class="mt-2 text-gray-600"></p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold mb-3" style="color: #0969DA">Status da Avaliação</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="mb-4">
                                <span class="font-medium text-gray-700">Orientador Responsável:</span>
                                <span id="detalheOrientador" class="ml-2"></span>
                            </div>
                            <div id="parecerContainer" class="mb-4 hidden">
                                <span class="font-medium text-gray-700">Parecer do Orientador:</span>
                                <p id="detalheParecer" class="mt-2 p-3 bg-blue-50 rounded text-gray-700 italic"></p>
                            </div>
                            <div id="horasAprovadasContainer" class="mb-4 hidden">
                                <span class="font-medium text-gray-700">Horas Aprovadas:</span>
                                <span id="detalheHorasAprovadas" class="ml-2 font-bold text-green-600"></span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold mb-3" style="color: #0969DA">Documentos Anexados</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div id="detalheDocumentos" class="space-y-2">
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold mb-3" style="color: #0969DA">Histórico</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div id="detalheHistorico" class="space-y-3">
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

    <script>
        // Dados simulados das atividades do aluno
        const minhasAtividades = [
            {
                id: 1,
                titulo: "Participação em Workshop de Programação",
                tipo: "Evento Científico",
                status: "Em Andamento",
                statusCor: "bg-yellow-100 text-yellow-800",
                descricao: "Participação no Workshop de Programação Web realizado pela UFOPA, abordando tecnologias modernas como React, Node.js e bancos de dados NoSQL.",
                horasSolicitadas: 20,
                horasAprovadas: null,
                dataSubmissao: "28/10/2024",
                dataAvaliacao: null,
                orientador: "Prof. Dr. João Silva",
                parecer: null,
                documentos: [
                    { nome: "inscricao_workshop.pdf", tipo: "Comprovante de Inscrição", tamanho: "1.2 MB" },
                    { nome: "programacao_workshop.pdf", tipo: "Programação do Evento", tamanho: "800 KB" }
                ],
                historico: [
                    { data: "28/10/2024", acao: "Atividade submetida para avaliação", usuario: "Você" },
                    { data: "29/10/2024", acao: "Atividade encaminhada para o orientador", usuario: "Sistema" }
                ]
            },
            {
                id: 2,
                titulo: "Curso de Extensão em Inteligência Artificial",
                tipo: "Curso",
                status: "Aprovado",
                statusCor: "bg-green-100 text-green-800",
                descricao: "Curso de extensão sobre fundamentos de Inteligência Artificial e Machine Learning, com carga horária de 40 horas, oferecido pelo Instituto de Computação.",
                horasSolicitadas: 40,
                horasAprovadas: 35,
                dataSubmissao: "15/09/2024",
                dataAvaliacao: "22/09/2024",
                orientador: "Prof. Dra. Maria Santos",
                parecer: "Atividade muito relevante para a formação acadêmica. Certificado válido e conteúdo alinhado com o curso. Aprovadas 35 horas conforme regulamento interno.",
                documentos: [
                    { nome: "certificado_ia.pdf", tipo: "Certificado", tamanho: "2.1 MB" },
                    { nome: "conteudo_curso.pdf", tipo: "Conteúdo Programático", tamanho: "1.5 MB" }
                ],
                historico: [
                    { data: "15/09/2024", acao: "Atividade submetida para avaliação", usuario: "Você" },
                    { data: "16/09/2024", acao: "Atividade encaminhada para o orientador", usuario: "Sistema" },
                    { data: "22/09/2024", acao: "Atividade aprovada com 35 horas", usuario: "Prof. Dra. Maria Santos" }
                ]
            }
        ];

        function verDetalhesAtividade(id) {
            const atividade = minhasAtividades.find(at => at.id === id);
            
            if (!atividade) {
                alert('Atividade não encontrada!');
                return;
            }
            
            // Preencher os dados no modal
            document.getElementById('detalheTitulo').textContent = atividade.titulo;
            document.getElementById('detalheTipo').textContent = atividade.tipo;
            document.getElementById('detalheHoras').textContent = atividade.horasSolicitadas + ' horas';
            document.getElementById('detalheDataSubmissao').textContent = atividade.dataSubmissao;
            document.getElementById('detalheDataAvaliacao').textContent = atividade.dataAvaliacao || 'Aguardando avaliação';
            document.getElementById('detalheDescricao').textContent = atividade.descricao;
            document.getElementById('detalheOrientador').textContent = atividade.orientador;
            
            // Status com cor
            const statusElement = document.getElementById('detalheStatus');
            statusElement.innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${atividade.statusCor}">${atividade.status}</span>`;
            
            // Parecer (se houver)
            const parecerContainer = document.getElementById('parecerContainer');
            if (atividade.parecer) {
                document.getElementById('detalheParecer').textContent = atividade.parecer;
                parecerContainer.classList.remove('hidden');
            } else {
                parecerContainer.classList.add('hidden');
            }
            
            // Horas aprovadas (se houver)
            const horasContainer = document.getElementById('horasAprovadasContainer');
            if (atividade.horasAprovadas) {
                document.getElementById('detalheHorasAprovadas').textContent = atividade.horasAprovadas + ' horas';
                horasContainer.classList.remove('hidden');
            } else {
                horasContainer.classList.add('hidden');
            }
            
            // Preencher documentos
            const containerDocumentos = document.getElementById('detalheDocumentos');
            containerDocumentos.innerHTML = '';
            
            atividade.documentos.forEach(doc => {
                const docElement = document.createElement('div');
                docElement.className = 'flex items-center justify-between p-3 bg-white rounded border';
                docElement.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">${doc.nome}</p>
                            <p class="text-sm text-gray-500">${doc.tipo} • ${doc.tamanho}</p>
                        </div>
                    </div>
                    <button onclick="visualizarDocumento('${doc.nome}')" 
                            class="text-blue-600 hover:text-blue-800 font-medium">
                        Visualizar
                    </button>
                `;
                containerDocumentos.appendChild(docElement);
            });
            
            // Preencher histórico
            const containerHistorico = document.getElementById('detalheHistorico');
            containerHistorico.innerHTML = '';
            
            atividade.historico.forEach(item => {
                const histElement = document.createElement('div');
                histElement.className = 'flex items-start space-x-3';
                histElement.innerHTML = `
                    <div class="flex-shrink-0">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-gray-900">${item.acao}</p>
                        <p class="text-xs text-gray-500">${item.data} • ${item.usuario}</p>
                    </div>
                `;
                containerHistorico.appendChild(histElement);
            });
            
            // Mostrar modal
            document.getElementById('modalDetalhesAtividade').classList.remove('hidden');
        }

        function fecharModalDetalhes() {
            document.getElementById('modalDetalhesAtividade').classList.add('hidden');
        }

        function visualizarDocumento(nomeDocumento) {
            // Simular visualização de documento
            alert(`Abrindo documento: ${nomeDocumento}\n\n(Em um sistema real, isso abriria o documento em uma nova aba)`);
        }

        // Fechar modal ao clicar fora dele
        document.getElementById('modalDetalhesAtividade').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalDetalhes();
            }
        });
    </script>
</body>
</html>
