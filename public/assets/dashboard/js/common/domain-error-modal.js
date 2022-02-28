$(document).ready(function () {
    $('body').on('click', '.domain-error-button', function (e) {
        e.preventDefault();
        let errorMessage = e.currentTarget.dataset.data;
        $('#news-sources-list-modal').find('.news-sources-links-list').html(errorMessage);
        $('#news-sources-list-modal').modal('show');

    });
});