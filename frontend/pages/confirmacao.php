<?php
session_start();
require 'config.php';
$uid = $_SESSION['uid_pending'] ?? 0;
if(!$uid) header('Location: cadastro.php');

if($_SERVER['REQUEST_METHOD']==='POST'){
  $codigo = $_POST['codigo'];
  $stmt = $mysqli->prepare(
    "SELECT id,expiracao,confirmado
     FROM EmailConfirm
     WHERE usuario_id=? AND codigo=?"
  );
  $stmt->bind_param("is",$uid,$codigo);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  if($res && !$res['confirmado'] && $res['expiracao']>=date('Y-m-d H:i:s')){
    // Confirma
    $mysqli->query(
      "UPDATE EmailConfirm
       SET confirmado=1
       WHERE id=".$res['id']
    );
    // Log user in
    $_SESSION['user_id'] = $uid;
    unset($_SESSION['uid_pending']);
    // Redireciona conforme tipo
    $tipo = $mysqli->query("SELECT tipo FROM Usuario WHERE id=$uid")
                   ->fetch_object()->tipo;
    if($tipo==='aluno')       header('Location: home_aluno.php');
    elseif($tipo==='coordenador') header('Location: home_coordenador.php');
    else                      header('Location: home_orientador.php');
    exit;
  } else {
    $error = "Código incorreto ou expirado.";
  }
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Confirmação</title></head><body>
  <h1>Confirmação de E‑mail</h1>
  <?php if(!empty($error)) echo "<p style='color:red'>$error</p>";?>
  <form method="post">
    <label>Código (6 dígitos): <input name="codigo" maxlength="6" required></label>
    <button>Confirmar</button>
  </form>
</body></html>
