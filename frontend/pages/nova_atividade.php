<?php
session_start();
if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'aluno') {
    header('Location: login.php');
    exit;
}

$atividades_disponiveis = [
    [
        'id' => 1,
        'nome' => 'Monitoria Acadêmica',
        'categoria' => 'Monitoria',
        'horas_max' => 80,
        'descricao' => 'Atividades de monitoria em disciplinas do curso',
        'tipo' => 'Participação'
    ],
    [
        'id' => 2,
        'nome' => 'Monitoria em Laboratório',
        'categoria' => 'Monitoria',
        'horas_max' => 60,
        'descricao' => 'Atividades de monitoria em laboratórios de informática ou outros',
        'tipo' => 'Participação'
    ],
    [
        'id' => 3,
        'nome' => 'Iniciação Científica',
        'categoria' => 'Pesquisa',
        'horas_max' => 120,
        'descricao' => 'Projetos de iniciação científica com orientação docente',
        'tipo' => 'Participação'
    ],
    [
        'id' => 4,
        'nome' => 'Projeto de Extensão',
        'categoria' => 'Extensão',
        'horas_max' => 80,
        'descricao' => 'Participação em projetos de extensão universitária',
        'tipo' => 'Participação'
    ],
    [
        'id' => 5,
        'nome' => 'Estágio',
        'categoria' => 'Estágio',
        'horas_max' => 160,
        'descricao' => 'Atividades práticas supervisionadas em empresas ou instituições',
        'tipo' => 'Participação'
    ],
    [
        'id' => 6,
        'nome' => 'Eventos',
        'categoria' => 'Extensão',
        'horas_max' => 40,
        'descricao' => 'Participação em congressos, seminários e workshops',
        'tipo' => 'Participação'
    ],
];

$atividades_filtradas = $atividades_disponiveis;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Atividade - ACC Discente</title>
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
                    <a href="home_aluno.php" class="text-white hover:text-gray-200 mr-4">Voltar</a>
                    <a href="login.php?logout=1" class="text-white hover:text-gray-200">Logout</a>
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
                        Escolher Nova Atividade
                    </h2>
                    <p class="text-gray-600">Selecione uma atividade para se cadastrar</p>
                </div>
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <input type="text" placeholder="Buscar atividades..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas as categorias</option>
                            <option value="ensino">Ensino</option>
                            <option value="pesquisa">Pesquisa</option>
                            <option value="extensao">Extensão</option>
                            <option value="estagio">Estágio</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($atividades_filtradas as $atividade): ?>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-4" style="background-color: #151B23">
                            <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($atividade['nome']) ?></h3>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mt-2">
                                <?= htmlspecialchars($atividade['categoria']) ?>
                            </span>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($atividade['descricao']) ?></p>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #0969DA">Tipo:</span>
                                    <span class="text-gray-600"><?= htmlspecialchars($atividade['tipo']) ?></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium" style="color: #0969DA">Horas Máximas:</span>
                                    <span class="text-gray-600"><?= $atividade['horas_max'] ?>h</span>
                                </div>
                            </div>
                            
                            <div class="flex gap-2">
                                <button onclick="verDetalhes(<?= $atividade['id'] ?>)" 
                                        class="flex-1 px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200"
                                        style="color: #0969DA">
                                    Ver Detalhes
                                </button>
                                <button onclick="selecionarAtividade(<?= $atividade['id'] ?>)" 
                                        class="flex-1 px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition duration-200"
                                        style="background-color: #1A7F37">
                                    Selecionar
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-8 p-6 rounded-lg" style="background-color: #E6F3FF; border-left: 4px solid #0969DA">
                    <h4 class="font-bold mb-2" style="color: #0969DA">Informações Importantes</h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>• Você pode se cadastrar em múltiplas atividades</li>
                        <li>• Após selecionar, você precisará enviar comprovantes</li>
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
    <script>
        const atividades = <?= json_encode(array_values($atividades_filtradas)) ?>;
        
        function verDetalhes(id) {
            const atividade = atividades.find(a => a.id === id);
            if (!atividade) return;
            
            const detalhes = `
                <h4 class="text-xl font-bold mb-4" style="color: #0969DA">${atividade.nome}</h4>
                <div class="space-y-3">
                    <div>
                        <span class="font-medium" style="color: #0969DA">Categoria:</span>
                        <span class="ml-2">${atividade.categoria}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #0969DA">Tipo:</span>
                        <span class="ml-2">${atividade.tipo}</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #0969DA">Horas Máximas:</span>
                        <span class="ml-2">${atividade.horas_max} horas</span>
                    </div>
                    <div>
                        <span class="font-medium" style="color: #0969DA">Descrição:</span>
                        <p class="mt-1 text-gray-600">${atividade.descricao}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('conteudoDetalhes').innerHTML = detalhes;
            document.getElementById('btnSelecionarModal').onclick = () => selecionarAtividade(id);
            document.getElementById('modalDetalhes').classList.remove('hidden');
            document.getElementById('modalDetalhes').classList.add('flex');
        }
        
        function fecharModal() {
            document.getElementById('modalDetalhes').classList.add('hidden');
            document.getElementById('modalDetalhes').classList.remove('flex');
        }
        
        function selecionarAtividade(id) {
            const atividade = atividades.find(a => a.id === id);
            if (!atividade) return;
            
            if (confirm(`Deseja se cadastrar na atividade "${atividade.nome}"?`)) {
                alert(`Redirecionando para cadastro na atividade: ${atividade.nome}`);
            }
            
            fecharModal();
        }
    </script>
</body>
</html>