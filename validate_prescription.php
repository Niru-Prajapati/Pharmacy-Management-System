<?php
include 'connection.php';

function validatePrescription($med_id, $quantity, $current_cart = []) {
    $errors = [];

    // Fetch medicine info
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM meds WHERE MED_ID = ?");
    $stmt->bind_param("i", $med_id);
    $stmt->execute();
    $med = $stmt->get_result()->fetch_assoc();

    if(!$med) {
        $errors[] = "Medicine not found.";
        return $errors;
    }

    // 1. Regex parsing for dosage format
    // Assume medicine dosage stored in 'dosage' column (if exists)
    $dosage = $med['dosage'] ?? ''; 
    if($dosage && !preg_match("/^\d+\s?(mg|ml)$/i", $dosage)) {
        $errors[] = "Invalid dosage format for " . $med['MED_NAME'];
    }

    // 2. Dosage limits (example: max 3 per order)
    $max_per_order = $med['max_per_order'] ?? 3;
    if($quantity > $max_per_order) {
        $errors[] = "You can only order up to $max_per_order units of " . $med['MED_NAME'];
    }

    // 3. Stock check
    if($quantity > $med['MED_QTY']) {
        $errors[] = "Not enough stock for " . $med['MED_NAME'];
    }

    // 4. Drug interaction (basic example)
    $interactions = [
        "Aspirin" => ["Ibuprofen"], 
        "Paracetamol" => ["Alcohol"]
    ];

    foreach($current_cart as $cart_med) {
        $cart_name = $cart_med['MED_NAME'] ?? $cart_med['med_name'] ?? '';
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
