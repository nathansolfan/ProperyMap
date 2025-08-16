<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Liverpool Property Map' }}</title>


    {{-- Leaflet.js --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {{-- Custom styles se precisar --}}
    <style>
        .leaflet-container {
            height: 100%;
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-50">
{{ $slot }}
</body>
</html>
