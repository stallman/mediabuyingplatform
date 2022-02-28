Share = {
    host : location.protocol + '//' + location.hostname,
    vk: function (e, o, t, i) {
        e = Share.host + e + "?utm_source=social&utm_medium=social&utm_campaign=vk";
        t = Share.host + t;
        url = "https://vk.com/share.php?", url += "url=" + encodeURIComponent(e), url += "&title=" + encodeURIComponent(o), url += "&description=" + encodeURIComponent(i), url += "&image=" + encodeURIComponent(t), url += "&noparse=true";
        Share.popup(url)
    },
    ok: function (e, o, t) {
        e = Share.host + e + "?utm_source=social&utm_medium=social&utm_campaign=ok";
        t = Share.host + t;
        url = "https://connect.ok.ru/offer?url=" + encodeURIComponent(e) + "&title=" + encodeURIComponent(o) + "&imageUrl=" + encodeURIComponent(t);
        Share.popup(url)
    },
    viber: function (e, o) {
        e = Share.host + e + "?utm_source=social&utm_medium=social&utm_campaign=viber";
        e = encodeURIComponent(e);
        o = encodeURIComponent(o);

        if ((o + ' ' + e).length > 200) {
            o = o.substr(0, 200 - e.length - 1 - 4) + '...';
        }
        var url = 'viber://forward?text=' + o + ' ' + e;
        Share.popup(url);
    },
    whatsapp: function (e, o) {
        e = Share.host + e + "?utm_source=social&utm_medium=social&utm_campaign=whatsapp";
        var url = 'whatsapp://send?text=' + encodeURIComponent(o + ' ' + e);
        Share.popup(url);
    },
    popup: function (e) {
        window.open(e, "", "toolbar=0,status=0,width=626,height=436")
    }
};