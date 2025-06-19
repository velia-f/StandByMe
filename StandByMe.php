<?php
require_once("../config.php");

/*if (!$connection) {
  echo "<pre>Connessione fallita</pre>";
} else{
	echo "<pre>connessa</pre>";
}

echo "<h1>DEBUG PHP ATTIVO</h1>";
*/

// Gestione delle richieste AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && 
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
  
  $json_input = file_get_contents('php://input');
  $data = json_decode($json_input, true);
  
  echo "<pre>Richiesta JSON ricevuta:</pre>";
  print_r($data);
  
  if ($data && isset($data['id_user']) && isset($data['chosen'])) {
    $id = filter_var($data['id_user'], FILTER_SANITIZE_STRING);
    $chosen = filter_var($data['chosen'], FILTER_SANITIZE_STRING);
    $notChosen = isset($data['notChosen']) ? filter_var($data['notChosen'], FILTER_SANITIZE_STRING) : '';
    $recommended = isset($data['recommended']) ? filter_var($data['recommended'], FILTER_SANITIZE_STRING) : '';
    
    try {
      $query = $connection->prepare("UPDATE StandByMe SET chosen = :chosen, notChosen = :notChosen, recommended = :recommended WHERE id_user = :id_user");
      $query->bindParam(":id_user", $id, PDO::PARAM_STR);
      $query->bindParam(":chosen", $chosen, PDO::PARAM_STR);
      $query->bindParam(":notChosen", $notChosen, PDO::PARAM_STR);
      $query->bindParam(":recommended", $recommended, PDO::PARAM_STR);
      
      if($query->execute()){
        echo "Attività salvate OK - Righe modificate: " . $query->rowCount() . "";
        echo json_encode(['success' => true]);
      } else{
        echo "Errore salvataggio attività";
        print_r($query->errorInfo());
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
      }
    } catch (Exception $e) {
      echo "Errore salvataggio attività: " . $e->getMessage() . "";
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
  }
  
  if ($data && isset($data['action']) && $data['action'] === 'preQuestionnaire') {
    $preUserIdQuestinnaire = isset($data["preUserIdQuestinnaire"]) ? filter_var($data["preUserIdQuestinnaire"], FILTER_SANITIZE_STRING) : '';
    
    if (empty($preUserIdQuestinnaire)) {
      echo "Errore: ID utente mancante";
      echo json_encode(['success' => false, 'error' => 'User ID missing']);
      exit;
    }
    
    echo "User ID: $preUserIdQuestinnaire";
    
    $campi = ["pq11", "pq12", "pq13", "pq14", "pq15", "pq16", "pq2", "pq3", "pq4", "pq5"];
    $risposte = [];

    foreach ($campi as $campo) {
      if (isset($data[$campo])) {
        $risposte[] = $data[$campo];
      } else {
        $risposte[] = "NA";
      }
    }
    
    //$open1 = isset($data["pq5"]) ? trim($data["pq5"]) : "";
    //$open2 = isset($data["pq6"]) ? trim($data["pq6"]) : "";
    //$open1 = filter_var($open1, FILTER_SANITIZE_STRING);
    //$open2 = filter_var($open2, FILTER_SANITIZE_STRING);
    $final_text = implode("-", $risposte); //. "-" . $open1 . "-" . $open2;
    echo "Final text: $final_text";

    try {
      $querypost = $connection->prepare("INSERT INTO StandByMe(id_user, preQuestionnaire, postQuestionnaire, chosen, notChosen, recommended) VALUES (:id_user, :preQuestionnaire, NULL, NULL, NULL, NULL)");
      $querypost->bindParam(":id_user", $preUserIdQuestinnaire, PDO::PARAM_STR);
      $querypost->bindParam(":preQuestionnaire", $final_text, PDO::PARAM_STR);
      
      if($querypost->execute()){
      	echo "Insert Pre OK";
        echo json_encode(['success' => true]);
      } else{
      	echo "Insert Pre NOT OK";
        print_r($querypost->errorInfo());
        echo json_encode(['success' => false, 'error' => 'Database insert failed']);
      }
    } catch (Exception $e) {
      echo "Errore: " . $e->getMessage() . "";
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
  }
  
  if ($data && isset($data['action']) && $data['action'] === 'postQuestionnaire') {
    $postUserIdQuestinnaire = isset($data["postUserIdQuestinnaire"]) ? filter_var($data["postUserIdQuestinnaire"], FILTER_SANITIZE_STRING) : '';
    
    if (empty($postUserIdQuestinnaire)) {
      echo "Errore: ID utente mancante per post-questionario";
      echo json_encode(['success' => false, 'error' => 'User ID missing']);
      exit;
    }
    
    echo "Post User ID: $postUserIdQuestinnaire";
    
    $campi = ["poq11", "poq12", "poq13", "poq14", "poq15", "poq21", "poq22", "poq23", "poq24", "poq25", "poq26", "poq3", "poq4", "poq5", "poq6"];
    //$campi = ["poq1", "poq2", "poq31", "poq32", "poq33", "poq34", "poq35", "poq36", "poq41", "poq42", "poq43"];
    $risposte = [];

    foreach ($campi as $campo) {
      if (isset($data[$campo])) {
        $risposte[] = $data[$campo];
      } else {
        $risposte[] = "NA";
      }
    }
    
    //$open1 = isset($data["poq5"]) ? trim($data["poq5"]) : "";
    //$open2 = isset($data["poq6"]) ? trim($data["poq6"]) : "";
    //$open1 = filter_var($open1, FILTER_SANITIZE_STRING);
    //$open2 = filter_var($open2, FILTER_SANITIZE_STRING);
    $final_text = implode("-", $risposte);// . "-" . $open1 . "-" . $open2;
    echo "Post Final text: $final_text";

    try {
      $querypost = $connection->prepare("UPDATE StandByMe SET postQuestionnaire = :postQuestionnaire WHERE id_user = :id_user");
      $querypost->bindParam(":id_user", $postUserIdQuestinnaire, PDO::PARAM_STR);
      $querypost->bindParam(":postQuestionnaire", $final_text, PDO::PARAM_STR);
      
      if($querypost->execute()){
      	echo "Update Post OK";
        echo "Righe modificate: " . $querypost->rowCount() . "";
        echo json_encode(['success' => true]);
      } else{
      	echo "Update Post NOT OK";
        print_r($querypost->errorInfo());
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
      }
    } catch (Exception $e) {
      echo "Errore Update: " . $e->getMessage() . "";
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
  }
}

// Gestione delle richieste POST normali (per debug o fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  echo "POST normale ricevuto:";
  print_r($_POST);
  
  session_start();

  if (isset($_POST['submitPre'])) {
    $preUserIdQuestinnaire = filter_var($_POST["preUserIdQuestinnaire"], FILTER_SANITIZE_STRING);
    
    echo "User ID: $preUserIdQuestinnaire";
    
    //$campi = ["pq1", "pq2", "pq31", "pq32", "pq33", "pq34", "pq35", "pq36", "pq41", "pq42", "pq43"];
    $campi = ["pq11", "pq12", "pq13", "pq14", "pq15", "pq16", "pq2", "pq3", "pq4", "pq5"];
    $risposte = [];

    foreach ($campi as $campo) {
      if (isset($_POST[$campo])) {
        $risposte[] = $_POST[$campo];
      } else {
        $risposte[] = "NA";
      }
    }
    
    //$open1 = isset($_POST["pq5"]) ? trim($_POST["pq5"]) : "";
    //$open2 = isset($_POST["pq6"]) ? trim($_POST["pq6"]) : "";
    //$open1 = filter_var($open1, FILTER_SANITIZE_STRING);
    //$open2 = filter_var($open2, FILTER_SANITIZE_STRING);
    $final_text = implode("-", $risposte); // . "-" . $open1 . "-" . $open2;
    echo "Final text: $final_text";

    try {
      $querypost = $connection->prepare("INSERT INTO StandByMe(id_user, preQuestionnaire, postQuestionnaire, chosen, notChosen, recommended) VALUES (:id_user, :preQuestionnaire, NULL, NULL, NULL, NULL)");
      $querypost->bindParam(":id_user", $preUserIdQuestinnaire, PDO::PARAM_STR);
      $querypost->bindParam(":preQuestionnaire", $final_text, PDO::PARAM_STR);
      
      if($querypost->execute()){
      	echo "Insert Pre OK";
      } else{
      	echo "Insert Pre NOT OK";
        print_r($querypost->errorInfo());
      }
    } catch (Exception $e) {
      echo "Errore: " . $e->getMessage() . "";
    }
  }

  if (isset($_POST['submitPost'])) {
    $postUserIdQuestinnaire = filter_var($_POST["postUserIdQuestinnaire"], FILTER_SANITIZE_STRING);
    
    echo "Post User ID: $postUserIdQuestinnaire";
    
    //$campi = ["poq1", "poq2", "poq31", "poq32", "poq33", "poq34", "poq35", "poq36", "poq41", "poq42", "poq43"];
    $campi = ["poq11", "poq12", "poq13", "poq14", "poq15", "poq21", "poq22", "poq23", "poq24", "poq25", "poq26", "poq3", "poq4", "poq5", "poq6"];
    $risposte = [];

    foreach ($campi as $campo) {
      if (isset($_POST[$campo])) {
        $risposte[] = $_POST[$campo];
      } else {
        $risposte[] = "NA";
      }
    }
    
    //$open1 = isset($_POST["poq5"]) ? trim($_POST["poq5"]) : "";
    //$open2 = isset($_POST["poq6"]) ? trim($_POST["poq6"]) : "";
    //$open1 = filter_var($open1, FILTER_SANITIZE_STRING);
    //$open2 = filter_var($open2, FILTER_SANITIZE_STRING);
    $final_text = implode("-", $risposte); // . "-" . $open1 . "-" . $open2;
    echo "Post Final text: $final_text";

    try {
      $querypost = $connection->prepare("UPDATE StandByMe SET postQuestionnaire = :postQuestionnaire WHERE id_user = :id_user");
      $querypost->bindParam(":id_user", $postUserIdQuestinnaire, PDO::PARAM_STR);
      $querypost->bindParam(":postQuestionnaire", $final_text, PDO::PARAM_STR);
      
      if($querypost->execute()){
      	echo "Update Post OK";
        echo "Righe modificate: " . $querypost->rowCount() . "";
      } else{
      	echo "Update Post NOT OK";
        print_r($querypost->errorInfo());
      }
    } catch (Exception $e) {
      echo "Errore Update: " . $e->getMessage() . "";
    }
  }
} else {
  //echo "Nessun POST ricevuto";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <?php include "../header.php"; ?>
  <title>SLPtPLP - StandByMe</title>
  <style>
  
html, body {
  height: 100%;
  margin: 0;
  font-family: sans-serif;
  overflow: auto;
}

main {
  padding-top: 20px;
}

.questionnaire{
  display: none;
  flex-direction: row; /* row o column */
  align-items: center;
  justify-content: center;
  min-height: 100vh;
}

/* Overlay iniziale */
.overlay {
  position: fixed;
  inset: 0;
  background: #fff;
  z-index: 999;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 30px;
  text-align: center;
}

.yourUsername {
  position: fixed;
  display: none;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 400px;
  max-width: 90%;
  background: #fff;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 30px;
  text-align: center;
  border-radius: 10px;
  box-shadow: 0 0 20px rgba(0,0,0,0.2);
}


/* Contenitore principale */
.container {
  display: none;
  height: 100vh;
  padding-top: 80px;
  overflow: hidden;
}

/* Colonna sinistra */
.left {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
  border-right: 2px solid #ccc;
}

/* Colonna destra */
.right {
  flex: 1;
  padding: 20px;
  background-color: #f9f9f9;
  overflow-y: auto;
}

/* Log interno */
#log {
  max-height: 70vh;
  overflow-y: auto;
  white-space: pre-wrap;
  background: #fff;
  border: 1px solid #ddd;
  padding: 10px;
  border-radius: 5px;
}

#instructionOverlay {
    display: none;
}

