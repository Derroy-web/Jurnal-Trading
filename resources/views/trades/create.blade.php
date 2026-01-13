<!DOCTYPE html>
<html lang="id">
<head>
    <title>Upload Trade - AI Journal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-10">
    <div class="max-w-md mx-auto bg-gray-800 p-6 rounded-lg shadow-xl">
        <h2 class="text-2xl font-bold mb-6 text-blue-400">🤖 Input New Setup</h2>
        
        <form action="{{ route('trades.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            
            <div>
                <label class="block mb-1">Pair</label>
                <input type="text" name="pair" placeholder="e.g. XAUUSD" class="w-full bg-gray-700 p-2 rounded border border-gray-600 focus:outline-none focus:border-blue-500" required>
            </div>

            <div>
                <label class="block mb-1">Direction</label>
                <select name="direction" class="w-full bg-gray-700 p-2 rounded border border-gray-600">
                    <option value="LONG">LONG (Buy)</option>
                    <option value="SHORT">SHORT (Sell)</option>
                </select>
            </div>

            <div>
                <label class="block mb-1">Screenshot Chart</label>
                <input type="file" name="screenshot" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" required>
            </div>

            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 py-2 rounded font-bold transition">
                Analyze with AI 🚀
            </button>
        </form>
    </div>
</body>
</html>