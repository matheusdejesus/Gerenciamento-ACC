<?php
session_start();
if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'coordenador') {
    header('Location: login.php');
    exit;
}

// Dados simulados para demonstração
$certificados_pendentes = [
    [
        'id' => 1,
        'aluno' => 'João Silva Santos',
        'matricula' => '202012345',
        'curso' => 'Ciência da Computação',
        'atividade' => 'Monitoria Acadêmica',
        'horas_solicitadas' => 40,
        'data_submissao' => '2025-05-28',
        'orientador' => 'Prof. Dr. Carlos Lima',
        'status_orientador' => 'Aprovado',
        'horas_aprovadas_orientador' => 35
    ],
    [
        'id' => 2,
        'aluno' => 'Maria Santos Costa',
        'matricula' => '202098765',
        'curso' => 'Sistemas de Informação',
        'atividade' => 'Iniciação Científica',
        'horas_solicitadas' => 80,
        'data_submissao' => '2025-05-25',
        'orientador' => 'Prof. Dra. Ana Oliveira',
        'status_orientador' => 'Aprovado',
        'horas_aprovadas_orientador' => 75
    ],
    [
        'id' => 3,
        'aluno' => 'Pedro Henrique Lima',
        'matricula' => '202087654',
        'curso' => 'Engenharia de Software',
        'atividade' => 'Projeto de Extensão',
        'horas_solicitadas' => 60,
        'data_submissao' => '2025-05-20',
        'orientador' => 'Prof. Dr. João Pereira',
        'status_orientador' => 'Aprovado',
        'horas_aprovadas_orientador' => 60
    ]
];

