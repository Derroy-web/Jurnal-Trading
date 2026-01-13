<!DOCTYPE html>
<html lang="id">
<head>
    <title>AI Trading Journal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-200 min-h-screen p-5">
    
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">
                SMC AI Journal
            </h1>
            <a href="{{ route('trades.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-bold">
                + New Trade
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-800 text-green-100 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($trades as $trade)
            <div class="bg-gray-800 rounded-xl overflow-hidden shadow-lg border border-gray-700 hover:border-blue-500 transition">
                <div class="h-48 overflow-hidden">
                    <img src="{{ asset('storage/' . $trade->screenshot_path) }}" class="w-full h-full object-cover">
                </div>

                <div class="p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-bold text-xl">{{ $trade->pair }}</h3>
                            <span class="text-xs px-2 py-1 rounded bg-gray-700 text-gray-300">{{ $trade->direction }}</span>
                        </div>
                        <div class="text-center">
                            <span class="block text-xs text-gray-400">AI Grade</span>
                            <span class="text-2xl font-black 
                                {{ $trade->system_grade == 'A' ? 'text-green-400' : 
                                  ($trade->system_grade == 'B' ? 'text-yellow-400' : 'text-red-400') }}">
                                {{ $trade->system_grade }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-gray-900 p-3 rounded text-sm text-gray-400 space-y-2">
                        <p><strong class="text-gray-300">Structure:</strong> {{ $trade->ai_analysis_data['market_structure'] ?? '-' }}</p>
                        
                        <div>
                            <strong class="text-gray-300">Patterns:</strong>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @if(isset($trade->ai_analysis_data['patterns_detected']))
                                    @foreach($trade->ai_analysis_data['patterns_detected'] as $pattern)
                                        <span class="px-2 py-0.5 bg-blue-900 text-blue-200 text-xs rounded-full">{{ $pattern }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <p class="italic text-xs border-t border-gray-700 pt-2 mt-2">
                            "{{ $trade->ai_analysis_data['summary_comment'] ?? 'No comment' }}"
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-between items-center border-t border-gray-700 pt-3">
                <div class="text-sm">
                    @if($trade->result == 'OPEN')
                        <span class="text-gray-500">Running... ⏳</span>
                    @elseif($trade->result == 'WIN')
                        <span class="text-green-400 font-bold">+$ {{ $trade->pnl }} ({{ $trade->rr_obtained }}R)</span>
                    @elseif($trade->result == 'LOSS')
                        <span class="text-red-400 font-bold">-$ {{ abs($trade->pnl) }}</span>
                    @endif
                </div>
                
                <a href="{{ route('trades.edit', $trade->id) }}" 
                class="bg-gray-700 hover:bg-blue-600 text-white text-xs px-3 py-2 rounded transition">
                📝 Update Result
                </a>
            </div>
            @endforeach
        </div>
    </div>
</body>
</html>