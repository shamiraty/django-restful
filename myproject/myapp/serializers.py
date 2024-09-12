from rest_framework import serializers
from .models import *

class DocumentSerializer(serializers.ModelSerializer):
    class Meta:
        model = Document
        fields = ['id', 'title', 'description', 'created_at', 'image', 'file', 'uploaded_by']

class DocumentDetailsSerializer(serializers.ModelSerializer):
    document = DocumentSerializer()  # Nested serializer for the related Document
    class Meta:
        model = DocumentDetails
        fields = '__all__'