#finalMessage{
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #4CAF50;
  color: white;
  padding: 40px;
  border-radius: 10px;
  text-align: center;
  font-size: 1.5em;
}
  </style>
</head>

<body>
<?php include "../navbar.php"; ?>

<div class="overlay" id="introOverlay" style="display: flex; font-size: 0.8em;">
  <h1>Crea un profilo su <a href="https://standbymeplatform.eu/register/" target="_blank">StandByMe</a> e ricorda il tuo Username.
  <br>POI vai su <a href="https://standbymeplatform.eu/en/preferences/" target="_blank">Mie Preferenze</a> e rispondi dicendo cosa vorresti imparare e cosa ti interessa.
  <br>NON ricaricare mai la pagina, se no dovrai fare tutto da capo.</h1>
  <br><p>SOLO dopo aver creato il profilo(vedi sopra), scrivi "T" nel campo qui sotto per continuare.</p>
  <input id="unlockKey" type="text" placeholder="Scrivi qui la parola segreta per andare avanti...">
</div>

<div class="yourUsername" id="usernameOverlay" style="display: none; font-size: 0.8em;">
  <h1>Scrivi il tuo Username, cosìm ti diremo il tuo ID che useremo dopo:</h1>
  <input type="text" id="UserIdFromUsername" required style="flex: 1;">
  <button id="yourUsernameButton" onclick="getUserIdFromUsername()" type="submit" style="margin-top: 10px;">Continua</button>
