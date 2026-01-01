<?php
session_start();
include 'connection.php';
include 'validate_prescription.php';

// Get search and category filters
$search = $_POST['search'] ?? '';
$category = $_POST['category'] ?? '';

// Use session cart
$current_cart = $_SESSION['cart'] ?? [];

// Base SQL query
$sql = "SELECT * FROM meds WHERE 1";

// Fuzzy search (supports misspelled medicine names)
if (!empty($search)) {
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (
        MED_NAME LIKE '%$searchEscaped%'
        OR SOUNDEX(MED_NAME) = SOUNDEX('$searchEscaped')
    )";
}

// Category filter
if (!empty($category)) {
    $categoryEscaped = mysqli_real_escape_string($conn, $category);
    $sql .= " AND CATEGORY = '$categoryEscaped'";
}

// Execute query
$result = mysqli_query($conn, $sql);

// Generate table rows
while ($med = mysqli_fetch_assoc($result)) {

    $med_id = $med['MED_ID'];
    $med_name = $med['MED_NAME'];
    $med_qty = $med['MED_QTY'];
    $med_category = $med['CATEGORY'];
    $med_price = $med['MED_PRICE'];

    // Validate prescription (including drug interactions)
    $errors = validatePrescription($med_id, 1, $current_cart);
?>
<tr>
    <td><?= htmlspecialchars($med_name); ?></td>
    <td><?= htmlspecialchars($med_category); ?></td>
    <td>Rs. <?= htmlspecialchars($med_price); ?></td>
    <td><?= htmlspecialchars($med_qty); ?></td>
    <td>
        <?php
        if (!empty($errors)) {
            // Show popup alert on click for conflicting medicines
            $error_msg = implode(", ", $errors);
            echo "<a href='#' onclick='alert(\"$error_msg\"); return false;' class='disabled'>⚠️ Cannot Add</a>";
        } elseif ($med_qty > 0) {
            // Add to Cart link
            echo "<a href='add_to_cart.php?med_id=$med_id'>✅ Add to Cart</a>";
        } else {
            // Out of stock
            echo "<span style='color:red;'>❌ Out of stock</span>";
        }
        ?>
    </td>
</tr>
<?php } ?>
