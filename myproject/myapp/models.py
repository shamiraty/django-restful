from django.db import models
class Document(models.Model):
    title = models.CharField(max_length=100)
    description = models.TextField()
    created_at = models.DateTimeField(auto_now_add=True)
    image = models.ImageField(upload_to='images/')
    file = models.FileField(upload_to='files/')
    uploaded_by = models.CharField(max_length=100)

    def __str__(self):
        return self.title


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