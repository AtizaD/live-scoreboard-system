<?php
require_once '../config/auth.php';
requireLoginPage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Scoreboard</title>
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="/dashboard" class="back-link">&larr; Back to Dashboard</a>
            <h1 id="scoreboardTitle">Loading...</h1>
            <button id="logoutBtn" class="btn btn-secondary">Logout</button>
        </div>
    </nav>

    <div class="container">
        <div class="manage-controls">
            <div class="scoreboard-settings">
                <div class="setting-group">
                    <label>Status:</label>
                    <select id="statusSelect">
                        <option value="active">Active</option>
                        <option value="paused">Paused</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="setting-group">
                    <label>Current Round:</label>
                    <input type="number" id="currentRound" min="1" value="1">
                    <span id="totalRoundsDisplay">/ 1</span>
                </div>
            </div>
            <div class="action-buttons">
                <button id="addTeamBtn" class="btn btn-primary">Add Team</button>
                <a id="viewPublicBtn" href="#" class="btn btn-secondary" target="_blank">View Public Display</a>
            </div>
        </div>

        <div id="teamsContainer" class="teams-list"></div>
    </div>

    <!-- Add Team Modal -->
    <div id="addTeamModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add Team</h2>
            <form id="addTeamForm">
                <div class="form-group">
                    <label for="teamName">Team Name</label>
                    <input type="text" id="teamName" required>
                </div>
                <div class="form-group">
                    <label for="displayOrder">Display Order</label>
                    <input type="number" id="displayOrder" min="0" value="0">
                </div>
                <button type="submit" class="btn btn-primary">Add Team</button>
            </form>
        </div>
    </div>

    <script>
        let scoreboardId = new URLSearchParams(window.location.search).get('id');
        let scoreboardData = null;
        const modal = document.getElementById('addTeamModal');
        const addTeamBtn = document.getElementById('addTeamBtn');
        const closeBtn = document.querySelector('.close');

        // Modal handlers
        addTeamBtn.onclick = () => modal.style.display = 'block';
        closeBtn.onclick = () => modal.style.display = 'none';
        window.onclick = (e) => { if (e.target == modal) modal.style.display = 'none'; };

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', async () => {
            await fetch('../api/logout.php');
            window.location.href = '/login';
        });

        // Load scoreboard and teams
        async function loadScoreboard() {
            try {
                const response = await fetch(`../api/scoreboards.php?id=${scoreboardId}`);
                scoreboardData = await response.json();

                document.getElementById('scoreboardTitle').textContent = scoreboardData.title;
                document.getElementById('statusSelect').value = scoreboardData.status;
                document.getElementById('currentRound').value = scoreboardData.current_round;
                document.getElementById('totalRoundsDisplay').textContent = `/ ${scoreboardData.total_rounds}`;
                document.getElementById('viewPublicBtn').href = `/?id=${scoreboardId}`;

                displayTeams();
            } catch (error) {
                console.error('Error loading scoreboard:', error);
            }
        }

        // Display teams
        function displayTeams() {
            const container = document.getElementById('teamsContainer');

            if (!scoreboardData.teams || scoreboardData.teams.length === 0) {
                container.innerHTML = '<p class="no-data">No teams yet. Add teams to get started!</p>';
                return;
            }

            // Save current input values, button states and focus state before re-render
            const inputValues = {};
            const buttonStates = {};
            const focusedInputId = document.activeElement?.id;
            scoreboardData.teams.forEach(team => {
                const input = document.getElementById(`score-input-${team.id}`);
                if (input && input.value) {
                    inputValues[team.id] = input.value;
                }

                const button = document.getElementById(`set-btn-${team.id}`);
                if (button && button.disabled) {
                    buttonStates[team.id] = {
                        disabled: button.disabled,
                        text: button.textContent,
                        opacity: button.style.opacity
                    };
                }
            });

            container.innerHTML = scoreboardData.teams.map(team => `
                <div class="team-card">
                    <div class="team-header">
                        <h3>${team.name}</h3>
                        <button onclick="deleteTeam(${team.id})" class="btn btn-danger btn-sm">Delete</button>
                    </div>
                    <div class="team-score">
                        <div class="score-display">
                            <span class="score-label">Total Score:</span>
                            <span class="score-value" id="total-${team.id}">${team.total_score || 0}</span>
                        </div>
                        <div class="score-controls-wrapper">
                            <div class="quick-controls">
                                <span class="control-label">Quick adjust:</span>
                                <button onclick="updateScore(${team.id}, -5)" class="btn btn-danger btn-sm">-5</button>
                                <button onclick="updateScore(${team.id}, -1)" class="btn btn-danger btn-sm">-1</button>
                                <button onclick="updateScore(${team.id}, 1)" class="btn btn-success btn-sm">+1</button>
                                <button onclick="updateScore(${team.id}, 5)" class="btn btn-success btn-sm">+5</button>
                            </div>
                            <div class="manual-entry">
                                <span class="control-label">Enter score:</span>
                                <input type="number" id="score-input-${team.id}" placeholder="0" class="score-input" onkeypress="if(event.key==='Enter') setScore(${team.id})">
                                <button onclick="setScore(${team.id})" class="btn btn-primary" id="set-btn-${team.id}">Set Score</button>
                            </div>
                        </div>
                    </div>
                    ${scoreboardData.show_rounds ? `
                        <div class="rounds-display">
                            ${team.scores_by_round.map(s => `
                                <span class="round-score">R${s.round_number}: ${s.score}</span>
                            `).join('')}
                        </div>
                    ` : ''}
                </div>
            `).join('');

            // Restore input values and focus after re-render
            scoreboardData.teams.forEach(team => {
                if (inputValues[team.id]) {
                    const input = document.getElementById(`score-input-${team.id}`);
                    if (input) {
                        input.value = inputValues[team.id];
                    }
                }
            });

            // Restore focus if an input was focused
            if (focusedInputId && focusedInputId.startsWith('score-input-')) {
                const focusedInput = document.getElementById(focusedInputId);
                if (focusedInput) {
                    focusedInput.focus();
                    // Restore cursor position to end
                    focusedInput.setSelectionRange(focusedInput.value.length, focusedInput.value.length);
                }
            }
        }

        // Add team
        document.getElementById('addTeamForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                scoreboard_id: scoreboardId,
                name: document.getElementById('teamName').value,
                display_order: parseInt(document.getElementById('displayOrder').value)
            };

            try {
                const response = await fetch('../api/teams.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    modal.style.display = 'none';
                    document.getElementById('addTeamForm').reset();
                    loadScoreboard();
                } else {
                    alert('Error adding team: ' + result.error);
                }
            } catch (error) {
                alert('Connection error. Please try again.');
            }
        });

        // Update score (increment/decrement)
        async function updateScore(teamId, increment) {
            const currentRound = parseInt(document.getElementById('currentRound').value);

            try {
                const response = await fetch('../api/scores.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        team_id: teamId,
                        scoreboard_id: scoreboardId,
                        round_number: currentRound,
                        increment: increment
                    })
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById(`total-${teamId}`).textContent = result.total_score;
                } else {
                    alert('Error updating score: ' + result.error);
                }
            } catch (error) {
                console.error('Error updating score:', error);
            }
        }

        // Set score directly
        async function setScore(teamId) {
            const scoreInput = document.getElementById(`score-input-${teamId}`);
            const inputValue = scoreInput.value.trim();

            // Validate input
            if (!inputValue || inputValue === '') {
                scoreInput.focus();
                return;
            }

            const score = parseInt(inputValue);
            if (isNaN(score)) {
                alert('Please enter a valid number');
                scoreInput.focus();
                return;
            }

            const currentRound = parseInt(document.getElementById('currentRound').value);

            // Get button and disable it during processing
            const button = event?.target;
            const originalText = button?.textContent;
            if (button) {
                button.disabled = true;
                button.textContent = 'Setting...';
                button.style.opacity = '0.6';
            }

            try {
                const response = await fetch('../api/scores.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        team_id: teamId,
                        scoreboard_id: scoreboardId,
                        round_number: currentRound,
                        score: score
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Update total score immediately
                    document.getElementById(`total-${teamId}`).textContent = result.total_score;
                    scoreInput.value = '';
                    scoreInput.blur();

                    // Force immediate refresh to show updated data
                    await loadScoreboard();
                } else {
                    alert('Error setting score: ' + result.error);
                }
            } catch (error) {
                console.error('Error setting score:', error);
                alert('Failed to set score. Please try again.');
            } finally {
                // Re-enable button
                if (button) {
                    button.disabled = false;
                    button.textContent = originalText;
                    button.style.opacity = '1';
                }
            }
        }

        // Delete team
        async function deleteTeam(teamId) {
            if (!confirm('Are you sure you want to delete this team?')) return;

            try {
                const response = await fetch(`../api/teams.php?id=${teamId}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    loadScoreboard();
                } else {
                    alert('Error deleting team: ' + result.error);
                }
            } catch (error) {
                alert('Connection error. Please try again.');
            }
        }

        // Update scoreboard settings
        document.getElementById('statusSelect').addEventListener('change', async (e) => {
            await updateScoreboardSetting('status', e.target.value);
        });

        document.getElementById('currentRound').addEventListener('change', async (e) => {
            await updateScoreboardSetting('current_round', parseInt(e.target.value));
        });

        async function updateScoreboardSetting(field, value) {
            try {
                const response = await fetch('../api/scoreboards.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: scoreboardId,
                        [field]: value
                    })
                });

                const result = await response.json();

                if (!result.success) {
                    alert('Error updating scoreboard: ' + result.error);
                }
            } catch (error) {
                console.error('Error updating scoreboard:', error);
            }
        }

        // Initial load
        if (!scoreboardId) {
            alert('No scoreboard ID provided');
            window.location.href = '/dashboard';
        } else {
            loadScoreboard();
            // Refresh every 2 seconds to show updates
            setInterval(loadScoreboard, 2000);
        }
    </script>
</body>
</html>
