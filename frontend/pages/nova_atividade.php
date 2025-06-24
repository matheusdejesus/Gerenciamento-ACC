<?php
function buscarAtividades() {
    $url = 'http://localhost/Gerenciamento-de-ACC/backend/api/routes/listar_atividades.php';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        error_log("Erro cURL: " . $curlError);
        return [];
    }
    
    if ($httpCode !== 200) {
        error_log("Erro HTTP: " . $httpCode);
        return [];
    }
    
    $data = json_decode($response, true);
    
    if (!$data || !isset($data['success']) || !$data['success']) {
        error_log("Erro na resposta da API: " . $response);
        return [];
    }
    
    return $data['data'] ?? [];
}

// Buscar atividades da API
$atividades_disponiveis = buscarAtividades();

$categoria_filtro = $_GET['categoria'] ?? '';
$busca_filtro = $_GET['busca'] ?? '';

$atividades_filtradas = $atividades_disponiveis;

if (!empty($categoria_filtro)) {
    $atividades_filtradas = array_filter($atividades_filtradas, function($atividade) use ($categoria_filtro) {
        return strtolower($atividade['categoria']) === strtolower($categoria_filtro);
    });
}

if (!empty($busca_filtro)) {
    $atividades_filtradas = array_filter($atividades_filtradas, function($atividade) use ($busca_filtro) {
        return stripos($atividade['nome'], $busca_filtro) !== false || 
               stripos($atividade['descricao'], $busca_filtro) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Atividade - ACC Discente</title>
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
                    <a href="home_aluno.php" class="text-white hover:text-gray-200 mr-4">Voltar</a>
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
                    <a href="home_aluno.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Início
                    </a>
                    <a href="nova_atividade.php" class="block p-3 rounded bg-gray-200 text-[#0969DA]">
                        Nova Atividade
                    </a>
                    <a href="enviar_comprovante.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Enviar Comprovante
                    </a>
                    <a href="configuracoes_aluno.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Configurações da Conta
                    </a>
                </nav>
            </aside>
            <main class="lg:w-3/4 p-6 rounded-lg" style="background-color: #F6F8FA">
                <div class="mb-8">
                    <h2 class="text-3xl font-extralight mb-2" style="color: #0969DA">
                        Escolher Nova Atividade
                    </h2>
                    <p class="text-gray-600">Selecione uma atividade para se cadastrar</p>
                    
                    <?php if (empty($atividades_disponiveis)): ?>
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-yellow-800">⚠️ Não foi possível carregar as atividades. Verifique a conexão com o banco de dados.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <input type="text" name="busca" value="<?= htmlspecialchars($busca_filtro) ?>" 
                               placeholder="Buscar atividades..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex space-x-2">
                        <select name="categoria" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas as categorias</option>
                            <?php 
                            $categorias = array_unique(array_column($atividades_disponiveis, 'categoria'));
                            foreach ($categorias as $categoria): ?>
                                <option value="<?= htmlspecialchars($categoria) ?>" 
                                        <?= $categoria_filtro === $categoria ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="px-4 py-2 text-white rounded-lg hover:opacity-90" style="background-color: #0969DA">
                            Filtrar
                        </button>
                        <?php if (!empty($categoria_filtro) || !empty($busca_filtro)): ?>
                            <a href="nova_atividade.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center">
                                Limpar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
                <?php if (empty($atividades_filtradas)): ?>
                    <div class="text-center py-12">
                        <p class="text-gray-500 text-lg">Nenhuma atividade encontrada com os filtros aplicados.</p>
                        <?php if (!empty($categoria_filtro) || !empty($busca_filtro)): ?>
                            <a href="nova_atividade.php" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                                Ver todas as atividades
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
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
                                    <a href="cadastrar_atividade.php?id=<?= $atividade['id'] ?>" 
                                       class="flex-1 px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition duration-200 text-center"
                                       style="background-color: #1A7F37">
                                        Selecionar
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-8 p-6 rounded-lg" style="background-color: #E6F3FF; border-left: 4px solid #0969DA">
                    <h4 class="font-bold mb-2" style="color: #0969DA">Informações Importantes</h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>• Você pode se cadastrar em múltiplas atividades</li>
                        <li>• Após selecionar, você precisará enviar comprovantes</li>
                        <li>• As atividades são organizadas por categoria</li>
                        <li>• Verifique os requisitos antes de se inscrever</li>
                    </ul>
                </div>
                
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="cadastrar_atividade.php?id=1" class="p-4 rounded-lg text-center transition duration-200 text-white" style="background-color: #1A7F37">
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
        }

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
            
            // Redirecionar para a página de cadastro
            window.location.href = `cadastrar_atividade.php?id=${id}`;
            
            fecharModal();
        }
    </script>
</body>
</html>