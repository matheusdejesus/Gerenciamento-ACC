<?php
// Dashboard de 240 horas para alunos com matrículas 2017-2022
?>

<div id="dashboard240h" class="mb-8 hidden">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold" style="color: #0969DA">
                <i class="fas fa-clock mr-2"></i>
                Limite de 240 Horas - Atividades Extracurriculares
            </h3>
            <div class="text-sm text-gray-500">
                Matrícula: <span id="matriculaAluno" class="font-medium"></span>
            </div>
        </div>

        <!-- Barra de Progresso Principal -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Progresso Total</span>
                <span id="progressoTexto" class="text-sm font-bold">0h / 240h</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div id="barraProgresso" class="h-4 rounded-full transition-all duration-500" style="width: 0%; background-color: #1A7F37;"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>0h</span>
                <span>120h</span>
                <span>240h</span>
            </div>
        </div>

        <!-- Status e Alertas -->
        <div id="statusContainer" class="mb-6">
            <div id="statusNormal" class="hidden p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-green-800 font-medium">Dentro do limite permitido</span>
                </div>
                <p class="text-green-700 text-sm mt-1">Você ainda pode cadastrar mais atividades.</p>
            </div>

            <div id="statusAtencao" class="hidden p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                    <span class="text-yellow-800 font-medium">Atenção: Próximo do limite</span>
                </div>
                <p class="text-yellow-700 text-sm mt-1">Você está próximo do limite de 240 horas. Planeje suas próximas atividades.</p>
            </div>

            <div id="statusLimite" class="hidden p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-times-circle text-red-500 mr-2"></i>
                    <span class="text-red-800 font-medium">Limite excedido</span>
                </div>
                <p class="text-red-700 text-sm mt-1">Você excedeu o limite de 240 horas. Novas atividades podem não ser aceitas.</p>
            </div>
        </div>

        <!-- Estatísticas por Categoria -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <!-- ACC -->
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-blue-800">ACC</h4>
                    <span id="horasACC" class="text-lg font-bold text-blue-600">0h</span>
                </div>
                <div class="w-full bg-blue-200 rounded-full h-2 mb-1">
                    <div id="barraProgressoACC" class="h-2 rounded-full transition-all duration-500" style="width: 0%; background-color: #3B82F6;"></div>
                </div>
                <div class="flex justify-between text-xs text-blue-600">
                    <span id="progressoTextoACC">0h / 80h</span>
                    <span id="porcentagemACC">0%</span>
                </div>
            </div>

            <!-- Ensino -->
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-green-800">Ensino</h4>
                    <span id="horasEnsino" class="text-lg font-bold text-green-600">0h</span>
                </div>
                <div class="w-full bg-green-200 rounded-full h-2 mb-1">
                    <div id="barraProgressoEnsino" class="h-2 rounded-full transition-all duration-500" style="width: 0%; background-color: #10B981;"></div>
                </div>
                <div class="flex justify-between text-xs text-green-600">
                    <span id="progressoTextoEnsino">0h / 80h</span>
                    <span id="porcentagemEnsino">0%</span>
                </div>
            </div>

            <!-- Pesquisa -->
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-purple-800">Pesquisa</h4>
                    <span id="horasPesquisa" class="text-lg font-bold text-purple-600">0h</span>
                </div>
                <div class="w-full bg-purple-200 rounded-full h-2 mb-1">
                    <div id="barraProgressoPesquisa" class="h-2 rounded-full transition-all duration-500" style="width: 0%; background-color: #8B5CF6;"></div>
                </div>
                <div class="flex justify-between text-xs text-purple-600">
                    <span id="progressoTextoPesquisa">0h / 80h</span>
                    <span id="porcentagemPesquisa">0%</span>
                </div>
            </div>

            <!-- Estágio -->
            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-orange-800">Estágio</h4>
                    <span id="horasEstagio" class="text-lg font-bold text-orange-600">0h</span>
                </div>
                <div class="w-full bg-orange-200 rounded-full h-2 mb-1">
                    <div id="barraProgressoEstagio" class="h-2 rounded-full transition-all duration-500" style="width: 0%; background-color: #F97316;"></div>
                </div>
                <div class="flex justify-between text-xs text-orange-600">
                    <span id="progressoTextoEstagio">0h / 100h</span>
                    <span id="porcentagemEstagio">0%</span>
                </div>
            </div>

            <!-- Ação Social -->
            <div class="bg-pink-50 p-4 rounded-lg border border-pink-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-pink-800">Ação Social</h4>
                    <span id="horasAcaoSocial" class="text-lg font-bold text-pink-600">0h</span>
                </div>
                <div class="w-full bg-pink-200 rounded-full h-2 mb-1">
                    <div id="barraProgressoAcaoSocial" class="h-2 rounded-full transition-all duration-500" style="width: 0%; background-color: #EC4899;"></div>
                </div>
                <div class="flex justify-between text-xs text-pink-600">
                    <span id="progressoTextoAcaoSocial">0h / 30h</span>
                    <span id="porcentagemAcaoSocial">0%</span>
                </div>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Informações Importantes</h4>
            <ul class="text-xs text-gray-600 space-y-1">
                <li>• Este limite se aplica apenas a alunos com matrícula entre 2017-2022</li>
                <li>• O limite total é de 240 horas distribuídas entre as categorias</li>
                <li>• ACC: máximo 80h | Ensino: máximo 80h | Pesquisa: máximo 80h</li>
                <li>• Estágio: máximo 100h | Ação Social: máximo 30h</li>
            </ul>
        </div>
    </div>
