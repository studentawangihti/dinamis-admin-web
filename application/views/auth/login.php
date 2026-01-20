<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | Dinamis Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: white; }
        .brand { text-align: center; margin-bottom: 20px; font-weight: bold; font-size: 24px; color: #6200EE; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand">Admin Dashboard</div>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
    <?php endif; ?>

    <form action="<?= base_url('auth/process') ?>" method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">LOGIN</button>
    </form>
    
    <div class="mt-3 text-center text-muted">
        <small>Gunakan username: <b>admin</b> / pass: <b>admin123</b></small>
    </div>
</div>

</body>
</html>