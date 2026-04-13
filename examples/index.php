<?php
/**
 * examples/index.php
 *
 * Entry point: ask for a myrace.gr event page URL, validate it and
 * redirect to event.php with the extracted eventId.
 *
 * Usage: php -S localhost:8080 -t examples/
 */

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($_POST['url'] ?? '');

    // Validate that it looks like a myrace.gr event results URL
    // Expected format: https://www.myrace.gr/en/event/{eventId}/results.html
    if (!preg_match('#https?://(?:www\.)?myrace\.gr/\w+/event/(\d+)/results\.html#i', $url, $matches)) {
        $error = 'Invalid URL. Please enter a valid myrace.gr event results URL '
               . '(e.g. https://www.myrace.gr/en/event/5896/results.html)';
    } else {
        $eventId = $matches[1];
        header('Location: event.php?eventId=' . urlencode($eventId));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>myrace.gr – Event URL</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 60px auto; }
        input[type=text] { width: 100%; padding: 8px; font-size: 1rem; box-sizing: border-box; }
        button { margin-top: 10px; padding: 8px 20px; font-size: 1rem; cursor: pointer; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
<h1>myrace.gr Explorer</h1>
<form method="post">
    <label for="url"><strong>Event results URL:</strong></label><br><br>
    <input type="text" id="url" name="url"
           placeholder="https://www.myrace.gr/en/event/5896/results.html"
           value="<?= htmlspecialchars($_POST['url'] ?? '') ?>">
    <br>
    <button type="submit">Go</button>
</form>
<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
</body>
</html>