</div>


<div class="questionnaire" id="preQuestionnaire" style="display: none; font-family: 'Segoe UI', sans-serif; font-size: 1em; background-color: #f9f9f9; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 600px; margin: auto;">
  <form id="preForm" method="post" style="display: flex; flex-direction: column; gap: 16px; width: 100%;">
    <h2 style="text-align: center; color: #333;"><br><br>Pre-Questionario</h2>

    <div style="display: flex; flex-direction: column;">
      <label for="preUserId" style="margin-bottom: 5px; font-weight: bold;">ID Utente:</label>
      <input type="number" id="preUserIdQuestinnaire" name="preUserIdQuestinnaire" required style="padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
    </div>
    
    
    <div>
      <p style="margin-bottom: 8px;">
          Ti chiediamo di rispondere a queste affermazioni utilizzando una scala da 1 a 5, dove 1 = per nulla e 5 = moltissimo
      </p>
    </div>

    <!-- DOMANDE RADIO -->
    
    <div>
      <p style="margin-bottom: 8px;">1) Penso di conoscere questi argomenti:</p>
      <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <p>Violenza di genere (VG):</p>
          <label><input type="radio" name="pq11" value="1" required checked> 1</label>
          <label><input type="radio" name="pq11" value="2"> 2</label>
          <label><input type="radio" name="pq11" value="3"> 3</label>
          <label><input type="radio" name="pq11" value="4"> 4</label>
          <label><input type="radio" name="pq11" value="5"> 5</label>
      </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p>Violenza di genere online:</p>
      <label><input type="radio" name="pq12" value="1" required checked> 1</label>
      <label><input type="radio" name="pq12" value="2"> 2</label>
      <label><input type="radio" name="pq12" value="3"> 3</label>
      <label><input type="radio" name="pq12" value="4"> 4</label>
      <label><input type="radio" name="pq12" value="5"> 5</label>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p>Identità di Genere:</p>
      <label><input type="radio" name="pq13" value="1" required checked> 1</label>
      <label><input type="radio" name="pq13" value="2"> 2</label>
      <label><input type="radio" name="pq13" value="3"> 3</label>
      <label><input type="radio" name="pq13" value="4"> 4</label>
      <label><input type="radio" name="pq13" value="5"> 5</label>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p>Stereotipi di Genere:</p>
      <label><input type="radio" name="pq14" value="1" required checked> 1</label>
      <label><input type="radio" name="pq14" value="2"> 2</label>
      <label><input type="radio" name="pq14" value="3"> 3</label>
      <label><input type="radio" name="pq14" value="4"> 4</label>
      <label><input type="radio" name="pq14" value="5"> 5</label>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p>Mascolinità Tossica:</p>
      <label><input type="radio" name="pq15" value="1" required checked> 1</label>
      <label><input type="radio" name="pq15" value="2"> 2</label>
      <label><input type="radio" name="pq15" value="3"> 3</label>
      <label><input type="radio" name="pq15" value="4"> 4</label>
      <label><input type="radio" name="pq15" value="5"> 5</label>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p>Consenso Affettivo:</p>
      <label><input type="radio" name="pq16" value="1" required checked> 1</label>
      <label><input type="radio" name="pq16" value="2"> 2</label>
      <label><input type="radio" name="pq16" value="3"> 3</label>
      <label><input type="radio" name="pq16" value="4"> 4</label>
      <label><input type="radio" name="pq16" value="5"> 5</label>
    </div>
  </div>

    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p style="margin-bottom: 8px;">2) Mi informo e mi aggiorno sui termini e sui concetti legati al tema della violenza di genere.</p>
      <label><input type="radio" name="pq2" value="1" required checked> 1</label>
      <label><input type="radio" name="pq2" value="2"> 2</label>
      <label><input type="radio" name="pq2" value="3"> 3</label>
      <label><input type="radio" name="pq2" value="4"> 4</label>
      <label><input type="radio" name="pq2" value="5"> 5</label>
    </div>

    <!-- BLOCCO 3 -->
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p style="font-weight: bold; margin-bottom: 5px;">3) Provo empatia verso le vittime di violenza e cerco di comprenderne le esperienze.</p>
        <label><input type="radio" name="pq3" value="1" required checked> 1</label>
        <label><input type="radio" name="pq3" value="2"> 2</label>
        <label><input type="radio" name="pq3" value="3"> 3</label>
        <label><input type="radio" name="pq3" value="4"> 4</label>
        <label><input type="radio" name="pq3" value="5"> 5</label>
    </div>
    
    <!-- BLOCCO 4 -->
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p style="margin-bottom: 8px;">4) Partecipo attivamente ad azioni di sensibilizzazione e contrasto.</p>
      <label><input type="radio" name="pq4" value="1" required checked> 1</label>
      <label><input type="radio" name="pq4" value="2"> 2</label>
      <label><input type="radio" name="pq4" value="3"> 3</label>
      <label><input type="radio" name="pq4" value="4"> 4</label>
      <label><input type="radio" name="pq4" value="5"> 5</label>
    </div>

    <!-- DOMANDA 5 -->
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p style="margin-bottom: 8px;">5) Penso che questa esperienza mi aiuterà ad imparare qualcosa di nuovo.</p>
      <label><input type="radio" name="pq5" value="1" required checked> 1</label>
      <label><input type="radio" name="pq5" value="2"> 2</label>
      <label><input type="radio" name="pq5" value="3"> 3</label>
      <label><input type="radio" name="pq5" value="4"> 4</label>
      <label><input type="radio" name="pq5" value="5"> 5</label>
    </div>

    <!-- BOTTONE -->
    <button id="submitPre" name="submitPre" type="submit" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
      Invia e inizia
    </button>
  </form>
