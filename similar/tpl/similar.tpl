{% if entries %}
<section class="similar-news">
    <h3>{{ heading|escape }}</h3>
    <ul class="similar-news-list">
        {{ entries|raw }}
    </ul>
</section>
{% endif %}
