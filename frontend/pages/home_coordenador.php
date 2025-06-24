<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Coordenador - SACC UFOPA</title>
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
                    <span id="nomeUsuario" class="text-white mr-4 font-extralight">Carregando...</span>
                    <button onclick="AuthClient.logout()" class="text-white hover:text-gray-200">Logout</button>
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
                        <p class="text-3xl font-bold" style="color: #B45309">0</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Certificados</h3>
                        <p class="text-3xl font-bold" style="color: #1A7F37">0</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Rejeitados</h3>
                        <p class="text-3xl font-bold" style="color: #DA1A3A">0</p>
                    </div>
                    <div class="p-6 rounded-lg shadow-sm" style="background-color: #FFFFFF">
                        <h3 class="text-lg font-regular mb-2" style="color: #0969DA">Horas Certificadas</h3>
                        <p class="text-3xl font-bold" style="color: #1A7F37">0</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                    <div class="p-4" style="background-color: #151B23">
                        <h3 class="text-xl font-bold text-white">Certificados Pendentes</h3>
                    </div>
                    <div class="overflow-x-auto">
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
                            <tbody id="tabelaCertificadosPendentes" class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                        <div id="mensagemVaziaPendentes" class="p-8 text-center text-gray-500 hidden">
                            <p class="text-lg">Não há certificados pendentes no momento.</p>
                        </div>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Horas Contabilizadas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #0969DA">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaCertificadosProcessados" class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                        <div id="mensagemVaziaProcessados" class="p-8 text-center text-gray-500 hidden">
                            <p class="text-lg">Nenhum certificado processado ainda.</p>
                        </div>
                    </div>
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

    <script src="../assets/js/auth.js"></script>
    <script>
        // Verificar autenticação JWT
        if (!AuthClient.isLoggedIn()) {
            window.location.href = 'login.php';
        }
        
        const user = AuthClient.getUser();
        if (user.tipo !== 'coordenador') {
            AuthClient.logout();
        }
        
        // Atualizar nome do usuário na interface
        if (user && user.nome) {
            document.getElementById('nomeUsuario').textContent = user.nome;
        }

        // Carregar dados iniciais
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Página coordenador carregada, usuário:', user);
            carregarCertificadosPendentes();
            carregarHistoricoCertificados();
        });

        function carregarCertificadosPendentes() {
            console.log('Carregando certificados pendentes...');
        }

        function carregarHistoricoCertificados() {
            console.log('Carregando histórico de certificados...');
        }
    </script>
</body>
</html>