</div>
   

<div class="container" id="mainContent" style="display: none;">
  <div class="left">
    <h1>Suggerimento Attivtà</h1>
    <label for="userId">Inserisci il tuo ID (numero):</label>
    <input type="number" id="userId" required>
    <button onclick="getSuggestion()">Ottieni Raccomandazione</button>
    <div id="loading" style="margin-top:10px;display:none;color:#666;font-style:italic;">⏳ Caricamento...</div>
    <div id="result"></div>
  </div>

  <div class="right">
    <h2>Log</h2>
    <div id="log" style="white-space: pre-wrap;"></div>
  </div>
</div>


<div class="questionnaire" id="postQuestionnaire" style="display: none; font-family: 'Segoe UI', sans-serif; font-size: 1em; background-color: #f9f9f9; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 600px; margin: auto;">
  <form id="postForm" method="post" style="display: flex; flex-direction: column; gap: 16px; width: 100%;">
    <h2 style="text-align: center; color: #333;"><br><br>Post-Questionario</h2>
    
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <label for="preUserId" style="flex: 1;">ID Utente:</label>
      <input type="number" id="postUserIdQuestinnaire" name="postUserIdQuestinnaire" required style="flex: 1;">
    </div>

  <div>
    <p style="margin-bottom: 8px;">1) Ho trovato le attività/quiz a cui ho partecipato:</p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <label><input type="checkbox" name="poq11" value="adatte"> Adatte a me</label>
      <label><input type="checkbox" name="poq12" value="noiose"> Noiose</label>
      <label><input type="checkbox" name="poq13" value="divertenti"> Divertenti</label>
      <label><input type="checkbox" name="poq14" value="ripetitive"> Ripetitive</label>
      <label><input type="checkbox" name="poq15" value="interessanti"> Interessanti</label>
    </div>
  </div>


  <div>
    <p style="margin-bottom: 8px;">2) Ora, penso di conoscere questi argomenti:</p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <p>Violenza di genere (VG):</p>
        <label><input type="radio" name="poq21" value="1" required checked> 1</label>
        <label><input type="radio" name="poq21" value="2"> 2</label>
        <label><input type="radio" name="poq21" value="3"> 3</label>
        <label><input type="radio" name="poq21" value="4"> 4</label>
        <label><input type="radio" name="poq21" value="5"> 5</label>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p>Violenza di genere online:</p>
      <label><input type="radio" name="poq22" value="1" required checked> 1</label>
      <label><input type="radio" name="poq22" value="2"> 2</label>
      <label><input type="radio" name="poq22" value="3"> 3</label>
      <label><input type="radio" name="poq22" value="4"> 4</label>
      <label><input type="radio" name="poq22" value="5"> 5</label>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p>Identità di Genere:</p>
      <label><input type="radio" name="poq23" value="1" required checked> 1</label>
      <label><input type="radio" name="poq23" value="2"> 2</label>
      <label><input type="radio" name="poq23" value="3"> 3</label>
      <label><input type="radio" name="poq23" value="4"> 4</label>
      <label><input type="radio" name="poq23" value="5"> 5</label>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p>Stereotipi di Genere:</p>
      <label><input type="radio" name="poq24" value="1" required checked> 1</label>
      <label><input type="radio" name="poq24" value="2"> 2</label>
      <label><input type="radio" name="poq24" value="3"> 3</label>
      <label><input type="radio" name="poq24" value="4"> 4</label>
      <label><input type="radio" name="poq24" value="5"> 5</label>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p>Mascolinità Tossica:</p>
      <label><input type="radio" name="poq25" value="1" required checked> 1</label>
      <label><input type="radio" name="poq25" value="2"> 2</label>
      <label><input type="radio" name="poq25" value="3"> 3</label>
      <label><input type="radio" name="poq25" value="4"> 4</label>
      <label><input type="radio" name="poq25" value="5"> 5</label>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p>Consenso Affettivo:</p>
      <label><input type="radio" name="poq26" value="1" required checked> 1</label>
      <label><input type="radio" name="poq26" value="2"> 2</label>
      <label><input type="radio" name="poq26" value="3"> 3</label>
      <label><input type="radio" name="poq26" value="4"> 4</label>
      <label><input type="radio" name="poq26" value="5"> 5</label>
    </div>
  </div>

    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p style="margin-bottom: 8px;">3) In futuro, mi informerò e mi aggiornerò sui termini e sui concetti legati al tema della violenza di genere.</p>
      <label><input type="radio" name="poq3" value="1" required checked> 1</label>
      <label><input type="radio" name="poq3" value="2"> 2</label>
      <label><input type="radio" name="poq3" value="3"> 3</label>
      <label><input type="radio" name="poq3" value="4"> 4</label>
      <label><input type="radio" name="poq3" value="5"> 5</label>
    </div>

    <!-- BLOCCO 4 -->
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p style="font-weight: bold; margin-bottom: 5px;">4) In futuro, proverò empatia verso le vittime di violenza e cercherò di comprenderne le esperienze.</p>
        <label><input type="radio" name="poq4" value="1" required checked> 1</label>
        <label><input type="radio" name="poq4" value="2"> 2</label>
        <label><input type="radio" name="poq4" value="3"> 3</label>
        <label><input type="radio" name="poq4" value="4"> 4</label>
        <label><input type="radio" name="poq4" value="5"> 5</label>
    </div>
    
    <!-- BLOCCO 5 -->
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p style="margin-bottom: 8px;">5) In futuro, parteciperò attivamente ad azioni di sensibilizzazione e contrasto.</p>
      <label><input type="radio" name="poq5" value="1" required checked> 1</label>
      <label><input type="radio" name="poq5" value="2"> 2</label>
      <label><input type="radio" name="poq5" value="3"> 3</label>
      <label><input type="radio" name="poq5" value="4"> 4</label>
      <label><input type="radio" name="poq5" value="5"> 5</label>
    </div>

    <!-- DOMANDA 6 -->
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <p style="margin-bottom: 8px;">6) Penso che questa esperienza mi ha aiutatu ad imparare qualcosa di nuovo</p>
      <label><input type="radio" name="poq6" value="1" required checked> 1</label>
      <label><input type="radio" name="poq6" value="2"> 2</label>
      <label><input type="radio" name="poq6" value="3"> 3</label>
      <label><input type="radio" name="poq6" value="4"> 4</label>
      <label><input type="radio" name="poq6" value="5"> 5</label>
    </div>

    <!-- BOTTONE -->
    <button id="submitPost" name="submitPost" type="submit" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
      Invia e termina
    </button>
  </form>
