<?php
// create_user_sql.php
// Genera el SQL para insertar un usuario en la tabla `users`

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $password  = $_POST['password'] ?? '';
    $isAdmin   = isset($_POST['is_admin']) ? 1 : 0;

    if ($email === '' || $password === '') {
        echo "<p style='color:red'>Email y password son obligatorios.</p>";
    } else {
        // Generar hash BCRYPT
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Crear SQL listo para pegar en MySQL
        $sql = sprintf(
            "INSERT INTO users (email, password_hash, first_name, is_admin, created_at)\n".
            "VALUES ('%s', '%s', '%s', %d, NOW());",
            addslashes($email),
            addslashes($hash),
            addslashes($firstName),
            $isAdmin
        );

        echo "<h3>Consulta SQL generada:</h3>";
        echo "<pre>$sql</pre>";
    }
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Generador de SQL para Usuarios</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 2rem; }
    label { display:block; margin-top: 1rem; }
  </style>
</head>
<body>
  <h1>Crear consulta SQL para nuevo usuario</h1>
  <form method="post">
    <label>Email:
      <input type="email" name="email" required>
    </label>
    <label>Nombre:
      <input type="text" name="first_name">
    </label>
    <label>Password:
      <input type="text" name="password" required>
    </label>
    <label>
      <input type="checkbox" name="is_admin"> ¿Es admin?
    </label>
    <button type="submit">Generar SQL</button>
  </form>
</body>
</html>
