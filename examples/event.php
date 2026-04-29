<?php
/**
 * examples/event.php
 *
 * If no parameters: show a form to enter an eventId.
 * If eventId is provided: scrape the event page and list all races,
 * each with a button that links to race.php with the required raceId.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Sportic\Omniresult\MyraceGr\MyraceGrClient;
use Sportic\Omniresult\MyraceGr\Scrapers\EventPage as EventScraper;

$eventId    = trim($_GET['eventId'] ?? '');
$error      = null;
$races      = null;
$requestUrl = null;

if ($eventId !== '') {
    if (!ctype_digit($eventId)) {
        $error = 'Invalid eventId – must be a numeric value.';
        $eventId = '';
    } else {
        try {
            $scraper = new EventScraper();
            $scraper->initialize(['eventId' => $eventId]);
            $requestUrl = $scraper->getCrawlerUri();

            $client  = new MyraceGrClient();
            $content = $client->event(['eventId' => $eventId])->getContent();
            $races   = $content->getRecords();
        } catch (\Exception $e) {
            $error = 'Failed to fetch event: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>myrace.gr – Event <?= htmlspecialchars($eventId) ?></title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 40px auto; }
        input[type=text] { width: 100%; padding: 8px; font-size: 1rem; box-sizing: border-box; }
        button { margin-top: 10px; padding: 8px 20px; font-size: 1rem; cursor: pointer; }
        .error { color: red; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th { background: #f0f0f0; }
        a.btn { display: inline-block; padding: 5px 14px; background: #0066cc; color: #fff;
                text-decoration: none; border-radius: 3px; font-size: 0.9rem; }
        a.btn:hover { background: #0055aa; }
        .back { margin-bottom: 20px; display: inline-block; }
        .request-details { background: #f8f8f8; border: 1px solid #ddd; border-radius: 4px;
                           padding: 12px 16px; margin: 20px 0; font-size: 0.9rem; }
        .request-details h3 { margin: 0 0 8px; font-size: 1rem; }
        .request-details dl { margin: 0; display: grid; grid-template-columns: 80px 1fr; gap: 4px 12px; }
        .request-details dt { font-weight: bold; color: #555; }
        .request-details dd { margin: 0; word-break: break-all; }
    </style>
</head>
<body>
<h1>myrace.gr – Event Results</h1>

<a class="back" href="index.php">← Back to event URL entry</a>

<?php if ($requestUrl !== null): ?>
<div class="request-details">
    <h3>Request sent by crawler</h3>
    <dl>
        <dt>Method</dt><dd>GET</dd>
        <dt>URL</dt><dd><a href="<?= htmlspecialchars($requestUrl) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($requestUrl) ?></a></dd>
    </dl>
</div>
<?php endif; ?>

<?php if ($races === null): ?>
<form method="get">
    <label for="eventId"><strong>Event ID:</strong></label><br><br>
    <input type="text" id="eventId" name="eventId"
           placeholder="e.g. 5896"
           value="<?= htmlspecialchars($eventId) ?>">
    <br>
    <button type="submit">Load Event</button>
</form>
<?php endif; ?>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($races !== null): ?>
    <h2>Races for event #<?= htmlspecialchars($eventId) ?></h2>
    <?php if (count($races) === 0): ?>
        <p>No races found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Race name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($races as $i => $race): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($race->getName()) ?></td>
                    <td>
                        <a class="btn"
                           href="race.php?raceId=<?= urlencode($race->getId()) ?>">
                            View results
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
