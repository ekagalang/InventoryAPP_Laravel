@props(['action', 'method' => 'GET', 'fields' => [], 'currentFilters' => []])

<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-funnel"></i> Advanced Search & Filter
            </h6>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearchCollapse" aria-expanded="false">
                <i class="bi bi-chevron-down"></i> Toggle
            </button>
        </div>
    </div>
    <div class="collapse" id="advancedSearchCollapse">
        <div class="card-body">
            <form action="{{ $action }}" method="{{ $method }}" class="row g-3">
                @foreach($fields as $field)
                    <div class="col-md-{{ $field['width'] ?? 3 }}">
                        <label for="{{ $field['name'] }}" class="form-label">{{ $field['label'] }}</label>
                        
                        @if($field['type'] === 'select')
                            <select class="form-select form-select-sm" id="{{ $field['name'] }}" name="{{ $field['name'] }}">
                                <option value="">{{ $field['placeholder'] ?? 'Semua' }}</option>
                                @foreach($field['options'] as $value => $text)
                                    <option value="{{ $value }}" {{ ($currentFilters[$field['name']] ?? '') == $value ? 'selected' : '' }}>
                                        {{ $text }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'date')
                            <input type="date" class="form-control form-control-sm" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ $currentFilters[$field['name']] ?? '' }}">
                        @elseif($field['type'] === 'number')
                            <input type="number" class="form-control form-control-sm" id="{{ $field['name'] }}" name="{{ $field['name'] }}" placeholder="{{ $field['placeholder'] ?? '' }}" value="{{ $currentFilters[$field['name']] ?? '' }}" min="{{ $field['min'] ?? '' }}" max="{{ $field['max'] ?? '' }}">
                        @elseif($field['type'] === 'search')
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="{{ $field['name'] }}" name="{{ $field['name'] }}" placeholder="{{ $field['placeholder'] ?? 'Search...' }}" value="{{ $currentFilters[$field['name']] ?? '' }}">
                                <button class="btn btn-outline-secondary" type="button" onclick="clearField('{{ $field['name'] }}')">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        @else
                            <input type="{{ $field['type'] }}" class="form-control form-control-sm" id="{{ $field['name'] }}" name="{{ $field['name'] }}" placeholder="{{ $field['placeholder'] ?? '' }}" value="{{ $currentFilters[$field['name']] ?? '' }}">
                        @endif
                    </div>
                @endforeach
                
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <a href="{{ $action }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                        <button type="button" class="btn btn-info btn-sm" onclick="saveSearch()">
                            <i class="bi bi-bookmark"></i> Save Search
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Saved Searches -->
            <div class="mt-3" id="savedSearches" style="display: none;">
                <h6>Saved Searches:</h6>
                <div id="savedSearchList"></div>
            </div>
        </div>
    </div>
</div>

<script>
function clearField(fieldName) {
    document.getElementById(fieldName).value = '';
}

function saveSearch() {
    const form = document.querySelector('#advancedSearchCollapse form');
    const formData = new FormData(form);
    const searchData = {};
    let searchName = '';
    
    for (let [key, value] of formData.entries()) {
        if (value) {
            searchData[key] = value;
            searchName += value + ' ';
        }
    }
    
    if (Object.keys(searchData).length === 0) {
        alert('Please enter some search criteria first');
        return;
    }
    
    const searchTitle = prompt('Enter name for this search:', searchName.trim().substring(0, 30));
    if (searchTitle) {
        let savedSearches = JSON.parse(localStorage.getItem('savedSearches') || '{}');
        const currentUrl = window.location.pathname;
        
        if (!savedSearches[currentUrl]) {
            savedSearches[currentUrl] = [];
        }
        
        savedSearches[currentUrl].push({
            name: searchTitle,
            data: searchData,
            created: new Date().toISOString()
        });
        
        localStorage.setItem('savedSearches', JSON.stringify(savedSearches));
        loadSavedSearches();
        alert('Search saved successfully!');
    }
}

function loadSavedSearches() {
    const savedSearches = JSON.parse(localStorage.getItem('savedSearches') || '{}');
    const currentUrl = window.location.pathname;
    const searches = savedSearches[currentUrl] || [];
    
    if (searches.length > 0) {
        document.getElementById('savedSearches').style.display = 'block';
        const searchList = document.getElementById('savedSearchList');
        searchList.innerHTML = searches.map(search => `
            <button type="button" class="btn btn-outline-info btn-sm me-2 mb-2" onclick="applySavedSearch('${encodeURIComponent(JSON.stringify(search.data))}')">
                ${search.name}
                <button type="button" class="btn-close btn-close-sm ms-1" onclick="deleteSavedSearch('${search.name}')" style="font-size: 0.6em;"></button>
            </button>
        `).join('');
    }
}

function applySavedSearch(searchDataEncoded) {
    const searchData = JSON.parse(decodeURIComponent(searchDataEncoded));
    const form = document.querySelector('#advancedSearchCollapse form');
    
    // Clear all fields first
    form.querySelectorAll('input, select').forEach(field => {
        field.value = '';
    });
    
    // Apply saved values
    Object.entries(searchData).forEach(([key, value]) => {
        const field = document.getElementById(key);
        if (field) {
            field.value = value;
        }
    });
    
    form.submit();
}

function deleteSavedSearch(searchName) {
    if (confirm('Delete this saved search?')) {
        let savedSearches = JSON.parse(localStorage.getItem('savedSearches') || '{}');
        const currentUrl = window.location.pathname;
        
        if (savedSearches[currentUrl]) {
            savedSearches[currentUrl] = savedSearches[currentUrl].filter(search => search.name !== searchName);
            localStorage.setItem('savedSearches', JSON.stringify(savedSearches));
            loadSavedSearches();
        }
    }
}

// Load saved searches on page load
document.addEventListener('DOMContentLoaded', loadSavedSearches);
</script>