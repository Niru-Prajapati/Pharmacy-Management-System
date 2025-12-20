<?php
session_start();
include 'connection.php';
include 'validate_prescription.php';

// No cart table
$current_cart = [];

// Get search term and category from AJAX POST
$search = $_POST['search'] ?? '';
$category = $_POST['category'] ?? '';

// Build SQL query with search and optional category filter
$sql = "SELECT * FROM meds WHERE MED_NAME LIKE ?";
$params = ["%$search%"];
$types = "s";

// Only add category filter if selected
if(!empty($category)){
    $sql .= " AND CATEGORY = ?";
    $params[] = $category;
    $types .= "s";
}

// Limit results
$sql .= " LIMIT 50";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Loop through medicines and output table rows
while($med = $result->fetch_assoc()){
    $med_id = $med['MED_ID'];
    $med_name = $med['MED_NAME'];
    $med_qty = $med['MED_QTY'];
    $med_category = $med['CATEGORY'];
    $med_price = $med['MED_PRICE'];

    // Validate medicine (dosage limits, stock, etc.)
    $errors = validatePrescription($med_id, 1, $current_cart);

    echo "<tr>";
    echo "<td>".htmlspecialchars($med_name)."</td>";
    echo "<td>".htmlspecialchars($med_category)."</td>";
    echo "<td>Rs. ".htmlspecialchars($med_price)."</td>";
    echo "<td>".htmlspecialchars($med_qty)."</td>";
    echo "<td>";
    if(!empty($errors)){
        echo "<span class='disabled' title='".implode(", ", $errors)."'>⚠️ Cannot Add</span>";
    } else if($med_qty > 0){
        echo "<a href='add_to_cart.php?med_id=$med_id'>✅ Add to Cart</a>";
    } else {
        echo "<span style='color:red;'>❌ Out of stock</span>";
    }
    echo "</td>";
    echo "</tr>";
}
?>
