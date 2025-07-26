<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Atividades - SACC UFOPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #0D1117;
            min-height: 100vh;
        }
        .bg-pattern {
            background: repeating-linear-gradient(
                135deg,
                #f6f8fa,
                #f6f8fa 20px,
                #eaecef 20px,
                #eaecef 40px
            );
        }
        .shadow-custom {
            box-shadow: 0 4px 24px 0 rgba(9, 105, 218, 0.10);
        }
    </style>
</head>
<body class="bg-pattern font-montserrat min-h-screen flex flex-col">
    <nav class="bg-white shadow-lg fixed top-0 w-full z-50" style="background-color: #151B23">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <span class="text-white text-xl font-semibold">SACC Admin</span>
            <a href="home_admin.php" class="text-white text-sm">Voltar</a>
        </div>
    </nav>
    <div class="flex-grow pt-24 flex" style="background-color: #0D1117">
        <div class="container mx-auto flex flex-col p-4">
            <main class="w-full p-6 rounded-lg shadow-custom bg-white">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold" style="color: #0969DA">Atividades Cadastradas</h1>
                    <a href="nova_atividade.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Adicionar Nova Atividade</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm mt-2">
                        <thead>
                            <tr>
                                <th class="px-2 py-2 text-left">ID</th>
                                <th class="px-2 py-2 text-left">Título</th>
                                <th class="px-2 py-2 text-left">Descrição</th>
                                <th class="px-2 py-2 text-left">Categoria</th>
                                <th class="px-2 py-2 text-left">Horas Máx</th>
                                <th class="px-2 py-2 text-left">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="atividades-tbody">
                            <tr>
                                <td colspan="6" class="text-center text-gray-500 py-4">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    <script src="../assets/js/auth.js"></script>
    <script>
    async function popularCategoriasSelect(selectedId) {
        const select = document.getElementById('edit-categoria');
        select.innerHTML = `<option value="">Carregando...</option>`;
        try {
            const resp = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/listar_categorias.php', {
                method: 'POST'
            });
            const data = await resp.json();
            if (data.success && Array.isArray(data.data)) {
                select.innerHTML = data.data.map(cat =>
                    `<option value="${cat.id}" ${cat.id == selectedId ? 'selected' : ''}>${cat.nome}</option>`
                ).join('');
            } else {
                select.innerHTML = `<option value="">Nenhuma categoria encontrada</option>`;
            }
        } catch (e) {
            select.innerHTML = `<option value="">Erro ao carregar categorias</option>`;
        }
    }

    // Função para remover atividade
    window.removerAtividade = async function(id, titulo) {
        console.log('Função removerAtividade chamada:', id, titulo);
        
        if (!confirm(`Tem certeza que deseja remover a atividade "${titulo}"?\n\nEsta ação não pode ser desfeita.`)) {
            return;
        }

        try {
            console.log('Enviando requisição DELETE...');
            
            const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/auditoria.php?acao=remover_atividade', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: parseInt(id) })
            });

            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('Response data:', data);

            if (data.success) {
                alert('Atividade removida com sucesso!');
                location.reload();
            } else {
                alert('Erro ao remover atividade: ' + (data.error || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Erro ao remover atividade:', error);
            alert('Erro ao remover atividade: ' + error.message);
        }
    };

    // Função para abrir modal de edição
    window.abrirModalEditar = async function(id) {
        const modal = document.getElementById('modalEditarAtividade');
        const atividade = atividadesCache.find(a => a.id == id);
        if (!atividade) return;

        document.getElementById('edit-id').value = atividade.id;
        document.getElementById('edit-titulo').value = atividade.nome || atividade.titulo;
        document.getElementById('edit-descricao').value = atividade.descricao || '';
        document.getElementById('edit-horas').value = atividade.horas_max || atividade.carga_horaria;
        await popularCategoriasSelect(atividade.categoria_id);

        const form = document.getElementById('formEditarAtividade');
        form.onsubmit = null;
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const id = document.getElementById('edit-id').value;
            const titulo = document.getElementById('edit-titulo').value;
            const descricao = document.getElementById('edit-descricao').value;
            const categoria_id = document.getElementById('edit-categoria').value;
            const carga_horaria = document.getElementById('edit-horas').value;

            if (!id || !titulo || !descricao || !categoria_id || !carga_horaria) {
                alert('Preencha todos os campos obrigatórios.');
                return;
            }

            try {
                console.log('Enviando dados:', { id, titulo, descricao, categoria_id, carga_horaria });
                
                const resp = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/editar_atividade.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id, titulo, descricao, categoria_id, carga_horaria })
                });
                
                const data = await resp.json();
                
                if (data.success) {
                    modal.classList.add('hidden');
                    alert('Alteração feita com sucesso!');
                    location.reload();
                } else {
                    alert(data.error || 'Erro ao salvar');
                }
            } catch (error) {
                console.error('Erro completo:', error);
                alert('Erro ao salvar: ' + error.message);
            }
        }, { once: true });

        modal.classList.remove('hidden');
    };

    document.addEventListener('DOMContentLoaded', async function() {
        console.log('=== PÁGINA CARREGADA ===');
        
        // Verifica autenticação
        if (!AuthClient.isLoggedIn()) {
            window.location.href = 'login.php';
            return;
        }

        const tbody = document.getElementById('atividades-tbody');
        try {
            console.log('Buscando atividades...');
            
            const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/listar_atividades.php');
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('Dados recebidos:', data);

            if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                window.atividadesCache = data.data;
                tbody.innerHTML = data.data.map(a => `
                    <tr>
                        <td class="px-2 py-2">${a.id}</td>
                        <td class="px-2 py-2">${a.nome || a.titulo}</td>
                        <td class="px-2 py-2">${a.descricao || ''}</td>
                        <td class="px-2 py-2">${a.categoria}</td>
                        <td class="px-2 py-2">${a.horas_max || a.carga_horaria}</td>
                        <td class="px-2 py-2">
                            <a href="#" onclick="abrirModalEditar(${a.id})" class="text-blue-600 hover:underline mr-2">Editar</a>
                            <button onclick="removerAtividade(${a.id}, '${(a.nome || a.titulo).replace(/'/g, "\\'")}');" class="text-red-600 hover:underline cursor-pointer border-none bg-transparent">Remover</button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-gray-500 py-4">Nenhuma atividade cadastrada.</td></tr>`;
            }
        } catch (e) {
            console.error('Erro ao carregar atividades:', e);
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-red-500 py-4">Erro ao carregar atividades: ${e.message}</td></tr>`;
        }

        // Configurar eventos dos modais
        const fecharModalEditar = document.getElementById('fecharModalEditar');
        const cancelarEditar = document.getElementById('cancelarEditar');
        const fecharModalAdicionar = document.getElementById('fecharModalAdicionar');
        const cancelarAdicionar = document.getElementById('cancelarAdicionar');
        const botaoAdicionar = document.querySelector('a[href="nova_atividade.php"]');
        const formAdicionar = document.getElementById('formAdicionarAtividade');

        console.log('Elementos encontrados:', {
            fecharModalEditar: !!fecharModalEditar,
            cancelarEditar: !!cancelarEditar,
            fecharModalAdicionar: !!fecharModalAdicionar,
            cancelarAdicionar: !!cancelarAdicionar,
            botaoAdicionar: !!botaoAdicionar,
            formAdicionar: !!formAdicionar
        });

        if (fecharModalEditar && cancelarEditar) {
            fecharModalEditar.onclick = cancelarEditar.onclick = function() {
                document.getElementById('modalEditarAtividade').classList.add('hidden');
            };
        }

        if (fecharModalAdicionar && cancelarAdicionar) {
            fecharModalAdicionar.onclick = cancelarAdicionar.onclick = function() {
                document.getElementById('modalAdicionarAtividade').classList.add('hidden');
            };
        }

        // Configurar botão de adicionar
        if (botaoAdicionar) {
            console.log('Configurando evento do botão adicionar...');
            botaoAdicionar.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('=== BOTÃO ADICIONAR CLICADO ===');
                const modal = document.getElementById('modalAdicionarAtividade');
                if (modal) {
                    console.log('Abrindo modal...');
                    modal.classList.remove('hidden');
                    popularCategoriasAdicionar();
                } else {
                    console.error('Modal não encontrado');
                }
            });
        } else {
            console.error('Botão adicionar não encontrado');
        }

        // Configurar formulário de adicionar
        if (formAdicionar) {
            console.log('Configurando evento do formulário...');
            formAdicionar.addEventListener('submit', async function(e) {
                e.preventDefault();
                console.log('=== FORMULÁRIO SUBMETIDO ===');
                
                const titulo = document.getElementById('add-titulo')?.value;
                const descricao = document.getElementById('add-descricao')?.value;
                const categoria_id = document.getElementById('add-categoria')?.value;
                const carga_horaria = document.getElementById('add-horas')?.value;

                console.log('Dados coletados:', { titulo, descricao, categoria_id, carga_horaria });

                if (!titulo || !descricao || !categoria_id || !carga_horaria) {
                    alert('Preencha todos os campos obrigatórios.');
                    return;
                }

                try {
                    console.log('Enviando requisição...');
                    
                    const formData = new FormData();
                    formData.append('acao', 'adicionar_atividade_disponivel');
                    formData.append('titulo', titulo);
                    formData.append('descricao', descricao);
                    formData.append('categoria_id', categoria_id);
                    formData.append('carga_horaria', carga_horaria);
                    
                    console.log('FormData criado:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key + ':', value);
                    }
                    
                    const resp = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/cadastrar_atividade_complementar.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    console.log('Status da resposta:', resp.status);
                    
                    const responseText = await resp.text();
                    console.log('Resposta bruta:', responseText);
                    
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('Erro ao fazer parse do JSON:', parseError);
                        throw new Error('Resposta inválida do servidor: ' + responseText);
                    }
                    
                    console.log('Resposta da API:', data);
                    
                    if (data.success) {
                        alert('Atividade adicionada com sucesso!');
                        document.getElementById('modalAdicionarAtividade').classList.add('hidden');
                        location.reload();
                    } else {
                        alert(data.error || 'Erro ao adicionar atividade');
                    }
                } catch (error) {
                    console.error('Erro completo:', error);
                    alert('Erro ao adicionar: ' + error.message);
                }
            });
        } else {
            console.error('Formulário não encontrado');
        }
    });

    // Função para popular categorias no modal de adicionar
    async function popularCategoriasAdicionar() {
        console.log('=== CARREGANDO CATEGORIAS PARA ADICIONAR ===');
        const select = document.getElementById('add-categoria');
        if (!select) {
            console.error('Elemento add-categoria não encontrado');
            return;
        }
        
        select.innerHTML = `<option value="">Carregando...</option>`;
        try {
            console.log('Fazendo requisição para listar categorias...');
            const resp = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/listar_categorias.php', {
                method: 'POST'
            });
            
            console.log('Status da resposta:', resp.status);
            const data = await resp.json();
            console.log('Dados das categorias:', data);
            
            if (data.success && Array.isArray(data.data)) {
                select.innerHTML = '<option value="">Selecione uma categoria</option>' + 
                    data.data.map(cat => `<option value="${cat.id}">${cat.nome}</option>`).join('');
                console.log('Categorias carregadas com sucesso');
            } else {
                console.error('Erro nos dados das categorias:', data);
                select.innerHTML = `<option value="">Nenhuma categoria encontrada</option>`;
            }
        } catch (e) {
            console.error('Erro ao carregar categorias:', e);
            select.innerHTML = `<option value="">Erro ao carregar categorias</option>`;
        }
    }
    </script>

<div id="modalAdicionarAtividade" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Adicionar Nova Atividade</h3>
            <button id="fecharModalAdicionar" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="formAdicionarAtividade">
            <div class="mb-4">
                <label for="add-titulo" class="block text-sm font-medium text-gray-700 mb-2">Título</label>
                <input type="text" id="add-titulo" name="titulo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div class="mb-4">
                <label for="add-descricao" class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                <textarea id="add-descricao" name="descricao" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" required></textarea>
            </div>
            
            <div class="mb-4">
                <label for="add-categoria" class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
                <select id="add-categoria" name="categoria_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Selecione uma categoria</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="add-horas" class="block text-sm font-medium text-gray-700 mb-2">Carga Horária</label>
                <input type="number" id="add-horas" name="carga_horaria" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="1" required>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" id="cancelarAdicionar" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Adicionar
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
