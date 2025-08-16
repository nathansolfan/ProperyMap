<x-layout title="Liverpool Property Map">
    <div class="h-screen flex">
        {{-- Sidebar --}}
        <div class="w-80 bg-white shadow-lg p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Liverpool</h1>
                <p class="text-gray-600">Property Price Map</p>
            </div>

            {{-- Year Selector --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Year
                </label>
                <select id="yearSelect" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                    <option value="2021">2021</option>
                    <option value="2020">2020</option>
                </select>
            </div>

            {{-- Stats Card --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Statistics</h3>
                <div id="stats" class="text-sm text-gray-600 space-y-2">
                    <p>Loading...</p>
                </div>
            </div>

            {{-- Legend --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Price Range</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                        <span>< £200k</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-yellow-500 rounded-full mr-3"></div>
                        <span>£200k - £300k</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-red-500 rounded-full mr-3"></div>
                        <span>> £300k</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Map Container --}}
        <div class="flex-1 relative">
            <div id="map" class="absolute inset-0"></div>
        </div>
    </div>

    {{-- JavaScript --}}
    <script src="{{ asset('js/map.js') }}"></script>
</x-layout>
