<?php
include 'connection.php';

$search = $_POST['query'] ?? '';
if ($search === '') exit;

$searchEscaped = mysqli_real_escape_string($conn, $search);
$len = strlen($searchEscaped);

$sql = "
SELECT MED_NAME FROM meds
WHERE
    MED_NAME LIKE '%$searchEscaped%'
    OR SOUNDEX(LEFT(MED_NAME, $len)) = SOUNDEX('$searchEscaped')
LIMIT 5
";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    echo "<div class='suggestion'
        onclick=\"selectMedicine('".htmlspecialchars($row['MED_NAME'], ENT_QUOTES)."')\">"
        . htmlspecialchars($row['MED_NAME']) .
        "</div>";
}
