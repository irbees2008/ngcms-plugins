{% if entries %}
<div class="social-bookmarks">
    <ul class="social-list">
    {% for entry in entries %}
        <li class="social-item">
            <a rel="nofollow" target="_blank" href="{{ entry.url }}" title="{{ entry.desc }}">
                {% if entry.has_img %}
                    <img src="{{ entry.img }}" title="{{ entry.desc }}" alt="{{ entry.title }}" class="social-icon" />
                {% else %}
                    {{ entry.desc }}
                {% endif %}
            </a>
        </li>
    {% endfor %}
    </ul>
</div>
{% endif %}
