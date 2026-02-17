{% if qrcode starts with 'data:image' %}
	<img src="{{ qrcode }}" alt="QR-код для: {{ title }}" width="{{ size }}" height="{{ size }}"/>
{% else %}
	<img src="{{ qrcode }}" alt="QR-код для: {{ title }}" width="{{ size }}" height="{{ size }}"/>
{% endif %}

