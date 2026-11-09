<li class="similar-news-item">
    <a href="{{ news.url.full|escape }}">{{ news.title|escape }}</a>
    <span class="similar-news-meta">
        {{ news.date|escape }}{% if news.author.name %} — {{ news.author.name|escape }}{% endif %}
    </span>
    {% if news.categories.masterText %}
        <span class="similar-news-category">{{ news.categories.masterText|escape }}</span>
    {% endif %}
</li>