</div>


<div id="instructionOverlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background-color: rgba(0,0,0,0.7); color: white; display: flex; align-items: center;
  justify-content: center; text-align: center; padding: 20px;">
  <div style="background: #222; padding: 20px; border-radius: 10px;">
    <h3>Ben tornatu! <br> Premi il pulsante "Ottieni Raccomandazione" per un nuovo suggerimento.</h3>
    <button onclick="document.getElementById('instructionOverlay').style.display='none'">Ok</button>
  </div>
</div>

<div id="postQuestionnaireOverlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background-color: rgba(0,0,0,0.7); color: white; display: flex; align-items: center;
  justify-content: center; text-align: center; padding: 20px;">
  <div style="background: #222; padding: 20px; border-radius: 10px;">
    <h3>Grazie! <br> Ora per piacere compila il questionario finale.</h3>
    <button onclick="showPostQuestionnaire()">Continua</button>
  </div>
</div>

<div id="finalMessage" style="display: none;">
  <h2>Grazie 1000! <br> ;3</h2>
  <button onclick="hideFinalMessage()">Prego 500*2 :)</button>
</div>

<script>
let clickCount = 0;
let blocked = false;
let storedId = null;
let storedUsername = null;

/*
document.getElementById("introOverlay").style.display = 'none';
document.getElementById('instructionOverlay').style.display = 'flex';
document.getElementById("postQuestionnaireOverlay").style.display = 'flex';
document.getElementById("usernameOverlay").style.display = 'flex';
document.getElementById("preQuestionnaire").style.display = 'flex';
document.getElementById("postQuestionnaire").style.display = 'flex';
document.getElementById("mainContent").style.display = 'flex';
*/

