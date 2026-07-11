// Author: Sidi Mohamed Ebnou Oumar

const searchForm = document.getElementById('search-form');
const queryInput = document.getElementById('query');
const resultsList = document.getElementById('results');
const statusMessage = document.getElementById('status-message');
const citiesTableBody = document.querySelector('#cities-table tbody');

function showStatus(message, isSuccess = false) {
    statusMessage.textContent = message;
    statusMessage.hidden = false;
    statusMessage.classList.toggle('success', isSuccess);
}

function hideStatus() {
    statusMessage.hidden = true;
}

// Integration contract with the other dashboard modules (e.g. weather-card.js):
// they listen for this event to know which city to load data for.
function dispatchCitySelected(city) {
    document.dispatchEvent(new CustomEvent('city-selected', {
        detail: { cityId: city.id, city },
    }));
}

async function searchCities(query) {
    const response = await fetch(`/api/cities/search?q=${encodeURIComponent(query)}`);
    const payload = await response.json();
    if (!response.ok) {
        throw new Error(payload.error || 'Search failed');
    }
    return payload.data;
}

async function registerCity(name, countryCode) {
    const response = await fetch('/api/cities', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, country_code: countryCode }),
    });
    const payload = await response.json();
    if (!response.ok) {
        throw new Error(payload.error || 'Registration failed');
    }
    return payload.data;
}

async function loadCities() {
    const response = await fetch('/api/cities');
    const payload = await response.json();
    citiesTableBody.innerHTML = '';

    for (const city of payload.data || []) {
        const row = document.createElement('tr');
        row.classList.add('city-row');
        row.title = 'Click to select this city';
        row.innerHTML = `
            <td>${city.name}</td>
            <td>${city.country}</td>
            <td>${city.latitude}</td>
            <td>${city.longitude}</td>
            <td>${city.population ?? '—'}</td>
            <td>${city.created_at}</td>
        `;
        row.addEventListener('click', () => dispatchCitySelected(city));
        citiesTableBody.appendChild(row);
    }
}

function renderResults(candidates) {
    resultsList.innerHTML = '';

    if (candidates.length === 0) {
        const li = document.createElement('li');
        li.textContent = 'No matches found.';
        resultsList.appendChild(li);
        return;
    }

    for (const candidate of candidates) {
        const li = document.createElement('li');

        const label = document.createElement('span');
        label.textContent = `${candidate.name}, ${candidate.country ?? candidate.country_code ?? 'unknown'}`;

        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = 'Register';
        button.addEventListener('click', async () => {
            button.disabled = true;
            try {
                const registeredCity = await registerCity(candidate.name, candidate.country_code);
                showStatus(`"${candidate.name}" registered successfully.`, true);
                await loadCities();
                dispatchCitySelected(registeredCity);
            } catch (error) {
                showStatus(error.message);
            } finally {
                button.disabled = false;
            }
        });

        li.appendChild(label);
        li.appendChild(button);
        resultsList.appendChild(li);
    }
}

searchForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    hideStatus();

    const query = queryInput.value.trim();
    if (query.length < 2) {
        showStatus('Type at least 2 characters.');
        return;
    }

    try {
        const candidates = await searchCities(query);
        renderResults(candidates);
    } catch (error) {
        showStatus(error.message);
    }
});

loadCities();
