# **DJANGO REST API DOCS**
![hh](https://github.com/user-attachments/assets/6db67633-2340-4836-b16b-d5be4cebca3b)
## **AUTOMATED DATA SYNCHRONIZATION AND CLIENT-SIDE DISPLAY OF RECORDS FROM REST API TO CLIENT MYSQL USING OPEN SOURCE TECHNOLOGIES**
### **Efficiently Automating API-Driven Record Management with MySQL and Dynamic Client-Side Data Display**
**Technologies:**  
*Python, HTML, JavaScript, CSS, Bootstrap, jQuery, AJAX, PHP, MySQL*
**Frontend Integration Documentation**
This is the frontend integration documentation: [https://github.com/shamiraty/api-calling-client-side](https://github.com/shamiraty/api-calling-client-side)

---
### **What Will Be Done in This Project?**

1. A Django project will be created.
2. The Django REST Framework will be installed.
3. Two models will be developed.
4. API routing will be configured.
5. An API authentication key will be generated for each connecting client.
6. Data will be added to the models.
7. A client-side application will be built to interact with the API.
8. AJAX will be used to fetch data from the API.
9. The fetched data will be stored in the client's MySQL database.

### **Summary:**

This project combines the power of a Django RESTful API with PHP to automate the fetching, storing, and displaying of document data and its associated details. By utilizing secure API keys, the system fetches document information—such as titles, descriptions, and uploaded files—along with additional metadata like status, reference numbers, and categories, which are seamlessly stored in a MySQL database. The project employs `INSERT ... ON DUPLICATE KEY UPDATE` queries to prevent data duplication and ensure efficient updates in the database.

On the client side, data is dynamically displayed using AJAX, jQuery, HTML, Bootstrap, and CSS. Documents are presented in an interactive and responsive format, allowing users to browse through the information easily. The combination of server-side automation and client-side display delivers a fully integrated, scalable solution for efficient document management, enhancing both data handling and user experience.

### **Security**

1. **API Key Authentication:** API keys will be used to restrict access to the API. Each client will need a valid API key to interact with the API. This prevents unauthorized access.
2. **CORS Configuration:** The API will be configured with CORS headers to allow only specific domains to access it. During development, all origins will be allowed, but in production, it should be restricted to the client’s domain.
3. **HTTPS:** It's recommended to deploy the API over HTTPS in production to encrypt data during transmission.
4. **Access Control:** Only authenticated users with a valid API key will be able to access certain endpoints of the API.
5. **SQL Injection Protection:** Django ORM automatically protects against SQL injection, ensuring that the database remains secure from malicious queries.


### STEP 1
**Install Libraries**
> First, ensure you have Django and Django REST Framework installed, along with djangorestframework-api-key for handling API key authentication.
```bash
pip install django djangorestframework pillow djangorestframework-api-key
pip install django-cors-headers
```

### STEP 2
**Create Django Project and App**
> Create a New Django Project and App and Start a new Django project and app.
```bash
django-admin startproject myproject
cd myproject
python manage.py startapp myapp
```
### STEP 3
**Configure Settings**
> Add rest_framework, myapp, and rest_framework_api_key to your INSTALLED_APPS in myproject/settings.py.

```python
INSTALLED_APPS = [
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
    'rest_framework',
    'rest_framework_api_key',
    'myapp',
    'corsheaders',
]

MEDIA_URL = '/media/'
MEDIA_ROOT = BASE_DIR / 'media'
```

### STEP 3 (B)
**settings.py**
> Also, add corsheaders.middleware.CorsMiddleware to your MIDDLEWARE in settings.py, above CommonMiddleware:
```python
MIDDLEWARE = [
    'corsheaders.middleware.CorsMiddleware',  # Add this
    'django.middleware.common.CommonMiddleware',
    'django.middleware.security.SecurityMiddleware',
    # other middleware...
]
```
### STEP 3 (C)
**settings.py**
> Finally, allow all origins for development (you can restrict this in production):
```python
CORS_ALLOW_ALL_ORIGINS = True
```


### STEP 4
**settings.py**
> Also, configure Django REST Framework authentication in settings.py.
```python
REST_FRAMEWORK = {
    'DEFAULT_AUTHENTICATION_CLASSES': [
        'rest_framework.authentication.SessionAuthentication',  # or any other authentication method
    ],
    'DEFAULT_PERMISSION_CLASSES': [
        'rest_framework_api_key.permissions.HasAPIKey',  # Use HasAPIKey as a permission class, not authentication class
    ]
}

```
### STEP 5
**models.py**
> Create a model fields, including image and file fields in myapp/models.py then make migrations and migrate
```python
from django.db import models
#mother table
class Document(models.Model):
    title = models.CharField(max_length=100)
    description = models.TextField()
    created_at = models.DateTimeField(auto_now_add=True)
    image = models.ImageField(upload_to='images/')
    file = models.FileField(upload_to='files/')
    uploaded_by = models.CharField(max_length=100)
    def __str__(self):
        return self.title

#child table
class DocumentDetails(models.Model):
    document = models.ForeignKey(Document, related_name='details', on_delete=models.CASCADE)
    additional_info = models.CharField(max_length=255)
    reference_number = models.CharField(max_length=100, unique=True)
    status = models.CharField(max_length=50)
    category = models.CharField(max_length=100)
    tags = models.TextField()
    remarks = models.TextField()
    def __str__(self):
        return f"Details for {self.document.title}"

```
- after creating models  run these commands
```python
python manage.py makemigrations
python manage.py migrate
python manage.py createsuperuser
python manage.py runserver
```

### STEP 6
**Create a Serializer**
> Next, create a serializer to convert the model data to JSON in myapp/serializers.py.
```python
from rest_framework import serializers
from .models import *

#for mother table
class DocumentSerializer(serializers.ModelSerializer):
    class Meta:
        model = Document
        fields = ['id', 'title', 'description', 'created_at', 'image', 'file', 'uploaded_by']

# for child table
class DocumentDetailsSerializer(serializers.ModelSerializer):
    document = DocumentSerializer()  # Nested serializer for the related Document
    class Meta:
        model = DocumentDetails
        fields = '__all__'
```


### STEP 7
**Create the API View**
> In myapp/views.py, create the API views using Django REST Framework’s ModelViewSet to handle all CRUD operations.

```python
from rest_framework import viewsets
from rest_framework_api_key.permissions import HasAPIKey
from .models import *
from .serializers import DocumentSerializer,DocumentDetailsSerializer

#for mother table
class DocumentViewSet(viewsets.ModelViewSet):
    queryset = Document.objects.all()
    serializer_class = DocumentSerializer
    permission_classes = [HasAPIKey]  # Apply permission class here

#for child table
class DocumentDetailsViewSet(viewsets.ModelViewSet):
    queryset = DocumentDetails.objects.all()
    serializer_class = DocumentDetailsSerializer
    permission_classes = [HasAPIKey]  # Apply permission class here
```

### STEP 8
**Add API Key Authentication**
> In myapp/admin.py, register API keys  and models.
```python
from django.contrib import admin
from . models import *
from rest_framework_api_key.models import APIKey

#this line is commented, because is registered by default
#admin.site.register(APIKey)
admin.site.register(Document)
admin.site.register(DocumentDetails)
```

### STEP 9
**Configure URLs**
> In myapp/urls.py, add the routes for the API.
```python
from django.urls import path, include
from rest_framework.routers import DefaultRouter
from .views import DocumentViewSet, DocumentDetailsViewSet

router = DefaultRouter()
#for mother table
router.register(r'documents', DocumentViewSet)
#for child table
router.register(r'document-details', DocumentDetailsViewSet)

urlpatterns = [
    path('', include(router.urls)),
]
```
### STEP 10
**myproject/urls.py**
> In myproject/urls.py, configure the global URL routing and media settings for serving uploaded files during development.

```python
from django.contrib import admin
from django.urls import path,include
from django.conf import settings

urlpatterns = [
    path('admin/', admin.site.urls),
    path('api/', include('myapp.urls')),
]
from django.conf.urls.static import static
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)

```


### STEP 11
**Configure API Key and Client Authentication**
- Create API Key
> Log in to the Django admin at http://127.0.0.1:8000/admin/ and go to the API Keys section to create an API key for clients.
Copy the generated API key.

### STEP 11
**testing API**
> run this command on command prompt, replace (YOUR_API_KEY)  with generated key
- for example
```bash
curl -H "Authorization: Api-Key YOUR_API_KEY" http://127.0.0.1:8000/api/documents/
```
- for example for my key, it would be
```bash
curl -H "Authorization: Api-Key vlWnzo3b.LzCsbJisS4JxZvFA9IqM5Z0udtSgjnpq" http://127.0.0.1:8000/api/documents/
```

### STEP 13
**client side:  HTML, CSS, BOOTSTRAP, JAVASCRIPT, AJAX, JQUERY**
> load data from Django API to client side  HTML file,  create an HTML file anywhere within your computer, and add these codes

> To show a loading spinner (progress dialog) while data is being fetched from the API and then hide it once the data is displayed, you can use Bootstrap's loading spinner component. Here's how you can integrate it:

> Add a loading spinner that is displayed before the data is loaded. Hide the spinner and show the document cards once the data is successfully fetched.

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Data Display</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Material Design Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.2/css/mdb.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .card-img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .card {
            margin-bottom: 1.5rem;
        }
        .card-body {
            display: flex;
            flex-direction: column;
        }
        .card-details {
            margin-top: 10px;
        }
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }
        .spinner-border {
            width: 2rem;
            height: 2rem;
        }
        .container-fluid-custom {
            margin: 0 auto;
            padding-left: 100px;
            padding-right: 100px;
        }
    </style>
</head>
<body class="bg-primary">

    <div class="container-fluid-custom mt-5">
        <h1 class="text-center mb-4 text-white">DJANGO RESTFUL API  WITH API KEY</h1>
        
        <!-- Documents Container -->
        <div id="documents" class="row"></div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- MDB JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.2/js/mdb.min.js"></script>

    <script>
        $(document).ready(function () {
            // API Key and URLs
            const API_KEY = 'vlWnzo3b.LzCsbJisS4JxZvFA9IqM5Z0udtSgjnpq';
            const DOCUMENT_API_URL = 'http://127.0.0.1:8000/api/documents/';
            const DETAILS_API_URL = 'http://127.0.0.1:8000/api/document-details/';

            // Fetch documents and their details from the APIs
            $.when(
                $.ajax({
                    url: DOCUMENT_API_URL,
                    method: 'GET',
                    headers: {
                        'Authorization': 'Api-Key ' + API_KEY
                    }
                }),
                $.ajax({
                    url: DETAILS_API_URL,
                    method: 'GET',
                    headers: {
                        'Authorization': 'Api-Key ' + API_KEY
                    }
                })
            ).done(function (documentsData, detailsData) {
                let documents = documentsData[0];
                let details = detailsData[0];
                let detailsMap = {};

                // Map DocumentDetails by document ID
                details.forEach(detail => {
                    if (!detailsMap[detail.document.id]) {
                        detailsMap[detail.document.id] = [];
                    }
                    detailsMap[detail.document.id].push(detail);
                });

                let output = '';
                documents.forEach(document => {
                    let documentDetails = detailsMap[document.id] || [];
                    let detailsList = '';

                    documentDetails.forEach(detail => {
                        detailsList += `
                            <ul class="list-group-item bg-light">
                                <li class="list-group-item">Additional Info: ${detail.additional_info}</li>
                                <li class="list-group-item">Reference Number: ${detail.reference_number}</li>
                                <li class="list-group-item">Status: ${detail.status}</li>
                                <li class="list-group-item">Category: ${detail.category}</li>
                                <li class="list-group-item">Tags: ${detail.tags}</li>
                                <li class="list-group-item">Remarks: ${detail.remarks}</li>
                            </ul>
                        `;
                    });

                    // Card with loading spinner
                    output += `
                        <div class="col-md-6">
                            <div class="card">
                                <div class="row no-gutters">
                                    <div class="col-md-6">
                                        <!-- Placeholder for image and loading spinner -->
                                        <div class="loading-spinner" id="loading-${document.id}">
                                            <div class="spinner-border text-success" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </div>
                                        <img src="${document.image}" class="card-img" id="image-${document.id}" alt="Document Image" style="display:none;">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card-body">
                                            <div class="card-content">
                                                <h5 class="card-title">${document.title}</h5>
                                                <p class="card-text">${document.description}</p>
                                                <a href="${document.file}" class="btn btn-primary" download>
                                                    <i class="fas fa-download"></i> Download File
                                                </a>
                                                <span class="badge badge-info">${document.uploaded_by}</span>
                                            </div>
                                            <div class="card-details">
                                                <ul class="list-group list-group-flush">
                                                    ${detailsList}
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="card-footer text-muted">
                                            <small>Uploaded on ${new Date(document.created_at).toLocaleDateString()}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    // Simulate loading time for each card
                    setTimeout(function () {
                        // Hide the spinner and show the image once loaded
                        $(`#loading-${document.id}`).hide();
                        $(`#image-${document.id}`).fadeIn();
                    }, 1000); // Simulate a 1 second delay for loading the image
                });

                // Show document cards
                $('#documents').html(output).fadeIn();
            }).fail(function (error) {
                console.log("Error fetching data: ", error);
            });
        });
    </script>
