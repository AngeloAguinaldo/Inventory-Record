<!DOCTYPE html>
<html>
<head>
    <title>Products List</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .form-container { background: #fff; padding: 50px; border-radius: 10px; width: 500px; margin: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #007BFF; color: white; }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%; padding: 10px; margin: 8px 0; border-radius: 5px; border: 1px solid #ccc;
        }
        input[type="submit"], .btn {
            background: #007BFF; color: white; border: none; padding: 8px 15px; border-radius: 5px;
            cursor: pointer; text-decoration: none;
        }
        input[type="submit"]:hover, .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #a71d2a; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-warning:hover { background: #d39e00; }
        h2 { text-align: center; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Products List</h2>
    <?php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "inventory_db";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Handle Delete
        if (isset($_GET['delete'])) {
            $id = $_GET['delete'];
            $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
            $stmt->execute([$id]);
            echo "<script>alert('Product deleted successfully!'); window.location='".$_SERVER['PHP_SELF']."';</script>";
            exit;
        }

        // Handle Edit
        $edit_id = null;
        $edit_data = [];
        if (isset($_GET['edit'])) {
            $edit_id = $_GET['edit'];
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
            $stmt->execute([$edit_id]);
            $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $product_name = $_POST['product_name'];
            $category = $_POST['category'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $supplier = $_POST['supplier'];
            $description = $_POST['description'];

            if (isset($_POST['update_id']) && $_POST['update_id'] != '') {
                // Update product
                $id = $_POST['update_id'];
                $stmt = $pdo->prepare("UPDATE products SET product_name=?, category=?, price=?, stock=?, supplier=?, description=? WHERE id=?");
                $stmt->execute([$product_name, $category, $price, $stock, $supplier, $description, $id]);
                echo "<script>alert('Product updated successfully!'); window.location='".$_SERVER['PHP_SELF']."';</script>";
                exit;
            } else {
                // Insert new product
                $stmt = $pdo->prepare("INSERT INTO products (product_name, category, price, stock, supplier, description)
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$product_name, $category, $price, $stock, $supplier, $description]);
                echo "<script>alert('Product added successfully!'); window.location='".$_SERVER['PHP_SELF']."';</script>";
                exit;
            }
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    }
    ?>

    <form method="POST">
        <input type="hidden" name="update_id" value="<?php echo $edit_data['id'] ?? ''; ?>">

        <label>Product Name:</label>
        <input type="text" name="product_name" required value="<?php echo $edit_data['product_name'] ?? ''; ?>">

        <label>Category:</label>
        <select name="category" required>
            <option value="">Select Category</option>
            <?php
            $categories = ["Electronics", "Clothing", "Home & Kitchen", "Sports", "Books"];
            foreach ($categories as $cat) {
                $selected = (isset($edit_data['category']) && $edit_data['category'] == $cat) ? "selected" : "";
                echo "<option value='$cat' $selected>$cat</option>";
            }
            ?>
        </select>

        <label>Price:</label>
        <input type="number" step="0.01" name="price" required value="<?php echo $edit_data['price'] ?? ''; ?>">

        <label>Stock:</label>
        <input type="number" name="stock" required value="<?php echo $edit_data['stock'] ?? ''; ?>">

        <label>Supplier:</label>
        <input type="text" name="supplier" value="<?php echo $edit_data['supplier'] ?? ''; ?>">

        <label>Description:</label>
        <textarea name="description" rows="3"><?php echo $edit_data['description'] ?? ''; ?></textarea>

        <input type="submit" value="<?php echo $edit_id ? 'Update Product' : 'Add Product'; ?>">
    </form>
</div>

<?php
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($products) {
        echo "<table><tr>
                <th>ID</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th>
                <th>Supplier</th><th>Description</th><th>Created At</th><th>Actions</th>
              </tr>";
        foreach ($products as $row) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['category']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['stock']}</td>
                    <td>{$row['supplier']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['created_at']}</td>
                    <td>
                        <a class='btn btn-warning' href='?edit={$row['id']}'>Edit</a>
                        <a class='btn btn-danger' href='?delete={$row['id']}' onclick=\"return confirm('Are you sure you want to delete this product?');\">Delete</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='text-align:center;'>No products found.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>

</body>
</html>