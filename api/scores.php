<?php
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->connect();

// POST - Update score
if ($method === 'POST') {
    requireLogin();

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['team_id']) || !isset($data['scoreboard_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Team ID and Scoreboard ID are required']);
        exit();
    }

    try {
        $roundNumber = $data['round_number'] ?? 1;
        $score = $data['score'] ?? 0;

        // Check if score exists for this team and round
        $stmt = $db->prepare('
            SELECT id FROM scores
            WHERE team_id = ? AND scoreboard_id = ? AND round_number = ?
        ');
        $stmt->execute([$data['team_id'], $data['scoreboard_id'], $roundNumber]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update existing score
            $stmt = $db->prepare('
                UPDATE scores
                SET score = ?
                WHERE team_id = ? AND scoreboard_id = ? AND round_number = ?
            ');
            $stmt->execute([$score, $data['team_id'], $data['scoreboard_id'], $roundNumber]);
        } else {
            // Insert new score
            $stmt = $db->prepare('
                INSERT INTO scores (team_id, scoreboard_id, round_number, score)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([$data['team_id'], $data['scoreboard_id'], $roundNumber, $score]);
        }

        // Get updated total score
        $stmt = $db->prepare('
            SELECT SUM(score) as total_score
            FROM scores
            WHERE team_id = ? AND scoreboard_id = ?
        ');
        $stmt->execute([$data['team_id'], $data['scoreboard_id']]);
        $result = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'message' => 'Score updated',
            'total_score' => $result['total_score'] ?? 0
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// PUT - Increment/Decrement score
elseif ($method === 'PUT') {
    requireLogin();

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['team_id']) || !isset($data['scoreboard_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Team ID and Scoreboard ID are required']);
        exit();
    }

    try {
        $roundNumber = $data['round_number'] ?? 1;
        $increment = $data['increment'] ?? 1;

        // Check if score exists for this team and round
        $stmt = $db->prepare('
            SELECT id, score FROM scores
            WHERE team_id = ? AND scoreboard_id = ? AND round_number = ?
        ');
        $stmt->execute([$data['team_id'], $data['scoreboard_id'], $roundNumber]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update existing score
            $newScore = max(0, $existing['score'] + $increment); // Prevent negative scores
            $stmt = $db->prepare('
                UPDATE scores
                SET score = ?
                WHERE id = ?
            ');
            $stmt->execute([$newScore, $existing['id']]);
        } else {
            // Insert new score
            $newScore = max(0, $increment);
            $stmt = $db->prepare('
                INSERT INTO scores (team_id, scoreboard_id, round_number, score)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([$data['team_id'], $data['scoreboard_id'], $roundNumber, $newScore]);
        }

        // Get updated total score
        $stmt = $db->prepare('
            SELECT SUM(score) as total_score
            FROM scores
            WHERE team_id = ? AND scoreboard_id = ?
        ');
        $stmt->execute([$data['team_id'], $data['scoreboard_id']]);
        $result = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'message' => 'Score updated',
            'total_score' => $result['total_score'] ?? 0
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
