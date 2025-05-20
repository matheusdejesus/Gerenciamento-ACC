<?php
session_start();
require 'config.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = $_POST['email'];
  $senha = $_POST['senha'];
  $stmt = $mysqli->prepare(
    "SELECT id,senha,tipo,nome
     FROM Usuario
     WHERE email=?"
  );
  $stmt->bind_param("s",$email);
  $stmt->execute();
  $u = $stmt->get_result()->fetch_assoc();
  if($u && password_verify($senha,$u['senha'])){
    // Checa confirmação
    $c = $mysqli->query("SELECT confirmado FROM EmailConfirm WHERE usuario_id={$u['id']} ORDER BY id DESC LIMIT 1")
                 ->fetch_object()->confirmado;
    if($c){
      $_SESSION['user_id']=$u['id'];
      $_SESSION['user_nome']=$u['nome'];
      $_SESSION['user_tipo']=$u['tipo'];
      // redireciona
      header("Location: home_{$u['tipo']}.php");
      exit;
    } else $error="E‑mail não confirmado.";
  } else $error="Usuário ou senha inválidos.";
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Login</title></head><body>
  <h1>Login</h1>
  <?php if(!empty($error)) echo "<p style='color:red'>$error</p>";?>
  <form method="post">
    <label>E‑mail: <input name="email" required></label><br>
    <label>Senha:  <input type="password" name="senha" required></label><br>
    <button>Entrar</button>
  </form>
</body></html>
