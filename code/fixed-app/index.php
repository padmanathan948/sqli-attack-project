<?php
require_once 'config.php';

$result_message = '';
$logged_in_user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ============================================================
    // FIXED: parameterized query (prepared statement).
    // User input is bound as data, never concatenated into the
    // SQL string, so it can never change the query's structure -
    // the exact defense recommended in the reference document.
    // ============================================================
    $stmt = mysqli_prepare($conn, "SELECT * FROM app_users WHERE username = ? AND password = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $query_result = mysqli_stmt_get_result($stmt);

    if ($query_result && mysqli_num_rows($query_result) > 0) {
        $logged_in_user = mysqli_fetch_assoc($query_result);
        $result_message = "Login successful.";
    } else {
        $result_message = "Login failed. Invalid credentials.";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mini App - FIXED Login</title>
    <style>
        body { font-family: sans-serif; max-width: 640px; margin: 40px auto; padding: 0 20px; }
        h1 { color: #1e7e34; }
        input { display: block; margin: 8px 0; padding: 8px; width: 100%; box-sizing: border-box; }
        button { padding: 8px 16px; }
        .success { color: green; font-weight: bold; }
        .fail { color: #b02a2a; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Mini App - FIXED Login</h1>
    <p>This version uses a parameterized query (prepared statement).
       Try the same payload that worked on the vulnerable version:</p>
    <p><code>' OR '1'='1' -- </code> in the username field.</p>
    <p>It will simply be treated as a literal (and incorrect) username - no bypass.</p>

    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
        <label>Password:</label>
        <input type="text" name="password" value="<?= isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '' ?>">
        <button type="submit">Login</button>
    </form>

    <?php if ($result_message): ?>
        <p class="<?= $logged_in_user ? 'success' : 'fail' ?>"><?= htmlspecialchars($result_message) ?></p>
    <?php endif; ?>

    <?php if ($logged_in_user): ?>
        <p><strong>Welcome, <?= htmlspecialchars($logged_in_user['username']) ?></strong>
           (role: <?= htmlspecialchars($logged_in_user['role']) ?>)</p>
    <?php endif; ?>
</body>
</html>
