<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scoreboard</title>
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .public-scoreboard {
            max-width: 1400px;
            margin: 0 auto;
        }

        .scoreboard-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .scoreboard-header h1 {
            margin: 0 0 15px 0;
            font-size: 3em;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .scoreboard-info {
            display: flex;
            justify-content: center;
            gap: 40px;
            font-size: 1.3em;
            color: #555;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-label {
            font-weight: bold;
            color: #667eea;
        }

        .teams-display {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* Top 3 Podium */
        .top-three {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .top-three .team-row {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px 25px;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .top-three .team-row:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        /* Remaining teams - 2 column grid */
        .remaining-teams {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .remaining-teams .team-row {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 25px;
            border-radius: 12px;
            display: grid;
            grid-template-columns: 60px 1fr 120px;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .remaining-teams .team-row:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .team-row {
            /* Base styles for all team rows */
        }

        .team-rank {
            font-size: 3em;
            font-weight: bold;
            color: #667eea;
            text-align: center;
        }

        .team-rank.first {
            color: #FFD700;
        }

        .team-rank.second {
            color: #C0C0C0;
        }

        .team-rank.third {
            color: #CD7F32;
        }

        /* Top 3 specific styles */
        .top-three .team-rank {
            font-size: 4em;
            margin-bottom: 10px;
        }

        .top-three .team-info h2 {
            margin: 0;
            font-size: 1.8em;
            color: #333;
        }

        .top-three .team-score {
            margin-top: 10px;
        }

        .top-three .score-value {
            font-size: 3.5em;
            font-weight: bold;
            color: #667eea;
            display: block;
            line-height: 1;
        }

        .top-three .score-label {
            font-size: 1em;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Remaining teams styles */
        .remaining-teams .team-rank {
            font-size: 2em;
        }

        .remaining-teams .team-info h2 {
            margin: 0;
            font-size: 1.3em;
            color: #333;
        }

        .remaining-teams .team-type {
            color: #888;
            font-size: 0.9em;
        }

        .remaining-teams .team-score {
            text-align: right;
        }

        .remaining-teams .score-value {
            font-size: 2.2em;
            font-weight: bold;
            color: #667eea;
            display: block;
            line-height: 1;
        }

        .remaining-teams .score-label {
            font-size: 0.75em;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .status-active {
            background: #10b981;
            color: white;
        }

        .status-paused {
            background: #f59e0b;
            color: white;
        }

        .status-completed {
            background: #6b7280;
            color: white;
        }

        .timer {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }

        .loading {
            text-align: center;
            padding: 50px;
            font-size: 1.5em;
            color: white;
        }

        @media (max-width: 1024px) {
            .remaining-teams {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .scoreboard-header h1 {
                font-size: 2em;
            }

            .top-three {
                grid-template-columns: 1fr;
            }

            .top-three .team-rank {
                font-size: 3em;
            }

            .top-three .team-info h2 {
                font-size: 1.5em;
            }

            .top-three .score-value {
                font-size: 2.5em;
            }

            .remaining-teams {
                grid-template-columns: 1fr;
            }

            .remaining-teams .team-row {
                grid-template-columns: 50px 1fr 100px;
                padding: 15px 20px;
            }

            .remaining-teams .team-rank {
                font-size: 1.8em;
            }

            .remaining-teams .team-info h2 {
                font-size: 1.2em;
            }

            .remaining-teams .score-value {
                font-size: 2em;
            }

            .scoreboard-info {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="public-scoreboard">
        <div id="loadingMessage" class="loading">Loading scoreboard...</div>
        <div id="scoreboardContent" style="display: none;">
            <div class="scoreboard-header">
                <h1 id="title">Scoreboard</h1>
                <div class="scoreboard-info">
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span id="status" class="status-badge"></span>
                    </div>
                    <div class="info-item" id="roundInfo" style="display: none;">
                        <span class="info-label">Round:</span>
                        <span id="round">1 / 1</span>
                    </div>
                    <div class="info-item" id="timerInfo" style="display: none;">
                        <span class="info-label">Time:</span>
                        <span id="timer" class="timer">00:00</span>
                    </div>
                </div>
            </div>
            <div id="teamsDisplay" class="teams-display"></div>
        </div>
    </div>

    <script>
        let scoreboardId = new URLSearchParams(window.location.search).get('id');
        let startTime = null;

        async function loadScoreboard() {
            try {
                const response = await fetch(`../api/public.php?id=${scoreboardId}`);
                const data = await response.json();

                if (data.error) {
                    document.getElementById('loadingMessage').textContent = 'Error: ' + data.error;
                    return;
                }

                // Show content, hide loading
                document.getElementById('loadingMessage').style.display = 'none';
                document.getElementById('scoreboardContent').style.display = 'block';

                // Update header
                document.getElementById('title').textContent = data.title;

                const statusBadge = document.getElementById('status');
                statusBadge.textContent = data.status;
                statusBadge.className = `status-badge status-${data.status}`;

                // Show/hide round info
                if (data.show_rounds == 1) {
                    document.getElementById('roundInfo').style.display = 'flex';
                    document.getElementById('round').textContent = `${data.current_round} / ${data.total_rounds}`;
                }

                // Show/hide timer
                if (data.show_time == 1) {
                    document.getElementById('timerInfo').style.display = 'flex';
                    if (!startTime && data.start_time) {
                        startTime = new Date(data.start_time);
                    }
                }

                // Display teams
                displayTeams(data.teams);

            } catch (error) {
                console.error('Error loading scoreboard:', error);
                document.getElementById('loadingMessage').textContent = 'Connection error. Retrying...';
            }
        }

        function displayTeams(teams) {
            const container = document.getElementById('teamsDisplay');

            if (!teams || teams.length === 0) {
                container.innerHTML = '<div class="loading">No teams yet</div>';
                return;
            }

            // Split teams into top 3 and rest
            const topThree = teams.slice(0, 3);
            const remaining = teams.slice(3);

            let html = '';

            // Render top 3 in podium style
            if (topThree.length > 0) {
                html += '<div class="top-three">';
                topThree.forEach((team, index) => {
                    const rank = index + 1;
                    let rankClass = '';
                    if (rank === 1) rankClass = 'first';
                    else if (rank === 2) rankClass = 'second';
                    else if (rank === 3) rankClass = 'third';

                    html += `
                        <div class="team-row">
                            <div class="team-rank ${rankClass}">${rank}</div>
                            <div class="team-info">
                                <h2>${team.name}</h2>
                            </div>
                            <div class="team-score">
                                <span class="score-value">${team.total_score || 0}</span>
                                <span class="score-label">Points</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
            }

            // Render remaining teams in compact 2-column grid
            if (remaining.length > 0) {
                html += '<div class="remaining-teams">';
                remaining.forEach((team, index) => {
                    const rank = index + 4; // Starts from 4th place

                    html += `
                        <div class="team-row">
                            <div class="team-rank">${rank}</div>
                            <div class="team-info">
                                <h2>${team.name}</h2>
                                <div class="team-type">${team.display_order ? `Position ${team.display_order}` : ''}</div>
                            </div>
                            <div class="team-score">
                                <span class="score-value">${team.total_score || 0}</span>
                                <span class="score-label">Pts</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
            }

            container.innerHTML = html;
        }

        // Update timer
        function updateTimer() {
            if (!startTime) return;

            const now = new Date();
            const diff = Math.floor((now - startTime) / 1000);

            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            const timeString = hours > 0
                ? `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
                : `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            document.getElementById('timer').textContent = timeString;
        }

        // Check if scoreboard ID exists
        if (!scoreboardId) {
            document.getElementById('loadingMessage').textContent = 'No scoreboard ID provided';
        } else {
            // Initial load
            loadScoreboard();

            // Refresh every 2 seconds for real-time updates
            setInterval(loadScoreboard, 2000);

            // Update timer every second
            setInterval(updateTimer, 1000);
        }
    </script>
</body>
</html>
