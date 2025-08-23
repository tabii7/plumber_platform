# 🗺️ Nearby Cities Feature for Plumber Coverage

## Overview

The plumber coverage system has been enhanced to include a **Nearby Cities** feature that allows plumbers to easily select multiple nearby municipalities within a specified radius when setting up their service areas.

## ✨ Key Features

### 🎯 **Geographic Proximity**
- **20km Radius**: Automatically finds municipalities within 20km of the selected base municipality
- **Distance Calculation**: Uses Haversine formula for accurate geographic distance calculation
- **Real-time Results**: Shows distance in kilometers for each nearby municipality

### 🔧 **User Interface**
- **Dropdown Selection**: Choose a base municipality from a comprehensive list
- **Checkbox Selection**: Select multiple nearby cities with individual checkboxes
- **Bulk Operations**: "Select All" and "Add Selected" buttons for efficient management
- **Visual Feedback**: Shows distance information and selection count

### ⚡ **Performance**
- **Efficient Queries**: Optimized database queries with proper indexing
- **Debounced Search**: Prevents excessive API calls during typing
- **Error Handling**: Graceful fallbacks for network issues

## 🚀 How It Works

### 1. **Select Base Municipality**
```
1. Navigate to Plumber Dashboard → Coverage Areas
2. In the "Nearby Cities" section, select a base municipality
3. System automatically calculates nearby municipalities within 20km
```

### 2. **View Nearby Cities**
```
- Each nearby municipality shows:
  - Municipality name
  - Distance from base (in kilometers)
  - Checkbox for selection
```

### 3. **Bulk Selection**
```
- Use "Select All" to choose all nearby municipalities
- Or manually select specific municipalities
- Click "Add Selected" to add all selected municipalities to coverage
```

## 🛠️ Technical Implementation

### **Backend Components**

#### **Controller Methods**
```php
// Find nearby municipalities
public function nearbyMunicipalities(Request $request)
{
    $municipality = $request->query('municipality');
    $radius = $request->query('radius', 20); // Default 20km
    
    // Get center coordinates
    $center = DB::table('postal_codes')
        ->select('Latitude', 'Longitude')
        ->where('Hoofdgemeente', $municipality)
        ->whereNotNull('Latitude')
        ->whereNotNull('Longitude')
        ->first();
    
    // Calculate nearby municipalities using Haversine formula
    $nearby = DB::table('postal_codes')
        ->select('Hoofdgemeente', 'Latitude', 'Longitude')
        ->selectRaw('(6371 * acos(cos(radians(?)) * cos(radians(Latitude)) * 
        cos(radians(Longitude) - radians(?)) + sin(radians(?)) * 
        sin(radians(Latitude)))) AS distance', 
        [$center->Latitude, $center->Longitude, $center->Latitude])
        ->whereNotNull('Latitude')
        ->whereNotNull('Longitude')
        ->where('Hoofdgemeente', '!=', $municipality)
        ->having('distance', '<=', $radius)
        ->groupBy('Hoofdgemeente', 'Latitude', 'Longitude')
        ->orderBy('distance')
        ->limit(10)
        ->get();
}

// Bulk add multiple municipalities
public function bulkStore(Request $request)
{
    $data = $request->validate([
        'municipalities' => 'required|array',
        'municipalities.*' => 'string|max:255',
    ]);
    
    // Add each municipality with validation
    foreach ($data['municipalities'] as $municipality) {
        // Validate existence and prevent duplicates
        PlumberCoverage::create([
            'plumber_id' => $user->id,
            'hoofdgemeente' => $municipality,
        ]);
    }
}
```

#### **Routes**
```php
// Nearby municipalities search
Route::get('/municipalities/nearby', [PlumberCoverageController::class, 'nearbyMunicipalities'])
    ->name('municipalities.nearby');

// Bulk add municipalities
Route::post('/plumber/coverage/bulk', [PlumberCoverageController::class, 'bulkStore'])
    ->name('plumber.coverage.bulk');
```

### **Frontend Components**