function threePopup() {
  document.getElementById('instructionOverlay').style.display = 'flex';
}

function hideFinalMessage(){
  document.getElementById('finalMessage').style.display = 'flex';
  window.location.reload();
}

// Show main content on "T"
window.addEventListener("DOMContentLoaded", function() {
  document.getElementById("unlockKey").addEventListener("keydown", function(e) {
    if (e.key.toLowerCase() === 't') {
      document.getElementById("introOverlay").style.display = 'none';
      document.getElementById("usernameOverlay").style.display = 'flex';


      document.getElementById('instructionOverlay').style.display = 'none';
      document.getElementById("postQuestionnaireOverlay").style.display = 'none';
      document.getElementById("preQuestionnaire").style.display = 'none';
      document.getElementById("postQuestionnaire").style.display = 'none';
      document.getElementById("mainContent").style.display = 'none';
    }
  });
});

async function getUserIdFromUsername() {
  document.getElementById("preUserIdQuestinnaire").value = storedId;
  const usernameId = document.getElementById("UserIdFromUsername").value;
  if (!usernameId) return alert("Please insert a valid Username");
  storedUsername = usernameId;

  try {
    const response = await fetch(`proxy.php?url=https://standbymeplatform.eu//wp-json/wp/v2/get_user_id_by_username?username=${usernameId}`);
    const userData = await response.json();
    
    console.log("Data received:", userData);
    if (!userData || !userData.success || !userData.user_id) {
      alert("Nessun utente trovato con questo username.");
      return;
    }

    const userId = userData.user_id;
    console.log("User ID:", userId);

    alert(`Il tuo ID (ricavato dal tuo username) è: --> ${userId} <--\n(copialo e conservalo)`);
    storedId = userId;
    document.getElementById("usernameOverlay").style.display = 'none';
    document.getElementById("preQuestionnaire").style.display = 'flex';

    document.getElementById("introOverlay").style.display = 'none';
    document.getElementById('instructionOverlay').style.display = 'none';
    document.getElementById("postQuestionnaireOverlay").style.display = 'none';
    document.getElementById("postQuestionnaire").style.display = 'none';
    document.getElementById("mainContent").style.display = 'none';
    document.getElementById("preUserIdQuestinnaire").value = storedId;
  } catch (err) {
    logTo("Errore:", err);
    document.getElementById("result").innerText = "Errore getUserIdFromUsername.";
  }
}


function getFormData(form) {
  const formData = new FormData(form);
  const data = {};
  
  for (let [key, value] of formData.entries()) {
    data[key] = value;
  }
  
  return data;
}


