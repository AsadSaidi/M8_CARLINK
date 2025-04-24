<?php
class CarApi {
    private $apiUrl;
    private $apiKey;

    public function __construct() {
        $this->apiUrl = API_NINJAS_URL;
        $this->apiKey = API_NINJAS_KEY;
    }

    /**
     * Get cars by make and model
     * 
     * @param string $make Car make
     * @param string $model Car model (optional)
     * @param int $year Car year (optional)
     * @param string $fuel_type Car fuel type (optional)
     * @return array|null Car data or null on failure
     */
    public function getCars($make, $model = '', $year = '', $fuel_type = '') {
        // Build query parameters
        $params = ['make' => $make];
        
        if (!empty($model)) {
            $params['model'] = $model;
        }
        
        if (!empty($year) && is_numeric($year)) {
            $params['year'] = $year;
        }
        
        if (!empty($fuel_type)) {
            $params['fuel_type'] = $fuel_type;
        }
        
        // Make API request
        return $this->makeRequest($params);
    }

    /**
     * Get detailed information for a specific car
     * 
     * @param string $make Car make
     * @param string $model Car model
     * @param int $year Car year (optional)
     * @return array|null Car data or null on failure
     */
    public function getCarDetails($make, $model, $year = '') {
        // Build query parameters
        $params = [
            'make' => $make,
            'model' => $model
        ];
        
        if (!empty($year) && is_numeric($year)) {
            $params['year'] = $year;
        }
        
        // Make API request
        $results = $this->makeRequest($params);
        
        // Return the first result if available
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Get all available car makes
     * 
     * @return array List of car makes
     */
    public function getMakes() {
        // This API doesn't have a specific endpoint for getting all makes
        // So we'll return a predefined list of common car makes
        return [
            'Acura', 'Alfa Romeo', 'Aston Martin', 'Audi', 'Bentley', 'BMW', 'Bugatti',
            'Buick', 'Cadillac', 'Chevrolet', 'Chrysler', 'Citroen', 'Dodge', 'Ferrari',
            'Fiat', 'Ford', 'Genesis', 'GMC', 'Honda', 'Hyundai', 'Infiniti', 'Jaguar',
            'Jeep', 'Kia', 'Lamborghini', 'Land Rover', 'Lexus', 'Lincoln', 'Lotus',
            'Maserati', 'Mazda', 'McLaren', 'Mercedes-Benz', 'Mini', 'Mitsubishi',
            'Nissan', 'Pagani', 'Peugeot', 'Porsche', 'Ram', 'Renault', 'Rolls-Royce',
            'Saab', 'Subaru', 'Tesla', 'Toyota', 'Volkswagen', 'Volvo'
        ];
    }

    /**
     * Get models for a specific make
     * 
     * @param string $make Car make
     * @return array List of car models for the make
     */
    public function getModelsByMake($make) {
        // Make API request
        $cars = $this->makeRequest(['make' => $make]);
        
        // Extract unique models
        $models = [];
        
        if (!empty($cars) && is_array($cars)) {
            foreach ($cars as $car) {
                if (isset($car['model']) && !in_array($car['model'], $models)) {
                    $models[] = $car['model'];
                }
            }
            
            sort($models);
        }
        
        return $models;
    }

    /**
     * Make API request
     * 
     * @param array $params Query parameters
     * @return array|null API response data or null on failure
     */
    private function makeRequest($params) {
        // Build the URL with query parameters
        $url = $this->apiUrl . '?' . http_build_query($params);
        
        // Initialize cURL session
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Api-Key: ' . $this->apiKey
        ]);
        
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Close cURL session
        curl_close($ch);
        
        // Check for errors
        if ($httpCode !== 200 || $response === false) {
            error_log('API request failed: ' . ($response ?: 'No response'));
            return null;
        }
        
        // Decode the JSON response
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            return null;
        }
        
        return $data;
    }

    /**
     * Cache API responses to reduce API calls
     * 
     * @param string $cacheKey Cache key
     * @param callable $fetchCallback Function to fetch data if not cached
     * @param int $cacheDuration Cache duration in seconds
     * @return mixed Cached or fresh data
     */
    public function getCachedData($cacheKey, $fetchCallback, $cacheDuration = 86400) {
        $cacheFile = sys_get_temp_dir() . '/carlink_cache_' . md5($cacheKey) . '.json';
        
        // Check if cache file exists and is still valid
        if (file_exists($cacheFile)) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            
            if (isset($cacheData['expires']) && $cacheData['expires'] > time() && isset($cacheData['data'])) {
                return $cacheData['data'];
            }
        }
        
        // Cache missed or expired, fetch fresh data
        $data = $fetchCallback();
        
        // Save to cache
        if ($data !== null) {
            $cacheData = [
                'expires' => time() + $cacheDuration,
                'data' => $data
            ];
            
            file_put_contents($cacheFile, json_encode($cacheData));
        }
        
        return $data;
    }
}
?>
