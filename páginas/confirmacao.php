<?php
session_start();
require 'config.php';
$uid = $_SESSION['uid_pending'] ?? 0;
if (!$uid) {
    header('Location: cadastro.php');
}
// Verifica o código
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $stmt = $mysqli->prepare(
        "SELECT id, expiracao, confirmado
         FROM EmailConfirm
         WHERE usuario_id = ? AND codigo = ?"
    );
    $stmt->bind_param("is", $uid, $codigo);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res && !$res['confirmado'] && $res['expiracao'] >= date('Y-m-d H:i:s')) {
        // Atualiza o status para confirmado no banco
        $mysqli->query(
            "UPDATE EmailConfirm
             SET confirmado = 1
             WHERE id = " . $res['id']
        );

        // Login usuário
        $_SESSION['user_id'] = $uid;
        unset($_SESSION['uid_pending']);

        // Redireciona conforme tipo
        $tipo = $mysqli->query("SELECT tipo FROM Usuario WHERE id = $uid")
                      ->fetch_object()->tipo;
        
        if ($tipo === 'aluno') {
            header('Location: home_aluno.php');
        } elseif ($tipo === 'coordenador') {
            header('Location: home_coordenador.php');
        } else {
            header('Location: home_orientador.php');
        }
        exit;
    } else {
        $error = "Código incorreto ou expirado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Confirmação de Email</title>
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
                        Confirmação de Email
                    </h2>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 p-4 rounded-md">
                        <p class="text-sm text-red-600"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="post" class="mt-8 space-y-6">
                    <div class="space-y-4">
                        <div>
                            <label for="codigo" class="block text-sm font-regular" style="color: #0969DA">
                                Digite o código de 6 dígitos:
                            </label>
                            <input id="codigo" 
                                   name="codigo" 
                                   type="text" 
                                   maxlength="6" 
                                   required 
                                   class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                                style="background-color: #1A7F37">
                            Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
