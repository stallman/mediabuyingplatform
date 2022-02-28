$(document).ready(function () {
    $(document).on('click','.btn-delete', function (e) {
        e.preventDefault();
        let deleteUrl = $(this).data('delete-url');
        let confirmMessage = confirm('Удалить запись?')
        if (confirmMessage) {
            $.ajax({
                method: 'post',
                url: deleteUrl,
                success: function () {
                    window.location.reload();
                },
                error: function (data) {
                    alert('Во время удаления записи произошла ошибка');
                }
            })
        }
    })
})