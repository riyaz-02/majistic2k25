<?php 
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb+srv://majisticcoreadmin:IvkY2pYZBIwAbEZ5@majisticjisce.fxomy.mongodb.net/?retryWrites=true&w=majority&appName=majisticjisce");
    $db = $client->selectDatabase('majistic2k25');
    
    // Set common collections
    $registrations = $db->registrations;
    $alumni_registrations = $db->alumni_registrations;
    
    // Set timezone
    date_default_timezone_set('Asia/Kolkata');
} catch (Exception $e) {
    die("MongoDB connection failed: " . $e->getMessage());
}

/**
 * Helper function to generate a unique ID for MongoDB documents
 * @return string A unique identifier
 */
function generateUniqueId() {
    return (string) new MongoDB\BSON\ObjectId();
}

/**
 * Helper function to format MongoDB document for output
 * @param array $document The MongoDB document
 * @return array Formatted document
 */
function formatDocument($document) {
    if (isset($document['_id']) && $document['_id'] instanceof MongoDB\BSON\ObjectId) {
        $document['_id'] = (string) $document['_id'];
    }
    return $document;
}

/**
 * Helper function to count documents in a collection with a filter
 * @param MongoDB\Collection $collection The MongoDB collection
 * @param array $filter Filter criteria
 * @return int Count of matching documents
 */
function countDocuments($collection, $filter = []) {
    return $collection->countDocuments($filter);
}

/**
 * Helper function to paginate MongoDB results
 * @param MongoDB\Collection $collection The MongoDB collection
 * @param array $filter Filter criteria
 * @param array $options Options for sorting, limit, etc.
 * @return array Array of documents
 */
function paginateResults($collection, $filter = [], $options = []) {
    $cursor = $collection->find($filter, $options);
    $results = [];
    foreach ($cursor as $document) {
        $results[] = formatDocument((array)$document);
    }
    return $results;
}

/**
 * Helper function to find a single document
 * @param MongoDB\Collection $collection The MongoDB collection
 * @param array $filter Filter criteria
 * @return array|null The document or null if not found
 */
function findDocument($collection, $filter = []) {
    $document = $collection->findOne($filter);
    if ($document) {
        return formatDocument((array)$document);
    }
    return null;
}
?>