#### **JavaScript Features**
```javascript
// Load nearby municipalities
baseMunicipality.addEventListener('change', async () => {
    const selectedMunicipality = baseMunicipality.value;
    
    const res = await fetch(`/municipalities/nearby?municipality=${encodeURIComponent(selectedMunicipality)}&radius=20`);
    const data = await res.json();
    
    // Display nearby municipalities with checkboxes
    nearbyList.innerHTML = data.map(item => `
        <div class="flex items-center space-x-3 p-2 border rounded hover:bg-gray-50">
            <input type="checkbox" data-municipality="${item.Hoofdgemeente}">
            <label>
                <div class="font-medium">${item.Hoofdgemeente}</div>
                <div class="text-xs text-gray-500">${item.distance.toFixed(1)} km away</div>
            </label>
        </div>
    `).join('');
});

// Bulk add selected municipalities
addSelectedBtn.addEventListener('click', async () => {
    const response = await fetch('/plumber/coverage/bulk', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            municipalities: Array.from(selectedNearbyMunicipalities)
        })
    });
});
```

## 📊 Database Schema

### **Required Tables**
```sql
-- postal_codes table (existing)
CREATE TABLE postal_codes (
    id BIGINT PRIMARY KEY,
    Postcode VARCHAR(10),
    Plaatsnaam_NL VARCHAR(255),
    Hoofdgemeente VARCHAR(255),
    Latitude DECIMAL(10,8),
    Longitude DECIMAL(11,8),
    -- ... other fields
);

-- plumber_coverages table (existing)
CREATE TABLE plumber_coverages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    plumber_id BIGINT,
    hoofdgemeente VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 🎯 Use Cases

### **Scenario 1: New Plumber Setup**
```
1. Plumber selects "AALST" as base municipality
2. System shows nearby cities: AFFLIGEM (5.3km), DENDERLEEUW (6.6km), etc.
3. Plumber selects all nearby cities with "Select All"
4. Clicks "Add Selected" to add all to coverage
5. Now covers multiple municipalities efficiently
```

### **Scenario 2: Expanding Coverage**
```
1. Plumber already covers "BRUSSEL"
2. Wants to expand to nearby areas
3. Selects "BRUSSEL" in nearby cities section
4. Sees options like SCHAARBEEK, ANDERLECHT, etc.
5. Selects specific municipalities based on business needs
```

### **Scenario 3: Strategic Coverage**
```
1. Plumber analyzes nearby municipalities
2. Reviews distances and potential customer base
3. Selects only high-value nearby areas
4. Optimizes coverage for maximum efficiency
```

## 🔧 Configuration Options

### **Radius Settings**
```php
// Default radius (20km)
$radius = $request->query('radius', 20);

// Can be customized per request
// Example: /municipalities/nearby?municipality=AALST&radius=30
```

### **Result Limits**
```php
// Limit results to prevent UI overload
->limit(10)  // Show max 10 nearby municipalities
```

## 🚀 Benefits

### **For Plumbers**
- **Efficient Setup**: Quickly add multiple nearby areas
- **Strategic Planning**: See distances for business decisions
- **Time Saving**: Bulk operations instead of individual additions
- **Better Coverage**: Don't miss nearby opportunities

### **For Customers**
- **More Options**: Plumbers cover larger areas
- **Better Matching**: More plumbers available in each area
- **Faster Service**: Plumbers closer to customer locations

### **For Platform**
- **Improved UX**: Intuitive interface for coverage management
- **Better Data**: More comprehensive coverage data
- **Scalability**: Efficient bulk operations

## 🔍 Testing

### **Manual Testing**
```
1. Login as plumber
2. Navigate to Coverage Areas
3. Select a municipality in "Nearby Cities"
4. Verify nearby municipalities appear with distances
5. Test "Select All" functionality
6. Test "Add Selected" functionality
7. Verify municipalities are added to coverage list
```

### **API Testing**
```bash
# Test nearby municipalities endpoint
curl -X GET "http://localhost:8001/municipalities/nearby?municipality=AALST&radius=20"

# Test bulk add endpoint
curl -X POST "http://localhost:8001/plumber/coverage/bulk" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: [token]" \
  -d '{"municipalities":["AFFLIGEM","DENDERLEEUW"]}'
```

## 🎉 Success Metrics

- **Coverage Expansion**: Plumbers adding more municipalities
- **User Engagement**: Increased usage of coverage management
- **Customer Satisfaction**: Better plumber availability in areas
- **Platform Efficiency**: Faster coverage setup process

---

**The Nearby Cities feature transforms how plumbers manage their coverage areas, making it easy to expand service territories and reach more customers efficiently! 🗺️✨**