async function startExperience(e) {
  e.preventDefault();
  
  const form = document.getElementById("preForm");
  const formData = getFormData(form);
  
  formData.action = 'preQuestionnaire';
  
  if (!formData.preUserIdQuestinnaire && storedId) {
    formData.preUserIdQuestinnaire = storedId;
  }
  
  storedId = formData.preUserIdQuestinnaire;
  
  if (!storedId) {
    alert("Errore: ID utente mancante. Riprova il processo dall'inizio.");
    return;
  }
  
  console.log("Invio pre-questionario:", formData);
  
  try {
    const response = await fetch(window.location.href, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    });
    
    const responseText = await response.text();
    console.log("Risposta server pre-questionario:", responseText);
    
    if (responseText.includes('{"success":true}')) {
      console.log("Pre-questionario salvato con successo");
      
      document.getElementById("preQuestionnaire").style.display = 'none';
      document.getElementById("mainContent").style.display = 'flex';

      document.getElementById("introOverlay").style.display = 'none';
      document.getElementById("usernameOverlay").style.display = 'none';
      document.getElementById('instructionOverlay').style.display = 'none';
      document.getElementById("postQuestionnaireOverlay").style.display = 'none';
      document.getElementById("postQuestionnaire").style.display = 'none';
      document.getElementById("userId").value = storedId;
    } else {
      console.error("Errore nel salvataggio del pre-questionario");
      alert("Errore nel salvataggio. Riprova.");
    }
  } catch (err) {
    console.error("Errore invio pre-questionario:", err);
    alert("Errore di connessione. Riprova.");
  }
}


function endExperience() {
  document.getElementById("mainContent").style.display = 'none';
  document.getElementById("postQuestionnaire").style.display = 'flex';

  document.getElementById("introOverlay").style.display = 'none';
  document.getElementById("usernameOverlay").style.display = 'none';
  document.getElementById('instructionOverlay').style.display = 'none';
  document.getElementById("postQuestionnaireOverlay").style.display = 'none';
  document.getElementById("preQuestionnaire").style.display = 'none';
}


async function finalGreeting(e) {
  e.preventDefault();
  
  const form = document.getElementById("postForm");
  const formData = getFormData(form);
  formData.action = 'postQuestionnaire';
  
  if (!formData.postUserIdQuestinnaire && storedId) { formData.postUserIdQuestinnaire = storedId; }
  
  console.log("Invio post-questionario:", formData);
  
  try {
    const response = await fetch(window.location.href, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    });
    
    const responseText = await response.text();
    console.log("Risposta server post-questionario:", responseText);
    
    if (responseText.includes('{"success":true}')) {
      console.log("Post-questionario salvato con successo");
      
      document.getElementById("postQuestionnaire").style.display = 'none';

      document.getElementById("introOverlay").style.display = 'none';
      document.getElementById("usernameOverlay").style.display = 'none';
      document.getElementById('instructionOverlay').style.display = 'none';
      document.getElementById("postQuestionnaireOverlay").style.display = 'none';
      document.getElementById("preQuestionnaire").style.display = 'none';
      document.getElementById("mainContent").style.display = 'none';

      document.getElementById("finalMessage").style.display = 'flex';
    } else {
      console.error("Errore nel salvataggio del post-questionario");
      alert("Errore nel salvataggio. Riprova.");
    }
  } catch (err) {
    console.error("Errore invio post-questionario:", err);
    alert("Errore di connessione. Riprova.");
  }
}

function logTo(label, data) {
  const log = document.getElementById("log");
  const entry = document.createElement("div");
  entry.innerHTML = `<span style="color:red;font-weight:bold;">${label}</span> ${typeof data === "object" ? JSON.stringify(data, null, 2) : data}`;
  log.appendChild(entry);
}

function showLoading() {
  document.getElementById("loading").style.display = "block";
  document.getElementById("result").innerHTML = "";
}

function hideLoading() {
  document.getElementById("loading").style.display = "none";
}

