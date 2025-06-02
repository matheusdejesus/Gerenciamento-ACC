<?php
session_start();
require_once __DIR__ . '/config.php';

$message = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = 'Token inválido ou expirado.';
    $token_valid = false;
} else {
    // Verifica se o token é válido e não expirou (20 minutos)
    $stmt = $mysqli->prepare("SELECT usuario_id FROM recuperarsenha WHERE token = ? AND criacao > DATE_SUB(NOW(), INTERVAL 20 MINUTE)");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $recovery = $result->fetch_assoc();
    $stmt->close();

    if (!$recovery) {
        $message = 'Token inválido ou expirado.';
        $token_valid = false;
    } else {
        $token_valid = true;
        $usuario_id = $recovery['usuario_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if (empty($nova_senha) || empty($confirmar_senha)) {
        $message = 'Por favor, preencha todos os campos.';
    } elseif (strlen($nova_senha) < 6) {
        $message = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($nova_senha !== $confirmar_senha) {
        $message = 'As senhas não coincidem.';
    } else {
        // Atualiza a senha do usuário
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE usuario SET senha = ? WHERE id = ?");
        $stmt->bind_param("si", $senha_hash, $usuario_id);
        $stmt->execute();
        $stmt->close();

        // Remove o token usado
        $stmt = $mysqli->prepare("DELETE FROM recuperarsenha WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();

        $message = 'Senha alterada com sucesso!';
        $senha_alterada = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha - SACC UFOPA</title>
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
            
            <?php if (isset($senha_alterada) && $senha_alterada): ?>
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                        Senha Alterada!
                    </h2>
                </div>
                <div class="bg-green-50 p-4 rounded-md">
                    <p class="text-sm text-green-600"><?php echo htmlspecialchars($message); ?></p>
                </div>
                <div class="mt-6">
                    <a href="login.php" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                       style="background-color: #0969DA">
                        Fazer Login
                    </a>
                </div>

            <?php elseif (!$token_valid): ?>
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                        Link Inválido
                    </h2>
                </div>
                <div class="bg-red-50 p-4 rounded-md">
                    <p class="text-sm text-red-600"><?php echo htmlspecialchars($message); ?></p>
                </div>
                <div class="mt-6">
                    <a href="recuperar_senha.php" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                       style="background-color: #0969DA">
                        Solicitar Novo Link
                    </a>
                </div>

            <?php else: ?>
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                        Nova Senha
                    </h2>
                </div>

                <?php if ($message): ?>
                    <div class="bg-red-50 p-4 rounded-md">
                        <p class="text-sm text-red-600"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="mt-8 space-y-6">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="nova_senha" class="block text-sm font-regular" style="color: #0969DA">Nova Senha:</label>
                            <input type="password" name="nova_senha" id="nova_senha" required
                                   class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]"
                                   minlength="6">
                        </div>
                        
                        <div>
                            <label for="confirmar_senha" class="block text-sm font-regular" style="color: #0969DA">Confirmar Senha:</label>
                            <input type="password" name="confirmar_senha" id="confirmar_senha" required
                                   class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]"
                                   minlength="6">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div>
                            <button type="submit" 
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                                    style="background-color: #1A7F37">
                                Alterar Senha
                            </button>
                        </div>
                        
                        <div>
                            <a href="login.php" 
                               class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                               style="background-color: #0969DA">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>