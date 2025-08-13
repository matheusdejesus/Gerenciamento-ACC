<?php
session_start();
require_once __DIR__ . '/../../backend/api/config/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Certificado - ACC</title>
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
                    <a href="home_aluno.php" class="text-white hover:text-gray-200 mr-4">Voltar</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex-grow pt-24" style="background-color: #0D1117">
        <div class="container mx-auto max-w-4xl p-4">
            <div class="rounded-lg shadow-sm overflow-hidden" style="background-color: #F6F8FA">
                <div class="p-8" style="background-color: #151B23">
                    <div class="text-center">
                        <h1 class="text-3xl font-bold text-white mb-2">Enviar Certificado</h1>
                        <p class="text-gray-300">Envie certificados para suas atividades aprovadas</p>
                    </div>
                </div>
                
                <div class="p-8">
                    <form id="formComprovante" enctype="multipart/form-data" class="space-y-6">
                        <div class="bg-white p-6 rounded-lg border mb-6">
                            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Tipo de Envio</h3>
                            <div class="flex gap-6">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="tipo_envio" value="atividade" checked class="form-radio text-blue-600" id="radioAtividade">
                                    <span class="ml-2 text-sm">Enviar certificado de atividade aprovada</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="tipo_envio" value="avulso" class="form-radio text-blue-600" id="radioAvulso">
                                    <span class="ml-2 text-sm">Enviar certificado avulso</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Escolha "Avulso" para enviar um certificado que não está vinculado a uma atividade aprovada.</p>
                        </div>

                        <div id="camposAtividade" class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Selecionar Atividade</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Atividade Aprovada *</label>
                                <select name="atividade_id" id="selectAtividade" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Carregando atividades --</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Apenas atividades aprovadas aparecem na lista</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Coordenador Responsável *</label>
                                <select name="coordenador_id" id="selectCoordenador" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Carregando coordenadores --</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Selecione o coordenador que validará o certificado</p>
                            </div>
                        </div>
                        <div id="camposAvulso" class="bg-white p-6 rounded-lg border hidden">
                            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Dados do Certificado Avulso</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Título da Atividade *</label>
                                <input type="text" name="titulo_avulso" id="tituloAvulso"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ex: Palestra sobre IA">
                                <p class="text-xs text-gray-500 mt-1">Digite o título da atividade realizada</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Carga Horária *</label>
                                <input type="number" name="horas_avulso" id="horasAvulso" min="1" max="200"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ex: 20">
                                <p class="text-xs text-gray-500 mt-1">Carga horária em horas (conforme certificado)</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Observações</label>
                                <textarea name="observacao_avulso" id="observacaoAvulso" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Descreva brevemente a atividade realizada"></textarea>
                                <p class="text-xs text-gray-500 mt-1">Informações adicionais sobre a atividade (opcional)</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Coordenador Responsável *</label>
                                <select name="coordenador_avulso_id" id="selectCoordenadorAvulso"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Selecione um coordenador --</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Coordenador que analisará este certificado avulso</p>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Arquivo do Certificado</h3>
                            
                            <div class="p-6 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 hover:border-blue-400 transition-colors">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <label for="arquivo_comprovante" class="cursor-pointer">
                                        <span class="text-lg font-medium text-blue-600 hover:text-blue-500">
                                            Clique para selecionar o arquivo
                                        </span>
                                        <input type="file" id="arquivo_comprovante" name="arquivo_comprovante" accept=".pdf,.jpg,.jpeg,.png" required class="hidden">
                                    </label>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Certificado da Atividade
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Formatos aceitos: PDF, JPG, JPEG, PNG (máx. 10MB)
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

                        <div class="flex flex-col sm:flex-row gap-4 justify-center pt-6">
                            <a href="home_aluno.php" 
                               class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200 text-center"
                               style="color: #0969DA">
                                ← Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 text-white bg-green-600 rounded-lg hover:bg-green-700 transition duration-200">
                                Enviar Certificado →
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
        


        // Variáveis globais
        const form = document.getElementById('formComprovante');
        const inputFile = document.getElementById('arquivo_comprovante');
        const preview = document.getElementById('arquivo-selecionado');
        const nomeArquivo = document.getElementById('nome-arquivo');
        const selectAtividade = document.getElementById('selectAtividade');
        const selectCoordenador = document.getElementById('selectCoordenador');

        // Carregar atividades aprovadas
        async function carregarAtividadesAprovadas() {
            try {
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/minhas_atividades.php');
                const data = await response.json();
                
                if (data.success) {
                    const atividadesAprovadas = data.data.filter(a => a.status === 'Aprovada');
                    
                    selectAtividade.innerHTML = '<option value="">-- Selecione uma atividade --</option>';
                    
                    if (atividadesAprovadas.length === 0) {
                        selectAtividade.innerHTML = '<option value="">Nenhuma atividade aprovada encontrada</option>';
                        selectAtividade.disabled = true;
                    } else {
                        atividadesAprovadas.forEach(atividade => {
                            const option = document.createElement('option');
                            option.value = atividade.id;
                            option.textContent = `${atividade.titulo} - ${atividade.carga_horaria_aprovada}h`;
                            selectAtividade.appendChild(option);
                        });
                    }
                } else {
                    console.error('Erro ao carregar atividades:', data.error);
                    selectAtividade.innerHTML = '<option value="">Erro ao carregar atividades</option>';
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                selectAtividade.innerHTML = '<option value="">Erro de conexão</option>';
            }
        }

        // Carregar coordenadores
        async function carregarCoordenadores() {
            try {
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/cadastrar_atividade_complementar.php?coordenadores=1');
                const data = await response.json();
                
                if (data.success) {
                    selectCoordenador.innerHTML = '<option value="">-- Selecione um coordenador --</option>';
                    
                    if (data.data.length === 0) {
                        selectCoordenador.innerHTML = '<option value="">Nenhum coordenador encontrado</option>';
                        selectCoordenador.disabled = true;
                    } else {
                        data.data.forEach(coordenador => {
                            const option = document.createElement('option');
                            option.value = coordenador.id;
                            option.textContent = `${coordenador.nome} - ${coordenador.curso_nome}`;
                            selectCoordenador.appendChild(option);
                        });
                    }
                } else {
                    console.error('Erro ao carregar coordenadores:', data.error);
                    selectCoordenador.innerHTML = '<option value="">Erro ao carregar coordenadores</option>';
                }
            } catch (error) {
                console.error('Erro na requisição de coordenadores:', error);
                selectCoordenador.innerHTML = '<option value="">Erro de conexão</option>';
            }
        }

        // Upload
        inputFile.addEventListener('change', e => {
            const file = e.target.files[0];
            if (!file) return;
            
            if (file.size > 10 * 1024 * 1024) {
                alert('O arquivo deve ter no máximo 10MB.');
                e.target.value = '';
                return;
            }
            
            nomeArquivo.textContent = file.name;
            preview.classList.remove('hidden');
        });

        // Remover arquivo
        window.removerArquivo = () => {
            inputFile.value = '';
            preview.classList.add('hidden');
        };

        // Submissão do formulário
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const btnText = submitBtn.innerHTML;
            
            const tipoEnvio = document.querySelector('input[name="tipo_envio"]:checked').value;
            const arquivo = inputFile.files[0];

            if (!arquivo) {
                alert('❌ Selecione um arquivo');
                return;
            }

            // Confirmação para certificado avulso
            if (tipoEnvio === 'avulso' && !confirm('Tem certeza que deseja enviar este certificado?')) {
                return;
            }

            // Confirmação para atividade aprovada
            if (tipoEnvio === 'atividade' && !confirm('Tem certeza que deseja enviar este certificado?')) {
                return;
            }

            // Preparar FormData
            const formData = new FormData();
            formData.append('acao', tipoEnvio === 'avulso' ? 'enviar_certificado_avulso' : 'enviar_certificado_processado');
            formData.append('arquivo_comprovante', arquivo);

            if (tipoEnvio === 'atividade') {
                const atividadeId = selectAtividade.value;
                const coordenadorId = selectCoordenador.value;
                
                if (!atividadeId) {
                    alert('❌ Selecione uma atividade');
                    return;
                }
                if (!coordenadorId) {
                    alert('❌ Selecione um coordenador');
                    return;
                }
                
                formData.append('atividade_id', atividadeId);
                formData.append('coordenador_id', coordenadorId);
            } else {
                const titulo = document.getElementById('tituloAvulso').value.trim();
                const horas = document.getElementById('horasAvulso').value;
                const coordenadorAvulso = selectCoordenadorAvulso.value;
                const observacao = document.getElementById('observacaoAvulso').value.trim();
                
                if (!titulo) {
                    alert('❌ Informe o título da atividade');
                    return;
                }
                if (!horas || horas <= 0) {
                    alert('❌ Informe a carga horária válida');
                    return;
                }
                if (!coordenadorAvulso) {
                    alert('❌ Selecione um coordenador');
                    return;
                }
                
                formData.append('titulo_avulso', titulo);
                formData.append('horas_avulso', horas);
                formData.append('coordenador_id', coordenadorAvulso);
                formData.append('observacao_avulso', observacao);
            }
            
            // Escolhe endpoint conforme tipo de envio
            const endpoint = tipoEnvio === 'avulso'
                ? '/Gerenciamento-ACC/backend/api/routes/enviar_certificado_avulso.php'
                : '/Gerenciamento-ACC/backend/api/routes/avaliar_atividade.php';

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando…';
            
            try {
                console.log('Enviando para endpoint:', endpoint);
                console.log('FormData contents:');
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }
                
                const response = await AuthClient.fetch(endpoint, {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Verificar se a resposta é JSON válida
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    console.error('Resposta não é JSON:', textResponse);
                    console.error('Status da resposta:', response.status);
                    throw new Error('Erro no servidor. Resposta: ' + textResponse.substring(0, 200));
                }
                
                const result = await response.json();
                console.log('Response JSON:', result);
                
                if (result.success) {
                    alert('✅ ' + result.message);
                    form.reset();
                    preview.classList.add('hidden');
                    camposAvulso.classList.add('hidden');
                    window.location.href = 'home_aluno.php';
                } else {
                    console.error('Erro retornado:', result);
                    alert('❌ ' + (result.error || 'Erro desconhecido'));
                    if (result.debug) {
                        console.log('Debug info:', result.debug);
                    }
                }
            } catch (error) {
                console.error('Erro ao enviar:', error);
                alert('❌ Erro ao enviar: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = btnText;
            }
        });

        const radioAtividade = document.getElementById('radioAtividade');
        const radioAvulso = document.getElementById('radioAvulso');
        const camposAtividade = document.getElementById('camposAtividade');
        const camposAvulso = document.getElementById('camposAvulso');
        const selectCoordenadorAvulso = document.getElementById('selectCoordenadorAvulso');

        // Função para alternar campos conforme tipo de envio
        function alternarTipoEnvio() {
            if (radioAvulso.checked) {
                // Esconder seção de atividade aprovada
                camposAtividade.classList.add('hidden');
                selectAtividade.disabled = true;
                selectCoordenador.disabled = true;
                selectAtividade.value = '';
                selectCoordenador.value = '';
                selectAtividade.removeAttribute('required');
                selectCoordenador.removeAttribute('required');
                
                // Mostrar campos avulso
                camposAvulso.classList.remove('hidden');
                document.getElementById('tituloAvulso').setAttribute('required', '');
                document.getElementById('horasAvulso').setAttribute('required', '');
                selectCoordenadorAvulso.setAttribute('required', '');
            } else {
                // Mostrar seção de atividade aprovada
                camposAtividade.classList.remove('hidden');
                selectAtividade.disabled = false;
                selectCoordenador.disabled = false;
                selectAtividade.setAttribute('required', '');
                selectCoordenador.setAttribute('required', '');
                
                // Esconder campos avulso
                camposAvulso.classList.add('hidden');
                document.getElementById('tituloAvulso').removeAttribute('required');
                document.getElementById('horasAvulso').removeAttribute('required');
                selectCoordenadorAvulso.removeAttribute('required');
                
                // Limpar campos avulso
                document.getElementById('tituloAvulso').value = '';
                document.getElementById('horasAvulso').value = '';
                document.getElementById('observacaoAvulso').value = '';
                selectCoordenadorAvulso.value = '';
            }
        }

        radioAtividade.addEventListener('change', alternarTipoEnvio);
        radioAvulso.addEventListener('change', alternarTipoEnvio);

        // Carregar coordenadores também no select avulso
        async function carregarCoordenadores() {
            try {
                const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/cadastrar_atividade_complementar.php?coordenadores=1');
                const data = await response.json();
                
                if (data.success) {
                    // Preencher select original
                    selectCoordenador.innerHTML = '<option value="">-- Selecione um coordenador --</option>';
                    // Preencher select avulso
                    selectCoordenadorAvulso.innerHTML = '<option value="">-- Selecione um coordenador --</option>';
                    
                    if (data.data.length === 0) {
                        selectCoordenador.innerHTML = '<option value="">Nenhum coordenador encontrado</option>';
                        selectCoordenadorAvulso.innerHTML = '<option value="">Nenhum coordenador encontrado</option>';
                        selectCoordenador.disabled = true;
                        selectCoordenadorAvulso.disabled = true;
                    } else {
                        data.data.forEach(coordenador => {
                            // Opção para select original
                            const option1 = document.createElement('option');
                            option1.value = coordenador.id;
                            option1.textContent = `${coordenador.nome} - ${coordenador.curso_nome}`;
                            selectCoordenador.appendChild(option1);
                            
                            // Opção para select avulso
                            const option2 = document.createElement('option');
                            option2.value = coordenador.id;
                            option2.textContent = `${coordenador.nome} - ${coordenador.curso_nome}`;
                            selectCoordenadorAvulso.appendChild(option2);
                        });
                    }
                } else {
                    console.error('Erro ao carregar coordenadores:', data.error);
                    selectCoordenador.innerHTML = '<option value="">Erro ao carregar coordenadores</option>';
                    selectCoordenadorAvulso.innerHTML = '<option value="">Erro ao carregar coordenadores</option>';
                }
            } catch (error) {
                console.error('Erro na requisição de coordenadores:', error);
                selectCoordenador.innerHTML = '<option value="">Erro de conexão</option>';
                selectCoordenadorAvulso.innerHTML = '<option value="">Erro de conexão</option>';
            }
        }

        // Carregar atividades e coordenadores ao inicializar
        document.addEventListener('DOMContentLoaded', () => {
            carregarAtividadesAprovadas();
            carregarCoordenadores();
        });
    </script>
</body>
</html>