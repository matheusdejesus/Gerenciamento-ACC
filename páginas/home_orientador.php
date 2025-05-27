<?php
session_start();
if(empty($_SESSION['user_id'])||$_SESSION['user_tipo'] !== 'orientador'){
  header('Location: login.php'); exit;
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Home Aluno</title></head><body>
  <h1>Bem‑vindo, <?=htmlspecialchars($_SESSION['user_nome'])?></h1>
  <a href="logout.php">Logout</a>
</body></html>
