<?php
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->connect();

// GET - Fetch scoreboards or single scoreboard
if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Get single scoreboard with teams and scores
            $stmt = $db->prepare('SELECT * FROM scoreboards WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            $scoreboard = $stmt->fetch();

            if (!$scoreboard) {
                http_response_code(404);
                echo json_encode(['error' => 'Scoreboard not found']);
                exit();
            }

            // Get teams for this scoreboard
            $stmt = $db->prepare('SELECT * FROM teams WHERE scoreboard_id = ? ORDER BY display_order, name');
            $stmt->execute([$_GET['id']]);
            $teams = $stmt->fetchAll();

            // Get current scores for each team
            foreach ($teams as &$team) {
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

            $scoreboard['teams'] = $teams;
            echo json_encode($scoreboard);

        } else {
            // Get all scoreboards (admin only)
            requireLogin();
            $stmt = $db->query('SELECT * FROM scoreboards ORDER BY created_at DESC');
            $scoreboards = $stmt->fetchAll();
            echo json_encode($scoreboards);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// POST - Create new scoreboard
elseif ($method === 'POST') {
    requireLogin();

    $data = json_decode(file_get_contents('php://input'), true);

    try {
        $stmt = $db->prepare('
            INSERT INTO scoreboards (title, type, show_time, show_rounds, total_rounds, created_by, start_time)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ');

        $stmt->execute([
            $data['title'] ?? 'Untitled Scoreboard',
            $data['type'] ?? 'general',
            $data['show_time'] ?? 1,
            $data['show_rounds'] ?? 1,
            $data['total_rounds'] ?? 1,
            getAdminId()
        ]);

        $scoreboardId = $db->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Scoreboard created',
            'id' => $scoreboardId
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// PUT - Update scoreboard
elseif ($method === 'PUT') {
    requireLogin();

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Scoreboard ID is required']);
        exit();
    }

    try {
        $fields = [];
        $values = [];

        $allowedFields = ['title', 'type', 'status', 'show_time', 'show_rounds', 'current_round', 'total_rounds'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid fields to update']);
            exit();
        }

        $values[] = $data['id'];
        $sql = 'UPDATE scoreboards SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $db->prepare($sql);
        $stmt->execute($values);

        echo json_encode(['success' => true, 'message' => 'Scoreboard updated']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// DELETE - Delete scoreboard
elseif ($method === 'DELETE') {
    requireLogin();

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Scoreboard ID is required']);
        exit();
    }

    try {
        $stmt = $db->prepare('DELETE FROM scoreboards WHERE id = ?');
        $stmt->execute([$_GET['id']]);

        echo json_encode(['success' => true, 'message' => 'Scoreboard deleted']);
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
