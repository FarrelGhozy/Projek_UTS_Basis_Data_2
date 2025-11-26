<?php
// login.php
require_once 'koneksi.php'; // Hubungkan koneksi dan mulai session

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = MD5($_POST['password']); // Hashing password input (harus MD5 karena kita simpan di DB pakai MD5)

    // Query untuk mencari user
    $stmt = $conn->prepare("SELECT user_id, nama_kasir FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Login Sukses: Simpan data ke session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['nama_kasir'] = $user['nama_kasir'];
        
        // Arahkan ke halaman kasir
        header("Location: kasir.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Kasir</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-box">
        <h2>Login BENGKEL TI UNIDA</h2>
        <?php if ($error): ?>
            <p style="color: red; font-weight: bold;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST"> 
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" style="width: 100%; margin-top: 15px;">LOGIN</button>
        </form>
    </div>
</body>
</html>