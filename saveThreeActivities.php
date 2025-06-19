<?php
session_start();
include "../config.php";

try {
  // Ricevi i dati JSON
  $input = json_decode(file_get_contents("php://input"), true);

  $id = $input['id'] ?? '';
  $chosen = $input['chosen'] ?? '';
  $notChosen = $input['notChosen'] ?? '';

  if ($id && $chosen && $notChosen) {
    $stmt = $connection->prepare("UPDATE StandByMe SET chosen = :chosen, notChosen = :notChosen WHERE id = :id");
    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":chosen", $chosen);
    $stmt->bindParam(":notChosen", $notChosen);

    if ($stmt->execute()) {
      echo "Dati salvati con successo.";
    } else {
      echo "Errore nella query.";
    }
  } else {
    echo "Dati incompleti.";
  }

} catch (PDOException $e) {
  echo "Errore DB: " . $e->getMessage();
}
?>
