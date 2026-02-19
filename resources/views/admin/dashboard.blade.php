@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@php
/** @var \Illuminate\Support\Collection|\App\Models\User[] $users */
/** @var \App\Models\User|null $user */
$users = $users ?? collect();
@endphp

@section('content')

<!-- PAGE HEADER WITH LOGO -->
<div class="flex items-center gap-4 mb-8">
    <img src="{{ asset('storage/images/DTICPOLOGOQUEUEING.png') }}"
         alt="DTIR2 Logo"
         class="w-16 h-16 object-contain">

    <h1 class="text-4xl font-extrabold text-gray-100">
        DTIR2 Queueing
    </h1>
</div>

<!-- WELCOME CARD -->
<div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl shadow-lg max-w-xl mb-10 text-gray-100 border border-white/20">
    <p class="text-lg">
        Welcome,
        <span class="font-semibold text-yellow-400">
            {{ optional(Auth::user())->full_name ?? optional(Auth::user())->user_id ?? '-' }}
        </span>
    </p>
    <p class="text-sm mt-1">
        Role:
        <span class="font-semibold text-blue-400">
            {{ optional(Auth::user())->role ?? '-' }}
        </span>
    </p>
</div>

<!-- ================= DASHBOARD CARDS ================= -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

    <!-- COUNTERS CARD -->
    <div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-lg p-6 hover:scale-105 transition-transform duration-300 text-gray-100 border border-white/20">
        <h2 class="text-2xl font-bold mb-3 text-yellow-400">Counters</h2>
        <p class="mb-4">Select which counters are visible on the display screen.</p>

        <form id="counterForm">
            <div class="flex flex-col space-y-3 mt-2">
                @for ($i = 1; $i <= 5; $i++)
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox"
                               name="counters[]"
                               value="{{ $i }}"
                               class="form-checkbox h-5 w-5 text-blue-500 accent-blue-500 rounded">
                        <span class="text-gray-100 font-medium">Counter {{ $i }}</span>
                    </label>
                @endfor
            </div>

            <button type="submit"
                    class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold shadow-lg transition-colors duration-300">
                Display Screen
            </button>
        </form>
    </div>

    <!-- USERS CARD -->
    <div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-lg p-6 hover:scale-105 transition-transform duration-300 text-gray-100 border border-white/20">
        <h2 class="text-2xl font-bold mb-3 text-yellow-400">Users</h2>
        <p class="mb-4">Add and manage users including roles and counters.</p>

        <a href="{{ route('admin.createUserForm') }}"
           class="mt-2 block text-center bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold shadow-lg transition-colors duration-300">
            Create New User
        </a>

        <a href="{{ route('admin.users') }}"
           class="mt-3 block text-center bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-xl font-semibold shadow-lg transition-colors duration-300">
            View Users
        </a>
    </div>

    <!-- COUNTER STATUS CARD -->
    <div id="counterStatusCard" class="bg-white/10 backdrop-blur-md rounded-2xl shadow-lg p-6 hover:scale-105 transition-transform duration-300 text-gray-100 border border-white/20">
        <h2 class="text-2xl font-bold mb-3 text-yellow-400">Counter Status</h2>
        <p class="mb-4">Shows which counters are online/offline.</p>

        <div id="counterStatusList" class="flex flex-col gap-2">
            @for ($i = 1; $i <= 5; $i++)
                <div class="flex justify-between items-center p-3 rounded-xl bg-gray-800" id="counterBox{{ $i }}">
                    <span class="font-medium">Counter {{ $i }}</span>
                    <span class="font-bold text-sm text-white" id="counterStatus{{ $i }}">
                        Checking...
                    </span>
                </div>
            @endfor
        </div>
    </div>

</div>

<!-- JS -->
<script>
document.getElementById('counterForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let counters = Array.from(document.querySelectorAll('input[name="counters[]"]:checked'))
                        .map(cb => cb.value);

    if(counters.length === 0) {
        alert('Select at least one counter.');
        return;
    }

    let url = '{{ route("admin.displayScreen") }}' + '?counters[]=' + counters.join('&counters[]=');
    window.open(url, '_blank', 'width=1280,height=720');
});

function fetchCounterStatus() {
    fetch("{{ route('admin.getCounterStatus') }}")
        .then(res => res.json())
        .then(data => {
            for(let i = 1; i <= 5; i++) {
                const statusEl = document.getElementById('counterStatus'+i);

                if(data[i]) {
                    statusEl.textContent = data[i].user + ' - ' +
                        (data[i].status === 'online' ? '🟢 Online' : '🔴 Offline');
                } else {
                    statusEl.textContent = 'No user assigned - 🔴 Offline';
                }
            }
        })
        .catch(err => console.error('Error fetching counter status:', err));
}

fetchCounterStatus();
setInterval(fetchCounterStatus, 5000);
</script>

@endsection
