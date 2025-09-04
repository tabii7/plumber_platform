@extends('layouts.modern-dashboard')

@section('title', 'Coverage Areas')

@section('page-title', 'Coverage Areas')

@section('sidebar-nav')
    <div class="nav-item">
        <a href="{{ route('plumber.dashboard') }}" class="nav-link">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('plumber.coverage.index') }}" class="nav-link active">
            <i class="fas fa-map-marker-alt"></i>
            <span>Coverage Areas</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('plumber.schedule.index') }}" class="nav-link">
            <i class="fas fa-clock"></i>
            <span>Schedule</span>
        </a>
    </div>
    

    
    <div class="nav-item">
        <a href="{{ route('support') }}" class="nav-link">
            <i class="fas fa-headset"></i>
            <span>Support</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('profile.edit') }}" class="nav-link">
            <i class="fas fa-user-cog"></i>
            <span>Profile</span>
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Nearby cities section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    Nearby Cities
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Expand municipalities to see nearby cities within selected radius.</p>

                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <div>
                            <strong>Your registered city:</strong> {{ Auth::user()->city }}
                        </div>
                    </div>
                </div>
                
                <div class="row align-items-center mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <label class="form-label me-2 mb-0">Radius:</label>
                            <select id="radius-selector" class="form-select form-select-sm" style="width: auto;">
                                <option value="10">10 km</option>
                                <option value="20" selected>20 km</option>
                                <option value="30">30 km</option>
                                <option value="50">50 km</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button id="refresh-nearby" class="btn btn-primary btn-sm">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh
                        </button>
                    </div>
                </div>

                <div id="nearby-section">
                    <h6 class="mb-2">Nearby Municipalities</h6>
                    <p class="text-muted small mb-3">
                        <span id="nearby-description">Click to expand municipalities and select nearby cities:</span>
                        <span id="auto-load-message" class="d-none text-primary fw-medium">Showing nearby municipalities based on your registered city!</span>
                    </p>
                    
                    <div id="nearby-tree" class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                        <div class="text-muted text-center py-4">Loading nearby cities...</div>
                    </div>
                    
                    <div class="mt-3 d-flex gap-2">
                        <button id="select-all-nearby" class="btn btn-primary btn-sm">
                            Select All
                        </button>
                        <button id="add-selected-nearby" class="btn btn-success btn-sm">
                            Add Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current coverages -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Your Municipalities
                </h5>
            </div>
            <div class="card-body">
                @if ($coverages->isEmpty())
                    <p class="text-muted">You have not added any municipalities yet.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($coverages as $cov)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    @if ($cov->coverage_type === 'municipality')
                                        <div class="fw-medium">{{ $cov->hoofdgemeente }}</div>
                                        <div class="text-muted small">
                                            {{ $counts[$cov->hoofdgemeente] ?? 0 }} towns covered (entire municipality)
                                        </div>
                                    @else
                                        <div class="fw-medium">{{ $cov->hoofdgemeente }} - {{ $cov->city }}</div>
                                        <div class="text-muted small">
                                            1 city covered (specific city only)
                                        </div>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('plumber.coverage.destroy', $cov->id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>

const nearbySection = document.getElementById('nearby-section');
const nearbyTree = document.getElementById('nearby-tree');
const selectAllBtn = document.getElementById('select-all-nearby');
const addSelectedBtn = document.getElementById('add-selected-nearby');
const radiusSelector = document.getElementById('radius-selector');
const refreshBtn = document.getElementById('refresh-nearby');

let selectedNearbyMunicipalities = new Set();



// Select all functionality
selectAllBtn.addEventListener('click', () => {
    const checkboxes = nearbyTree.querySelectorAll('input[type="checkbox"]:not(:disabled)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        selectedNearbyMunicipalities.add(checkbox.dataset.municipality);
    });
    updateAddButton();
});

