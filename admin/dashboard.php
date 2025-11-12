<?php
require_once '../config/auth.php';
requireLoginPage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Scoreboard System</title>
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>Scoreboard Admin</h1>
            <button id="logoutBtn" class="btn btn-secondary">Logout</button>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h2>Scoreboards</h2>
            <button id="createScoreboardBtn" class="btn btn-primary">Create New Scoreboard</button>
        </div>

        <div id="scoreboardsList" class="scoreboards-grid"></div>
    </div>

    <!-- Create Scoreboard Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Create New Scoreboard</h2>
            <form id="createScoreboardForm">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" required>
                </div>
                <div class="form-group">
                    <label for="type">Type</label>
                    <select id="type">
                        <option value="sports">Sports</option>
                        <option value="quiz">Quiz</option>
                        <option value="competition">Competition</option>
                        <option value="game">Game</option>
                        <option value="general">General</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="totalRounds">Total Rounds</label>
                    <input type="number" id="totalRounds" min="1" value="1">
                </div>
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="showTime" checked>
                        Show Timer
                    </label>
                </div>
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="showRounds" checked>
                        Show Rounds
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('createModal');
        const createBtn = document.getElementById('createScoreboardBtn');
        const closeBtn = document.querySelector('.close');

        // Modal handlers
        createBtn.onclick = () => modal.style.display = 'block';
        closeBtn.onclick = () => modal.style.display = 'none';
        window.onclick = (e) => { if (e.target == modal) modal.style.display = 'none'; };

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', async () => {
            await fetch('../api/logout.php');
            window.location.href = '/login';
        });

        // Load scoreboards
        async function loadScoreboards() {
            try {
                const response = await fetch('../api/scoreboards.php');
                const scoreboards = await response.json();

                const container = document.getElementById('scoreboardsList');

                if (scoreboards.length === 0) {
                    container.innerHTML = '<p class="no-data">No scoreboards yet. Create one to get started!</p>';
                    return;
                }

                container.innerHTML = scoreboards.map(sb => `
                    <div class="scoreboard-card">
                        <div class="scoreboard-header">
                            <h3>${sb.title}</h3>
                            <span class="badge badge-${sb.status}">${sb.status}</span>
                        </div>
                        <div class="scoreboard-info">
                            <p><strong>Type:</strong> ${sb.type}</p>
                            <p><strong>Rounds:</strong> ${sb.current_round} / ${sb.total_rounds}</p>
                            <p><strong>Created:</strong> ${new Date(sb.created_at).toLocaleDateString()}</p>
                        </div>
                        <div class="scoreboard-actions">
                            <a href="/admin/manage?id=${sb.id}" class="btn btn-primary">Manage</a>
                            <a href="/?id=${sb.id}" class="btn btn-secondary" target="_blank">View Public</a>
                            <button onclick="deleteScoreboard(${sb.id})" class="btn btn-danger">Delete</button>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading scoreboards:', error);
            }
        }

        // Create scoreboard
        document.getElementById('createScoreboardForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                title: document.getElementById('title').value,
                type: document.getElementById('type').value,
                total_rounds: parseInt(document.getElementById('totalRounds').value),
                show_time: document.getElementById('showTime').checked ? 1 : 0,
                show_rounds: document.getElementById('showRounds').checked ? 1 : 0
            };

            try {
                const response = await fetch('../api/scoreboards.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    modal.style.display = 'none';
                    document.getElementById('createScoreboardForm').reset();
                    loadScoreboards();
                } else {
                    alert('Error creating scoreboard: ' + result.error);
                }
            } catch (error) {
                alert('Connection error. Please try again.');
            }
        });

        // Delete scoreboard
        async function deleteScoreboard(id) {
            if (!confirm('Are you sure you want to delete this scoreboard?')) return;

            try {
                const response = await fetch(`../api/scoreboards.php?id=${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    loadScoreboards();
                } else {
                    alert('Error deleting scoreboard: ' + result.error);
                }
            } catch (error) {
                alert('Connection error. Please try again.');
            }
        }

        // Initial load
        loadScoreboards();
    </script>
</body>
</html>
