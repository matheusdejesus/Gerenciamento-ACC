<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Comprovante - ACC</title>
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
                        <h1 class="text-3xl font-bold text-white mb-2">Enviar Comprovante</h1>
                        <p class="text-gray-300">Envie comprovantes adicionais para suas atividades ACC</p>
                    </div>
                </div>
                
                <div class="p-8">
                    <form id="formComprovante" class="space-y-6" enctype="multipart/form-data">
                        <div class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Selecionar Atividade</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Atividade ACC *</label>
                                <select name="atividade_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Selecione uma atividade --</option>
                                    <option value="1">Monitoria Acadêmica</option>
                                    <option value="2">Monitoria em Laboratório</option>
                                    <option value="3">Ensino</option>
                                    <option value="4">Pesquisa</option>
                                    <option value="5">Extensão</option>
                                    <option value="6">Estágio</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Selecione a atividade para qual deseja enviar o comprovante</p>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Tipo de Comprovante</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Categoria do Comprovante *</label>
                                    <select name="tipo_comprovante" required onchange="atualizarDescricao()"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- Selecione o tipo --</option>
                                        <option value="certificado">Certificado de Participação em Atividades</option>
                
                                    </select>
                                </div>
                                
                                <div id="campoOutro" class="hidden">
                                    <label class="block text-sm font-medium mb-2">Especificar Tipo *</label>
                                    <input type="text" name="tipo_outro" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Descreva o tipo do comprovante">
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Informações do Comprovante</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Data do Documento</label>
                                    <input type="date" name="data_documento"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Instituição Emissora</label>
                                    <input type="text" name="instituicao"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Ex: UFOPA, SEBRAE, etc.">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Observações</label>
                                <textarea name="observacoes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Informações adicionais sobre o comprovante (opcional)"></textarea>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-bold mb-4" style="color: #0969DA">Arquivo do Comprovante</h3>
                            
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
                                        Arquivo do Comprovante
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
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                            <h3 class="text-sm font-bold mb-3" style="color: #B45309">
                                ⚠️ Declaração de Veracidade
                            </h3>
                            <div class="flex items-start">
                                <input type="checkbox" id="termos" name="termos" required class="mt-1 mr-3">
                                <label for="termos" class="text-sm text-gray-700">
                                    Declaro que o comprovante apresentado é verdadeiro e autêntico, referente à atividade 
                                    selecionada. Estou ciente de que a apresentação de documentos falsos pode resultar em 
                                    penalidades acadêmicas.
                                </label>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center pt-6">
                            <a href="home_aluno.php" 
                               class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200 text-center"
                               style="color: #0969DA">
                                ← Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 text-white rounded-lg hover:opacity-90 transition duration-200 font-medium"
                                    style="background-color: #1A7F37">
                                Enviar Comprovante →
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Manipulação do upload de arquivo
        document.getElementById('arquivo_comprovante').addEventListener('change', function(e) {
            const arquivo = e.target.files[0];
            if (arquivo) {
                // Validar tamanho (10MB = 10 * 1024 * 1024 bytes)
                if (arquivo.size > 10 * 1024 * 1024) {
                    alert('O arquivo deve ter no máximo 10MB.');
                    e.target.value = '';
                    return;
                }
                
                // Mostrar arquivo selecionado
                document.getElementById('nome-arquivo').textContent = arquivo.name;
                document.getElementById('arquivo-selecionado').classList.remove('hidden');
            }
        });

        function removerArquivo() {
            document.getElementById('arquivo_comprovante').value = '';
            document.getElementById('arquivo-selecionado').classList.add('hidden');
        }

        function atualizarDescricao() {
            const select = document.querySelector('select[name="tipo_comprovante"]');
            const campoOutro = document.getElementById('campoOutro');
            const inputOutro = document.querySelector('input[name="tipo_outro"]');
            
            if (select.value === 'outro') {
                campoOutro.classList.remove('hidden');
                inputOutro.required = true;
            } else {
                campoOutro.classList.add('hidden');
                inputOutro.required = false;
                inputOutro.value = '';
            }
        }

        document.getElementById('formComprovante').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Verificar se o arquivo foi selecionado
            const arquivo = document.getElementById('arquivo_comprovante').files[0];
            if (!arquivo) {
                alert('Por favor, selecione o arquivo do comprovante.');
                return;
            }
            
            // Verificar se a atividade foi selecionada
            const atividade = document.querySelector('select[name="atividade_id"]').value;
            if (!atividade) {
                alert('Por favor, selecione uma atividade.');
                return;
            }
            
            // Verificar se o tipo foi selecionado
            const tipo = document.querySelector('select[name="tipo_comprovante"]').value;
            if (!tipo) {
                alert('Por favor, selecione o tipo do comprovante.');
                return;
            }
            
            // Confirmação
            if (confirm('Deseja realmente enviar este comprovante?\n\nApós o envio, ele será enviado para análise.')) {
                document.querySelector('button[type="submit"]').disabled = true;
                document.querySelector('button[type="submit"]').innerHTML = 'Enviando...';
                
                setTimeout(() => {
                    alert('✅ Comprovante enviado com sucesso!\n\nVocê receberá uma notificação quando ele for analisado.');
                    window.location.href = 'home_aluno.php';
                }, 1500);
            }
        });

        // Validação em tempo real
        document.querySelector('input[name="data_documento"]').addEventListener('change', function() {
            const hoje = new Date();
            const dataDoc = new Date(this.value);
            
            if (dataDoc > hoje) {
                alert('A data do documento não pode ser futura.');
                this.value = '';
            }
        });
    </script>
</body>
</html>