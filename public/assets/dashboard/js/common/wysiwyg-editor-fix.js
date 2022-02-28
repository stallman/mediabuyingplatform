/*
* Фикс для полей типа wysiwyg. Исправляет следующие баги:
* 1) т.к. реальное поле textarea скрыто через display:none, если оно обязательно и не заполнено, сообщения об ошибке мы не увидим
* 2) эдитор добавляет различные html теги в текст даже после того, как мы очистим поле ввода, из-за чего поле будет считаться не пустым
* */
var hiddenTextareaSelector = '#news_mediabuyer_fullDescription, #news_fullDescription';
var wysiwygEditorSelecor = '.note-editable.card-block';

$(document).ready(function() {
    $(hiddenTextareaSelector).addClass('hidden-required-message-fix'); //заменяет display:none на другой способ скрытия, при котором сообщения об ошибке required будет отображаться
    $(wysiwygEditorSelecor).on('input keypress keyup keydown', function () {
        if (fullDescriptionIsEmpty()) {
            clearHiddenTextarea();
        }
    })
});

function fullDescriptionIsEmpty()
{
    return clearSpecialSymbols(getFullDescriptionText()).length > 0 ? false : true;
}

function clearHiddenTextarea()
{
    $(hiddenTextareaSelector).val('');

    //Из-за того, что скрытое поле textarea заполняется тегами уже после ввода, повторяем очистку через несколько миллисекунд
    setTimeout(()=>{
        $(hiddenTextareaSelector).val('');
    }, 10);
}

function getFullDescriptionText()
{
    return $(wysiwygEditorSelecor).text();
}

function clearSpecialSymbols(str)
{
    str = str.replace(/\s/g, '').replace(/[&]nbsp[;]/gi," ").replace(/[<]br[^>]*[>]/gi,"").trim();
    console.log(str.length);
    return str;
}