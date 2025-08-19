<x-layout title="HM Land Registry API Test">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">HM Land Registry API Test</h1>

        <!-- Status do API Key -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold mb-2">API Configuration</h2>
            <p class="text-sm text-gray-600">
                API Key: {{ env('HM_LAND_REGISTRY_API_KEY') ? 'Configurado ✅' : 'Não configurado ❌' }}
            </p>
        </div>

        <!-- Botões de Teste -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <button onclick="testConnection()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Test Connection
            </button>

            <button onclick="getDatasets()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                Get Datasets
            </button>

            <button onclick="testAll()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                Test All Endpoints
            </button>

            <div class="flex">
                <input type="text" id="postcodeInput" placeholder="L1" class="flex-1 px-3 py-2 border rounded-l">
                <button onclick="testPublicAPI()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-r">
                    Test Public API (CSV)
                </button>
            </div>

            <div class="flex">
                <input type="text" id="postcodeJsonInput" placeholder="L1" class="flex-1 px-3 py-2 border rounded-l">
                <button onclick="getPropertyPrices()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-r">
                    Get Property Prices (JSON)
                </button>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loading" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <p class="text-yellow-800">Loading... ⏳</p>
        </div>

        <!-- Results -->
        <div id="results" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold mb-2">Results</h3>
            <pre id="resultsContent" class="text-sm bg-white p-4 rounded border overflow-auto max-h-96">
Click a button above to test the API...
            </pre>
        </div>

        <!-- Error Display -->
        <div id="error" class="hidden bg-red-50 border border-red-200 rounded-lg p-4 mt-4">
            <h3 class="text-lg font-semibold text-red-800 mb-2">Error</h3>
            <pre id="errorContent" class="text-sm text-red-700"></pre>
        </div>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('error').classList.add('hidden');
        }

        function hideLoading() {
            document.getElementById('loading').classList.add('hidden');
        }

        function showResults(data) {
            document.getElementById('resultsContent').textContent = JSON.stringify(data, null, 2);
            hideLoading();
        }

        function showError(error) {
            document.getElementById('error').classList.remove('hidden');
            document.getElementById('errorContent').textContent = JSON.stringify(error, null, 2);
            hideLoading();
        }

        async function testConnection() {
            showLoading();
            try {
                const response = await fetch('/test/connection');
                const data = await response.json();
                showResults(data);
            } catch (error) {
                showError(error);
            }
        }

        async function getDatasets() {
            showLoading();
            try {
                const response = await fetch('/test/datasets');
                const data = await response.json();
                showResults(data);
            } catch (error) {
                showError(error);
            }
        }

        async function testAll() {
            showLoading();
            try {
                const response = await fetch('/test/all');
                const data = await response.json();
                showResults(data);
            } catch (error) {
                showError(error);
            }
        }

        async function testPublicAPI() {
            const postcode = document.getElementById('postcodeInput').value || 'L1';
            showLoading();
            try {
                const response = await fetch(`/test/public-api?postcode=${postcode}`);
                const data = await response.json();
                showResults(data);
            } catch (error) {
                showError(error);
            }
        }

        async function getPropertyPrices() {
            const postcode = document.getElementById('postcodeJsonInput').value || 'L1';
            showLoading();
            try {
                const response = await fetch(`/test/property-prices?postcode=${postcode}`);
                const data = await response.json();
                showResults(data);
            } catch (error) {
                showError(error);
            }
        }

        async function checkApiKey() {
            showLoading();
            try {
                const response = await fetch('/test/check-api-key');
                const data = await response.json();
                showResults(data);
            } catch (error) {
                showError(error);
            }
        }
    </script>
</x-layout>