// Add selected municipalities
addSelectedBtn.addEventListener('click', async () => {
    if (selectedNearbyMunicipalities.size === 0) {
        alert('Please select at least one municipality.');
        return;
    }

    try {
        addSelectedBtn.disabled = true;
        addSelectedBtn.textContent = 'Adding...';

        const response = await fetch('{{ route("plumber.coverage.bulk") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                municipalities: Array.from(selectedNearbyMunicipalities)
            })
        });

        const result = await response.json();

        if (result.success) {
            // Show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'alert alert-success alert-dismissible fade show';
            successDiv.innerHTML = `
                ${result.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Insert at the top of the current coverages section
            const coveragesSection = document.querySelector('.card:last-child .card-body');
            if (coveragesSection) {
                coveragesSection.insertBefore(successDiv, coveragesSection.firstChild);
            }

            // Remove success message after 5 seconds
            setTimeout(() => {
                successDiv.remove();
            }, 5000);

            // Clear selections
            selectedNearbyMunicipalities.clear();
            nearbyTree.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            updateAddButton();

            // Reload the page to show updated coverages
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('Error: ' + (result.message || 'Failed to add municipalities'));
        }
    } catch (error) {
        console.error('Error adding municipalities:', error);
        alert('Error adding municipalities. Please try again.');
    } finally {
        addSelectedBtn.disabled = false;
        addSelectedBtn.textContent = 'Add Selected';
    }
});

function updateAddButton() {
    addSelectedBtn.textContent = `Add Selected (${selectedNearbyMunicipalities.size})`;
    addSelectedBtn.disabled = selectedNearbyMunicipalities.size === 0;
}



// Auto-populate nearby cities when page loads based on user's registered city
document.addEventListener('DOMContentLoaded', async () => {
    // Get user's registered city
    const userCity = '{{ Auth::user()->city }}';
    
    if (userCity) {
        // Show auto-load message
        document.getElementById('nearby-description').classList.add('d-none');
        document.getElementById('auto-load-message').classList.remove('d-none');
        
        // Load nearby municipalities based on user's registered city
        await loadNearbyMunicipalitiesFromCity(userCity, parseInt(radiusSelector.value));
    } else {
        // No city registered, show empty state
        nearbyTree.innerHTML = '<div class="text-muted text-center py-4">No city registered. Please update your profile first.</div>';
    }
});

// Radius selector change event
radiusSelector.addEventListener('change', async () => {
    const userCity = '{{ Auth::user()->city }}';
    
    if (userCity) {
        nearbyTree.innerHTML = '<div class="text-muted text-center py-4">Loading nearby cities...</div>';
        await loadNearbyMunicipalitiesFromCity(userCity, parseInt(radiusSelector.value));
    }
});

// Refresh button click event
refreshBtn.addEventListener('click', async () => {
    const userCity = '{{ Auth::user()->city }}';
    
    if (userCity) {
        nearbyTree.innerHTML = '<div class="text-muted text-center py-4">Loading nearby cities...</div>';
        await loadNearbyMunicipalitiesFromCity(userCity, parseInt(radiusSelector.value));
    }
});

// Function to load nearby municipalities from user's registered city
async function loadNearbyMunicipalitiesFromCity(city, radius = 20) {
    try {
        // First, find the municipality (Hoofdgemeente) for the user's city
        const municipalityRes = await fetch(`{{ route('municipalities.search') }}?term=${encodeURIComponent(city)}`);
        const municipalities = await municipalityRes.json();
        
        if (municipalities.length === 0) {
            nearbyTree.innerHTML = '<div class="text-muted text-center py-4">Could not find municipality for your city: ' + city + '</div>';
            return;
        }
        
        // Use the first matching municipality as the base
        const baseMunicipality = municipalities[0];
        
        // Now find nearby municipalities from this base
        const nearbyRes = await fetch(`{{ route('municipalities.nearby') }}?municipality=${encodeURIComponent(baseMunicipality)}&radius=${radius}`);
        const nearbyData = await nearbyRes.json();
        
        if (nearbyData.length === 0) {
            nearbyTree.innerHTML = '<div class="text-muted text-center py-4">No nearby municipalities found within ' + radius + 'km of ' + city + '</div>';
            return;
        }
        
        // Display hierarchical tree
        displayNearbyTree(nearbyData.map(item => ({
            municipality: item.Hoofdgemeente,
            distance: item.distance,
            sources: [baseMunicipality]
        })));
        
    } catch (error) {
        console.error('Error loading nearby municipalities from city:', error);
        nearbyTree.innerHTML = '<div class="text-danger text-center py-4">Error loading nearby municipalities</div>';
    }
}

// Function to load nearby cities for all municipalities
async function loadAllNearbyCities(municipalities, radius = 20) {
    try {
        const municipalityData = new Map(); // Group by municipality
        
        // Load nearby cities for each municipality
        for (const municipality of municipalities) {
            try {
                const res = await fetch(`{{ route('municipalities.nearby') }}?municipality=${encodeURIComponent(municipality)}&radius=${radius}`);
                const data = await res.json();
                
                // Group nearby cities by municipality
                data.forEach(item => {
                    if (!municipalityData.has(item.Hoofdgemeente)) {
                        municipalityData.set(item.Hoofdgemeente, {
                            municipality: item.Hoofdgemeente,
                            distance: item.distance,
                            sources: [municipality],
                            cities: []
                        });
                    } else {
                        // If already exists, add this municipality as another source
                        municipalityData.get(item.Hoofdgemeente).sources.push(municipality);
                    }
                });
            } catch (error) {
                console.error(`Error loading nearby cities for ${municipality}:`, error);
            }
        }
        
        // Display hierarchical tree
        displayNearbyTree(Array.from(municipalityData.values()));
        
    } catch (error) {
        console.error('Error loading nearby cities:', error);
        nearbyTree.innerHTML = '<div class="text-danger text-center py-4">Error loading nearby cities</div>';
    }
}

// Function to display nearby cities in tree structure
function displayNearbyTree(municipalityData) {
    if (municipalityData.length === 0) {
        nearbyTree.innerHTML = '<div class="text-muted text-center py-4">No nearby municipalities found within selected radius</div>';
        return;
    }
    
    // Get list of already covered municipalities
    const coverageItems = document.querySelectorAll('.list-group-item');
    const existingMunicipalities = [];
    coverageItems.forEach(item => {
        const municipalityName = item.querySelector('.fw-medium').textContent;
        existingMunicipalities.push(municipalityName);
    });
    
    selectedNearbyMunicipalities.clear();
    
    // Sort by distance
    municipalityData.sort((a, b) => a.distance - b.distance);
    
    nearbyTree.innerHTML = municipalityData.map(item => {
        const isAlreadyCovered = existingMunicipalities.includes(item.municipality);
        const checkboxDisabled = isAlreadyCovered ? 'disabled' : '';
        const checkboxClass = isAlreadyCovered ? 'form-check-input text-muted' : 'form-check-input text-primary';
        const itemClass = isAlreadyCovered ? 'bg-light' : '';
        const statusText = isAlreadyCovered ? ' (Already covered)' : '';
        const sourcesText = item.sources.length > 1 ? ` (Near ${item.sources.join(', ')})` : ` (Near ${item.sources[0]})`;
        
        return `
            <div class="municipality-item border rounded ${itemClass}">
                <div class="d-flex align-items-center p-2">
                    <div class="expand-area d-flex align-items-center me-2" data-municipality="${item.municipality}" style="cursor: pointer; padding: 4px;">
                        <i class="fas fa-plus expand-icon text-muted" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
                    </div>
                    <input type="checkbox" id="municipality-${item.municipality}" 
                           class="${checkboxClass} me-2"
                           data-municipality="${item.municipality}"
                           data-type="municipality"
                           ${checkboxDisabled}>
                    <label for="municipality-${item.municipality}" class="flex-fill small" style="cursor: pointer;">
                        <div class="fw-medium">${item.municipality} (${item.distance.toFixed(1)}km)${statusText}</div>
                        <div class="text-muted small">${sourcesText}</div>
                    </label>
                </div>
                <div class="cities-container d-none ps-5 pe-2 pb-2">
                    <div class="text-muted small py-2">Loading cities...</div>
                </div>
            </div>
        `;
    }).join('');
    
    // Use event delegation for expand area clicks
    nearbyTree.addEventListener('click', async (e) => {
        const expandArea = e.target.closest('.expand-area');
        if (!expandArea) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Expand area clicked'); // Debug log
        
        const municipality = expandArea.dataset.municipality;
        const municipalityItem = expandArea.closest('.municipality-item');
        const citiesContainer = municipalityItem.querySelector('.cities-container');
        const expandIcon = expandArea.querySelector('.expand-icon');
        
        console.log('Municipality:', municipality); // Debug log
        console.log('Cities container:', citiesContainer); // Debug log
        
        if (citiesContainer && citiesContainer.classList.contains('d-none')) {
            // Expand
            console.log('Expanding...'); // Debug log
            citiesContainer.classList.remove('d-none');
            expandIcon.className = 'fas fa-minus expand-icon text-muted';
            expandIcon.style.transform = 'rotate(0deg)';
            
            // Load cities for this municipality
            await loadCitiesForMunicipality(municipality, citiesContainer);
        } else if (citiesContainer) {
            // Collapse
            console.log('Collapsing...'); // Debug log
            citiesContainer.classList.add('d-none');
            expandIcon.className = 'fas fa-plus expand-icon text-muted';
            expandIcon.style.transform = 'rotate(0deg)';
        }
    });
    
    // Wire checkbox events
    nearbyTree.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const municipality = checkbox.dataset.municipality;
            const type = checkbox.dataset.type;
            
            if (checkbox.checked && !checkbox.disabled) {
                if (type === 'municipality') {
                    // Select municipality and all its cities
                    selectedNearbyMunicipalities.add(municipality);
                    // Also select all cities in this municipality
                    const citiesContainer = checkbox.closest('.municipality-item').querySelector('.cities-container');
                    citiesContainer.querySelectorAll('input[type="checkbox"]').forEach(cityCheckbox => {
                        if (!cityCheckbox.disabled) {
                            cityCheckbox.checked = true;
                            selectedNearbyMunicipalities.add(cityCheckbox.dataset.municipality);
                        }
                    });
                } else {
                    selectedNearbyMunicipalities.add(municipality);
                }
            } else {
                if (type === 'municipality') {
                    // Deselect municipality and all its cities
                    selectedNearbyMunicipalities.delete(municipality);
                    const citiesContainer = checkbox.closest('.municipality-item').querySelector('.cities-container');
                    citiesContainer.querySelectorAll('input[type="checkbox"]').forEach(cityCheckbox => {
                        cityCheckbox.checked = false;
                        selectedNearbyMunicipalities.delete(cityCheckbox.dataset.municipality);
                    });
                } else {
                    selectedNearbyMunicipalities.delete(municipality);
                }
            }
            updateAddButton();
        });
    });
    
    updateAddButton();
}

// Function to load cities for a specific municipality
async function loadCitiesForMunicipality(municipality, container) {
    try {
        const res = await fetch(`{{ url('/municipalities') }}/${encodeURIComponent(municipality)}/towns`);
        const cities = await res.json();
        
        if (cities.length === 0) {
            container.innerHTML = '<div class="text-muted small py-2">No cities found</div>';
            return;
        }
        
        // Get list of already covered municipalities
        const coverageItems = document.querySelectorAll('.list-group-item');
        const existingMunicipalities = [];
        coverageItems.forEach(item => {
            const municipalityName = item.querySelector('.fw-medium').textContent;
            existingMunicipalities.push(municipalityName);
        });
        
        container.innerHTML = cities.map(city => {
            const isAlreadyCovered = existingMunicipalities.includes(city.Plaatsnaam_NL);
            const checkboxDisabled = isAlreadyCovered ? 'disabled' : '';
            const checkboxClass = isAlreadyCovered ? 'form-check-input text-muted' : 'form-check-input text-primary';
            const itemClass = isAlreadyCovered ? 'bg-light' : '';
            const statusText = isAlreadyCovered ? ' (Already covered)' : '';
            
            return `
                <div class="d-flex align-items-center p-2 border-start border-2 ${itemClass}">
                    <input type="checkbox" id="city-${city.Plaatsnaam_NL}" 
                           class="${checkboxClass} me-2"
                           data-municipality="${city.Plaatsnaam_NL}"
                           data-type="city"
                           ${checkboxDisabled}>
                    <label for="city-${city.Plaatsnaam_NL}" class="flex-fill small" style="cursor: pointer;">
                        <div class="fw-medium">➖ ${city.Plaatsnaam_NL} (${city.Postcode})${statusText}</div>
                    </label>
                </div>
            `;
        }).join('');
        
        // Wire city checkbox events
        container.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const cityMunicipality = checkbox.dataset.municipality;
                
                if (checkbox.checked && !checkbox.disabled) {
                    selectedNearbyMunicipalities.add(cityMunicipality);
                } else {
                    selectedNearbyMunicipalities.delete(cityMunicipality);
                }
                updateAddButton();
            });
        });
        
    } catch (error) {
        console.error(`Error loading cities for ${municipality}:`, error);
        container.innerHTML = '<div class="text-danger small py-2">Error loading cities</div>';
    }
}
</script>
@endsection
