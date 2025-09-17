<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Admin - SACC UFOPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wght@0,200..900;1,200..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        .bg-pattern { background-color: #0D1117; }
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
                    <a href="configuracoes_admin.php" class="block p-3 rounded text-[#0969DA] hover:bg-gray-200 transition duration-200">
                        Configurações da Conta
                    </a>
                </nav>
            </aside>
            <main class="lg:w-3/4 p-6 rounded-lg" style="background-color: #F6F8FA">
                <div class="mb-8">
                    <h2 class="text-3xl font-extralight mb-2" style="color: #0969DA">
                        Olá, <span id="nomeUsuarioMain">Carregando...</span>
                    </h2>
                    <p class="text-gray-600">Bem-vindo ao painel administrativo do SACC UFOPA.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-6 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 transition flex-1" style="background-color: #FFFFFF" id="cardUsuarios">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Usuários Cadastrados</h3>
                        <p class="text-3xl font-bold" id="totalUsuarios">-</p>
                        <p class="text-xs text-gray-500 mt-1">Total de usuários no sistema</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 transition flex-1" style="background-color: #FFFFFF" id="cardAuditoria">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Auditoria</h3>
                        <p class="text-xs text-gray-500 mt-1">Ver ações dos usuários</p>
                    </div>
                    <a href="editar_atividades.php" class="p-6 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 transition flex-1 block" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Editar Atividades</h3>
                        <p class="text-xs text-gray-500 mt-1">Gerenciar atividades disponíveis</p>
                    </a>
                </div>
            </main>
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
    <div id="modalAuditoria" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 relative">
            <button id="fecharModalAuditoria" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl">&times;</button>
            <h2 class="text-2xl font-semibold mb-4" style="color: #0969DA">Ações dos Usuários</h2>
            <div id="auditoriaLogs" class="max-h-96 overflow-y-auto">
                <p class="text-gray-500 text-center">Carregando...</p>
            </div>
        </div>
    </div>
    <div id="modalUsuarios" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
            <button id="fecharModalUsuarios" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl">&times;</button>
            <h2 class="text-2xl font-semibold mb-4" style="color: #0969DA">Usuários Cadastrados</h2>
            <div id="usuariosLista" class="max-h-96 overflow-y-auto">
                <p class="text-gray-500 text-center">Carregando...</p>
            </div>
        </div>
    </div>
    <script src="https://fonts.googleapis.com/icon?family=Material+Icons"></script>
    <script src="../assets/js/auth.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar autenticação JWT
            if (!AuthClient.isLoggedIn()) {
                window.location.href = 'login.php';
            }
            const user = AuthClient.getUser();
            if (user.tipo !== 'admin') {
                AuthClient.logout();
            }
            // Atualizar nome do usuário na interface
            if (user && user.nome) {
                document.getElementById('nomeUsuario').textContent = user.nome;
                document.getElementById('nomeUsuarioMain').textContent = user.nome;
            }

            // Buscar estatísticas
            async function carregarEstatisticas() {
                try {
                    const response = await AuthClient.fetch('../../backend/api/routes/listar_usuarios.php');
                    const data = await response.json();
                    if (data.success && Array.isArray(data.data)) {
                        document.getElementById('totalUsuarios').textContent = data.data.length;
                    } else {
                        document.getElementById('totalUsuarios').textContent = '-';
                    }
                } catch (error) {
                    document.getElementById('totalUsuarios').textContent = '-';
                }
            }
            carregarEstatisticas();

            // Auditoria: abrir modal e buscar logs
            const cardAuditoria = document.getElementById('cardAuditoria');
            const modalAuditoria = document.getElementById('modalAuditoria');
            const fecharModalAuditoria = document.getElementById('fecharModalAuditoria');
            const auditoriaLogs = document.getElementById('auditoriaLogs');

            cardAuditoria.addEventListener('click', async function() {
                modalAuditoria.classList.remove('hidden');
                auditoriaLogs.innerHTML = '<p class="text-gray-500 text-center">Carregando...</p>';
                try {
                    const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/auditoria.php');
                    const data = await response.json();
                    const logs = data.success ? data.data : data;
                    
                    if (Array.isArray(logs) && logs.length > 0) {
                        auditoriaLogs.innerHTML = `
                            <table class="min-w-full text-xs">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-1 text-left">Usuário</th>
                                        <th class="px-2 py-1 text-left">Ação</th>
                                        <th class="px-2 py-1 text-left">Descrição</th>
                                        <th class="px-2 py-1 text-left">Data/Hora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${logs.map(log => `
                                        <tr>
                                            <td class="border-t px-2 py-1">${log.nome_usuario || log.usuario_nome || log.usuario?.nome || '-'}</td>
                                            <td class="border-t px-2 py-1">${log.acao.toUpperCase()}</td>
                                            <td class="border-t px-2 py-1">${log.descricao || '-'}</td>
                                            <td class="border-t px-2 py-1">${log.data_hora}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        `;
                    } else {
                        auditoriaLogs.innerHTML = '<p class="text-gray-500 text-center">Nenhuma ação encontrada.</p>';
                    }
                } catch (error) {
                    console.error('Erro detalhado:', error);
                    auditoriaLogs.innerHTML = '<p class="text-red-500 text-center">Erro ao carregar logs.</p>';
                }
            });

            fecharModalAuditoria.addEventListener('click', function() {
                modalAuditoria.classList.add('hidden');
            });

            // Usuários: abrir modal e buscar lista de usuários
            const cardUsuarios = document.getElementById('cardUsuarios');
            const modalUsuarios = document.getElementById('modalUsuarios');
            const fecharModalUsuarios = document.getElementById('fecharModalUsuarios');
            const usuariosLista = document.getElementById('usuariosLista');

            cardUsuarios.addEventListener('click', async function() {
                modalUsuarios.classList.remove('hidden');
                usuariosLista.innerHTML = '<p class="text-gray-500 text-center">Carregando...</p>';
                try {
                    const response = await AuthClient.fetch('/Gerenciamento-ACC/backend/api/routes/listar_usuarios.php');
                    const data = await response.json();
                    if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                        usuariosLista.innerHTML = `
                            <table class="min-w-full text-xs">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-1 text-left">ID</th>
                                        <th class="px-2 py-1 text-left">Nome</th>
                                        <th class="px-2 py-1 text-left">Email</th>
                                        <th class="px-2 py-1 text-left">Tipo</th>
                                        <th class="px-2 py-1 text-left">Matrícula/SIAPE</th>
                                        <th class="px-2 py-1 text-left">Curso ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.data.map(u => `
                                        <tr>
                                            <td class="border-t px-2 py-1">${u.id}</td>
                                            <td class="border-t px-2 py-1">${u.nome}</td>
                                            <td class="border-t px-2 py-1">${u.email}</td>
                                            <td class="border-t px-2 py-1">${u.tipo}</td>
                                            <td class="border-t px-2 py-1">${u.matricula || u.siape || '-'}</td>
                                            <td class="border-t px-2 py-1">${u.curso_id || '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        `;
                    } else {
                        usuariosLista.innerHTML = '<p class="text-gray-500 text-center">Nenhum usuário encontrado.</p>';
                    }
                } catch (error) {
                    usuariosLista.innerHTML = '<p class="text-red-500 text-center">Erro ao carregar usuários.</p>';
                }
            });

            fecharModalUsuarios.addEventListener('click', function() {
                modalUsuarios.classList.add('hidden');
            });
        });
    </script>
</body>
</html>