</body>
</html>

```
---
## PART  2 PHP LOAD DATA FROM DJANGO API TO CLIENT MYSQL SERVER
> To create a PHP script that fetches data from the Django API and saves it into a MySQL database, you we need to:
- Set up your MySQL database.
- Create a PHP script to fetch the data from the API.
- Store the fetched data in the MySQL database.
- Here's a step-by-step guide.

### STEP 1
**open XAMPP phpmyadmin and create a database**

```sql
CREATE DATABASE api_data;
USE api_data;

CREATE TABLE documents (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    created_at DATETIME,
    image_url VARCHAR(255),
    file_url VARCHAR(255),
    uploaded_by VARCHAR(100)
);

CREATE TABLE document_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT,
    additional_info TEXT,
    reference_number VARCHAR(255),
    status VARCHAR(255),
    category VARCHAR(255),
    tags TEXT,
    remarks TEXT,
    FOREIGN KEY (document_id) REFERENCES documents(id)
);


```
### STEP 2
> open Disk C and open xampp,  create a file name it save_data.php
```php
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

```

### STEP 3: Explanation of the Script

#### Fetch API Data:
We use the `fetch_api_data()` function to make the API requests for both the documents and document details.

#### Inserting Document Data:
We loop through each document and insert it into the `documents` table using an `INSERT ... ON DUPLICATE KEY UPDATE` query to avoid duplicates.

#### Inserting Document Details:
For each detail, we insert the document detail into the `document_details` table. The `document_id` column links the detail to its respective document using the document ID.

#### Error Handling:
The script includes basic error handling, such as checking for failed API requests and SQL errors.

### STEP 4: Running the Script

- Ensure that both the `documents` and `document_details` tables are created in your MySQL database.
- Update the API URL and MySQL credentials as needed.
- Run the script to fetch and store the data from the API into MySQL.

This script allows you to fetch the document details from the API and store them in the database, linked to their respective documents.

---
**My Contacts**

**WhatsApp**  
+255675839840  
+255656848274

**YouTube**  
[Visit my YouTube Channel](https://www.youtube.com/channel/UCjepDdFYKzVHFiOhsiVVffQ)

**Telegram**  
+255656848274  
+255738144353

**PlayStore**  
[Visit my PlayStore Developer Page](https://play.google.com/store/apps/dev?id=7334720987169992827&hl=en_US&pli=1)

**GitHub**  
[Visit my GitHub](https://github.com/shamiraty/)



