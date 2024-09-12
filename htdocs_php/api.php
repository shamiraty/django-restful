<?php
// MySQL connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "api_data";

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// API URL and Key for documents and details
$document_api_url = "http://127.0.0.1:8000/api/documents/";
$details_api_url = "http://127.0.0.1:8000/api/document-details/";
$api_key = "vlWnzo3b.LzCsbJisS4JxZvFA9IqM5Z0udtSgjnpq";

// Function to fetch API data
function fetch_api_data($url, $api_key) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Authorization: Api-Key $api_key"
    ]);

    $response = curl_exec($curl);
    if ($response === false) {
        die("Error fetching data: " . curl_error($curl));
    }

    curl_close($curl);
    return json_decode($response, true);
}

// Fetch documents
$documents = fetch_api_data($document_api_url, $api_key);

// Fetch document details
$details = fetch_api_data($details_api_url, $api_key);

if (is_array($documents)) {
    foreach ($documents as $document) {
        // Insert or update documents in the database
        $stmt = $conn->prepare("INSERT INTO documents (id, title, description, created_at, image_url, file_url, uploaded_by)
                                VALUES (?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE
                                title=VALUES(title), description=VALUES(description), created_at=VALUES(created_at),
                                image_url=VALUES(image_url), file_url=VALUES(file_url), uploaded_by=VALUES(uploaded_by)");

        $stmt->bind_param("issssss",
            $document['id'],
            $document['title'],
            $document['description'],
            $document['created_at'],
            $document['image'],
            $document['file'],
            $document['uploaded_by']
        );

        if ($stmt->execute()) {
            echo "Document ID " . $document['id'] . " saved successfully.\n";
        } else {
            echo "Error saving document ID " . $document['id'] . ": " . $stmt->error . "\n";
        }

        $stmt->close();
    }
} else {
    echo "No data received from document API.\n";
}

// Now insert document details
if (is_array($details)) {
    foreach ($details as $detail) {
        // Insert or update document details in the database
        $stmt = $conn->prepare("INSERT INTO document_details (document_id, additional_info, reference_number, status, category, tags, remarks)
                                VALUES (?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE
                                additional_info=VALUES(additional_info), reference_number=VALUES(reference_number),
                                status=VALUES(status), category=VALUES(category), tags=VALUES(tags), remarks=VALUES(remarks)");

        $stmt->bind_param("issssss",
            $detail['document']['id'], // Use the document ID to link
            $detail['additional_info'],
            $detail['reference_number'],
            $detail['status'],
            $detail['category'],
            $detail['tags'],
            $detail['remarks']
        );

        if ($stmt->execute()) {
            echo "Detail for Document ID " . $detail['document']['id'] . " saved successfully.\n";
        } else {
            echo "Error saving detail for Document ID " . $detail['document']['id'] . ": " . $stmt->error . "\n";
        }

        $stmt->close();
    }
} else {
    echo "No data received from details API.\n";
}

// Close the MySQL connection
$conn->close();
?>