async function getSuggestion() {
  const userId = document.getElementById("userId").value;
  if (!userId) return alert("Please insert a valid ID");
  storedId = userId;

  showLoading();

  try {
    const [userRes, actRes] = await Promise.all([
      fetch(`proxy.php?url=https://standbymeplatform.eu/wp-json/wp/v2/get_user_data?user_id=${userId}`),
      fetch(`proxy.php?url=https://standbymeplatform.eu/wp-json/wp/v2/activities?language=it`)
    ]);

    const userData = await userRes.json();
    const activities = await actRes.json();
    logTo("\nData received:", {userData, activities});

    const response = await fetch("suggest.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ user: userData, activities_it: activities })
    });

    const responseText = await response.text();
    const finalPart = responseText.substring(responseText.lastIndexOf("]") + 1).trim();
    const cleanedJson = finalPart.replace(/^"+|"+$/g, '').replace(/\\"/g, '"').replace(/\\\//g, '/').replace(/""/g, '"');
    const jsonResponse = JSON.parse(cleanedJson);

    hideLoading();

    if (jsonResponse?.title_1 && jsonResponse?.title_2) {
      // Randomizza l'ordine
      const isRecommendedFirst = Math.random() < 0.5;
      
      if (isRecommendedFirst) {
        // Raccomandato prima
        document.getElementById("result").innerHTML = `
        <h2 style="color: red;">SCEGLI UNA TRA LE DUE ATTIVITA'</h2><br>
          <div class="result-box" id="box1">
            <strong>${jsonResponse.title_1}</strong>
            <p>Descrizione: ${jsonResponse.description_1}</p>
            <p>Motivo: ${jsonResponse.reason_1}</p>
            <button onclick="choose(1, '${jsonResponse.url_1}', '${jsonResponse.title_1}', '${jsonResponse.title_2}', true)">Avvia questa attività</button>
          </div>
          <br>
          <hr>
          <br>
          <div class="result-box" id="box2">
            <strong>${jsonResponse.title_2}</strong>
            <p>Descrizione: ${jsonResponse.description_2}</p>
            <p>Motivo: ${jsonResponse.reason_2}</p>
            <button onclick="choose(2, '${jsonResponse.url_2}', '${jsonResponse.title_2}', '${jsonResponse.title_1}', false)">Avvia questa attività</button>
          </div>`;
      } else {
        // Non raccomandato prima
        document.getElementById("result").innerHTML = `
        <h2 style="color: red;">SCEGLI UNA TRA LE DUE ATTIVITA'</h2><br>
          <div class="result-box" id="box1">
            <strong>${jsonResponse.title_2}</strong>
            <p>Descrizione: ${jsonResponse.description_2}</p>
            <p>Motivo: ${jsonResponse.reason_2}</p>
            <button onclick="choose(1, '${jsonResponse.url_2}', '${jsonResponse.title_2}', '${jsonResponse.title_1}', false)">Avvia questa attività</button>
          </div>
          <br>
          <hr>
          <br>
          <div class="result-box" id="box2">
            <strong>${jsonResponse.title_1}</strong>
            <p>Descrizione: ${jsonResponse.description_1}</p>
            <p>Motivo: ${jsonResponse.reason_1}</p>
            <button onclick="choose(2, '${jsonResponse.url_1}', '${jsonResponse.title_1}', '${jsonResponse.title_2}', true)">Avvia questa attività</button>
          </div>`;
      }
    }
  } catch (err) {
    logTo("Errore:", err);
    alert(err);
    document.getElementById("result").innerText = "Errore generico.";
  }
}

function showPostQuestionnaire() {
  document.getElementById("instructionOverlay").style.display = 'none';
  document.getElementById("postQuestionnaireOverlay").style.display = 'flex';
  document.getElementById("mainContent").style.display = 'none';
  document.getElementById("postUserIdQuestinnaire").value = storedId;
  document.getElementById("postQuestionnaire").style.display = 'flex';

  document.getElementById("introOverlay").style.display = 'none';
  document.getElementById("usernameOverlay").style.display = 'none';
  document.getElementById("preQuestionnaire").style.display = 'none';
  endExperience();
}

let clickedIdsChosen = [];
let clickedIdsNotChosen = [];
let recommendedActivity = [];

function choose(which, url, activityTitleChosen, activityTitleNotChosen, isRecommended) {
  if (blocked) return;
  blocked = true;
  document.getElementById(which === 1 ? "box2" : "box1").style.opacity = 0.5;
  clickCount++;

  clickedIdsChosen.push(activityTitleChosen);
  clickedIdsNotChosen.push(activityTitleNotChosen);
  
  if(isRecommended === true || which === 1) { 
    recommendedActivity.push(activityTitleChosen); 
  } else if(isRecommended === false || which === 2) {
    recommendedActivity.push(activityTitleNotChosen); 
  }

  window.open(url, '_blank');

  if (clickCount < 3) {
    threePopup();
    document.getElementById("result").innerHTML = '';
    blocked = false;
  } else {
    setTimeout(() => {
      document.getElementById("postQuestionnaireOverlay").style.display = 'flex';

      document.getElementById("introOverlay").style.display = 'none';
      document.getElementById("usernameOverlay").style.display = 'none';
      document.getElementById('instructionOverlay').style.display = 'none';
      document.getElementById("preQuestionnaire").style.display = 'none';
      document.getElementById("postQuestionnaire").style.display = 'none';
      document.getElementById("mainContent").style.display = 'none';

      const data = {
        id_user: storedId,
        chosen: clickedIdsChosen.join("-"),
        notChosen: clickedIdsNotChosen.join("-"),
        recommended: recommendedActivity.join("-")
      };

      console.log("Invio dati attività:", data);

      // Invia i dati allo stesso file PHP
      fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      })
      .then(res => res.text())
      .then(response => {
        console.log("Risposta dal server:", response);
        document.getElementById("postUserIdQuestinnaire").value = storedId;
        endExperience();
      })
      .catch(err => {
        console.error("Errore nell'invio:", err);
        document.getElementById("postUserIdQuestinnaire").value = storedId;
        endExperience();
      });
    }, 1000);
  }
}

// Event listeners per i form
document.addEventListener('DOMContentLoaded', function() {
  const preForm = document.getElementById("preForm");
  if (preForm) {
    preForm.addEventListener("submit", startExperience);
  }

  const postForm = document.getElementById("postForm");
  if (postForm) {
    postForm.addEventListener("submit", finalGreeting);
  }
});
</script>

</body>
</html>
