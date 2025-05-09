<?php
//private key to FBK OpenAI access 
$apiKey = 'XXXY';

// Take the json input (user + activities)
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['user']) || !isset($data['activities_it']) || !isset($data['activities_en'])) {
  http_response_code(400);
  echo json_encode(["error" => "Missing data in the body"]);
  exit;
}

// Serialize user and activities for the prompt
$user = json_encode($data['user'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$activities_it = json_encode($data['activities_it'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$activities_en = json_encode($data['activities_en'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo($user);
echo($activities_it);
echo($activities_en);

//here the "prompt v2" revisited
$prompt = <<<PROMPT
Context:
User ID: $user
Activities in Italian: $activities_it
Activities in english: $activities_en

**Detailed Prompt for AI - StandByMe and the game selection system** StandByMe is an innovative educational project that uses an interactive platform based on digital games to raise awareness and educate users on **issues related to gender violence, consent, stereotypes and social dynamics that influence behaviors related to discrimination and gender equality**. 
The goal of the platform is **to create a highly personalized learning pathway**, in which each user does not freely choose the games to play, but is guided through an **AI recommendation system**, which selects the next game based on **stated interests, measured skills, and performance in previous games**.
**System structure and game selection logic** The platform is based on **three core competencies**, which are continuously evaluated and updated based on
based on the user's responses and choices during the learning journey. These skills are, in level of difficulty, i.e., how intellectually challenging they are and the prerequisites they require one to possess:
1. **Raising Awareness (RA)** → The ability to identify and challenge harmful social norms, gender biases, toxic masculinity and limiting cultural expectations. This level of awareness allows us to see how gender stereotypes and dynamics influence society and our behavior. Primarily this level is introductory and lays the groundwork for “raising awareness” of all the topics discussed 
2. **Empathy (E)** → The ability to understand other people's point of view, recognize situations of discrimination and violence, and reflect on the psychological effects of certain behaviors. 
3. **Action (A)** → The ability to actively challenge, that is, to be able to
produce material, stereotypes and imposed gender roles, both in one's own behavior and by promoting change in one's social circles. It includes supporting those who break the mould, countering prejudice and spreading a model of equity and inclusion. This level is the highest, as lu studentu should be able to master almost all the topics of GBV and GS, and therefore it is considered possible stimullu as material production requests.
Each user starts his path with no test done, they are asked what they want to learn and what they are interested in, after based on the games present and preferences expressed, you have to provide the first game that measures his basic knowledge and gives him a score. After finishing the first game, you will be given this message, combined with the preferences and combined with the score of the first game with possibly wrong answers. Then based on the info you already knew and the first and new score, you will give the second best quiz to pedagogically increase his learning in a relevant way. Then they will take the second quiz and you will be given as input a new prompt with always the two first parts plus the first quiz and the second, and that to repeat.
Unlike other gamified educational platforms, StandByMe **does not allow users to freely choose the next game**. Instead, selection is done through an AI-based system that takes into account:
- **The skills in which the user is most lacking** (to strengthen weaker areas). 
- **The user's stated interests** (to make the experience more engaging). 
- **Scores and performance achieved in previous games** (to ensure a balanced and challenging progression).

You use this data to propose the most appropriate game to improve missing skills, ensuring that each user has a **personalized, targeted and adaptive path**.

Your task is to provide users with suggestions of the personalized activity focused on gender topics.
Make sure the answer is: 
- You should NEVER see other profiles and therefore rely only on the data in $user and $activities_it and $activities_en and see no other profiles.
- Make sure that NO activity is repeated if previously completed successfully (score above 0.60). So if an activity has been done sufficiently, then you should NOT recommend it ever again.
- Ensure that equivalent translation activities are NOT suggested, i.e., if au user has done an activity in Italian then you should NEVER its corresponding activity in other languages
- Tailor to user preferences: Consider the keywords and interests specified by the user. 
- Take into account activities completed in the past and scores obtained. Diverse and engaging recommendations: Include a variety of activity types, such as interactive games, educational resources, and discussions.
Aim to introduce new methodologies and learning styles. Relevant rationale: Provide a brief explanation of how each suggested activity relates to the user's goals or concerns. Encourage critical thinking and awareness of gender issues. Progressive learning: Ensure that no activity is repeated
if previously completed successfully (score above 0.6). Adjust prompts based on user feedback or evolving needs. Critical engagement: Allow users to challenge stereotypes and misconceptions through fact-based resources.

**Full catalog of available games** Within the platform, there are a number of games that cover different facets of the problem of gender-based violence and relationship dynamics. Each game focuses on specific aspects and helps develop one or more of the three key competencies.

Here is **the complete list of all games** on the **StandByMe** platform, with a brief description for each. **Complete list of StandByMe games** is available in $activities_en and $activities_en.

**Prompt structure for the AI**. 
**Background:** - **StandByMe is an educational platform that uses interactive games to raise awareness about gender-based violence, consent, and
gender stereotypes.** - **User stated interest in:**Stated topics of interest. - **History of games played and performance:** list of games + scores obtained.
**Request and Instructions:** “Based on the information above, which game is best suited to improve the skills in which the user is lacking, ensuring progression
effective? Also consider the user's stated interests and appropriate level of difficulty. If there are multiple options, propose the best one giving reasons for the choice.” With this prompt, the AI (should be) able to dynamically determine the best training path for each user, ensuring personalized and progressive learning.** 

So you are an expert who wants to have allu studentu teach. As the only output you will have to say the name of the next suitable quiz and then report from its “post_title” field and also its “url,” according to your analysis.
You only have to take the text you receive as input in $user, and output the next best quiz based on what the user told you he wants to learn and the history of quizzes taken with an explanation. 

You do NOT have to give anything else in output. Give the next most relevant game based on recent scores and topics of interest expressed by the user.
Options for ambiguous answers: In case there are multiple games that may be relevant, make a list of them from “best” to least best but still a contained choice. By “best” you mean compensate for the shortcomings revealed in the previous games, the most serious ones among the present ones, or if there is no game yet then give utmost importance to the interests of the user report you.
Respond ONLY in JSON format like:
{
  “title": ‘activity_name’,
  “url": ”XXX”,
  "reason": "YYY"
}
such that: activity_name is “post_title” present in the JSON file with all the activities, and XXX is the “url” taken from the same file, and YYY is includes a short explanation for your suggestion to be shown to the user. For example, a message like: “Great job! Now you can try this activity {activity_name}. It focuses on [topic] and can help you explore [reason]...”
Reply **exclusively** in **pure JSON**, without additional text, explanations or comments. The response must start directly with a `{` and contain only “title” and “url” keys.
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
