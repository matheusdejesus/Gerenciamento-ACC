<?php
session_start();

// Carregar configurações
require_once __DIR__ . '/../../backend/api/config/config.php';
require_once __DIR__ . '/../../backend/api/config/database.php';
require_once __DIR__ . '/../../backend/api/models/Cadastro.php';

use backend\api\config\Database;
use backend\api\models\Cadastro;

$email = $_GET['email'] ?? '';
error_log("=== PÁGINA DE CONFIRMAÇÃO ===");
error_log("Email da URL: " . $email);

// Verificar se existem dados temporários na sessão
if (!isset($_SESSION['cadastro_temp'])) {
    error_log("ERRO: Dados temporários não encontrados na sessão");
    header('Location: cadastro.php');
    exit;
}

$dadosTemp = $_SESSION['cadastro_temp'];
error_log("Dados temporários da sessão: " . json_encode($dadosTemp));

// Verificar se não expirou
if (time() > $dadosTemp['expiracao']) {
    error_log("ERRO: Sessão de cadastro expirada");
    unset($_SESSION['cadastro_temp']);
    header('Location: cadastro.php?erro=expirado');
    exit;
}

$error = '';
$success = false;

if($_SERVER['REQUEST_METHOD']==='POST'){
    $codigo = $_POST['codigo'];
    error_log("Código digitado: " . $codigo);
    error_log("Código esperado: " . $dadosTemp['codigo']);
    
    if($codigo == $dadosTemp['codigo']){
        error_log("CÓDIGO VÁLIDO! Criando usuário...");
        
        // Criar o usuário no banco
        $cadastro = new Cadastro();
        $result = $cadastro->create($dadosTemp['dados']);
        
        if ($result) {
            error_log("USUÁRIO CRIADO COM SUCESSO! ID: " . $result);
            
            // Limpar dados temporários da sessão
            unset($_SESSION['cadastro_temp']);
            
            // Criar sessão de usuário logado
            $_SESSION['usuario'] = [
                'id' => $result,
                'nome' => $dadosTemp['dados']['nome'],
                'email' => $dadosTemp['dados']['email'],
                'tipo' => $dadosTemp['dados']['tipo']
            ];
            
            $success = true;
            
        } else {
            error_log("ERRO ao criar usuário");
            $error = "Erro ao criar usuário no banco de dados. Verifique os logs do servidor.";
        }
    } else {
        error_log("CÓDIGO INVÁLIDO");
        $error = "Código incorreto.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Confirmação de E-mail</title>
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
            
            <?php if (!$success): ?>
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #0969DA">
                        Confirmação de E-mail
                    </h2>
                    <p class="mt-4 text-center text-gray-600">Digite o código de 6 dígitos enviado para:</p>
                    <p class="mt-2 text-center font-semibold" style="color: #0969DA"><?= htmlspecialchars($email) ?></p>
                </div>
                
                <?php if($error): ?>
                    <div class="bg-red-50 p-4 rounded-md">
                        <p class="text-sm text-red-600"><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="mt-8 space-y-6">
                    <div class="space-y-4">
                        <div>
                            <label for="codigo" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Código de Verificação</label>
                            <input id="codigo" 
                                   name="codigo" 
                                   type="text"
                                   maxlength="6" 
                                   required
                                   class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53] text-center text-lg font-mono tracking-widest">
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" style="background-color: #1A7F37">
                            Confirmar Código
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <a href="cadastro.php" class="text-sm" style="color: #0969DA">
                        Voltar ao cadastro
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #1A7F37">
                        Cadastro Realizado!
                    </h2>
                    <p class="mt-4 text-gray-600">Seu cadastro foi realizado com sucesso!</p>
                    <div class="mt-6">
                        <?php 
                        $tipo = $_SESSION['usuario']['tipo'];
                        if($tipo === 'aluno'): ?>
                            <a href="home_aluno.php" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" style="background-color: #1A7F37">
                                Ir para Dashboard
                            </a>
                        <?php elseif($tipo === 'coordenador'): ?>
                            <a href="home_coordenador.php" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" style="background-color: #1A7F37">
                                Ir para Dashboard
                            </a>
                        <?php else: ?>
                            <a href="home_orientador.php" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" style="background-color: #1A7F37">
                                Ir para Dashboard
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</body>
</html>
