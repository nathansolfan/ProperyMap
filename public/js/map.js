// Aguardar página carregar
document.addEventListener('DOMContentLoaded', function() {

    // Inicializar mapa centrado em Liverpool
    const map = L.map('map').setView([53.4084, -2.9916], 13);

    // Adicionar tiles (fundo do mapa)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Dados hardcoded temporários
    const areas = [
        {
            name: 'City Centre',
            lat: 53.4084,
            lng: -2.9916,
            price: 280000
        },
        {
            name: 'Business District',
            lat: 53.4094,
            lng: -2.9856,
            price: 250000
        }
    ];

    // Adicionar pontos no mapa
    areas.forEach(area => {
        L.circleMarker([area.lat, area.lng], {
            radius: 10,
            color: 'red',
            weight: 2,
            fillOpacity: 0.7
        })
            .bindPopup(`${area.name}: £${area.price.toLocaleString()}`)
            .addTo(map);
    });
});
