from django.contrib import admin
from rest_framework_api_key.models import APIKey
from . models import *

#admin.site.register(APIKey)
admin.site.register(Document)
admin.site.register(DocumentDetails)

