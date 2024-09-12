from django.urls import path, include
from rest_framework.routers import DefaultRouter
from .views import DocumentViewSet, DocumentDetailsViewSet

router = DefaultRouter()
router.register(r'documents', DocumentViewSet)
router.register(r'document-details', DocumentDetailsViewSet)

urlpatterns = [
    path('', include(router.urls)),
]
