{% if qrcode starts with 'data:image' %}
	<img src="{{ qrcode }}" alt="{{ lang.alt_prefix }} {{ title }}" width="{{ size }}" height="{{ size }}"/>
{% else %}
	<img src="{{ qrcode }}" alt="{{ lang.alt_prefix }} {{ title }}" width="{{ size }}" height="{{ size }}"/>
{% endif %}