$certificados_processados = [
    [
        'id' => 4,
        'aluno' => 'Ana Carolina Ferreira',
        'matricula' => '202076543',
        'curso' => 'Ciência da Computação',
        'atividade' => 'Curso de Extensão IA',
        'horas_solicitadas' => 40,
        'horas_aprovadas_orientador' => 35,
        'horas_contabilizadas' => 35,
        'data_certificacao' => '2025-05-15',
        'status' => 'Certificado'
    ],
    [
        'id' => 5,
        'aluno' => 'Lucas Rodrigues Silva',
        'matricula' => '202065432',
        'curso' => 'Sistemas de Informação',
        'atividade' => 'Workshop Programação',
        'horas_solicitadas' => 20,
        'horas_aprovadas_orientador' => 20,
        'horas_contabilizadas' => 15,
        'data_certificacao' => '2025-05-10',
        'status' => 'Certificado'
    ],
    [
        'id' => 6,
        'aluno' => 'Carla Mendes Santos',
        'matricula' => '202054321',
        'curso' => 'Engenharia de Software',
        'atividade' => 'Estágio Supervisionado',
        'horas_solicitadas' => 120,
        'horas_aprovadas_orientador' => 100,
        'horas_contabilizadas' => 0,
        'data_certificacao' => '2025-05-08',
        'status' => 'Rejeitado'
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Coordenador - SACC UFOPA</title>
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
                    <span class="text-white mr-4 font-extralight mb-0">Olá, <?= htmlspecialchars($_SESSION['usuario']['nome']) ?></span>
                    <a href="login.php?logout=1" class="text-white hover:text-gray-200">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex-grow pt-24" style="background-color: #0D1117">
        <div class="flex max-w-7xl mx-auto px-4">
            <aside class="lg:w-1/4 p-6 pr-8">
                <nav class="space-y-2">
                    <a href="#" class="block p-3 rounded text-white font-medium" style="background-color: #0969DA">
                        Início
                    </a>
                    <a href="configuracoes_coordenador.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Configurações da Conta
                    </a>
                </nav>
            </aside>
            
            <main class="lg:w-3/4 p-6 rounded-lg" style="background-color: #F6F8FA">
                <div class="mb-8">
                    <h2 class="text-3xl font-extralight mb-2" style="color: #0969DA">
                        Painel do Coordenador
                    </h2>
                    <p class="text-gray-600">Gerencie certificados e contabilize horas de ACC dos estudantes.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Pendentes</h3>
                        <p class="text-3xl font-bold" style="color: #B45309"><?= count($certificados_pendentes) ?></p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Certificados</h3>
                        <p class="text-3xl font-bold" style="color: #1A7F37">
                            <?= count(array_filter($certificados_processados, fn($c) => $c['status'] === 'Certificado')) ?>
                        </p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Rejeitados</h3>
                        <p class="text-3xl font-bold" style="color: #DA1A3A">
                            <?= count(array_filter($certificados_processados, fn($c) => $c['status'] === 'Rejeitado')) ?>
                        </p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Horas Certificadas</h3>
                        <p class="text-3xl font-bold" style="color: #1A7F37">
                            <?= array_sum(array_column(array_filter($certificados_processados, fn($c) => $c['status'] === 'Certificado'), 'horas_contabilizadas')) ?>
                        </p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                    <div class="p-4" style="background-color: #151B23">
                        <h3 class="text-xl font-bold text-white">Certificados Pendentes de Aprovação</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <?php if (empty($certificados_pendentes)): ?>
                            <div class="p-8 text-center text-gray-500">
                                <p>Não há certificados pendentes no momento.</p>
                            </div>
                        <?php else: ?>
                            <table class="w-full">
                                <thead style="background-color: #F6F8FA">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Aluno</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Curso</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Atividade</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Horas Aprovadas</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Data</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($certificados_pendentes as $certificado): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($certificado['aluno']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($certificado['matricula']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($certificado['curso']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($certificado['atividade']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= $certificado['horas_aprovadas_orientador'] ?>h
                                            <div class="text-xs text-gray-500">por <?= htmlspecialchars($certificado['orientador']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= date('d/m/Y', strtotime($certificado['data_submissao'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="processarCertificado(<?= $certificado['id'] ?>)" 
                                                    class="text-white px-3 py-1 rounded mr-2 hover:opacity-80" 
                                                    style="background-color: #0969DA">
                                                Processar
                                            </button>
                                            <button onclick="verDetalhesCertificado(<?= $certificado['id'] ?>)" 
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
                        <h3 class="text-xl font-bold text-white">Histórico de Certificados Processados</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead style="background-color: #F6F8FA">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Aluno</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Curso</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Atividade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Horas Certificadas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Data Certificação</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($certificados_processados as $certificado): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($certificado['aluno']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($certificado['matricula']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($certificado['curso']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($certificado['atividade']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= $certificado['horas_contabilizadas'] ?>h / <?= $certificado['horas_aprovadas_orientador'] ?>h
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $certificado['status'] === 'Certificado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $certificado['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('d/m/Y', strtotime($certificado['data_certificacao'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="verDetalhesCertificado(<?= $certificado['id'] ?>)" 
                                                class="text-[#0969DA] hover:text-[#061B53]">
                                            Ver Detalhes
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
        <div class="pb-8 pr-6"></div>
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
    <div id="modalProcessamento" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
            <div class="p-4" style="background-color: #151B23">
                <h3 class="text-xl font-bold text-white">Processar Certificado</h3>
            </div>
            <div class="p-6">
                <form id="formProcessamento">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2" style="color: #0969DA">Horas a Certificar</label>
                        <input type="number" id="horasCertificar" min="0" max="200" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Informe quantas horas serão efetivamente contabilizadas para ACC</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2" style="color: #0969DA">Observações do Coordenador</label>
                        <textarea id="observacoes" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Adicione observações sobre a certificação..."></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="fecharModalProcessamento()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="button" onclick="rejeitarCertificado()" 
                                class="px-4 py-2 text-white rounded-lg hover:opacity-90" 
                                style="background-color: #DA1A3A">
                            Rejeitar
                        </button>
                        <button type="button" onclick="aprovarCertificado()" 
                                class="px-4 py-2 text-white rounded-lg hover:opacity-90" 
                                style="background-color: #1A7F37">
                            Certificar
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
                    <h3 class="text-xl font-bold" style="color: #0969DA">Detalhes do Certificado</h3>
                    <button onclick="fecharModalDetalhes()" class="text-gray-400 hover:text-gray-600">
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
                                    <span class="font-medium text-gray-700">Atividade:</span>
                                    <span id="detalhesAtividade" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Horas Solicitadas:</span>
                                    <span id="detalhesHorasSolicitadas" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Horas Aprovadas pelo Orientador:</span>
                                    <span id="detalhesHorasAprovadas" class="ml-2"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Orientador:</span>
                                    <span id="detalhesOrientador" class="ml-2"></span>
                                </div>
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

    <script>
        // Dados simulados dos certificados para demonstração
        const certificadosPendentes = [
            {
                id: 1,
                estudante: {
                    nome: "João Silva Santos",
                    matricula: "202012345",
                    curso: "Bacharelado em Ciência da Computação"
                },
                atividade: "Monitoria Acadêmica",
                horasSolicitadas: 40,
                horasAprovadasOrientador: 35,
                orientador: "Prof. Dr. Carlos Lima",
                dataSubmissao: "28/05/2025",
                documentos: [
                    { nome: "declaracao_monitoria.pdf", tipo: "Declaração", tamanho: "800 KB" },
                    { nome: "relatorio_atividades.pdf", tipo: "Relatório", tamanho: "1.2 MB" }
                ]
            },
            {
                id: 2,
                estudante: {
                    nome: "Maria Santos Costa",
                    matricula: "202098765",
                    curso: "Bacharelado em Sistemas de Informação"
                },
                atividade: "Iniciação Científica",
                horasSolicitadas: 80,
                horasAprovadasOrientador: 75,
                orientador: "Prof. Dra. Ana Oliveira",
                dataSubmissao: "25/05/2025",
                documentos: [
                    { nome: "certificado_iniciacao.pdf", tipo: "Certificado", tamanho: "1.5 MB" },
                    { nome: "resumo_projeto.pdf", tipo: "Resumo", tamanho: "900 KB" }
                ]
            }
        ];

        let certificadoAtual = null;

        function processarCertificado(id) {
            certificadoAtual = certificadosPendentes.find(cert => cert.id === id);
            
            if (!certificadoAtual) {
                alert('Certificado não encontrado!');
                return;
            }
            
            // Pré-preencher com as horas aprovadas pelo orientador
            document.getElementById('horasCertificar').value = certificadoAtual.horasAprovadasOrientador;
            document.getElementById('horasCertificar').max = certificadoAtual.horasAprovadasOrientador;
            document.getElementById('observacoes').value = '';
            
            document.getElementById('modalProcessamento').classList.remove('hidden');
            document.getElementById('modalProcessamento').classList.add('flex');
        }

        function fecharModalProcessamento() {
            document.getElementById('modalProcessamento').classList.add('hidden');
            document.getElementById('modalProcessamento').classList.remove('flex');
            certificadoAtual = null;
        }

        function aprovarCertificado() {
            const horas = document.getElementById('horasCertificar').value;
            const observacoes = document.getElementById('observacoes').value;

            if (!horas || parseInt(horas) <= 0) {
                alert('Por favor, informe um número válido de horas a certificar.');
                return;
            }

            if (parseInt(horas) > certificadoAtual.horasAprovadasOrientador) {
                alert('As horas certificadas não podem ser maiores que as aprovadas pelo orientador.');
                return;
            }

            if (confirm(`Confirma a certificação de ${horas} horas para esta atividade?`)) {
                alert('✅ Certificado aprovado com sucesso!');
                fecharModalProcessamento();
                // Recarregar a página para atualizar os dados
                window.location.reload();
            }
        }

        function rejeitarCertificado() {
            const observacoes = document.getElementById('observacoes').value;

            if (!observacoes.trim()) {
                alert('Por favor, adicione observações explicando o motivo da rejeição.');
                return;
            }

            if (confirm('Confirma a rejeição desta certificação?')) {
                alert('❌ Certificado rejeitado.');
                fecharModalProcessamento();
                // Recarregar a página para atualizar os dados
                window.location.reload();
            }
        }

        function verDetalhesCertificado(id) {
            // Procurar tanto nos pendentes quanto nos processados
            let certificado = certificadosPendentes.find(cert => cert.id === id);
            
            if (!certificado) {
                // Dados simulados para certificados processados
                const certificadosProcessadosDetalhes = [
                    {
                        id: 4,
                        estudante: {
                            nome: "Ana Carolina Ferreira",
                            matricula: "202076543",
                            curso: "Bacharelado em Ciência da Computação"
                        },
                        atividade: "Curso de Extensão IA",
                        horasSolicitadas: 40,
                        horasAprovadasOrientador: 35,
                        orientador: "Prof. Dra. Maria Santos",
                        documentos: [
                            { nome: "certificado_ia.pdf", tipo: "Certificado", tamanho: "2.1 MB" }
                        ]
                    }
                ];
                certificado = certificadosProcessadosDetalhes.find(cert => cert.id === id);
            }
            
            if (!certificado) {
                alert('Certificado não encontrado!');
                return;
            }
            
            // Preencher os dados no modal
            document.getElementById('detalhesNomeEstudante').textContent = certificado.estudante.nome;
            document.getElementById('detalhesMatricula').textContent = certificado.estudante.matricula;
            document.getElementById('detalhesCurso').textContent = certificado.estudante.curso;
            document.getElementById('detalhesAtividade').textContent = certificado.atividade;
            document.getElementById('detalhesHorasSolicitadas').textContent = certificado.horasSolicitadas + ' horas';
            document.getElementById('detalhesHorasAprovadas').textContent = certificado.horasAprovadasOrientador + ' horas';
            document.getElementById('detalhesOrientador').textContent = certificado.orientador;
            
            // Preencher documentos
            const containerDocumentos = document.getElementById('detalhesDocumentos');
            containerDocumentos.innerHTML = '';
            
            certificado.documentos.forEach(doc => {
                const docElement = document.createElement('div');
                docElement.className = 'flex items-center justify-between p-3 bg-white rounded border';
                docElement.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 18h12V6h-4V2H4v16zM2 2a2 2 0 012-2h8l4 4v14a2 2 0 01-2 2H4a2 2 0 01-2-2V2z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">${doc.nome}</p>
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

        function fecharModalDetalhes() {
            document.getElementById('modalDetalhes').classList.add('hidden');
        }

        function visualizarDocumento(nomeDocumento) {
            // Simular visualização de documento
            alert(`Abrindo documento: ${nomeDocumento}\n\n(Em um sistema real, isso abriria o documento em uma nova aba)`);
        }

        // Fechar modais ao clicar fora deles
        document.getElementById('modalProcessamento').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalProcessamento();
            }
        });

        document.getElementById('modalDetalhes').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalDetalhes();
            }
        });
    </script>
</body>
</html>
