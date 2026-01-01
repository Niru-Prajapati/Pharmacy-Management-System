<?php
include 'connection.php';

function validatePrescription($med_id, $quantity, $current_cart = []) {
    $errors = [];

    global $conn;

    // Fetch medicine info
    $stmt = $conn->prepare("SELECT * FROM meds WHERE MED_ID = ?");
    $stmt->bind_param("i", $med_id);
    $stmt->execute();
    $med = $stmt->get_result()->fetch_assoc();

    if(!$med) {
        $errors[] = "Medicine not found.";
        return $errors;
    }

    // 1. Regex for dosage
    $dosage = $med['dosage'] ?? ''; 
    if($dosage && !preg_match("/^\d+\s?(mg|ml)$/i", $dosage)) {
        $errors[] = "Invalid dosage format for " . $med['MED_NAME'];
    }

    // 2. Max per order
    $max_per_order = $med['max_per_order'] ?? 3;
    if($quantity > $max_per_order) {
        $errors[] = "You can only order up to $max_per_order units of " . $med['MED_NAME'];
    }

    // 3. Stock check
    if($quantity > $med['MED_QTY']) {
        $errors[] = "Not enough stock for " . $med['MED_NAME'];
    }

    // 4. Drug interactions
    $interactions = [
        "Aspirin" => ["Ibuprofen"], 
        "Paracetamol" => ["Alcohol"]
    ];

    // Loop through current cart
    foreach($current_cart as $cart_med_id => $cart_qty) {
        // Fetch the medicine name from DB
        $stmt2 = $conn->prepare("SELECT MED_NAME FROM meds WHERE MED_ID = ?");
        $stmt2->bind_param("i", $cart_med_id);
        $stmt2->execute();
        $cart_med = $stmt2->get_result()->fetch_assoc();
        $cart_name = $cart_med['MED_NAME'] ?? '';

        // Check interactions
        if(isset($interactions[$med['MED_NAME']]) && in_array($cart_name, $interactions[$med['MED_NAME']])) {
            $errors[] = $med['MED_NAME'] . " should not be combined with " . $cart_name;
        }
        if(isset($interactions[$cart_name]) && in_array($med['MED_NAME'], $interactions[$cart_name])) {
            $errors[] = $med['MED_NAME'] . " should not be combined with " . $cart_name;
        }
    }

    return $errors;
}
?>
