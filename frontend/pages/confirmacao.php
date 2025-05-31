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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <?php if ($success): ?>
            <div class="text-center">
                <h2 class="text-2xl font-bold mb-6 text-green-600">Cadastro Realizado!</h2>
                <p class="mb-4">Seu cadastro foi realizado com sucesso!</p>
                <div class="space-y-2">
                    <?php 
                    $tipo = $_SESSION['usuario']['tipo'];
                    if($tipo === 'aluno'): ?>
                        <a href="home_aluno.php" class="block w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-200">
                            Ir para Dashboard
                        </a>
                    <?php elseif($tipo === 'coordenador'): ?>
                        <a href="home_coordenador.php" class="block w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-200">
                            Ir para Dashboard
                        </a>
                    <?php else: ?>
                        <a href="home_orientador.php" class="block w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-200">
                            Ir para Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <h2 class="text-2xl font-bold mb-6 text-center">Confirmação de E-mail</h2>
            <p class="mb-4 text-gray-600">Digite o código de 6 dígitos enviado para:</p>
            <p class="mb-6 font-semibold text-blue-600"><?= htmlspecialchars($email) ?></p>
            
            <?php if($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <input type="text" 
                           name="codigo" 
                           placeholder="Código de 6 dígitos" 
                           maxlength="6" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>
                <button type="submit" 
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-200">
                    Confirmar
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <a href="cadastro.php" class="text-blue-500 hover:underline">Voltar ao cadastro</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
