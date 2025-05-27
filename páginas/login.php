<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    //Busca o usuário pelo e-mail
    $stmt = $mysqli->prepare(
        "SELECT id, senha, tipo, nome
         FROM Usuario
         WHERE email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $u = $result->fetch_assoc();
    $stmt->close();

    // Verifica se o usuário existe e a senha está correta
    if ($u && password_verify($senha, $u['senha'])) {
        $stmt2 = $mysqli->prepare(
            "SELECT confirmado FROM EmailConfirm WHERE usuario_id = ? ORDER BY id DESC LIMIT 1"
        );
        $stmt2->bind_param("i", $u['id']);
        $stmt2->execute();
        $c = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();

        // Faz login se o e-mail estiver confirmado
        if ($c && $c['confirmado']) {
            // Inicia a sessão e armazena os dados do usuário
            $_SESSION['user_id']   = $u['id'];
            $_SESSION['user_nome'] = $u['nome'];
            $_SESSION['user_tipo'] = $u['tipo'];
            header("Location: home_{$u['tipo']}.php");
            exit;
        } else {
            $error = "E‑mail não confirmado.";
        }
    } else {
        $error = "Usuário ou senha inválidos.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 p-4 rounded-md">
                    <p class="text-sm text-red-600"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form method="post" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-regular text-gray-700" style="color: #0969DA">E-mail</label>
                        <input id="email" name="email" type="email" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>

                    <div>
                        <label for="senha" class="block text-sm font-bold text-gray-700" style="color: #0969DA">Senha</label>
                        <input id="senha" name="senha" type="password" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <a href="recuperarsenha.php" class="text-sm hover:underline" style="color: #0969DA">
                        Esqueceu sua senha?
                    </a>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" style="background-color: #1A7F37">
                        Entrar
                    </button>
                </div>

                <div class="text-center">
                    <a href="cadastro.php" class="text-sm hover:underline" style="color: #0969DA">
                        Não tem conta? Crie!
                    </a>
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