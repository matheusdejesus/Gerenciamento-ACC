<?php
session_start();
if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'aluno') {
    header('Location: login.php');
    exit;
}

$atividade_id = $_GET['id'] ?? 1;

$atividades = [
    1 => [
        'id' => 1,
        'nome' => 'Monitoria Acadêmica',
        'categoria' => 'Monitoria',
        'horas_max' => 80,
        'descricao' => 'Atividades de monitoria em disciplinas do curso',
        'requisitos' => [
            'Estar matriculado regularmente',
            'Ter cursado a disciplina com aprovação',
            'Disponibilidade de pelo menos 4h semanais'
        ]
    ],
    2 => [
        'id' => 2,
        'nome' => 'Monitoria em Laboratório',
        'categoria' => 'Monitoria',
        'horas_max' => 60,
        'descricao' => 'Atividades de monitoria em laboratórios de informática ou outros',
        'requisitos' => [
            'Conhecimentos básicos em informática',
            'Disponibilidade para horários alternativos'
        ]
    ],
    3 => [
        'id' => 3,
        'nome' => 'Iniciação Científica',
        'categoria' => 'Pesquisa',
        'horas_max' => 120,
        'descricao' => 'Projetos de iniciação científica com orientação docente',
        'requisitos' => [
            'Estar matriculado regularmente',
            'Ter orientador definido',
            'Dedicação mínima de 12h semanais'
        ]
    ],
    4 => [
        'id' => 4,
        'nome' => 'Projeto de Extensão',
        'categoria' => 'Extensão',
        'horas_max' => 80,
        'descricao' => 'Participação em projetos de extensão universitária',
        'requisitos' => [
            'Estar matriculado regularmente',
            'Participação efetiva no projeto'
        ]
    ],
    5 => [
        'id' => 5,
        'nome' => 'Estágio',
        'categoria' => 'Estágio',
        'horas_max' => 160,
        'descricao' => 'Atividades práticas supervisionadas em empresas ou instituições',
        'requisitos' => [
            'Estar matriculado regularmente',
            'Ter supervisor na empresa'
        ]
    ],
    6 => [
        'id' => 6,
        'nome' => 'Eventos',
        'categoria' => 'Extensão',
        'horas_max' => 40,
        'descricao' => 'Participação em congressos, seminários e workshops',
        'requisitos' => [
            'Comprovar participação efetiva'
        ]
    ]
];

