<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Jurnal Trading</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    {{-- 1. BAGIAN PESAN SUKSES --}}
    @if(session('success'))
        <div class="max-w-2xl mx-auto mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <b>BERHASIL!</b> {{ session('success') }}
        </div>
    @endif

    {{-- 2. BAGIAN DASHBOARD SUMMARY --}}
    <div class="max-w-6xl mx-auto mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
            <h3 class="text-gray-500 text-sm font-medium">Win Rate</h3>
            <p class="text-3xl font-bold text-gray-800">{{ $winRate }}%</p>
            <p class="text-xs text-gray-400 mt-1">Target: >50%</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-gray-500">
            <h3 class="text-gray-500 text-sm font-medium">Total Trades</h3>
            <p class="text-3xl font-bold text-gray-800">{{ $totalTrades }}</p>
            <p class="text-xs text-green-600 mt-1">{{ $wins }} Wins - {{ $losses }} Losses</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
            <h3 class="text-gray-500 text-sm font-medium">Best Setup</h3>
            <p class="text-xl font-bold text-gray-800 truncate">
                {{ $bestSetup ? $bestSetup->setup_type : '-' }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Setup paling sering profit</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-purple-500">
            <h3 class="text-gray-500 text-sm font-medium">Net Profit / Loss</h3>
            <p class="text-3xl font-bold {{ ($totalProfit ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ ($totalProfit ?? 0) >= 0 ? '+' : '' }}${{ number_format($totalProfit ?? 0, 2) }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Growth: {{ $totalGainPercent ?? 0 }}%</p>
        </div>
    </div>

    {{-- 3. BAGIAN FORM INPUT --}}
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md mt-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">📝 Jurnal Trading Baru</h1>

        <form action="{{ route('trades.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h2 class="text-lg font-semibold mb-3 text-blue-800">✅ SOP Checklist</h2>
                <p class="text-sm text-gray-600 mb-4">Centang yang sudah dipenuhi. Yang tidak dicentang akan dicatat sebagai pelanggaran.</p>
                
                <div class="space-y-2">
                    @foreach($sopList as $key => $question)
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="sop[{{ $key }}]" value="1" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                        <span class="text-gray-700">{{ $question }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pair</label>
                    <select name="pair" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border bg-white">
                        <option value="XAU/USD">XAU/USD</option>
                        <option value="EUR/USD">EUR/USD</option>
                        <option value="GBP/USD">GBP/USD</option>
                        <option value="BTC/USD">BTC/USD</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Session</label>
                    <select name="session" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border bg-white">
                        <option value="London">London</option>
                        <option value="New York">New York</option>
                        <option value="Asian">Asian</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Position</label>
                    <select name="position" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border bg-white">
                        <option value="LONG">LONG (Buy)</option>
                        <option value="SHORT">SHORT (Sell)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Balance ($)</label>
                    <input type="number" step="0.01" name="account_balance" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" placeholder="1000.00">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Screenshot Setup</label>
                <input type="file" name="chart_image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-1">AI akan menganalisa gambar ini.</p>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded hover:bg-blue-700 transition duration-300">
                Analisa dengan AI & Simpan Log 🚀
            </button>
        </form>
    </div>

    {{-- 4. BAGIAN TABEL LOG --}}
    <div class="max-w-6xl mx-auto mt-12 mb-20 bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4 text-gray-800">📊 Trade Log (History)</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Pair</th>
                        <th class="px-6 py-3">Session</th>
                        <th class="px-6 py-3">Pos</th>
                        <th class="px-6 py-3">Setup (AI)</th>
                        <th class="px-6 py-3">SOP Check</th>
                        <th class="px-6 py-3">AI Analysis</th>
                        <th class="px-6 py-3">Image</th>
                        <th class="px-6 py-3">Result / Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($trades as $trade)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            {{ $trade->created_at->format('d M Y H:i') }}
                        </td>
                        
                        <td class="px-6 py-4 font-bold text-gray-900">
                            {{ $trade->pair }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $trade->session }}
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-white text-xs {{ $trade->position == 'LONG' ? 'bg-green-500' : 'bg-red-500' }}">
                                {{ $trade->position }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded border border-blue-400">
                                {{ $trade->setup_type }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            @php
                                $sopCount = count($trade->sop_data ?? []); 
                            @endphp
                            @if($sopCount >= 5)
                                <span class="text-green-600 font-bold">✅ DISIPLIN</span>
                            @else
                                <span class="text-red-600 font-bold">⚠️ MELANGGAR</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 max-w-xs truncate" title="{{ $trade->trade_note }}">
                            {{ Str::limit($trade->trade_note, 50) }}
                        </td>

                        <td class="px-6 py-4">
                            <a href="{{ asset('storage/' . $trade->chart_image) }}" target="_blank" class="text-blue-600 hover:underline">
                                Lihat Chart
                            </a>
                        </td>

                        <td class="px-6 py-4">
                            @if($trade->result == 'OPEN')
                                <form action="{{ route('trades.updateStatus', $trade->id) }}" method="POST" class="flex flex-col space-y-2">
                                    @csrf
                                    
                                    <input type="number" step="0.01" name="pnl" placeholder="Nominal ($)" required
                                        class="w-24 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">

                                    <div class="flex space-x-1">
                                        <button type="submit" name="result" value="WIN" class="bg-green-500 hover:bg-green-600 text-white text-xs font-bold py-1 px-2 rounded">
                                            WIN
                                        </button>
                                        
                                        <button type="submit" name="result" value="LOSS" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-1 px-2 rounded">
                                            LOSS
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="flex flex-col mb-2">
                                    <span class="px-2 py-1 rounded text-white text-xs font-bold text-center {{ $trade->result == 'WIN' ? 'bg-green-600' : 'bg-red-600' }}">
                                        {{ $trade->result }}
                                    </span>
                                    <span class="text-xs font-semibold text-center mt-1 {{ $trade->profit_loss > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $trade->profit_loss > 0 ? '+' : '' }}${{ number_format($trade->profit_loss, 2) }}
                                    </span>
                                </div>
                            @endif

                            <form action="{{ route('trades.destroy', $trade->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?');" class="mt-3 pt-2 border-t border-gray-100 text-center">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[10px] text-gray-400 hover:text-red-600 underline">
                                    Hapus Data
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="mt-4">
                {{ $trades->links() }}
            </div>
        </div>
    </div>

</body>
</html>