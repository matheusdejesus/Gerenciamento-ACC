<?php
session_start();
require 'vendor/autoload.php'; // PHPMailer
require 'config.php';          // $mysqli, DB connection

// 1) Buscar cursos para o combo
$cursos = [];
$res = $mysqli->query("SELECT id,nome FROM Curso");
while($row = $res->fetch_assoc()) $cursos[] = $row;

// 2) Processa submissão
if($_SERVER['REQUEST_METHOD']==='POST'){
  // Sanitização
  $nome      = trim($_POST['nome']);
  $email     = trim($_POST['email']);
  $senha     = $_POST['senha'];
  $confSenha = $_POST['conf_senha'];
  $tipo      = $_POST['tipo'];
  $matricula = $_POST['matricula'] ?? null;
  $curso_id  = $_POST['curso_id']  ?? null;
  $siape     = $_POST['siape']     ?? null;

  $errors = [];
  // 3) Validações back‐end
  //if(!preg_match('/^[^@\s]+@[^@\s]+\.ufopa\.edu\.br$/i',$email))
  //  $errors[] = "Use um e‑mail terminando em .ufopa.edu.br";

  if($senha !== $confSenha)
    $errors[] = "As senhas não coincidem.";

  if(!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/',$senha))
    $errors[] = "Senha fraca (mínimo 8 chars, 1 upper, 1 lower, 1 dígito e 1 símbolo).";

  // Verifica campos por tipo
  if($tipo==='aluno'){
    if(!$matricula||!$curso_id) $errors[]="Informe matrícula e curso.";
  } else {
    if(!$siape) $errors[]="Informe siape.";
  }

  // Se OK, insere usuário “pendente”
  if(empty($errors)){
    $hash = password_hash($senha,PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare(
      "INSERT INTO Usuario(nome,email,senha,tipo)
       VALUES(?,?,?,?)"
    );
    $stmt->bind_param("ssss",$nome,$email,$hash,$tipo);
    $stmt->execute();
    $uid = $stmt->insert_id;

    // Insere dados específicos
    if($tipo==='aluno'){
      $stmt2 = $mysqli->prepare(
        "INSERT INTO Aluno(usuario_id,matricula,curso_id)
         VALUES(?,?,?)"
      );
      $stmt2->bind_param("isi",$uid,$matricula,$curso_id);
      $stmt2->execute();
    } elseif($tipo==='coordenador'){
      $stmt2 = $mysqli->prepare(
        "INSERT INTO Coordenador(usuario_id,siape,curso_id)
         VALUES(?,?,?)"
      );
      $stmt2->bind_param("isi",$uid,$siape,$curso_id);
      $stmt2->execute();
    } else { // orientador
      $stmt2 = $mysqli->prepare(
        "INSERT INTO Orientador(usuario_id,siape)
         VALUES(?,?)"
      );
      $stmt2->bind_param("is",$uid,$siape);
      $stmt2->execute();
    }

    // Gera código e insere token
    $codigo = str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT);
    $expira = date('Y-m-d H:i:s',strtotime('+1 day'));
    $stmt3 = $mysqli->prepare(
      "INSERT INTO EmailConfirm(usuario_id,codigo,expiracao)
       VALUES(?,?,?)"
    );
    $stmt3->bind_param("iss",$uid,$codigo,$expira);
    $stmt3->execute();

    // Envia e‑mail
    // Fim envia e-mail

    $_SESSION['uid_pending'] = $uid;
    header('Location: confirmacao.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html><head>
  <meta charset="UTF-8">
  <title>Cadastro</title>
  <style>/* mínimo CSS */</style>
  <script>
  // Validação front‐end
  function validate() {
  const email = document.getElementById('email').value.trim();
  const senha = document.getElementById('senha').value;
  const conf  = document.getElementById('conf_senha').value;

  const senhaRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*\W).{8,}$/;

  let errs = [];
  //if(!/^[^@\s]+@[^@\s]+\.ufopa\.edu\.br$/i.test(email))
  //  err.push('Use um e‑mail terminando em .ufopa.edu.br');
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
<body onload="toggleFields()">
  <h1>Cadastro</h1>
  <?php if(!empty($errors)):?>
    <ul style="color:red">
      <?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>";?>
    </ul>
  <?php endif;?>
  <form method="post" onsubmit="return validate()">
    <label>Nome: <input name="nome" required></label><br>
    <label>E‑mail: <input id="email" name="email" required></label><br>
    <label>Senha: <input type="password" id="senha" name="senha" required></label><br>
    <label>Confirma Senha: <input type="password" id="conf_senha" name="conf_senha" required></label><br>
    <label>Tipo:
      <select id="tipo" name="tipo" onchange="toggleFields()" required>
        <option value="aluno">Aluno</option>
        <option value="coordenador">Coordenador</option>
        <option value="orientador">Orientador</option>
      </select>
    </label><br>
    <div id="alunoFields">
      <label>Matrícula: <input name="matricula"></label><br>
    </div>
    <div id="CursoFields">
      <label>Curso:
        <select name="curso_id">
          <option value="">-- escolha --</option>
          <?php foreach($cursos as $c):?>
            <option value="<?=$c['id']?>"><?=htmlspecialchars($c['nome'])?></option>
          <?php endforeach;?>
        </select>
      </label><br>
    </div>
    <div id="SiapeFilds">
      <label>Siape: <input name="siape"></label><br>
    </div>
    <button type="submit">Cadastrar</button>
  </form>
</body>
</html>
