@extends('layouts.admin')

@section('title', 'Display Screen')

@php
    $hideSidebar = true;
    $hideTopbar = true;
    $selectedCounters = request()->query('counters', range(1,5));
@endphp

@section('content')
<style>
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    width: 100%;
    overflow: hidden;
    background-color: #1f2937;
}

#displayScreenContainer {
    display: flex;
    height: 100vh;
    width: 100vw;
    gap: 1rem;
    padding: 0.5rem;
    box-sizing: border-box;
}

#videoPanel {
    flex: 3;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: relative;
}

#videoPlayer {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0.5rem;
}

#dateTimePanel {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #ffffff;
    color: #1e40af;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
}

#countersPanel {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 1rem;
}

#txtTopNowServing {
    font-size: 2.5rem;
    color: white;
    font-weight: bold;
}

.counterBox {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #1e3a8a;
    padding: 1rem 2rem;
    border-radius: 0.75rem;
    width: 100%;
}

.counterLabel {
    color: white;
    font-size: 1.5rem;
}

.counterNumber {
    color: #facc15;
    font-size: 2rem;
    font-weight: bold;
}
</style>

<div id="displayScreenContainer">

    <!-- VIDEO PANEL -->
    <div id="videoPanel">

        <!-- FIXED VIDEO (autoplay + muted required for browsers) -->
        <video id="videoPlayer" autoplay muted loop playsinline>
            <source src="{{ asset('storage/videos/VIDEOFORQUEUING.mp4') }}" type="video/mp4">
        </video>

        <!-- DATE / TIME -->
        <div id="dateTimePanel">
            <img src="{{ asset('storage/images/logoDTI.png') }}" style="height:80px;">

            <div style="text-align:center;">
                <div id="txtClock" style="font-size:40px;font-weight:bold;"></div>
                <div id="txtDate" style="font-size:20px;"></div>
            </div>

            <img src="{{ asset('storage/images/bagongpilipinas2.png') }}" style="height:80px;">
        </div>

    </div>

    <!-- COUNTERS -->
    <div id="countersPanel">
        <h1 id="txtTopNowServing">NOW SERVING</h1>

        @foreach($selectedCounters as $i)
        <div class="counterBox">
            <span class="counterLabel">Counter {{ $i }}:</span>
            <span id="txtServingNumber{{ $i }}" class="counterNumber">C000</span>
        </div>
        @endforeach
    </div>

</div>

<!-- AUDIO -->
<audio id="nextSound" preload="auto">
    <source src="{{ asset('storage/audios/doorbell-223669.mp3') }}" type="audio/mpeg">
</audio>

@endsection


@section('scripts')
<script>

// CLOCK
function updateClock() {
    const now = new Date();

    const hours = now.getHours() % 12 || 12;
    const minutes = now.getMinutes().toString().padStart(2,'0');
    const seconds = now.getSeconds().toString().padStart(2,'0');
    const ampm = now.getHours() >= 12 ? 'PM' : 'AM';

    document.getElementById('txtClock').textContent =
        `${hours}:${minutes}:${seconds} ${ampm}`;

    document.getElementById('txtDate').textContent =
        now.toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });
}

setInterval(updateClock, 1000);
updateClock();


// AUDIO FIX
const nextSound = document.getElementById('nextSound');

function playNextSound() {
    nextSound.currentTime = 0;
    nextSound.play().catch(() => {});
}


// FETCH COUNTERS
function fetchCounters() {

    fetch("{{ route('admin.getCounters') }}")
        .then(res => res.json())
        .then(data => {

            @foreach($selectedCounters as $i)
            let el{{ $i }} = document.getElementById('txtServingNumber{{ $i }}');

            if (el{{ $i }}) {

                let newTicket = 'C000';

                if (data[{{ $i }}] && data[{{ $i }}].ticket) {

                    let ticketNum = parseInt(data[{{ $i }}].ticket);

                    if (!isNaN(ticketNum)) {
                        newTicket = 'C' + ticketNum.toString().padStart(3,'0');
                    }
                }

                el{{ $i }}.textContent = newTicket;
            }
            @endforeach

        })
        .catch(err => console.log(err));
}

setInterval(fetchCounters, 2000);
fetchCounters();

</script>
@endsection
