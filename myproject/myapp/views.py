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






