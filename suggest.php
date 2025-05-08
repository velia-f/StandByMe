<?php
//private key to FBK OpenAI access 
$apiKey = 'XXXY';

// Take the json input (user + activities)
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['user']) || !isset($data['activities'])) {
  http_response_code(400);
  echo json_encode(["error" => "Missing data in the body"]);
  exit;
}

// Serialize user and activities for the prompt
$user = json_encode($data['user'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$activities = json_encode($data['activities'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo($user);
echo($activities);

//here the "prompt v2" revisited
$prompt = <<<PROMPT
Context:
User ID: $user
Attività in italiano: $activities_it
Attività in inglese: $activities_en

**Prompt dettagliato per AI – StandByMe e il sistema di selezione dei giochi** StandByMe è un progetto educativo innovativo che utilizza una piattaforma interattiva basata su giochi digitali per sensibilizzare e formare gli utenti su **tematiche legate alla violenza di genere, al consenso, agli stereotipi e alle dinamiche sociali che influenzano i comportamenti legati alla discriminazione e alla parit`a di genere**. 
L’obiettivo della piattaforma è **creare un percorso di apprendimento altamente personalizzato**, in cui ogni utente non sceglie liberamente i giochi da svolgere, ma viene guidato attraverso un **sistema di raccomandazione AI**, che seleziona il prossimo gioco sulla base di **interessi dichiarati, competenze misurate e performance ottenute nei giochi precedenti**.
**Struttura del sistema e logica di selezione dei giochi** La piattaforma si basa su **tre competenze chiave fondamentali**, che vengono continuamente valutate e aggiornate in
base alle risposte e alle scelte dell’utente durante il percorso di apprendimento. Queste competenze sono, in livello di difficoltà, ovvero quanto sono intellettualmente impegnative e i prerequisiti che richiedono di possedere:
1. **Raising Awareness (RA)** → La capacit`a di identificare e mettere in discussione norme sociali dannose, pregiudizi di genere, mascolinit`a tossica e aspettative culturali limitanti. Questo livello di consapevolezza permette di vedere come stereotipi e dinamiche di genere influenzano la societ`a e il nostro comportamento. Principalmente tale livello è introduttivo e pone le basi per ”aumentare l’attenzione” su tutti gli argomenti discussi 
2. **Empathy (E)** → La capacit`a di comprendere il punto di vista di altre persone, riconoscere situazioni di discriminazione e violenza e riflettere sugli effetti psicologici di determinati comportamenti. 
3. **Action (A)** → L’abilit`a di sfidare attivamente, ovvero essere in grado di
produrre materiale, gli stereotipi e i ruoli di genere imposti, sia nel proprio comportamento sia promuovendo un cambiamento nelle proprie cerchie sociali. Include il supporto a chi rompe gli schemi, il contrasto ai pregiudizi e la diffusione di un modello di equit`a e inclusione. Tale livello è il pi`u alto, in quanto lu studentu dovrebbe essere in grado di padroneggiare quasi tutti gli argomenti di GBV e GS, e quindi viene ritenuto possibili stimolarlu come richieste di produzione di materiale.
Ogni utente inizia il suo percorso con nessun test fatto, viene chiesto a loro cosa vogliono imparare e a cosa sono interessatu, dopo sulla base dei giochi presenti e delle preferenze espresse, devi fornire il primo gioco che misura le sue conoscenze di base e gli assegna un punteggio. Dopo aver finito il primo gioco, ti verrà dato questo messaggio, unito alle preferenze e unito al punteggio del primo gioco con eventualmente le risposte sbagliate. Quindi sulla base delle info che gi`a sapevi e del primo e nuovo punteggio, darai il secondo quiz migliore per aumentare pedagogicamente in modo rilevante il suo apprendimento. Poi loro faranno il secondo quiz e ti verrà dato in input un nuovo prompt con sempre le due prime parti pi`u il primo quiz e il secondo, e questo a ripetere.
A differenza di altre piattaforme educative gamificate, StandByMe **non permette agli utenti di scegliere liberamente il prossimo gioco**. Invece, la selezione avviene attraverso un sistema basato su AI che tiene conto di:
- **Le competenze in cui l’utente `e pi`u carente** (per rafforzare le aree pi`u deboli). 
- **Gli interessi dichiarati dall’utente** (per rendere l’esperienza più coinvolgente). 
- **Ipunteggi e le performance ottenute nei giochi precedenti** (per garantire una progressione equilibrata e stimolante).

Tu utilizzi questi dati per proporre il gioco pi`u adatto a migliorare le competenze mancanti, garantendo che ogni utente abbia un percorso **personalizzato, mirato e adattivo**.

Il vostro compito è quello di fornire agli utenti suggerimenti della attività personalizzata incentrata su argomenti di genere.
Assicuratevi che la risposta sia: 
- NON devi MAI vedere altri profili e quindi basarti solamente sui dati in $user e $activities_it e $activities_en e non vedere altri profili.
- Assicurarsi che NESSUNA attività venga ripetuta se precedentemente completata con successo (punteggio superiore a 0.60). Quindi se una attività è stata fatta in modo sufficiente, allora NON devi raccomandarla mai più.
- Assicurarsi che NON vengano suggerite attività equivalenti di traduzione, ovvero se unu utente ha fatto una attività in italiano allora non dovrai MAI la sua corrispettiva attività in altre lingue
- Su misura per le preferenze dell’utente: Considerare le parole chiave e gli interessi specificati dall’utente. 
- Tenete conto delle attivit`a completate in passato e dei punteggi ottenuti. Raccomandazioni diversificate e coinvolgenti: Includere una variet`a di tipi di attivit`a, come giochi interattivi, risorse educative e discussioni.
Puntate a introdurre nuove metodologie e stili di apprendimento. Motivazione pertinente: Fornire una breve spiegazione di come ogni attivit`a suggerita si collega agli obiettivi o alle preoccupazioni dell’utente. Incoraggiare il pensiero critico e la consapevolezza delle questioni di genere. Apprendimento progressivo: Assicurarsi che nessuna attivit`a venga ripetuta
se precedentemente completata con successo (punteggio superiore a 0.6). Adattare i suggerimenti in base al feedback dell’utente o all’evoluzione delle esigenze. Impegno critico: Consentire agli utenti di sfidare gli stereotipi e le idee sbagliate attraverso risorse basate sui fatti

**Catalogo completo dei giochi disponibili** All’interno della piattaforma, sono presenti una serie di giochi che coprono diverse sfaccettature del problema della violenza di genere e delle dinamiche relazionali. Ogni gioco si concentra su aspetti specifici e contribuisce a sviluppare una o pi`u delle tre competenze chiave.

Ecco **l’elenco completo di tutti i giochi** presenti nella piattaforma **StandByMe**, con una breve descrizione per ciascuno. **Lista completa dei giochi StandByMe** è disponibile in $activities_it e $activities_en

**Struttura del prompt per l’AI** 
**Contesto:** - **StandByMe `e una piattaforma educativa che utilizza giochi interattivi per sensibilizzare sulla violenza di genere, il consenso e
gli stereotipi di genere.** - **L’utente ha dichiarato di essere interessato a:** temi di interesse dichiarati. - **Storico dei giochi svolti e performance:** elenco giochi + punteggi ottenuti.
**Richiesta e Istruzioni:** ”Sulla base delle informazioni sopra, quale gioco `e il pi`u adatto per migliorare le competenze in cui l’utente `e carente, garantendo una progressione
efficace? Considera anche gli interessi dichiarati dall’utente e il livello di difficolt`a adeguato. Se esistono pi`u opzioni, proponi la migliore motivando la scelta.” Con questo prompt, l’AI (dovrebbe essere) in grado di determinare dinamicamente il percorso formativo migliore per ogni utente, garantendo un apprendimento personalizzato e progressivo.** 

Quindi te sei una esperta che vuole far insegnare allu studentu. Come unico output dovrai dire il nome del prossimo quiz adatto e quindi riportare dal suo campo "post_title" e anche il suo "url", secondo le tue analisi.
Devi esclusivamente prendere il testo che ricevi in input in $user, e dare in output il prossimo miglior quiz sulla base di quello che ti ha detto l’utente che vuole imparare e dello storico dei quiz fatti con una spiegazione. 

NON devi dare altro in output. Fornisci il gioco successivo pi`u pertinente in base ai punteggi recenti e agli argomenti di interesse espressi dall’utente.
Opzioni per risposte ambigue: In caso ci siano pi`u giochi che possono essere pertinenti, fai un elenco di essi dal ”migliore” al meno migliore ma comunque una scelta contenuta. Per ”migliore” si intende compensare le carenze emerse nei giochi precedenti, quelle pi`u gravi tra le presenti, o se non c’`e ancora nessun gioco allora dare massima importanza agli interessi dellu utente riportarti.
Respond ONLY in JSON format like:
{
  "title": "nome_della_attività",
  "url": "XXX"
}
tale che: nome_della_attività è "post_title" presente nel file JSON con tutte le attvità, e XXX è il "url" preso dallo stesso file
Rispondi **esclusivamente** in **JSON puro**, senza testo aggiuntivo, spiegazioni o commenti. La risposta deve iniziare direttamente con una `{` e contenere solo chiavi "title" e "url".
PROMPT;


// to extract only valid JSON from responses with extra text
function extractJsonFromMarkdown($text) {
  // searching for the block: ```json ... ```
  if (preg_match('/```json(.*?)```/s', $text, $matches)) {
    return json_decode(trim($matches[1]), true);
  }
  // Else, take the first valid "{}"
  $start = strpos($text, '{');
  $end = strrpos($text, '}');
  if ($start !== false && $end !== false && $end > $start) {
    $jsonString = substr($text, $start, $end - $start + 1);
    return json_decode($jsonString, true);
  }
  return null;
}

// Call to OpenAI
$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer $apiKey",
  "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  "model" => "gpt-4o-mini",
  "messages" => [
    ["role" => "user", "content" => $prompt]
  ],
  "temperature" => 0
]));

$response = curl_exec($ch);

if (curl_errno($ch)) {
  http_response_code(500);
  echo json_encode(["error" => "Error CURL: " . curl_error($ch)]);
  curl_close($ch);
  exit;
}

curl_close($ch);

// Decode the response
$result = json_decode($response, true);
$content = $result['choices'][0]['message']['content'] ?? null;

// Extract only JSON
$json = extractJsonFromMarkdown($content);

if ($json === null) {
  http_response_code(500);
  echo json_encode([
    "error" => "It was not possible to extract a valid JSON from the AI response.",
    "output_raw" => $content
  ]);
  exit;
}

// All ok: return it
header("Content-Type: application/json");
echo json_encode($json);
?>
