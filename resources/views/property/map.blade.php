<x-layout>
    <form method="GET" action="{{ route('property.index') }}" class="mb-4">
        <input type="text" name="postcode" placeholder="Digite o postcode"
               value="{{ $postcode }}" class="border p-2 rounded"/>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
            Buscar
        </button>
    </form>

    @if(empty($transactions))
        <p class="text-red-500">Nenhuma transação encontrada para este postcode.</p>
    @endif

    <div id="map" class="w-full h-[600px]"></div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        let map = L.map('map').setView([{{ $lat }}, {{ $lng }}], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
        }).addTo(map);

        // Marcador do postcode
        L.marker([{{ $lat }}, {{ $lng }}]).addTo(map)
            .bindPopup(`<b>{{ $postcode }}</b><br>
Total transações: {{ count($transactions) }}`)
            .openPopup();
    </script>
</x-layout>
