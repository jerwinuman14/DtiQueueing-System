<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Counter Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        /* ====== Reset & Body ====== */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #121212;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 10px;
        }

        /* ====== Card Container ====== */
        .card {
            width: 100%;
            max-width: 400px;
            background: #1e1e1e;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.6);
            color: #fff;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* ====== Title ====== */
        .counter-title {
            font-size: 16px;
            font-weight: 600;
            color: #ffd700;
        }

        h1 {
            font-size: 22px;
            font-weight: 700;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }

        .username {
            font-size: 18px;
            font-weight: 500;
            color: #00e676;
        }

        /* ====== Buttons ====== */
        .buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .buttons button {
            flex: 1 1 45%;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            min-width: 120px;
        }

        .buttons button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.4);
        }

        .btn-next { background: #2979ff; color: #fff; }
        .btn-done { background: #d32f2f; color: #fff; }

        /* ====== Stats ====== */
        .stats {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .stat-box {
            flex: 1 1 30%;
            background: #2c2c2c;
            padding: 15px 10px;
            border-radius: 10px;
            text-align: center;
            min-width: 80px;
        }

        .stat-box h3 {
            font-size: 14px;
            font-weight: 500;
            color: #ccc;
        }

        .stat-box h2 {
            font-size: 24px;
            font-weight: 700;
            margin-top: 8px;
            color: #fff;
        }

        /* ====== Logout Button ====== */
        .logout {
            margin-top: 10px;
        }

        .logout button {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: none;
            border-radius: 10px;
            background: #555;
            color: #fff;
            cursor: pointer;
            transition: background 0.2s;
        }

        .logout button:hover {
            background: #777;
        }

        /* ====== Responsive ====== */
        @media(max-width: 450px) {
            h1 { font-size: 20px; }
            .username { font-size: 16px; }
            .stat-box h2 { font-size: 20px; }
            .buttons button { font-size: 14px; padding: 10px; }
            .stat-box { flex: 1 1 45%; margin-bottom: 8px; }
        }

        @media(max-width: 350px) {
            h1 { font-size: 18px; }
            .username { font-size: 14px; }
            .stat-box h2 { font-size: 18px; }
            .buttons button { font-size: 13px; padding: 8px; }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="counter-title">
        DTIR2 CAGAYAN ONLINE QUEUEING SYSTEM
    </div>

    <!-- Counter Number and Full Name -->
    <h1>
        Counter {{ $counterId }}
        <span class="username">{{ $fullName ?? $user->user_id }}</span>
    </h1>

    <!-- Buttons -->
    <div class="buttons">
        <button class="btn-next" onclick="nextTicket()">NEXT</button>
        <button class="btn-done" onclick="completeTicket()">DONE</button>
    </div>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-box">
            <h3>SERVING</h3>
            <h2 id="serving">---</h2>
        </div>
        <div class="stat-box">
            <h3>WAITING</h3>
            <h2 id="waiting">0</h2>
        </div>
        <div class="stat-box">
            <h3>DONE</h3>
            <h2 id="done">---</h2>
        </div>
    </div>

    <!-- Logout -->
    <div class="logout">
        <form method="POST" action="{{ route('counter.logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>

    <!-- 🔊 AUDIO -->
    <audio id="nextSound" preload="auto">
        <source src="{{ asset('storage/audios/doorbell-223669.mp3') }}" type="audio/mpeg">
    </audio>
</div>

<script>
axios.defaults.headers.common['X-CSRF-TOKEN'] =
    document.querySelector('meta[name="csrf-token"]').content;

// 🔊 PLAY SOUND ON NEXT ONLY
function playNextSound() {
    const sound = document.getElementById('nextSound');
    sound.currentTime = 0;
    sound.play().catch(e => console.log("Audio blocked:", e));
}

/* SERVE NEXT TICKET */
function nextTicket() {
    axios.post("{{ route('counter.serveTicket') }}")
        .then(() => {
            loadStats();
            playNextSound(); // 🔥 play sound exactly once per NEXT click
        })
        .catch(error => {
            alert(error.response?.data?.message ?? 'No waiting tickets or already serving.');
        });
}

/* COMPLETE CURRENT TICKET */
function completeTicket() {
    axios.post("{{ route('counter.completeTicket') }}")
        .then(() => loadStats())
        .catch(error => {
            alert(error.response?.data?.message ?? 'No ticket currently serving.');
        });
}

/* LOAD LIVE STATS */
function loadStats() {
    axios.get("{{ route('counter.status') }}")
        .then(response => {
            const servingNum = response.data.serving;
            const waitingNum = response.data.waiting;
            const lastDoneNum = response.data.last_done;

            document.getElementById('serving').innerText = servingNum !== null
                ? 'C' + String(parseInt(servingNum,10)).padStart(3,'0')
                : '---';

            document.getElementById('waiting').innerText = waitingNum ?? 0;

            document.getElementById('done').innerText = lastDoneNum !== null
                ? 'C' + String(parseInt(lastDoneNum,10)).padStart(3,'0')
                : '---';
        });
}

/* Auto-refresh every 3 seconds */
setInterval(loadStats, 3000);
loadStats();
</script>

</body>
</html>
