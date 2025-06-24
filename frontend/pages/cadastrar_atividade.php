<?php

$atividade_id = $_GET['id'] ?? 1;

// Função para buscar atividade específica da API
function buscarAtividade($id) {
    $url = 'http://localhost/Gerenciamento-de-ACC/backend/api/routes/listar_atividades.php';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (!$data || !isset($data['success']) || !$data['success']) {
        return null;
    }
    
    $atividades = $data['data'] ?? [];
    
    foreach ($atividades as $atividade) {
        if ($atividade['id'] == $id) {
            return $atividade;
        }
    }
    
    return null;
}

// Função para buscar orientadores
function buscarOrientadores() {
    $url = 'http://localhost/Gerenciamento-de-ACC/backend/api/routes/cadastrar_atividade_complementar.php?orientadores=1';
    
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
        error_log("Erro cURL ao buscar orientadores: " . $curlError);
        return [];
    }
    
    if ($httpCode !== 200) {
        error_log("Erro HTTP ao buscar orientadores: " . $httpCode . " - " . $response);
        return [];
    }
    
    $data = json_decode($response, true);
    
    if (!$data || !isset($data['success']) || !$data['success']) {
        error_log("Erro na resposta da API de orientadores: " . $response);
        return [];
    }
    
    return $data['data'] ?? [];
}

// Buscar atividade específica
$atividade = buscarAtividade($atividade_id);

// Buscar orientadores
$orientadores = buscarOrientadores();

// Se não encontrar a atividade, definir valores padrão
if (!$atividade) {
    $atividade = [
        'id' => $atividade_id,
        'nome' => 'Atividade não encontrada',
        'categoria' => 'Geral',
        'horas_max' => 40,
        'descricao' => 'Atividade complementar',
        'requisitos' => ['Estar matriculado regularmente']
    ];
}

