$(document).ready(function () {
    $('#domain_parking_domain').on('input', function () {
        $(this).val(removeProtocol($(this).val()));
        $(this).val(removeLastSlash($(this).val()));
    })
});

function removeProtocol(domain) {
    return domain.replace(/(^\w+:|^)\/\//, '')
}

function removeLastSlash(string) {
    if (string.charAt(0) == "/") string = string.substr(1);
    if (string.charAt(string.length - 1) == "/") string = string.substr(0, string.length - 1);
    return string
}