<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->connect();

// Public endpoint - no authentication required
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Scoreboard ID is required']);
    exit();
}

try {
    // Get scoreboard details
    $stmt = $db->prepare('SELECT * FROM scoreboards WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $scoreboard = $stmt->fetch();

    if (!$scoreboard) {
        http_response_code(404);
        echo json_encode(['error' => 'Scoreboard not found']);
        exit();
    }

    // Get teams and their scores
    $stmt = $db->prepare('SELECT * FROM teams WHERE scoreboard_id = ? ORDER BY display_order, name');
    $stmt->execute([$_GET['id']]);
    $teams = $stmt->fetchAll();

    // Get scores for each team
    foreach ($teams as &$team) {
        // Get total score
        $stmt = $db->prepare('
            SELECT SUM(score) as total_score
            FROM scores
            WHERE team_id = ? AND scoreboard_id = ?
        ');
        $stmt->execute([$team['id'], $_GET['id']]);
        $scoreData = $stmt->fetch();
        $team['total_score'] = $scoreData['total_score'] ?? 0;

        // Get scores by round
        $stmt = $db->prepare('
            SELECT round_number, score
            FROM scores
            WHERE team_id = ? AND scoreboard_id = ?
            ORDER BY round_number
        ');
        $stmt->execute([$team['id'], $_GET['id']]);
        $team['scores_by_round'] = $stmt->fetchAll();
    }

    // Sort teams by total score (descending)
    usort($teams, function($a, $b) {
        return $b['total_score'] - $a['total_score'];
    });

    $scoreboard['teams'] = $teams;
    $scoreboard['last_updated'] = time();

    echo json_encode($scoreboard);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
