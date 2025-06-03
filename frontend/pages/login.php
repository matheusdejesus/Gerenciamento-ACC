<?php
session_start();

// Para limpar a sessão se necessário
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario'])) {
    $tipo = $_SESSION['usuario']['tipo'];
    
    switch ($tipo) {
        case 'aluno':
            header('Location: home_aluno.php');
            break;
        case 'coordenador':
            header('Location: home_coordenador.php');
            break;
        case 'orientador':
            header('Location: home_orientador.php');
            break;
        default:
            header('Location: index.php');
    }
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        //Requisição para a API de login
        $ch = curl_init('http://localhost/Gerenciamento-de-ACC/backend/api/routes/login.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => $email,
            'senha' => $senha
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        error_log("=== FRONTEND DEBUG ===");
        error_log("HTTP Code: " . $httpCode);
        error_log("Raw Response: " . $response);
        error_log("Response Length: " . strlen($response));

        if (empty($response)) {
            $erro = 'Resposta vazia da API';
        } else if (curl_errno($ch)) {
            $erro = 'Erro de conexão com o servidor: ' . curl_error($ch);
        } else {
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                
                if ($data === null) {
                    $erro = 'Erro ao decodificar JSON: ' . json_last_error_msg() . ' | Raw: ' . substr($response, 0, 500);
                } else if (isset($data['success']) && $data['success'] === true) {
                    $_SESSION['usuario'] = $data['usuario'];
                    
                    switch ($data['usuario']['tipo']) {
                        case 'aluno':
                            header('Location: home_aluno.php');
                            break;
                        case 'coordenador':
                            header('Location: home_coordenador.php');
                            break;
                        case 'orientador':
                            header('Location: home_orientador.php');
                            break;
                        default:
                            header('Location: index.php');
                    }
                    exit;
                } else {
                    $erro = isset($data['error']) ? $data['error'] : 'Resposta inválida da API';
                }
            } else if ($httpCode === 429) {
                $responseData = json_decode($response, true);
                if (isset($responseData['error'])) {
                    $erro = $responseData['error'];
                } else {
                    $erro = 'Muitas tentativas de login. Tente novamente em alguns minutos.';
                }
            } else {
                $responseData = json_decode($response, true);
                $erro = isset($responseData['error']) ? $responseData['error'] : 'HTTP Error: ' . $httpCode;
            }
        }
        
        curl_close($ch);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SACC UFOPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wght@0,200..900;1,200..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="bg-pattern font-montserrat min-h-screen flex flex-col">
    <nav class="bg-white shadow-lg fixed top-0 w-full z-50" style="background-color: #151B23">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="#" class="flex-shrink-0 flex items-center">
                        <span class="text-2xl font-regular" style="color: #FFFFFF">SACC</span>
                    </a>
                </div> 
            </div>
        </div>
    </nav>

    <div class="flex-grow pt-24 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" style="background-color: #0D1117">
        <div class="max-w-md w-full space-y-8 bg-white/90 p-8 rounded-xl shadow-md backdrop-blur-sm form-container" style="background-color: #F6F8FA">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                    Entrar
                </h2>
            </div>
            <?php if (!empty($erro)): ?>
                <div class="bg-red-50 p-4 rounded-md">
                    <div class="text-sm text-red-600">
                        <?php echo htmlspecialchars($erro); ?>
                        <?php if (strpos($erro, 'Tente novamente em') !== false): ?>
                            <div id="countdown" class="mt-2 font-mono text-red-700"></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Email</label>
                        <input type="email" name="email" id="email" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm">
                    </div>
                    <div>
                        <label for="senha" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Senha</label>
                        <input type="password" name="senha" id="senha" required
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm">
                    </div>
                </div>

                <div class="space-y-2">
                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Entrar
                        </button>
                    </div>
                    <div class="flex justify-center space-x-4">
                        <a href="cadastro.php" class="text-sm text-indigo-600 hover:text-indigo-500 hover:underline">
                            Criar conta
                        </a>
                        <a href="recuperar_senha.php" class="text-sm text-indigo-600 hover:text-indigo-500 hover:underline">
                            Esqueceu a senha?
                        </a>
                    </div>
                </div>
            </form>
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
    <script>
        const errorMessage = <?php echo json_encode($erro ?? ''); ?>;
        if (errorMessage.includes('Tente novamente em') && errorMessage.includes('minuto')) {
            const match = errorMessage.match(/(\d+) minuto/);
            if (match) {
                let minutosRestantes = parseInt(match[1]);
                let segundosRestantes = minutosRestantes * 60;
                
                const countdownElement = document.getElementById('countdown');
                if (countdownElement) {
                    const timer = setInterval(() => {
                        const minutos = Math.floor(segundosRestantes / 60);
                        const segundos = segundosRestantes % 60;
                        
                        countdownElement.textContent = `Tempo restante: ${minutos}:${segundos.toString().padStart(2, '0')}`;
                        
                        if (segundosRestantes <= 0) {
                            clearInterval(timer);
                            countdownElement.textContent = 'Você pode tentar fazer login novamente.';
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        }
                        
                        segundosRestantes--;
                    }, 1000);
                }
            }
        }
    </script>
</body>
</html>