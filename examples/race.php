<?php
/**
 * examples/race.php
 *
 * If no parameters: show a form to enter a raceId.
 * If raceId is provided: scrape the race results page and list results
 * for the requested page, with pagination controls.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Sportic\Omniresult\MyraceGr\MyraceGrClient;
use Sportic\Omniresult\MyraceGr\Scrapers\ResultsPage as ResultsScraper;

$raceId     = trim($_GET['raceId'] ?? '');
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = max(1, (int)($_GET['perPage'] ?? 50));
$error      = null;
$results    = null;
$pagination = null;
$requestUrl = null;

if ($raceId !== '') {
    if (!ctype_digit($raceId)) {
        $error  = 'Invalid raceId – must be a numeric value.';
        $raceId = '';
    } else {
        try {
            $scraper = new ResultsScraper();
            $scraper->initialize([
                'raceId'  => $raceId,
                'page'    => $page,
                'perPage' => $perPage,
            ]);
            $requestUrl = $scraper->getCrawlerUri();

            $client     = new MyraceGrClient();
            $content    = $client->results([
                'raceId'  => $raceId,
                'page'    => $page,
                'perPage' => $perPage,
            ])->getContent();
            $results    = $content->getRecords();
            $pagination = $content->getPagination();
        } catch (\Exception $e) {
            $error = 'Failed to fetch race results: ' . $e->getMessage();
        }
    }
}

/**
 * Build a URL for a given page keeping all other GET params intact.
 */
function paginationUrl($targetPage, $raceId, $perPage) {
    return '?'
        . http_build_query(['raceId' => $raceId, 'page' => $targetPage, 'perPage' => $perPage]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>myrace.gr – Race <?= htmlspecialchars($raceId) ?></title>
    <style>
        body { font-family: sans-serif; max-width: 960px; margin: 40px auto; }
        input[type=text], select { padding: 6px 8px; font-size: 1rem; }
        button { margin-top: 10px; padding: 8px 20px; font-size: 1rem; cursor: pointer; }
        .error { color: red; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background: #f0f0f0; }
        a.btn { display: inline-block; padding: 4px 12px; background: #0066cc; color: #fff;
                text-decoration: none; border-radius: 3px; font-size: 0.85rem; }
        a.btn:hover { background: #0055aa; }
        a.btn.disabled { background: #aaa; pointer-events: none; }
        .back { margin-bottom: 20px; display: inline-block; }
        .request-details { background: #f8f8f8; border: 1px solid #ddd; border-radius: 4px;
                           padding: 12px 16px; margin: 20px 0; font-size: 0.9rem; }
        .request-details h3 { margin: 0 0 8px; font-size: 1rem; }
        .request-details dl { margin: 0; display: grid; grid-template-columns: 80px 1fr; gap: 4px 12px; }
        .request-details dt { font-weight: bold; color: #555; }
        .request-details dd { margin: 0; word-break: break-all; }
        .pagination { margin-top: 16px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .pagination-info { color: #555; font-size: 0.9rem; }
        .per-page-form { display: inline-flex; align-items: center; gap: 6px; font-size: 0.9rem; }
    </style>
</head>
<body>
<h1>myrace.gr – Race Results</h1>

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

<?php if ($results === null): ?>
<form method="get">
    <label for="raceId"><strong>Race ID:</strong></label><br><br>
    <input type="text" id="raceId" name="raceId"
           placeholder="e.g. 7654"
           value="<?= htmlspecialchars($raceId) ?>">
    <br>
    <label for="perPage"><strong>Results per page:</strong></label>
    <select id="perPage" name="perPage">
        <?php foreach ([10, 25, 50, 100] as $opt): ?>
            <option value="<?= $opt ?>" <?= $opt === $perPage ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
    </select>
    <br>
    <button type="submit">Load Race</button>
</form>
<?php endif; ?>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($results !== null): ?>
    <h2>Results for race #<?= htmlspecialchars($raceId) ?></h2>

    <?php
    $total      = (int)($pagination['items']    ?? 0);
    $filtered   = (int)($pagination['filtered'] ?? 0);
    $totalPages = (int)($pagination['all']    ?? 1);
    $offset     = ($page - 1) * $perPage + 1;
    $offsetEnd  = min($page * $perPage, $total);
    ?>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a class="btn" href="<?= paginationUrl(1, $raceId, $perPage) ?>">« First</a>
            <a class="btn" href="<?= paginationUrl($page - 1, $raceId, $perPage) ?>">‹ Prev</a>
        <?php else: ?>
            <a class="btn disabled">« First</a>
            <a class="btn disabled">‹ Prev</a>
        <?php endif; ?>

        <span class="pagination-info">
            Page <?= $page ?> of <?= $totalPages ?>
            &nbsp;|&nbsp;
            Showing <?= $offset ?>–<?= $offsetEnd ?> of <?= $total ?> results
        </span>

        <?php if ($page < $totalPages): ?>
            <a class="btn" href="<?= paginationUrl($page + 1, $raceId, $perPage) ?>">Next ›</a>
            <a class="btn" href="<?= paginationUrl($totalPages, $raceId, $perPage) ?>">Last »</a>
        <?php else: ?>
            <a class="btn disabled">Next ›</a>
            <a class="btn disabled">Last »</a>
        <?php endif; ?>

        <form class="per-page-form" method="get">
            <input type="hidden" name="raceId" value="<?= htmlspecialchars($raceId) ?>">
            <input type="hidden" name="page" value="1">
            <label for="perPageSwitch">Per page:</label>
            <select id="perPageSwitch" name="perPage" onchange="this.form.submit()">
                <?php foreach ([10, 25, 50, 100] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $opt === $perPage ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (count($results) === 0): ?>
        <p>No results found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Pos Gen</th>
                    <th>Pos Cat</th>
                    <th>Pos Gender</th>
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
                    <td><?= htmlspecialchars((string)$result->getPosCategory()) ?></td>
                    <td><?= htmlspecialchars((string)$result->getPosGender()) ?></td>
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

        <div class="pagination" style="margin-top:12px;">
            <?php if ($page > 1): ?>
                <a class="btn" href="<?= paginationUrl(1, $raceId, $perPage) ?>">« First</a>
                <a class="btn" href="<?= paginationUrl($page - 1, $raceId, $perPage) ?>">‹ Prev</a>
            <?php else: ?>
                <a class="btn disabled">« First</a>
                <a class="btn disabled">‹ Prev</a>
            <?php endif; ?>

            <span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?></span>

            <?php if ($page < $totalPages): ?>
                <a class="btn" href="<?= paginationUrl($page + 1, $raceId, $perPage) ?>">Next ›</a>
                <a class="btn" href="<?= paginationUrl($totalPages, $raceId, $perPage) ?>">Last »</a>
            <?php else: ?>
                <a class="btn disabled">Next ›</a>
                <a class="btn disabled">Last »</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>

