// Author: Student 3
// Frontend integration for the Current Weather Module.
// Exposes loadCurrentWeather(cityId) which fetches from the backend
// and renders a weather card into #weather-card-container.
// Expects an element <div id="weather-card-container"></div> on the dashboard page.

const WEATHER_API_BASE = '/api/weather';

async function loadCurrentWeather(cityId) {
    const container = document.getElementById('weather-card-container');
    if (!container) {
        console.error('weather-card-container element not found');
        return;
    }

    renderLoadingState(container);

    try {
        const response = await fetch(`${WEATHER_API_BASE}?city_id=${encodeURIComponent(cityId)}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`,
            },
        });

        if (!response.ok) {
            const errorBody = await response.json().catch(() => ({}));
            throw new Error(errorBody.error || `Request failed with status ${response.status}`);
        }

        const data = await response.json();
        renderWeatherCard(container, data);
    } catch (err) {
        renderErrorState(container, err.message);
    }
}

async function refreshCurrentWeather(cityId) {
    const container = document.getElementById('weather-card-container');
    renderLoadingState(container);

    try {
        const response = await fetch(`${WEATHER_API_BASE}/current`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`,
            },
            body: JSON.stringify({ city_id: cityId }),
        });

        if (!response.ok) {
            const errorBody = await response.json().catch(() => ({}));
            throw new Error(errorBody.error || `Request failed with status ${response.status}`);
        }

        const data = await response.json();
        renderWeatherCard(container, data);
    } catch (err) {
        renderErrorState(container, err.message);
    }
}

function renderWeatherCard(container, data) {
    container.innerHTML = `
        <div class="weather-card">
            <div class="weather-card__header">
                <h3>${escapeHtml(data.city_name || 'Selected city')}</h3>
                <button class="weather-card__refresh" onclick="refreshCurrentWeather(${data.city_id})">⟳ Refresh</button>
            </div>
            <div class="weather-card__body">
                <div class="weather-card__metric">
                    <span class="weather-card__value">${formatNumber(data.temperature)}°C</span>
                    <span class="weather-card__label">Temperature</span>
                </div>
                <div class="weather-card__metric">
                    <span class="weather-card__value">${formatNumber(data.humidity)}%</span>
                    <span class="weather-card__label">Humidity</span>
                </div>
                <div class="weather-card__metric">
                    <span class="weather-card__value">${formatNumber(data.wind_speed)} km/h</span>
                    <span class="weather-card__label">Wind speed</span>
                </div>
            </div>
            <div class="weather-card__footer">
                Last updated: ${formatTimestamp(data.recorded_at)}
            </div>
        </div>
    `;
}

function renderLoadingState(container) {
    container.innerHTML = `<div class="weather-card weather-card--loading">Loading current weather...</div>`;
}

function renderErrorState(container, message) {
    container.innerHTML = `
        <div class="weather-card weather-card--error">
            <p>Unable to load weather data.</p>
            <p class="weather-card__error-detail">${escapeHtml(message)}</p>
        </div>
    `;
}

function formatNumber(value) {
    return Number(value).toFixed(1);
}

function formatTimestamp(isoString) {
    if (!isoString) return 'N/A';
    const date = new Date(isoString.replace(' ', 'T'));
    return date.toLocaleString();
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Example wiring: call this when the city selector (Student 2/5's component)
// fires a "city-selected" event.
document.addEventListener('city-selected', (event) => {
    loadCurrentWeather(event.detail.cityId);
});
