<!DOCTYPE html>
<html>
<head>
    <title>UK Property Sales</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .search-form { background: #ecf0f1; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .search-form input { width: 200px; padding: 10px; border: 1px solid #bdc3c7; border-radius: 4px; margin-right: 10px; font-size: 16px; }
        .search-form button { padding: 10px 20px; background: #27ae60; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .search-form button:hover { background: #229954; }
        .property { border-left: 4px solid #3498db; margin: 20px 0; padding: 20px; background: #fafafa; border-radius: 5px; }
        .price { color: #27ae60; font-weight: bold; font-size: 24px; margin-bottom: 8px; }
        .address { font-size: 16px; margin-bottom: 5px; }
        .postcode { color: #3498db; font-weight: bold; }
        .meta { color: #666; font-size: 14px; margin-top: 8px; }
        .search-links { margin-top: 30px; padding: 20px; background: #ecf0f1; border-radius: 5px; }
        .search-links a { margin-right: 15px; padding: 8px 16px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 8px; }
        .search-links a:hover { background: #2980b9; }
        .sort-options { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; border: 1px solid #e9ecef; }
        .sort-btn { display: inline-block; margin-right: 15px; padding: 8px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; cursor: pointer; border: none; font-size: 14px; }
        .sort-btn.active { background: #3498db; font-weight: bold; }
        .sort-btn:hover { background: #5a6268; }
        .sort-btn.active:hover { background: #2980b9; }
    </style>
</head>
<body>
<div class="container">
    <h1>UK Property Sales</h1>

    <div class="search-form">
        <h3>Search by Street:</h3>
        <form>
            <input type="text" name="street" placeholder="e.g., Windsor Road" required>
            <input type="text" name="city" value="LONDON">
            <button type="submit">Search Street</button>
        </form>
    </div>

    <div class="search-form">
        <h3>Search by Postcode or City:</h3>
        <form>
            <input type="text" name="search" placeholder="e.g., SW1A, L44LW, Manchester..." value="{{ request('search') }}" required>
            <button type="submit">Search</button>
        </form>
    </div>

    <h2>Results for: {{ $search }}</h2>
    <p><strong>{{ count($properties) }}</strong> properties found</p>

    @if(count($properties) > 0)
        <div class="sort-options">
            <strong>Sort By:</strong>
            <a href="?{{ http_build_query(array_merge(request()->query(), ['sort' => 'street_number'])) }}"
               class="sort-btn {{ ($sortBy ?? 'street_number') == 'street_number' ? 'active' : '' }}">
                Street Number
            </a>
            <a href="?{{ http_build_query(array_merge(request()->query(), ['sort' => 'date'])) }}"
               class="sort-btn {{ $sortBy == 'date' ? 'active' : '' }}">
                Date (Recent First)
            </a>
        </div>

        @foreach($properties as $property)
            <div class="property">
                <div class="price">£{{ number_format($property['price']) }}</div>
                <div class="address"><strong>{{ $property['address'] }}</strong></div>
                <div><strong>Postcode:</strong> <span class="postcode">{{ $property['postcode'] }}</span></div>
                <div class="meta">
                    <strong>Sale Date:</strong> {{ $property['date'] }} |
                    <strong>Type:</strong> {{ $property['type'] }}
                </div>
            </div>
        @endforeach
    @else
        <p><strong>No properties found for "{{ $search }}".</strong></p>
        <p>Try: specific postcodes (SW1A, E14), partial postcodes (SW1, E1), or city names (London, Manchester)</p>
    @endif

    <div class="search-links">
        <h3>Quick Search Examples:</h3>
        <strong>Popular Postcodes:</strong><br>
        <a href="?search=SW1">SW1</a>
        <a href="?search=L4">L4</a>
        <a href="?search=M1">M1</a>
        <a href="?search=B1">B1</a>
    </div>
</div>
</body>
</html>
