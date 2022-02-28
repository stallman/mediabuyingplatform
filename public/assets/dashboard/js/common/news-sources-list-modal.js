$(document).ready(function () {
   $('body').on('click', '.open-news-sources-list-modal', function (e) {
       e.preventDefault();
       $.ajax({
           url: "/mediabuyer/news_sources/" + $(this).data('news-id'),
           type: 'POST',
           success: function(data) {
               let links = "Источники отсутствуют";

               if (data.length > 0) {
                   links = data.map(function (item) {
                       return generateSourceLinkHtml(item);
                   })
               }

               $('#news-sources-list-modal').find('.news-sources-links-list').html(links);
               $('#news-sources-list-modal').modal('show');
           }
       });
   });
});

function generateSourceLinkHtml(item) {
    return '<div class="news-sources-links-list__item">' +
    '<button class="js-copy-button btn btn-primary btn-sm" data-source-id="' + item.source_id + '">Copy <i class="far fa-copy"></i></button>' +
    '    <span class="news-sources-links-list__item-heading">' + item.title + ': </span>' +
    '    <a href="' + item.link + '" class="news-sources-links-list__item-link"  id="link' + item.source_id + '">' + item.link + '</a>' +
    '</div>'
}