<?php
require_once 'config.php';

$result_message = '';
$logged_in_user = null;
$executed_query = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ============================================================
    // VULNERABLE: raw string concatenation, no sanitization,
    // no parameter binding. User input goes straight into the
    // SQL string. This is the exact pattern from the reference
    // document this project started from.
    // ============================================================
    $query = "SELECT * FROM app_users WHERE username = '$username' AND password = '$password'";
    $executed_query = $query;

    $query_result = mysqli_query($conn, $query);

    if ($query_result && mysqli_num_rows($query_result) > 0) {
        $logged_in_user = mysqli_fetch_assoc($query_result);
        $result_message = "Login successful.";
    } else {
        $error = mysqli_error($conn);
        $result_message = $error ? "Login failed. DB error: $error" : "Login failed. Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mini App - VULNERABLE Login</title>
    <style>
        body { font-family: sans-serif; max-width: 640px; margin: 40px auto; padding: 0 20px; }
        h1 { color: #b02a2a; }
        input { display: block; margin: 8px 0; padding: 8px; width: 100%; box-sizing: border-box; }
        button { padding: 8px 16px; }
        .query-box { background: #f4f4f4; padding: 12px; border-left: 4px solid #b02a2a; margin-top: 20px; font-family: monospace; white-space: pre-wrap; word-break: break-all; }
        .success { color: green; font-weight: bold; }
        .fail { color: #b02a2a; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Mini App - VULNERABLE Login</h1>
    <p>This login form builds its SQL query using raw string concatenation
       (no parameterized queries, no input sanitization). Try a payload like:</p>
    <p><code>' OR '1'='1' -- </code> in the username field.</p>

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

    <?php if ($executed_query): ?>
        <p>Query that was executed against the database:</p>
        <div class="query-box"><?= htmlspecialchars($executed_query) ?></div>
    <?php endif; ?>
</body>
</html>
