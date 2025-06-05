<?php
session_start();
if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'orientador') {
    header('Location: login.php');
    exit;
}

// Dados simulados para demonstração
$atividades_pendentes = [
    [
        'id' => 1,
        'aluno' => 'João Silva',
        'atividade' => 'Monitoria Acadêmica',
        'horas_solicitadas' => 40,
        'data_submissao' => '2025-05-28',
        'status' => 'Pendente'
    ],
    [
        'id' => 2,
        'aluno' => 'Maria Santos',
        'atividade' => 'Iniciação Científica',
        'horas_solicitadas' => 80,
        'data_submissao' => '2025-05-25',
        'status' => 'Pendente'
    ]
];

$atividades_avaliadas = [
    [
        'id' => 3,
        'aluno' => 'Pedro Costa',
        'atividade' => 'Projeto de Extensão',
        'horas_solicitadas' => 60,
        'horas_aprovadas' => 50,
        'data_avaliacao' => '2025-05-20',
        'status' => 'Aprovada'
    ],
    [
        'id' => 4,
        'aluno' => 'Ana Lima',
        'atividade' => 'Monitoria em Laboratório',
        'horas_solicitadas' => 30,
        'horas_aprovadas' => 0,
        'data_avaliacao' => '2025-05-18',
        'status' => 'Rejeitada'
    ]
];
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
                    <span class="text-white mr-4 font-extralight mb-0"><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></span>
                    <a href="login.php?logout=1" class="text-white hover:text-gray-200">Logout</a>
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
                        Olá, <?= htmlspecialchars($_SESSION['usuario']['nome']) ?>
                    </h2>
                    <p class="text-gray-600">Gerencie as solicitações de ACC dos seus orientandos.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Pendentes</h3>
                        <p class="text-3xl font-bold" style="color: #B45309"><?= count($atividades_pendentes) ?></p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Aprovadas</h3>
                        <p class="text-3xl font-bold" style="color: #1A7F37">
                            <?= count(array_filter($atividades_avaliadas, fn($a) => $a['status'] === 'Aprovada')) ?>
                        </p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Rejeitadas</h3>
                        <p class="text-3xl font-bold" style="color: #DA1A3A">
                            <?= count(array_filter($atividades_avaliadas, fn($a) => $a['status'] === 'Rejeitada')) ?>
                        </p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Total de Horas Aprovadas</h3>
                        <p class="text-3xl font-bold" style="color: #1A7F37">
                            <?= array_sum(array_column($atividades_avaliadas, 'horas_aprovadas')) ?>
                        </p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                    <div class="p-4" style="background-color: #151B23">
                        <h3 class="text-xl font-bold text-white">Atividades Pendentes de Avaliação</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <?php if (empty($atividades_pendentes)): ?>
                            <div class="p-8 text-center text-gray-500">
                                <p>Não há atividades pendentes no momento.</p>
                            </div>
                        <?php else: ?>
                            <table class="w-full">
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
                                    <?php foreach ($atividades_pendentes as $atividade): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($atividade['aluno']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($atividade['atividade']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= $atividade['horas_solicitadas'] ?>h
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= date('d/m/Y', strtotime($atividade['data_submissao'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="avaliarAtividade(<?= $atividade['id'] ?>)" 
                                                    class="text-white px-3 py-1 rounded mr-2 hover:opacity-80" 
                                                    style="background-color: #0969DA">
                                                Avaliar
                                            </button>
                                            <button onclick="verDetalhes(<?= $atividade['id'] ?>)" 
                                                    class="text-[#0969DA] hover:text-[#061B53]">
                                                Ver Detalhes
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4" style="background-color: #151B23">
                        <h3 class="text-xl font-bold text-white">Atividades Avaliadas Recentemente</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <?php if (empty($atividades_avaliadas)): ?>
                            <div class="p-8 text-center text-gray-500">
                                <p>Nenhuma atividade avaliada ainda.</p>
                            </div>
                        <?php else: ?>
                            <table class="w-full">
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
                                    <?php foreach ($atividades_avaliadas as $atividade): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($atividade['aluno']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($atividade['atividade']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= $atividade['horas_solicitadas'] ?>h / <?= $atividade['horas_aprovadas'] ?>h
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= $atividade['status'] === 'Aprovada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= $atividade['status'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= date('d/m/Y', strtotime($atividade['data_avaliacao'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="verDetalhes(<?= $atividade['id'] ?>)" 
                                                    class="text-[#0969DA] hover:text-[#061B53]">
                                                Ver Detalhes
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div id="modalAvaliacao" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
            <div class="p-4" style="background-color: #151B23">
                <h3 class="text-xl font-bold text-white">Avaliar Atividade</h3>
            </div>
            <div class="p-6">
                <form id="formAvaliacao">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2" style="color: #0969DA">Horas Aprovadas</label>
                        <input type="number" id="horasAprovadas" min="0" max="200" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Informe quantas horas serão aprovadas para esta atividade</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2" style="color: #0969DA">Parecer</label>
                        <textarea id="parecer" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Descreva seu parecer sobre a atividade..."></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="fecharModalAvaliacao()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="button" onclick="rejeitarAtividade()" 
                                class="px-4 py-2 text-white rounded-lg hover:opacity-90" 
                                style="background-color: #DA1A3A">
                            Rejeitar
                        </button>
                        <button type="button" onclick="aprovarAtividade()" 
                                class="px-4 py-2 text-white rounded-lg hover:opacity-90" 
                                style="background-color: #1A7F37">
                            Aprovar
                        </button>
                    </div>
                </form>
            </div>
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

    <script>
        // Dados simulados das atividades para demonstração
        const atividadesPendentes = [
            {
                id: 1,
                estudante: {
                    nome: "Maria Silva Santos",
                    matricula: "202012345",
                    email: "maria.santos@discente.ufopa.edu.br",
                    curso: "Bacharelado em Ciência da Computação"
                },
                titulo: "Participação em Congresso de Tecnologia",
                tipo: "Evento Científico",
                descricao: "Participação como ouvinte no XXV Congresso Brasileiro de Engenharia de Software, realizado de 20 a 24 de setembro de 2024, na cidade de São Paulo/SP. O evento contou com palestras, minicursos e apresentações de trabalhos científicos na área de engenharia de software e tecnologias relacionadas.",
                dataSubmissao: "15/10/2024",
                horasSolicitadas: 20,
                documentos: [
                    { nome: "certificado_congresso.pdf", tipo: "Certificado", tamanho: "2.3 MB" },
                    { nome: "programacao_evento.pdf", tipo: "Programação", tamanho: "1.8 MB" }
                ]
            },
            {
                id: 2,
                estudante: {
                    nome: "João Pedro Lima",
                    matricula: "202098765",
                    email: "joao.lima@discente.ufopa.edu.br",
                    curso: "Bacharelado em Sistemas de Informação"
                },
                titulo: "Curso Online de Python Avançado",
                tipo: "Curso",
                descricao: "Curso online de Python Avançado oferecido pela plataforma Coursera, com duração de 40 horas, abordando tópicos como programação orientada a objetos, estruturas de dados avançadas, e desenvolvimento web com Django.",
                dataSubmissao: "12/10/2024",
                horasSolicitadas: 40,
                documentos: [
                    { nome: "certificado_python.pdf", tipo: "Certificado", tamanho: "1.5 MB" }
                ]
            },
            {
                id: 3,
                estudante: {
                    nome: "Ana Carolina Ferreira",
                    matricula: "202087654",
                    email: "ana.ferreira@discente.ufopa.edu.br",
                    curso: "Bacharelado em Engenharia de Software"
                },
                titulo: "Monitoria de Algoritmos e Programação",
                tipo: "Monitoria",
                descricao: "Atividade de monitoria na disciplina de Algoritmos e Programação I, auxiliando estudantes em exercícios práticos, esclarecimento de dúvidas e preparação para avaliações, durante o semestre 2024.1.",
                dataSubmissao: "10/10/2024",
                horasSolicitadas: 60,
                documentos: [
                    { nome: "declaracao_monitoria.pdf", tipo: "Declaração", tamanho: "800 KB" },
                    { nome: "relatorio_atividades.pdf", tipo: "Relatório", tamanho: "1.2 MB" }
                ]
            }
        ];

        let atividadeAtual = null;

        function avaliarAtividade(id) {
            atividadeAtual = id;
            document.getElementById('horasAprovadas').value = '';
            document.getElementById('parecer').value = '';
            document.getElementById('modalAvaliacao').classList.remove('hidden');
            document.getElementById('modalAvaliacao').classList.add('flex');
        }

        function fecharModalAvaliacao() {
            document.getElementById('modalAvaliacao').classList.add('hidden');
            document.getElementById('modalAvaliacao').classList.remove('flex');
            atividadeAtual = null;
        }

        function aprovarAtividade() {
            const horas = document.getElementById('horasAprovadas').value;
            const parecer = document.getElementById('parecer').value;

            if (!horas || parseInt(horas) <= 0) {
                alert('Por favor, informe um número válido de horas aprovadas.');
                return;
            }

            if (!parecer.trim()) {
                alert('Por favor, adicione um parecer sobre a atividade.');
                return;
            }

            if (confirm(`Confirma a aprovação de ${horas} horas para esta atividade?`)) {
                alert('✅ Atividade aprovada com sucesso!');
                fecharModalAvaliacao();
                // Recarregar a página para atualizar os dados
                window.location.reload();
            }
        }

        function rejeitarAtividade() {
            const parecer = document.getElementById('parecer').value;

            if (!parecer.trim()) {
                alert('Por favor, adicione um parecer explicando o motivo da rejeição.');
                return;
            }

            if (confirm('Confirma a rejeição desta atividade?')) {
                alert('❌ Atividade rejeitada.');
                fecharModalAvaliacao();
                // Recarregar a página para atualizar os dados
                window.location.reload();
            }
        }

        function verDetalhes(id) {
            // Encontrar a atividade pelo ID
            atividadeAtual = atividadesPendentes.find(atividade => atividade.id === id);
            
            if (!atividadeAtual) {
                alert('Atividade não encontrada!');
                return;
            }
            
            // Preencher os dados no modal
            document.getElementById('detalhesNomeEstudante').textContent = atividadeAtual.estudante.nome;
            document.getElementById('detalhesMatricula').textContent = atividadeAtual.estudante.matricula;
            document.getElementById('detalhesEmailEstudante').textContent = atividadeAtual.estudante.email;
            document.getElementById('detalhesCurso').textContent = atividadeAtual.estudante.curso;
            
            document.getElementById('detalhesTitulo').textContent = atividadeAtual.titulo;
            document.getElementById('detalhesTipo').textContent = atividadeAtual.tipo;
            document.getElementById('detalhesDataSubmissao').textContent = atividadeAtual.dataSubmissao;
            document.getElementById('detalhesHoras').textContent = atividadeAtual.horasSolicitadas + ' horas';
            document.getElementById('detalhesDescricao').textContent = atividadeAtual.descricao;
            
            // Preencher documentos
            const containerDocumentos = document.getElementById('detalhesDocumentos');
            containerDocumentos.innerHTML = '';
            
            atividadeAtual.documentos.forEach(doc => {
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
            
            // Mostrar modal
            document.getElementById('modalDetalhes').classList.remove('hidden');
        }

        function fecharModal() {
            document.getElementById('modalDetalhes').classList.add('hidden');
            atividadeAtual = null;
        }

        function visualizarDocumento(nomeDocumento) {
            // Simular visualização de documento
            alert(`Abrindo documento: ${nomeDocumento}\n\n(Em um sistema real, isso abriria o documento em uma nova aba)`);
        }
        document.getElementById('modalAvaliacao').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalAvaliacao();
            }
        });

        document.getElementById('modalDetalhes').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModal();
            }
        });
    </script>
</body>
</html>