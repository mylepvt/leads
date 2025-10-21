<?php
// fb-capi.php
header('Content-Type: application/json');

// Replace with your Pixel ID and Access Token
$PIXEL_ID = "1357484016013958";
$ACCESS_TOKEN = "EAALoKJYOfNABPse5X4I7OP9ufgf57u7DXFC68FvZC3kNPgpgwDUb81Pwj5oq1LdsaNBZCK36swLn1pMFO5L5gluoFPkaYKAjCPEMGLpT22ZCEFnhXkEeEb7dStAQntoMqZCwGpBEfw39E2uZBteYz8z7R4OGasO6BZCyA30dFIOj8TyT9wOS7AtooTM1NJgQZDZD";

// Get Zoho data (sent as POST)
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Fallback for form-urlencoded POST
if (!$data) $data = $_POST;

// Extract Zoho fields
$fname = $data['Your Full Name'] ?? '';
$phone = $data['Active Mobile Number'] ?? '';
$city = $data['Your City Name'] ?? '';
$gender = $data['Gender'] ?? '';
$age = $data['Your Age'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// Create event_id for deduplication
$event_id = 'myle_lead_' . time();

// Prepare data for Meta CAPI
$payload = [
  'data' => [
    [
      'event_name' => 'Lead',
      'event_time' => time(),
      'action_source' => 'website',
      'event_id' => $event_id,
      'user_data' => [
        'client_ip_address' => $ip,
        'client_user_agent' => $userAgent,
        'ph' => hash('sha256', $phone),
        'fn' => hash('sha256', explode(' ', $fname)[0]),
        'ln' => hash('sha256', explode(' ', $fname)[1] ?? ''),
        'ct' => hash('sha256', $city),
      ],
      'custom_data' => [
        'content_name' => 'Myle Community Zoho Lead',
        'gender' => $gender,
        'age' => $age,
      ],
    ],
  ],
  'access_token' => $ACCESS_TOKEN
];

// Send to Meta API
$ch = curl_init("https://graph.facebook.com/v19.0/$PIXEL_ID/events");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => json_encode($payload),
  CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);
$response = curl_exec($ch);
curl_close($ch);

// Return Meta’s response
echo $response;
