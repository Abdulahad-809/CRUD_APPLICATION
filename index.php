<?php
// Simple one‑file CRUD – MySQL connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "myshop";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("DB Connection failed: " . $conn->connect_error); }

// Create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address VARCHAR(200),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM clients WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Handle Create or Update
$name=$email=$phone=$address="";
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name    = $conn->real_escape_string($_POST['name'] ?? "");
    $email   = $conn->real_escape_string($_POST['email'] ?? "");
    $phone   = $conn->real_escape_string($_POST['phone'] ?? "");
    $address = $conn->real_escape_string($_POST['address'] ?? "");
    $id      = (int) ($_POST['id'] ?? 0);

    if ($id>0) {
        // Update
        $conn->query("UPDATE clients SET name='$name', email='$email', phone='$phone', address='$address' WHERE id=$id");
    } else {
        // Create
        $conn->query("INSERT INTO clients (name,email,phone,address) VALUES('$name','$email','$phone','$address')");
    }
    header("Location: index.php");
    exit;
}

// If edit mode, load record
$editData = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $res = $conn->query("SELECT * FROM clients WHERE id=$id");
    $editData = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Shop CRUD</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4"><?= $editData ? 'Edit Client' : 'New Client' ?></h2>
    <form method="post" class="mb-4">
        <input type="hidden" name="id" value="<?= htmlspecialchars($editData['id'] ?? 0) ?>">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="<?= htmlspecialchars($editData['name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($editData['email'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input name="phone" class="form-control" value="<?= htmlspecialchars($editData['phone'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Address</label>
            <input name="address" class="form-control" value="<?= htmlspecialchars($editData['address'] ?? '') ?>" required>
        </div>
        <button class="btn btn-primary" type="submit"><?= $editData ? 'Update' : 'Create' ?></button>
        <?php if ($editData): ?>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>

    <h2 class="mb-3">List of Clients</h2>
    <table class="table table-bordered table-hover bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Created</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $res = $conn->query("SELECT * FROM clients ORDER BY id DESC");
        if ($res && $res->num_rows>0):
            while($row=$res->fetch_assoc()):
        ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['address']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this client?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-center">No clients found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
