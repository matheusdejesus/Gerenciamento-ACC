<?php
session_start();

require_once __DIR__ . '/../../backend/api/config/config.php';
require_once __DIR__ . '/../../backend/api/config/database.php';

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

use backend\api\config\Database;

// Buscar cursos
try {
    $db = Database::getInstance();
    $cursos = [];
    $res = $db->query("SELECT id,nome FROM Curso");
    if ($res) {
        while($row = $res->fetch_assoc()) {
            $cursos[] = $row;
        }
    }
} catch (Exception $e) {
    $cursos = [];
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $nome      = trim($_POST['nome']);
    $email     = trim($_POST['email']);
    $senha     = $_POST['senha'];
    $confSenha = $_POST['conf_senha'];
    $tipo      = $_POST['tipo'];
    $matricula = $_POST['matricula'] ?? null;
    $curso_id  = $_POST['curso_id']  ?? null;
    $siape     = $_POST['siape']     ?? null;

    $errors = [];
    
    // Validações Backend
    if(!preg_match('/^[^@\s]+@[^@\s]+\.ufopa\.edu\.br$/i',$email)) {
        $errors[] = "Use um e‑mail terminando em .ufopa.edu.br";
    }

    if($senha !== $confSenha)
        $errors[] = "As senhas não coincidem.";

    if(!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/',$senha))
        $errors[] = "Senha fraca (mínimo 8 chars, 1 upper, 1 lower, 1 dígito e 1 símbolo).";

    if($tipo==='aluno'){
        if(!$matricula||!$curso_id) $errors[]="Informe matrícula e curso.";
    } else {
        if(!$siape) $errors[]="Informe siape.";
    }

    if(empty($errors)){
        $data = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'conf_senha' => $confSenha,
            'tipo' => $tipo,
            'matricula' => $matricula,
            'curso_id' => $curso_id ? (int)$curso_id : null,
            'siape' => $siape
        ];

        $ch = curl_init("http://localhost/Gerenciamento-de-ACC/backend/api/routes/cadastro.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $responseData = json_decode($response, true);

        if($status === 200 && $responseData !== null && isset($responseData['success']) && $responseData['success']){
            $_SESSION['cadastro_temp'] = [
                'dados' => $data,
                'codigo' => $responseData['codigo'],
                'expiracao' => time() + 600
            ];
            
            header("Location: confirmacao.php?email=" . urlencode($responseData['email']));
            exit;
        } else {
            $errors[] = "Erro ao realizar o cadastro.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wght@0,200..900;1,200..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script>
    function validate() {
        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value;
        const conf  = document.getElementById('conf_senha').value;

        const senhaRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*\W).{8,}$/;

        let errs = [];
        if (!/^[^@\s]+@[^@\s]+\.ufopa\.edu\.br$/i.test(email)) {
            errs.push('Use um e‑mail terminando em .ufopa.edu.br');
        }
        if (senha !== conf) {
            errs.push('As senhas não coincidem.');
        }
        if (!senhaRegex.test(senha)) {
            errs.push('Senha fraca (mín. 8 chars, 1 upper, 1 lower, 1 dígito e 1 símbolo).');
        }

        if (errs.length) {
            alert(errs.join('\n'));
            return false;
        }
        return true;
    }
    
    function toggleFields(){
        let t = document.getElementById('tipo').value;
        if(t==='aluno'){
            document.getElementById('alunoFields').style.display = 'block';
            document.getElementById('CursoFields').style.display = 'block';
            document.getElementById('SiapeFilds').style.display = 'none';
        }else if(t === 'coordenador'){
            document.getElementById('alunoFields').style.display = 'none';
            document.getElementById('CursoFields').style.display = 'block';
            document.getElementById('SiapeFilds').style.display = 'block';
        }else{
            document.getElementById('alunoFields').style.display = 'none';
            document.getElementById('CursoFields').style.display = 'none';
            document.getElementById('SiapeFilds').style.display = 'block';
        }
    }
    </script>
</head>
<body class="bg-pattern font-montserrat min-h-screen flex flex-col" onload="toggleFields()">
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
                    Cadastro de Usuário
                </h2>
            </div>

            <?php if(!empty($errors)): ?>
                <div class="bg-red-50 p-4 rounded-md">
                    <ul class="list-disc list-inside text-sm text-red-600">
                        <?php foreach($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" onsubmit="return validate()" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <div>
                        <label for="nome" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Nome Completo</label>
                        <input id="nome" name="nome" type="text" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-regular text-gray-700" style="color: #0969DA">E-mail</label>
                        <input id="email" name="email" type="email" required 
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>

                    <div>
                        <label for="senha" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Senha</label>
                        <input id="senha" name="senha" type="password" required 
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>

                    <div>
                        <label for="conf_senha" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Confirmar Senha</label>
                        <input id="conf_senha" name="conf_senha" type="password" required 
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>

                    <div>
                        <label for="tipo" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Tipo de Usuário</label>
                        <select id="tipo" name="tipo" onchange="toggleFields()" required 
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                            <option value="aluno">Aluno</option>
                            <option value="coordenador">Coordenador</option>
                            <option value="orientador">Orientador</option>
                        </select>
                    </div>

                    <div id="alunoFields">
                        <label for="matricula" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Matrícula</label>
                        <input id="matricula" name="matricula" type="text" 
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>

                    <div id="CursoFields">
                        <label for="curso_id" class="block text-sm font-regular text-gray-700" style="color: #0969DA">Curso</label>
                        <select id="curso_id" name="curso_id" 
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                            <option value="">-- Selecione um curso --</option>
                            <?php foreach($cursos as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="SiapeFilds">
                        <label for="siape" class="block text-sm font-regular text-gray-700" style="color: #0969DA">SIAPE</label>
                        <input id="siape" name="siape" type="text" 
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-[#061B53]">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white" style="background-color: #1A7F37">
                        Cadastrar
                    </button>
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