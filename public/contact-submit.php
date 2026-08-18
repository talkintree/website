<?php
declare(strict_types=1);

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed');
}

// Honeypot fields should remain empty. Return success so bots get no feedback.
if (trim((string)($_POST['website'] ?? '')) !== '') {
    header('Location: /thank-you/', true, 303);
    exit;
}

$clean = static function (string $value): string {
    return trim(str_replace(["\r", "\0"], '', $value));
};

$name = $clean((string)($_POST['name'] ?? ''));
$email = filter_var($clean((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$type = $clean((string)($_POST['inquiry-type'] ?? ''));
$organization = $clean((string)($_POST['organization'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

$allowedTypes = [
    'Speaking or workshop',
    'Collaboration',
    'Media or podcast',
    'Books or author inquiry',
    'General message',
];

if (
    $name === '' || strlen($name) > 100 ||
    $email === false || strlen((string)$email) > 254 ||
    !in_array($type, $allowedTypes, true) ||
    strlen($organization) > 150 ||
    $message === '' || strlen($message) > 5000
) {
    header('Location: /contact/?status=invalid#inquiry', true, 303);
    exit;
}

$recipient = 'contact@talkintree.com';
$subject = 'Talkintree website inquiry: ' . $type;
$body = "Name: {$name}\n"
    . "Email: {$email}\n"
    . "Inquiry type: {$type}\n"
    . "Organization: " . ($organization !== '' ? $organization : 'Not provided') . "\n\n"
    . "Message:\n{$message}\n";
$headers = [
    'From: Talkintree Website <contact@talkintree.com>',
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8',
];

if (!mail($recipient, $subject, $body, implode("\r\n", $headers))) {
    error_log('Talkintree contact form: mail delivery failed');
    header('Location: /contact/?status=unavailable#inquiry', true, 303);
    exit;
}

header('Location: /thank-you/', true, 303);
exit;
