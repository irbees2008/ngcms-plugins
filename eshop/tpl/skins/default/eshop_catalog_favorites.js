// JavaScript для работы с избранным в каталоге товаров
$(document).ready(function() {
    // Инициализация избранного для всех товаров в каталоге
    initCatalogFavorites();
});

function initCatalogFavorites() {
    // Проверяем статус избранного для всех товаров на странице
    $('.globalFrameProduct').each(function() {
        var productId = $(this).find('.toFavorites').data('product-id');
        if (productId) {
            checkFavoriteStatusForProduct(productId, $(this));
        }
    });
    
    // Обработчик добавления в избранное
    $(document).on('click', '.toFavorites', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        var productElement = $(this).closest('.globalFrameProduct');
        addToFavoritesFromCatalog(productId, productElement);
    });
    
    // Обработчик удаления из избранного
    $(document).on('click', '.inFavorites', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        var productElement = $(this).closest('.globalFrameProduct');
        removeFromFavoritesFromCatalog(productId, productElement);
    });
}

function checkFavoriteStatusForProduct(productId, productElement) {
    $.ajax({
        url: home + '/engine/rpc.php',
        type: 'POST',
        data: {
            plugin: 'eshop_favorites',
            action: 'check',
            product_id: productId
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.in_favorites) {
                productElement.find('.toFavorites').hide();
                productElement.find('.inFavorites').show();
            } else {
                productElement.find('.toFavorites').show();
                productElement.find('.inFavorites').hide();
            }
        }
    });
}

function addToFavoritesFromCatalog(productId, productElement) {
    $.ajax({
        url: home + '/engine/rpc.php',
        type: 'POST',
        data: {
            plugin: 'eshop_favorites',
            action: 'add',
            product_id: productId
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.status === 'success') {
                productElement.find('.toFavorites').hide();
                productElement.find('.inFavorites').show();
                showCatalogNotification('Товар добавлен в избранное', 'success');
            } else {
                showCatalogNotification(data.message || 'Ошибка при добавлении в избранное', 'error');
            }
        },
        error: function() {
            showCatalogNotification('Ошибка связи с сервером', 'error');
        }
    });
}

function removeFromFavoritesFromCatalog(productId, productElement) {
    $.ajax({
        url: home + '/engine/rpc.php',
        type: 'POST',
        data: {
            plugin: 'eshop_favorites',
            action: 'remove',
            product_id: productId
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.status === 'success') {
                productElement.find('.toFavorites').show();
                productElement.find('.inFavorites').hide();
                showCatalogNotification('Товар удален из избранного', 'success');
            } else {
                showCatalogNotification(data.message || 'Ошибка при удалении из избранного', 'error');
            }
        },
        error: function() {
            showCatalogNotification('Ошибка связи с сервером', 'error');
        }
    });
}

function showCatalogNotification(message, type) {
    var notification = $('<div class="catalog-notification catalog-notification-' + type + '">' + message + '</div>');
    $('body').append(notification);
    
    notification.css({
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '10px 20px',
        borderRadius: '4px',
        color: 'white',
        backgroundColor: type === 'success' ? '#27ae60' : '#e74c3c',
        zIndex: 9999
    });
    
    setTimeout(function() {
        notification.fadeOut(function() {
            notification.remove();
        });
    }, 3000);
}