$atividade = $atividades[$atividade_id] ?? $atividades[1];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar na <?= htmlspecialchars($atividade['nome']) ?> - ACC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="bg-pattern font-montserrat min-h-screen flex flex-col">
    <nav class="bg-white shadow-lg fixed top-0 w-full z-50" style="background-color: #151B23">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-regular text-white">SACC</span>
                </div>
                <div class="flex items-center">
                    <a href="nova_atividade.php" class="text-white hover:text-gray-200 mr-4">Voltar</a>
                    <a href="login.php?logout=1" class="text-white hover:text-gray-200">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex-grow pt-24" style="background-color: #0D1117">
        <div class="container mx-auto max-w-4xl p-4">
            <div class="rounded-lg shadow-sm overflow-hidden" style="background-color: #F6F8FA">
                <div class="p-8" style="background-color: #151B23">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-2"><?= htmlspecialchars($atividade['nome']) ?></h1>
                            <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?= htmlspecialchars($atividade['categoria']) ?>
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-white text-sm">Horas Máximas</div>
                            <div class="text-white text-2xl font-bold"><?= $atividade['horas_max'] ?>h</div>
                        </div>
                    </div>
                </div>
                <div class="p-8">
                    <div class="mb-8">
                        <h2 class="text-xl font-bold mb-3" style="color: #0969DA">Sobre esta Atividade</h2>
                        <p class="text-gray-700 leading-relaxed"><?= htmlspecialchars($atividade['descricao']) ?></p>
                    </div>
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-3" style="color: #0969DA">Requisitos</h3>
                        <ul class="space-y-2">
                            <?php foreach ($atividade['requisitos'] as $requisito): ?>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <span class="text-gray-700"><?= htmlspecialchars($requisito) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <form id="formCadastro" class="space-y-6" enctype="multipart/form-data">
                        <input type="hidden" name="atividade_id" value="<?= $atividade_id ?>">
                        <div class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Informações da Participação</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Data de Início *</label>
                                    <input type="date" name="data_inicio" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Data de Término *</label>
                                    <input type="date" name="data_fim" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Carga Horária Solicitada *</label>
                                <input type="number" name="horas_solicitadas" min="1" max="<?= $atividade['horas_max'] ?>" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Máximo: <?= $atividade['horas_max'] ?> horas">
                                <p class="text-xs text-gray-500 mt-1">Informe a quantidade de horas que você participou da atividade</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Descrição das Atividades Desenvolvidas *</label>
                                <textarea name="descricao_atividades" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Descreva detalhadamente as atividades que você desenvolveu, objetivos alcançados e resultados obtidos..."></textarea>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Documento Comprobatório</h3>
                            <p class="text-sm text-gray-600 mb-4">Envie a declaração para o professor responsável:</p>
                            
                            <div class="p-6 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 hover:border-blue-400 transition-colors">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <label for="declaracao" class="cursor-pointer">
                                        <span class="text-lg font-medium text-blue-600 hover:text-blue-500">
                                            Clique para selecionar a declaração
                                        </span>
                                        <input type="file" id="declaracao" name="declaracao" accept=".pdf,.jpg,.jpeg,.png" required class="hidden">
                                    </label>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Declaração de Participação
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Formatos aceitos: PDF, JPG, JPEG, PNG (máx. 5MB)
                                    </p>
                                </div>
                                <div id="arquivo-selecionado" class="hidden mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span id="nome-arquivo" class="text-sm text-blue-700"></span>
                                        <button type="button" onclick="removerArquivo()" class="ml-auto text-red-500 hover:text-red-700">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                            <h3 class="text-sm font-bold mb-3" style="color: #B45309">
                                ⚠️ Declaração de Veracidade
                            </h3>
                            <div class="flex items-start">
                                <input type="checkbox" id="termos" name="termos" required class="mt-1 mr-3">
                                <label for="termos" class="text-sm text-gray-700">
                                    Declaro que todas as informações fornecidas são verdadeiras e que o documento 
                                    apresentado comprova minha participação na atividade descrita. Estou ciente de que 
                                    informações falsas podem resultar no cancelamento da solicitação.
                                </label>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center pt-6">
                            <a href="nova_atividade.php" 
                               class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200 text-center"
                               style="color: #0969DA">
                                ← Voltar às Atividades
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 text-white rounded-lg hover:opacity-90 transition duration-200 font-medium"
                                    style="background-color: #1A7F37">
                                Enviar Solicitação →
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Manipulação do upload de arquivo
        document.getElementById('declaracao').addEventListener('change', function(e) {
            const arquivo = e.target.files[0];
            if (arquivo) {
                // Validar tamanho (5MB = 5 * 1024 * 1024 bytes)
                if (arquivo.size > 5 * 1024 * 1024) {
                    alert('O arquivo deve ter no máximo 5MB.');
                    e.target.value = '';
                    return;
                }
                
                // Mostrar arquivo selecionado
                document.getElementById('nome-arquivo').textContent = arquivo.name;
                document.getElementById('arquivo-selecionado').classList.remove('hidden');
            }
        });

        function removerArquivo() {
            document.getElementById('declaracao').value = '';
            document.getElementById('arquivo-selecionado').classList.add('hidden');
        }

        document.getElementById('formCadastro').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validações
            const dataInicio = new Date(document.querySelector('input[name="data_inicio"]').value);
            const dataFim = new Date(document.querySelector('input[name="data_fim"]').value);
            const hoje = new Date();
            
            if (dataInicio >= dataFim) {
                alert('A data de término deve ser posterior à data de início.');
                return;
            }
            
            if (dataFim > hoje) {
                alert('A data de término não pode ser futura.');
                return;
            }
            
            // Verificar se o arquivo foi selecionado
            const arquivo = document.getElementById('declaracao').files[0];
            if (!arquivo) {
                alert('Por favor, selecione a declaração do professor.');
                return;
            }
            
            // Confirmação
            if (confirm('Deseja realmente enviar esta solicitação?\n\nApós o envio, você não poderá mais editá-la e ela será enviada para análise.')) {
                document.querySelector('button[type="submit"]').disabled = true;
                document.querySelector('button[type="submit"]').innerHTML = 'Enviando...';
                
                setTimeout(() => {
                    alert('✅ Solicitação enviada com sucesso!\n\nVocê receberá uma notificação quando ela for avaliada.');
                    window.location.href = 'home_aluno.php';
                }, 1500);
            }
        });

        // Validação em tempo real das datas
        document.querySelector('input[name="data_inicio"]').addEventListener('change', function() {
            const dataFim = document.querySelector('input[name="data_fim"]');
            dataFim.min = this.value;
        });
    </script>
</body>
</html>