// Adicionar requisitos padrão se não existirem
if (!isset($atividade['requisitos'])) {
    $atividade['requisitos'] = [
        'Estar matriculado regularmente',
        'Comprovar participação efetiva'
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar na <?= htmlspecialchars($atividade['nome']) ?> - ACC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
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
                    <a href="nova_atividade.php" class="text-white hover:text-gray-200 mr-4">Voltar</a>
                    <span id="nomeUsuario" class="text-white mr-4 font-extralight">Carregando...</span>
                    <button onclick="AuthClient.logout()" class="text-white hover:text-gray-200">Logout</button>
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
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Título da Atividade</label>
                                <input type="text" name="titulo" required class="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Ex: Participação em Workshop de IA">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data de Início</label>
                                    <input type="date" name="data_inicio" required class="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data de Término</label>
                                    <input type="date" name="data_fim" required class="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Carga Horária Solicitada</label>
                                    <input type="number" name="horas_solicitadas" min="1" max="<?= $atividade['horas_max'] ?>" required class="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Ex: 20">
                                    <p class="text-sm text-gray-500 mt-1">Máximo: <?= $atividade['horas_max'] ?> horas</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Orientador Responsável</label>
                                    <select name="orientador_id" required class="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        <option value="">-- Selecione um orientador --</option>
                                        <?php foreach ($orientadores as $orientador): ?>
                                            <option value="<?= $orientador['id'] ?>">
                                                <?= htmlspecialchars($orientador['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (empty($orientadores)): ?>
                                        <p class="text-sm text-red-500 mt-1">⚠️ Nenhum orientador encontrado. Contate a coordenação.</p>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500 mt-1">Selecione o orientador que avaliará sua atividade</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição das Atividades Realizadas</label>
                                <textarea name="descricao_atividades" rows="4" required class="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Descreva detalhadamente as atividades realizadas..."></textarea>
                            </div>
                        </div>
                        
                        <div class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Documento Comprobatório</h3>
                            <p class="text-sm text-gray-600 mb-4">Envie a declaração para o professor responsável:</p>
                            
                            <div class="p-6 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 hover:border-blue-400 transition-colors">
                                <input type="file" id="declaracao" name="declaracao" accept=".pdf,.jpg,.jpeg,.png" required class="hidden">
                                <label for="declaracao" class="cursor-pointer block text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="mt-2 block text-sm font-medium text-gray-900">Clique para enviar arquivo</span>
                                    <span class="mt-1 block text-sm text-gray-500">PDF, JPG, PNG até 5MB</span>
                                </label>
                                <div id="arquivo-selecionado" class="hidden mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <div class="flex items-center justify-between">
                                        <span id="nome-arquivo" class="text-sm text-blue-700 font-medium"></span>
                                        <button type="button" onclick="removerArquivo()" class="text-red-600 hover:text-red-800 text-sm">Remover</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                            <div class="flex items-start">
                                <svg class="flex-shrink-0 h-5 w-5 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Atenção</h3>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        Após enviar, você não poderá mais editar esta solicitação. Verifique todos os dados antes de confirmar.
                                        O orientador selecionado será responsável por avaliar sua atividade.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center pt-6">
                            <button type="button" onclick="window.history.back()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                                Cancelar
                            </button>
                            <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                Enviar Solicitação
                            </button>
                        </div>
                    </form>
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
            const orientadorId = document.querySelector('select[name="orientador_id"]').value;
            
            if (dataInicio >= dataFim) {
                alert('A data de término deve ser posterior à data de início.');
                return;
            }
            
            // Verificar se orientador foi selecionado
            if (!orientadorId) {
                alert('Por favor, selecione um orientador responsável.');
                return;
            }
            
            // Verificar se a data de término não é superior a 1 ano
            const limiteFuturo = new Date();
            limiteFuturo.setFullYear(limiteFuturo.getFullYear() + 1);
            
            if (dataFim > limiteFuturo) {
                alert('A data de término não pode ser superior a 1 ano no futuro.');
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
                enviarFormulario();
            }
        });

        async function enviarFormulario() {
            const submitBtn = document.querySelector('button[type="submit"]');
            const btnText = submitBtn.innerHTML;
            
            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Enviando...';
                
                const formData = new FormData(document.getElementById('formCadastro'));
                
                // Log dos dados que estão sendo enviados
                console.log('=== DADOS SENDO ENVIADOS ===');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ':', value);
                }
                
                // Usar AuthClient.fetch com JWT
                const response = await AuthClient.fetch('/Gerenciamento-de-ACC/backend/api/routes/cadastrar_atividade_complementar.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                    }
                });
                
                console.log('Status HTTP:', response.status);
                
                // Obter o texto da resposta primeiro
                const responseText = await response.text();
                console.log('=== RESPOSTA BRUTA ===');
                console.log(responseText);
                
                // Verificar se a resposta está vazia
                if (!responseText.trim()) {
                    throw new Error('Resposta vazia do servidor');
                }
                
                // Tentar fazer parse do JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Erro ao fazer parse do JSON:', parseError);
                    console.error('Resposta que causou erro:', responseText);
                    throw new Error('Resposta inválida do servidor: ' + responseText.substring(0, 100));
                }
                
                console.log('=== RESULTADO PARSEADO ===');
                console.log(result);
                
                if (result.success) {
                    alert('✅ Solicitação enviada com sucesso!\n\nVocê receberá uma notificação quando ela for avaliada pelo orientador selecionado.');
                    window.location.href = 'home_aluno.php';
                } else {
                    alert('❌ Erro: ' + result.error);
                }
                
            } catch (error) {
                console.error('=== ERRO COMPLETO ===');
                console.error(error);
                alert('❌ Erro ao enviar solicitação: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = btnText;
            }
        }

        // Validação em tempo real das datas
        document.querySelector('input[name="data_inicio"]').addEventListener('change', function() {
            const dataFim = document.querySelector('input[name="data_fim"]');
            dataFim.min = this.value;
            
            // Definir data máxima como 1 ano a partir da data de início
            const dataInicioObj = new Date(this.value);
            const dataMaxima = new Date(dataInicioObj);
            dataMaxima.setFullYear(dataMaxima.getFullYear() + 1);
            
            // Converter para formato YYYY-MM-DD
            const dataMaximaFormatada = dataMaxima.toISOString().split('T')[0];
            dataFim.max = dataMaximaFormatada;
        });

        // Definir data máxima inicial (1 ano a partir de hoje)
        document.addEventListener('DOMContentLoaded', function() {
            const hoje = new Date();
            const dataMaxima = new Date();
            dataMaxima.setFullYear(dataMaxima.getFullYear() + 1);
            
            const dataFimInput = document.querySelector('input[name="data_fim"]');
            dataFimInput.max = dataMaxima.toISOString().split('T')[0];
        });
    </script>
    <script>
// Debug específico desta página
console.log('=== DEBUG CADASTRAR ATIVIDADE ===');
console.log('Página carregada:', window.location.href);
console.log('AuthClient definido:', typeof AuthClient !== 'undefined');

if (typeof AuthClient !== 'undefined') {
    console.log('Token existe:', !!AuthClient.getToken());
    console.log('Usuário logado:', AuthClient.isLoggedIn());
    console.log('Dados do usuário:', AuthClient.getUser());
} else {
    console.error('AuthClient não foi carregado!');
}
</script>
</body>
</html>