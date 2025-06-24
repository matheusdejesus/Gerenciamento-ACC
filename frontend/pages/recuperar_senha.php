<?php
session_start();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $message = 'Por favor, informe um email válido.';
        $messageType = 'error';
    } else {
        // Fazer requisição para a API de recuperação de senha
        $data = ['email' => $email];
        
        $ch = curl_init('http://localhost/Gerenciamento-de-ACC/backend/api/routes/recuperar_senha.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // Debug: Log da requisição
        error_log("=== RECUPERAR SENHA DEBUG ===");
        error_log("HTTP Code: " . $httpCode);
        error_log("cURL Error: " . $curl_error);
        error_log("Raw Response: " . $response);
        error_log("Response Length: " . strlen($response));

        if ($curl_error) {
            $message = "Erro de conexão: " . $curl_error;
            $messageType = 'error';
        } elseif ($response === false) {
            $message = "Falha na requisição para a API.";
            $messageType = 'error';
        } else {
            $responseData = json_decode($response, true);
            
            if ($responseData === null) {
                // Mostrar mais detalhes do erro JSON
                $json_error = json_last_error_msg();
                $message = "Resposta inválida da API. Erro JSON: " . $json_error . ". Resposta: " . substr($response, 0, 200);
                $messageType = 'error';
                error_log("JSON Error: " . $json_error);
            } elseif ($httpCode === 200) {
                if (isset($responseData['success']) && $responseData['success'] === true) {
                    $message = $responseData['message'] ?? "Email de recuperação enviado com sucesso. Verifique sua caixa de entrada.";
                    $messageType = 'success';
                } else {
                    $message = $responseData['error'] ?? "Erro desconhecido.";
                    $messageType = 'error';
                }
            } elseif ($httpCode === 404) {
                $message = "Este email não está cadastrado.";
                $messageType = 'error';
            } elseif ($httpCode === 400) {
                $message = $responseData['error'] ?? "Dados inválidos.";
                $messageType = 'error';
            } elseif ($httpCode === 500) {
                $message = "Erro interno do servidor. Tente novamente mais tarde.";
                $messageType = 'error';
            } else {
                $message = $responseData['error'] ?? "Erro HTTP: " . $httpCode;
                $messageType = 'error';
            }
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
            <?php if ($messageType === 'success'): ?>
                <div class="bg-green-50 p-4 rounded-md">
                    <p class="text-sm text-green-600"><?php echo htmlspecialchars($message); ?></p>
                </div>
                <div class="mt-6">
                    <a href="login.php" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                       style="background-color: #0969DA">
                        Voltar ao Login
                    </a>
                </div>
            <?php else: ?>
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                        Recuperar Senha
                    </h2>
                </div>

                <?php if ($message && $messageType === 'error'): ?>
                    <div class="bg-red-50 p-4 rounded-md">
                        <p class="text-sm text-red-600"><?php echo htmlspecialchars($message); ?></p>
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