</div>

<script>
    // Função para carregar dados do dashboard 240h
    async function carregarDashboard240h() {
        console.log('🔄 Iniciando carregamento do dashboard 240h...');
        console.log('🔍 Verificando AuthClient...');
        
        // Verificar se AuthClient existe
        if (typeof AuthClient === 'undefined') {
            console.error('❌ AuthClient não está definido!');
            return;
        }
        
        console.log('✅ AuthClient existe');

        try {
            const usuario = AuthClient.getUser();
            console.log('👤 Usuário obtido:', usuario);
            console.log('🔍 Tipo do usuário:', typeof usuario);
            console.log('🔍 Usuario é null?', usuario === null);
            console.log('🔍 Usuario é undefined?', usuario === undefined);

            if (!usuario || !usuario.id) {
                console.log('❌ Usuário não logado, ocultando dashboard 240h');
                console.log('🔍 Motivo: usuario =', usuario, ', usuario.id =', usuario ? usuario.id : 'N/A');
                
                // TEMPORÁRIO: Para teste, vamos simular um usuário logado
                console.log('🧪 TESTE: Simulando usuário logado para debug...');
                const usuarioTeste = { id: 1, nome: 'Teste' };
                console.log('🧪 Usuário de teste:', usuarioTeste);
                
                // Continuar com o usuário de teste
                await carregarDashboardComUsuario(usuarioTeste);
                return;
            }

            await carregarDashboardComUsuario(usuario);

        } catch (error) {
            console.error('❌ Erro ao carregar dashboard 240h:', error);
            console.error('📍 Stack trace:', error.stack);
        }
    }
    
    // Função auxiliar para carregar dashboard com usuário específico
    async function carregarDashboardComUsuario(usuario) {
        console.log('📡 Fazendo requisição para API calcular_horas_categorias.php...');
        console.log('📤 Dados enviados:', {
            aluno_id: usuario.id
        });

        // Fazer requisição para obter dados das horas
        const response = await AuthClient.fetch('../../backend/api/routes/calcular_horas_categorias.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                aluno_id: usuario.id
            })
        });

        console.log('📥 Resposta da API recebida:', response.status, response.statusText);

        const data = await response.json();
        console.log('📊 Dados recebidos da API:', data);

        if (data.success && data.data) {
            const horasData = data.data;
            console.log('✅ Dados processados com sucesso:', horasData);

            // Verificar se o aluno é elegível (matrícula 2017-2022)
            if (!horasData.elegivel) {
                console.log('❌ Aluno não elegível para limite 240h (matrícula:', horasData.matricula, '), ocultando dashboard');
                document.getElementById('dashboard240h').classList.add('hidden');
                return;
            }

            console.log('✅ Aluno elegível! Mostrando dashboard...');
            // Mostrar dashboard
            document.getElementById('dashboard240h').classList.remove('hidden');

            // Atualizar informações básicas
            document.getElementById('matriculaAluno').textContent = horasData.matricula || '';

            // Atualizar progresso total
            const horasTotal = horasData.total_horas || 0;
            const porcentagem = Math.min((horasTotal / 240) * 100, 100);

            document.getElementById('progressoTexto').textContent = `${horasTotal}h / 240h`;
            document.getElementById('barraProgresso').style.width = `${porcentagem}%`;

            // Atualizar cor da barra baseada no progresso
            const barraProgresso = document.getElementById('barraProgresso');
            if (horasTotal >= 240) {
                barraProgresso.style.backgroundColor = '#10B981'; // Verde (era vermelho)
            } else if (horasTotal >= 200) {
                barraProgresso.style.backgroundColor = '#D97706'; // Amarelo/Laranja
            } else {
                barraProgresso.style.backgroundColor = '#1A7F37'; // Verde
            }

            // Atualizar status
            document.querySelectorAll('#statusContainer > div').forEach(div => div.classList.add('hidden'));

            if (horasTotal >= 240) {
                document.getElementById('statusLimite').classList.remove('hidden');
            } else if (horasTotal >= 200) {
                document.getElementById('statusAtencao').classList.remove('hidden');
            } else {
                document.getElementById('statusNormal').classList.remove('hidden');
            }

            // Atualizar estatísticas por categoria - usando os nomes corretos da API
            const categorias = horasData.categorias || {};
            document.getElementById('horasACC').textContent = `${categorias.acc || 0}h`;
            document.getElementById('horasEnsino').textContent = `${categorias.ensino || 0}h`;
            document.getElementById('horasPesquisa').textContent = `${categorias.pesquisa || 0}h`;
            document.getElementById('horasEstagio').textContent = `${categorias.estagio || 0}h`;
            document.getElementById('horasAcaoSocial').textContent = `${categorias.acao_social || 0}h`;

            // Atualizar barras de progresso individuais
            atualizarBarraProgresso('ACC', categorias.acc || 0, 80);
            atualizarBarraProgresso('Ensino', categorias.ensino || 0, 80);
            atualizarBarraProgresso('Pesquisa', categorias.pesquisa || 0, 80);
            atualizarBarraProgresso('Estagio', categorias.estagio || 0, 100);
            atualizarBarraProgresso('AcaoSocial', categorias.acao_social || 0, 30);

        } else {
            console.log('❌ Erro ao carregar dados do dashboard 240h:', data.error || 'Erro desconhecido');
            console.log('📋 Resposta completa:', data);
            document.getElementById('dashboard240h').classList.add('hidden');
        }
    }

    // Função para atualizar barras de progresso individuais
    function atualizarBarraProgresso(categoria, horasAtuais, metaHoras) {
        const porcentagem = Math.min((horasAtuais / metaHoras) * 100, 100);

        // Atualizar texto do progresso
        document.getElementById(`progressoTexto${categoria}`).textContent = `${horasAtuais}h / ${metaHoras}h`;
        document.getElementById(`porcentagem${categoria}`).textContent = `${Math.round(porcentagem)}%`;

        // Atualizar largura da barra
        const barra = document.getElementById(`barraProgresso${categoria}`);
        barra.style.width = `${porcentagem}%`;

        // Definir cor baseada na porcentagem
        let cor;
        if (porcentagem < 70) {
            // Verde para menos de 70%
            cor = getCategoriaColor(categoria, 'green');
        } else if (porcentagem < 90) {
            // Amarelo para 70-90%
            cor = getCategoriaColor(categoria, 'yellow');
        } else {
            // Verde para mais de 90% (era vermelho)
            cor = getCategoriaColor(categoria, 'green');
        }

        barra.style.backgroundColor = cor;
    }

    // Função para obter cores específicas por categoria
    function getCategoriaColor(categoria, tipo) {
        const cores = {
            'ACC': {
                'green': '#3B82F6', // Azul
                'yellow': '#F59E0B', // Amarelo
                'red': '#10B981' // Verde (era vermelho)
            },
            'Ensino': {
                'green': '#10B981', // Verde
                'yellow': '#F59E0B', // Amarelo
                'red': '#10B981' // Verde (era vermelho)
            },
            'Pesquisa': {
                'green': '#8B5CF6', // Roxo
                'yellow': '#F59E0B', // Amarelo
                'red': '#10B981' // Verde (era vermelho)
            },
            'Estagio': {
                'green': '#F97316', // Laranja
                'yellow': '#F59E0B', // Amarelo
                'red': '#10B981' // Verde (era vermelho)
            },
            'AcaoSocial': {
                'green': '#EC4899', // Rosa
                'yellow': '#F59E0B', // Amarelo
                'red': '#10B981' // Verde (era vermelho)
            }
        };

        return cores[categoria] ? cores[categoria][tipo] : '#6B7280'; // Cinza como fallback
    }

    // Executar quando a página carregar
    document.addEventListener('DOMContentLoaded', function() {
        // Aguardar um pouco para garantir que o AuthClient esteja pronto
        setTimeout(carregarDashboard240h, 500);
    });
</script>