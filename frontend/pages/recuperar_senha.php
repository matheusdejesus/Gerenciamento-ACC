<?php
session_start();
require_once __DIR__ . '/config.php';
require 'vendor/autoload.php';

ini_set('SMTP', 'smtp.gmail.com');
ini_set('smtp_port', '587');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generateToken($length = 25) {
    return bin2hex(random_bytes($length));
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $message = 'Por favor, informe um email válido.';
    } else {
        // Verifica se o email cadastrado existe na tabela "usuario"
        $stmt = $mysqli->prepare("SELECT id FROM usuario WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $token = generateToken();

            // Armazena o token na tabela "recuperar_senha"
            $stmtInsert = $mysqli->prepare("INSERT INTO recuperarsenha (usuario_id, token, criacao) VALUES (?, ?, NOW())");
            $stmtInsert->bind_param("is", $user['id'], $token);
            $stmtInsert->execute();
            $stmtInsert->close();

            // Cria o link de recuperação.
            $recoveryLink = "http://" . $_SERVER['HTTP_HOST'] . "/Gerenciamento-de-ACC/frontend/pages/alterar_senha.php?token=" . $token;
            $subject = "Recuperação de Senha";
            $body = "Olá,\n\nClique no link abaixo para recuperar sua senha:\n$recoveryLink\n\nSe você não solicitou a recuperação, ignore este email.";

            $mail = new PHPMailer(true);
            try {
                // Configurações do servidor SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sistemaacc2025@gmail.com';
                $mail->Password   = 'ehgg wzxq bsxt blab';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Remetente e destinatário
                $mail->setFrom('sistemaacc2025@gmail.com', 'SACC UFOPA');
                $mail->addAddress($email);

                // Conteúdo do e-mail
                $mail->isHTML(false);
                $mail->Subject = $subject;
                $mail->Body    = $body;

                $mail->send();
                $message = "Email enviado com sucesso. Verifique sua caixa de entrada.";
            } catch (Exception $e) {
                $message = "Falha ao enviar o email. Erro: " . $mail->ErrorInfo;
            }
        } else {
            $message = "Esse email não está cadastrado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
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
            <?php if ($message === "Email enviado com sucesso. Verifique sua caixa de entrada."): ?>
                <div class="bg-blue-50 p-4 rounded-md">
                    <p class="text-sm text-blue-600"><?php echo htmlspecialchars($message); ?></p>
                </div>
                <div class="mt-6">
                    <a href="login.php" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                       style="background-color: #0969DA">
                        Voltar
                    </a>
                </div>
            <?php else: ?>
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                        Recuperar Senha
                    </h2>
                </div>

                <?php if ($message): ?>
                    <div class="bg-blue-50 p-4 rounded-md">
                        <p class="text-sm text-blue-600"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                <?php endif; ?>

                <form action="recuperar_senha.php" method="post" class="mt-8 space-y-6">
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-regular" style="color: #0969DA">Digite seu email:</label>
                            <input type="email" name="email" id="email" required
                                   class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div>
                            <button type="submit" 
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                                    style="background-color: #1A7F37">
                                Enviar
                            </button>
                        </div>
                        <div>
                            <a href="login.php" 
                               class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                               style="background-color: #0969DA">
                                Voltar
                            </a>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
