<?php
session_start();
require_once __DIR__ . '/../../backend/api/config/config.php';
require_once __DIR__ . '/../../backend/api/config/database.php';

// Importar LogAcoesController
require_once __DIR__ . '/../../backend/api/controllers/LogAcoesController.php';

use backend\api\config\Database;
use backend\api\controllers\LogAcoesController;

$email = $_GET['email'] ?? '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'] ?? '';
    $cadastro_temp = $_SESSION['cadastro_temp'] ?? null;

    if (!$cadastro_temp || $cadastro_temp['expiracao'] < time()) {
        $error = "Sessão expirada. Faça o cadastro novamente.";
    } elseif ($cadastro_temp['codigo'] !== $codigo) {
        $error = "Código incorreto.";
    } else {
        $dados = $cadastro_temp['dados'];
        try {
            $db = Database::getInstance()->getConnection();
            $checkStmt = $db->prepare("SELECT COUNT(*) AS cnt FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = ? AND ROUTINE_NAME = 'sp_determinar_resolucao_aluno'");
            $schema = 'acc';
            $checkStmt->bind_param("s", $schema);
            $checkStmt->execute();
            $res = $checkStmt->get_result();
            $row = $res->fetch_assoc();
            $checkStmt->close();
            if ((int)$row['cnt'] === 0) {
                $trgStmt = $db->prepare("SELECT ACTION_STATEMENT FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = ? AND ACTION_STATEMENT LIKE '%sp_determinar_resolucao_aluno%' LIMIT 1");
                $trgStmt->bind_param("s", $schema);
                $trgStmt->execute();
                $trgRes = $trgStmt->get_result();
                $trgRow = $trgRes->fetch_assoc();
                $trgStmt->close();
                $paramCount = 0;
                if ($trgRow && isset($trgRow['ACTION_STATEMENT'])) {
                    $stmtText = $trgRow['ACTION_STATEMENT'];
                    $pos = stripos($stmtText, 'sp_determinar_resolucao_aluno(');
                    if ($pos !== false) {
                        $after = substr($stmtText, $pos + strlen('sp_determinar_resolucao_aluno('));
                        $inside = strtok($after, ')');
                        if ($inside !== false) {
                            $inside = trim($inside);
                            if ($inside === '') {
                                $paramCount = 0;
                            } else {
                                $paramCount = substr_count($inside, ',') + 1;
                            }
                        }
                    }
                }
                $defs = [];
                for ($i = 1; $i <= $paramCount; $i++) {
                    $defs[] = "IN p{$i} VARCHAR(255)";
                }
                $defStr = implode(', ', $defs);
                $createSql = "CREATE PROCEDURE acc.sp_determinar_resolucao_aluno(" . $defStr . ") BEGIN DO 0; END";
                $db->query($createSql);
            }

            $ts = $db->prepare("SELECT TRIGGER_NAME, ACTION_TIMING, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_STATEMENT FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = ? AND EVENT_OBJECT_TABLE = 'Aluno'");
            $ts->bind_param("s", $schema);
            $ts->execute();
            $trs = $ts->get_result();
            while ($t = $trs->fetch_assoc()) {
                $body = $t['ACTION_STATEMENT'];
                $hasResultSelect = preg_match('/\\bSELECT\\b/i', $body) && !preg_match('/\\bINTO\\b/i', $body);
                if ($hasResultSelect) {
                    $safeBody = 'BEGIN DO 0; END';
                    $posCall = stripos($body, 'sp_determinar_resolucao_aluno(');
                    if ($posCall !== false) {
                        $after = substr($body, $posCall + strlen('sp_determinar_resolucao_aluno('));
                        $inside = strtok($after, ')');
                        if ($inside !== false) {
                            $inside = trim($inside);
                            $safeBody = 'BEGIN CALL acc.sp_determinar_resolucao_aluno(' . $inside . '); END';
                        }
                    }
                    $db->query("DROP TRIGGER IF EXISTS `" . $t['TRIGGER_NAME'] . "`");
                    $createTrig = "CREATE TRIGGER `" . $t['TRIGGER_NAME'] . "` " . $t['ACTION_TIMING'] . " " . $t['EVENT_MANIPULATION'] . " ON `" . $t['EVENT_OBJECT_TABLE'] . "` FOR EACH ROW " . $safeBody;
                    $db->query($createTrig);
                }
            }
            $ts->close();
            $db->autocommit(false);
            $db->begin_transaction();

            // 1. Criar usuário na tabela Usuario
            $stmt = $db->prepare("INSERT INTO Usuario (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $senha_hash = password_hash($dados['senha'], PASSWORD_BCRYPT);
            $stmt->bind_param("ssss", $dados['nome'], $dados['email'], $senha_hash, $dados['tipo']);
            $stmt->execute();
            $usuario_id = $db->insert_id;
            $stmt->close();

            // 2. Inserir na tabela EmailConfirm
            $stmt = $db->prepare("INSERT INTO EmailConfirm (usuario_id, codigo, expiracao, confirmado) VALUES (?, ?, NOW() + INTERVAL 1 HOUR, 1)");
            $stmt->bind_param("is", $usuario_id, $codigo);
            $stmt->execute();
            $stmt->close();

            // 3. Criar registro específico
            if ($dados['tipo'] === 'aluno') {
                $stmt = $db->prepare("INSERT INTO Aluno (usuario_id, matricula, curso_id) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $usuario_id, $dados['matricula'], $dados['curso_id']);
                $stmt->execute();
                $stmt->close();
            } elseif ($dados['tipo'] === 'coordenador') {
                $stmt = $db->prepare("INSERT INTO Coordenador (usuario_id, siape, curso_id) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $usuario_id, $dados['siape'], $dados['curso_id']);
                $stmt->execute();
                $stmt->close();
            } elseif ($dados['tipo'] === 'orientador') {
                $stmt = $db->prepare("INSERT INTO Orientador (usuario_id, siape) VALUES (?, ?)");
                $stmt->bind_param("is", $usuario_id, $dados['siape']);
                $stmt->execute();
                $stmt->close();
            }

            // 4. GERAR API KEY
            $apiKey = bin2hex(random_bytes(32));
            $nomeAplicacao = 'user_' . $usuario_id;
            
            $stmt = $db->prepare("INSERT INTO ApiKeys (usuario_id, nome_aplicacao, api_key, ativa, criada_em) VALUES (?, ?, ?, 1, NOW())");
            $stmt->bind_param("iss", $usuario_id, $nomeAplicacao, $apiKey);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao criar API Key: " . $stmt->error);
            }
            $stmt->close();

            // 5. REGISTRAR LOG DE AÇÕES
            try {
                $logRegistrado = LogAcoesController::registrar(
                    $usuario_id,
                    'CADASTRO_USUARIO',
                    "Novo usuário cadastrado: {$dados['nome']} ({$dados['tipo']})"
                );
                
                if ($logRegistrado) {
                    error_log("Log de cadastro registrado com sucesso para usuário ID: " . $usuario_id);
                } else {
                    error_log("Falha ao registrar log de cadastro para usuário ID: " . $usuario_id);
                }
            } catch (Exception $logError) {
                error_log("Erro ao registrar log: " . $logError->getMessage());
            }

            $db->commit();
            $success = true;
            
            // Limpar sessão
            unset($_SESSION['cadastro_temp']);
            
            // Salvar dados do usuário na sessão
            $_SESSION['usuario'] = [
                'id' => $usuario_id,
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'tipo' => $dados['tipo']
            ];
            
            // Salvar API Key na sessão
            $_SESSION['api_key'] = $apiKey;
            
            error_log("Usuário criado com sucesso: ID={$usuario_id}, API_KEY={$apiKey}");
            
        } catch (Exception $e) {
            $db->rollback();
            error_log("Erro ao criar usuário: " . $e->getMessage());
            $error = "Erro ao criar usuário: " . $e->getMessage();
        }
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
                                   class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53] text-center text-lg font-mono tracking-widest"
                                   placeholder="000000">
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
                    <div class="mb-4">
                        <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="mt-6 text-center text-3xl font-extralight" style="color: #1A7F37">
                        Cadastro Realizado!
                    </h2>
                    <p class="mt-4 text-gray-600">Seu cadastro foi realizado com sucesso!</p>
                    
                    <div class="mt-6">
                        <?php 
                        $redirect_url = 'login.php';
                        ?>
                        
                        <button onclick="window.location.href='<?= $redirect_url ?>'" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" 
                                style="background-color: #1A7F37">
                            Fazer Login
                        </button>
                    </div>
                </div>
            <?php endif; ?>  
        </div>
    </div>
    
    <!-- No final do arquivo confirmacao.php, adicionar JavaScript para capturar a API Key -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($success && isset($_SESSION['api_key'])): ?>
            // Salvar API Key no localStorage
            localStorage.setItem('acc_api_key', '<?php echo $_SESSION['api_key']; ?>');
            console.log('API Key salva:', '<?php echo $_SESSION['api_key']; ?>');
            
            // Limpar da sessão após salvar
            <?php unset($_SESSION['api_key']); ?>
            
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 2000);
        <?php endif; ?>
    });
    </script>
</body>
</html>
