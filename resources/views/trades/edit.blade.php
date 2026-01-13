<!DOCTYPE html>
<html lang="id">
<head>
    <title>Update Trade Result</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-10">
    <div class="max-w-2xl mx-auto bg-gray-800 p-8 rounded-lg shadow-xl border border-gray-700">
        <h2 class="text-2xl font-bold mb-6 text-blue-400">📝 Journaling Result: {{ $trade->pair }}</h2>
        
        <div class="mb-6 p-4 bg-gray-900 rounded text-sm text-gray-400">
            <p><strong>Entry Date:</strong> {{ $trade->created_at->format('d M Y H:i') }}</p>
            <p><strong>Direction:</strong> {{ $trade->direction }}</p>
            <p><strong>AI Initial Grade:</strong> {{ $trade->system_grade }}</p>
        </div>

        <form action="{{ route('trades.update', $trade->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block mb-2 font-bold text-gray-300">Result</label>
                    <select name="result" class="w-full bg-gray-700 p-3 rounded border border-gray-600 focus:border-blue-500 text-white">
                        <option value="WIN" {{ $trade->result == 'WIN' ? 'selected' : '' }}>✅ WIN (TP Hit)</option>
                        <option value="LOSS" {{ $trade->result == 'LOSS' ? 'selected' : '' }}>❌ LOSS (SL Hit)</option>
                        <option value="BE" {{ $trade->result == 'BE' ? 'selected' : '' }}>🛡️ BE (Break Even)</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-2 font-bold text-gray-300">Trading Session</label>
                    <select name="session" class="w-full bg-gray-700 p-3 rounded border border-gray-600 focus:border-blue-500 text-white">
                        <option value="London" {{ $trade->session == 'London' ? 'selected' : '' }}>🇬🇧 London</option>
                        <option value="New York" {{ $trade->session == 'New York' ? 'selected' : '' }}>🇺🇸 New York</option>
                        <option value="Asian" {{ $trade->session == 'Asian' ? 'selected' : '' }}>🇯🇵 Asian</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-2 font-bold text-gray-300">PnL (Dollar $)</label>
                    <input type="number" step="0.01" name="pnl" value="{{ $trade->pnl }}" placeholder="e.g. 15.50" 
                           class="w-full bg-gray-700 p-3 rounded border border-gray-600 focus:border-blue-500 text-white">
                </div>

                <div>
                    <label class="block mb-2 font-bold text-gray-300">RR Obtained (1:?)</label>
                    <input type="number" step="0.01" name="rr_obtained" value="{{ $trade->rr_obtained }}" placeholder="e.g. 3.5" 
                           class="w-full bg-gray-700 p-3 rounded border border-gray-600 focus:border-blue-500 text-white">
                </div>
            </div>

            <div>
                <label class="block mb-2 font-bold text-gray-300">Evaluation Notes</label>
                <textarea name="note" rows="4" placeholder="Apa yang bagus? Apa kesalahannya?" 
                          class="w-full bg-gray-700 p-3 rounded border border-gray-600 focus:border-blue-500 text-white">{{ $trade->note }}</textarea>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('trades.index') }}" class="px-6 py-3 bg-gray-600 hover:bg-gray-500 rounded font-bold transition">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 rounded font-bold transition">
                    Save & Close Trade 💾
                </button>
            </div>
        </form>
    </div>
</body>
</html>