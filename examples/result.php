<?php
/**
 * examples/result.php
 *
 * If no parameters: show a form to enter a bibcardId.
 * If bibcardId is provided: scrape the individual result page and show the details.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Sportic\Omniresult\MyraceGr\Scrapers\ResultPage as ResultScraper;

$bibcardId = trim($_GET['bibcardId'] ?? '');
$error     = null;
$result    = null;

if ($bibcardId !== '') {
    if (!ctype_digit($bibcardId)) {
        $error     = 'Invalid bibcardId – must be a numeric value.';
        $bibcardId = '';
    } else {
        try {
            $scraper = new ResultScraper();
            $scraper->initialize(['bibcardId' => $bibcardId]);
            $content = $scraper->execute()->getContent();
            $result  = $content->getRecord();
        } catch (\Exception $e) {
            $error = 'Failed to fetch result: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>myrace.gr – Result <?= htmlspecialchars($bibcardId) ?></title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 40px auto; }
        input[type=text] { width: 100%; padding: 8px; font-size: 1rem; box-sizing: border-box; }
        button { margin-top: 10px; padding: 8px 20px; font-size: 1rem; cursor: pointer; }
        .error { color: red; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th { background: #f0f0f0; width: 40%; }
        .back { margin-bottom: 20px; display: inline-block; }
    </style>
</head>
<body>
<h1>myrace.gr – Athlete Result</h1>

<a class="back" href="index.php">← Back to event URL entry</a>

<?php if ($result === null): ?>
<form method="get">
    <label for="bibcardId"><strong>Bibcard ID:</strong></label><br><br>
    <input type="text" id="bibcardId" name="bibcardId"
           placeholder="e.g. 2199045"
           value="<?= htmlspecialchars($bibcardId) ?>">
    <br>
    <button type="submit">Load Result</button>
</form>
<?php endif; ?>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($result !== null): ?>
    <h2>Result for bibcard #<?= htmlspecialchars($bibcardId) ?></h2>
    <table>
        <tr><th>Name</th>         <td><?= htmlspecialchars((string)$result->getFullName()) ?></td></tr>
        <tr><th>BIB</th>          <td><?= htmlspecialchars((string)$result->getBib()) ?></td></tr>
        <tr><th>Gender</th>       <td><?= htmlspecialchars((string)$result->getGender()) ?></td></tr>
        <tr><th>Category</th>     <td><?= htmlspecialchars((string)$result->getCategory()) ?></td></tr>
        <tr><th>Nationality</th>  <td><?= htmlspecialchars((string)$result->getCountry()) ?></td></tr>
        <tr><th>Position (Gen)</th><td><?= htmlspecialchars((string)$result->getPosGen()) ?></td></tr>
        <tr><th>Position (Cat)</th><td><?= htmlspecialchars((string)$result->getPosCategory()) ?></td></tr>
        <tr><th>Net Time</th>     <td><?= htmlspecialchars((string)$result->getTime()) ?></td></tr>
        <tr><th>Gun Time</th>     <td><?= htmlspecialchars((string)$result->getTimeGross()) ?></td></tr>
    </table>

    <?php
    $splits = $result->getSplits();
    if ($splits && count($splits) > 0):
    ?>
    <h3>Splits</h3>
    <table>
        <thead>
            <tr><th>Checkpoint</th><th>Time</th></tr>
        </thead>
        <tbody>
        <?php foreach ($splits as $split): ?>
            <tr>
                <td><?= htmlspecialchars((string)$split->getName()) ?></td>
                <td><?= htmlspecialchars((string)$split->getTime()) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
