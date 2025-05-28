<?php
session_start();
require_once __DIR__ . '/../../backend/api/config/config.php';
require_once __DIR__ . '/../../backend/api/config/database.php';

// Se já estiver logado, redireciona para a página apropriada
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
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $db = Database::getInstance();
        
        // Verificar tentativas de login
        $ip = $_SERVER['REMOTE_ADDR'];
        $sql = "SELECT COUNT(*) as tentativas FROM TentativasLogin 
                WHERE email = ? AND ip_address = ? 
                AND data_hora > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                AND sucesso = 0";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $email, $ip);
        $stmt->execute();
        $result = $stmt->get_result();
        $tentativas = $result->fetch_assoc()['tentativas'];

        if ($tentativas >= 5) {
            $erro = 'Muitas tentativas de login. Tente novamente em 15 minutos.';
        } else {
            // Registrar tentativa de login
            $sql = "INSERT INTO TentativasLogin (email, ip_address) VALUES (?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param('ss', $email, $ip);
            $stmt->execute();

            // Fazer requisição para a API
            $ch = curl_init('http://localhost/Gerenciamento-de-ACC/backend/api/usuarios/login');
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
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                $_SESSION['usuario'] = $data['usuario'];
                
                // Registrar login bem-sucedido
                $sql = "UPDATE TentativasLogin SET sucesso = 1 
                        WHERE email = ? AND ip_address = ? 
                        ORDER BY data_hora DESC LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->bind_param('ss', $email, $ip);
                $stmt->execute();

                // Redirecionar para a página apropriada
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
                $erro = 'Email ou senha inválidos.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gerenciamento de ACC</title>
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
                    Login
                </h2>
            </div>

            <?php if ($erro): ?>
                <div class="bg-red-50 p-4 rounded-md">
                    <p class="text-sm text-red-600"><?php echo htmlspecialchars($erro); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Email</label>
                        <input type="email" name="email" id="email" required
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>

                    <div>
                        <label for="senha" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Senha</label>
                        <input type="password" name="senha" id="senha" required
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>
                </div>

                <div class="space-y-2">
                    <div>
                        <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                            style="background-color: #1A7F37">
                            Entrar
                        </button>
                    </div>
                    
                    <div class="text-center space-x-4">
                        <a href="cadastro.php" class="text-sm" style="color: #0969DA">Criar conta</a>
                        <a href="recuperarsenha.php" class="text-sm" style="color: #0969DA">Esqueceu sua senha?</a>
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
</body>
</html>