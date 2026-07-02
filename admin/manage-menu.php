<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

$error = '';
$success = '';
$action = $_GET['action'] ?? '';
$menu_id = $_GET['id'] ?? '';

// Handle delete
if ($action === 'delete' && $menu_id) {
    $query = "DELETE FROM menu_items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $menu_id);
    if ($stmt->execute()) {
        $success = 'Menu berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus menu!';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    if (empty($name) || $category_id === 0 || $price === 0) {
        $error = 'Semua field harus diisi!';
    } else {
        if ($action === 'edit' && $menu_id) {
            $query = "UPDATE menu_items SET name = ?, category_id = ?, description = ?, price = ?, is_available = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sisdii", $name, $category_id, $description, $price, $is_available, $menu_id);
            if ($stmt->execute()) {
                $success = 'Menu berhasil diupdate!';
                header('refresh:2;url=manage-menu.php');
            } else {
                $error = 'Gagal mengupdate menu!';
            }
        } else {
            $query = "INSERT INTO menu_items (name, category_id, description, price, is_available) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sisdi", $name, $category_id, $description, $price, $is_available);
            if ($stmt->execute()) {
                $success = 'Menu berhasil ditambahkan!';
                header('refresh:2;url=manage-menu.php');
            } else {
                $error = 'Gagal menambahkan menu!';
            }
        }
    }
}

// Get menu item for edit
$edit_item = null;
if ($action === 'edit' && $menu_id) {
    $query = "SELECT * FROM menu_items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    $edit_item = $stmt->get_result()->fetch_assoc();
}

// Get all menu items
$query = "SELECT m.*, c.name as category_name FROM menu_items m 
          JOIN categories c ON m.category_id = c.id 
          ORDER BY m.name";
$all_menus = $conn->query($query);
?>

<?php include '../includes/header.php'; ?>

<div class="manage-menu-container">
    <h1>Kelola Menu</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="menu-layout">
        <div class="form-section">
            <h2><?php echo $action === 'edit' ? 'Edit Menu' : 'Tambah Menu Baru'; ?></h2>
            
            <form method="POST" class="form">
                <div class="form-group">
                    <label for="name">Nama Menu *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo $edit_item ? htmlspecialchars($edit_item['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="category_id">Kategori *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php
                        $categories = getCategories($conn);
                        while ($cat = $categories->fetch_assoc()):
                        ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $edit_item && $edit_item['category_id'] === $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price">Harga *</label>
                    <input type="number" id="price" name="price" step="1000" required
                           value="<?php echo $edit_item ? $edit_item['price'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description"><?php echo $edit_item ? htmlspecialchars($edit_item['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group checkbox">
                    <input type="checkbox" id="is_available" name="is_available" 
                           <?php echo $edit_item && $edit_item['is_available'] ? 'checked' : 'checked'; ?>>
                    <label for="is_available">Tersedia</label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $action === 'edit' ? 'Update Menu' : 'Tambah Menu'; ?>
                    </button>
                    <?php if ($action === 'edit'): ?>
                        <a href="manage-menu.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="list-section">
            <h2>Daftar Menu</h2>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($menu = $all_menus->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($menu['name']); ?></td>
                                <td><?php echo htmlspecialchars($menu['category_name']); ?></td>
                                <td><?php echo formatCurrency($menu['price']); ?></td>
                                <td>
                                    <?php if ($menu['is_available']): ?>
                                        <span class="badge badge-success">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Tidak Tersedia</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?action=edit&id=<?php echo $menu['id']; ?>" class="btn btn-small btn-primary">Edit</a>
                                    <a href="?action=delete&id=<?php echo $menu['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Hapus menu ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.manage-menu-container {
    max-width: 1200px;
    margin: 0 auto;
}

.menu-layout {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 2rem;
    margin-top: 2rem;
}

.form-section,
.list-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.checkbox label {
    margin: 0;
    font-weight: normal;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.table-responsive {
    overflow-x: auto;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

@media (max-width: 768px) {
    .menu-layout {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
