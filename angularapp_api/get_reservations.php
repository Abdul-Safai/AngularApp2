<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once("database.php");

// Grouped slots query
$query = "
  SELECT 
    conservationAreaName,
    reservationDate,
    reservationTime,
    SUM(spots_booked) AS total_booked,
    total_spots
  FROM reservations
  GROUP BY conservationAreaName, reservationDate, reservationTime
";
$statement = $db->prepare($query);
$statement->execute();
$groups = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();

// ✅ Add full customer list with IDs for each group
foreach ($groups as &$group) {
  $detailsQuery = "
    SELECT ID, customerName, spots_booked
    FROM reservations
    WHERE conservationAreaName = :area
      AND reservationDate = :date
      AND reservationTime = :time
  ";
  $stmt = $db->prepare($detailsQuery);
  $stmt->bindValue(':area', $group['conservationAreaName']);
  $stmt->bindValue(':date', $group['reservationDate']);
  $stmt->bindValue(':time', $group['reservationTime']);
  $stmt->execute();
  $group['customers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $stmt->closeCursor();
}

echo json_encode($groups);
?>
