<?php
/**
 * examples/race.php
 *
 * If no parameters: show a form to enter a raceId.
 * If raceId is provided: scrape the race results page and list all results,
 * each with a button that links to result.php with the required bibcardId.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Sportic\Omniresult\MyraceGr\Scrapers\ResultsPage as ResultsScraper;

$raceId  = trim($_GET['raceId'] ?? '');
$error   = null;
$results = null;
$pagination = null;

if ($raceId !== '') {
    if (!ctype_digit($raceId)) {
        $error  = 'Invalid raceId – must be a numeric value.';
        $raceId = '';
    } else {
        try {
            $scraper = new ResultsScraper();
            $scraper->initialize(['raceId' => $raceId]);
            $content    = $scraper->execute()->getContent();
            $results    = $content->getRecords();
            $pagination = $content->getParameter('pagination');
        } catch (\Exception $e) {
            $error = 'Failed to fetch race results: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>myrace.gr – Race <?= htmlspecialchars($raceId) ?></title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: 40px auto; }
        input[type=text] { width: 100%; padding: 8px; font-size: 1rem; box-sizing: border-box; }
        button { margin-top: 10px; padding: 8px 20px; font-size: 1rem; cursor: pointer; }
        .error { color: red; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background: #f0f0f0; }
        a.btn { display: inline-block; padding: 4px 12px; background: #0066cc; color: #fff;
                text-decoration: none; border-radius: 3px; font-size: 0.85rem; }
        a.btn:hover { background: #0055aa; }
        .back { margin-bottom: 20px; display: inline-block; }
    </style>
</head>
<body>
<h1>myrace.gr – Race Results</h1>

<a class="back" href="index.php">← Back to event URL entry</a>

<?php if ($results === null): ?>
<form method="get">
    <label for="raceId"><strong>Race ID:</strong></label><br><br>
    <input type="text" id="raceId" name="raceId"
           placeholder="e.g. 7654"
           value="<?= htmlspecialchars($raceId) ?>">
    <br>
    <button type="submit">Load Race</button>
</form>
<?php endif; ?>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($results !== null): ?>
    <h2>Results for race #<?= htmlspecialchars($raceId) ?></h2>
    <?php if (is_array($pagination)): ?>
        <p>Total records: <?= (int)($pagination['total'] ?? 0) ?></p>
    <?php endif; ?>
    <?php if (count($results) === 0): ?>
        <p>No results found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Pos</th>
                    <th>BIB</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$result->getPosGen()) ?></td>
                    <td><?= htmlspecialchars((string)$result->getBib()) ?></td>
                    <td><?= htmlspecialchars((string)$result->getFullName()) ?></td>
                    <td><?= htmlspecialchars((string)$result->getCategory()) ?></td>
                    <td><?= htmlspecialchars((string)$result->getTime()) ?></td>
                    <td>
                        <?php if ($result->getId()): ?>
                            <a class="btn"
                               href="result.php?bibcardId=<?= urlencode($result->getId()) ?>">
                                Detail
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
