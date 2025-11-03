const axios = require('axios');

class LaravelApiService {
    constructor(baseUrl, apiToken) {
        this.baseUrl = baseUrl;
        this.apiToken = apiToken;
    }

    async send(endpoint, data) {
        try {
            const url = endpoint.startsWith('http')
                ? endpoint
                : `${this.baseUrl}${endpoint}`;

            return await axios.post(url, data, {
                headers: {
                    Authorization: `Bearer ${this.apiToken}`,
                    'Content-Type': 'application/json'
                },
            });
        } catch (err) {
            console.error(`Error sending to Laravel ${endpoint}:`, err.response?.data || err.message);
        }
    }
}

module.exports = LaravelApiService;
