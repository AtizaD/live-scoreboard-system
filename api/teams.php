<?php
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->connect();

// GET - Fetch teams for a scoreboard
if ($method === 'GET') {
    if (!isset($_GET['scoreboard_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Scoreboard ID is required']);
        exit();
    }

    try {
        $stmt = $db->prepare('SELECT * FROM teams WHERE scoreboard_id = ? ORDER BY display_order, name');
        $stmt->execute([$_GET['scoreboard_id']]);
        $teams = $stmt->fetchAll();

        echo json_encode($teams);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// POST - Create new team
elseif ($method === 'POST') {
    requireLogin();

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['scoreboard_id']) || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Scoreboard ID and team name are required']);
        exit();
    }

    try {
        $stmt = $db->prepare('
            INSERT INTO teams (scoreboard_id, name, logo, display_order)
            VALUES (?, ?, ?, ?)
        ');

        $stmt->execute([
            $data['scoreboard_id'],
            $data['name'],
            $data['logo'] ?? null,
            $data['display_order'] ?? 0
        ]);

        $teamId = $db->lastInsertId();

        // Initialize score for current round
        $stmt = $db->prepare('
            INSERT INTO scores (team_id, scoreboard_id, round_number, score)
            VALUES (?, ?, 1, 0)
        ');
        $stmt->execute([$teamId, $data['scoreboard_id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Team created',
            'id' => $teamId
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// PUT - Update team
elseif ($method === 'PUT') {
    requireLogin();

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Team ID is required']);
        exit();
    }

    try {
        $stmt = $db->prepare('
            UPDATE teams
            SET name = ?, logo = ?, display_order = ?
            WHERE id = ?
        ');

        $stmt->execute([
            $data['name'] ?? '',
            $data['logo'] ?? null,
            $data['display_order'] ?? 0,
            $data['id']
        ]);

        echo json_encode(['success' => true, 'message' => 'Team updated']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// DELETE - Delete team
elseif ($method === 'DELETE') {
    requireLogin();

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Team ID is required']);
        exit();
    }

    try {
        $stmt = $db->prepare('DELETE FROM teams WHERE id = ?');
        $stmt->execute([$_GET['id']]);

        echo json_encode(['success' => true, 'message' => 'Team deleted']);
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
