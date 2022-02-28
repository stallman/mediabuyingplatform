$(document).ready(function (e) {
    let coordX = $("#coord-x");
    let coordY = $("#coord-y");
    let width = $("#width");
    let height = $("#height");
    let targetImgTag = $('<img src="" id="target" alt="img">');
    let imgContainer = $('.img-container');
    let JcropAPI = null

    $('#fileInput').on('change', function () {
        if (this.files && this.files[0]) {
            if (this.files[0].type.match(/^image\//)) {
                let reader = new FileReader();
                reader.onload = function (evt) {
                    let img = new Image();
                    img.onload = function () {
                        cropCreate(img.src)
                    };
                    img.src = evt.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                alert("Файл не изображение! Пожалуйста, выберете изображение.");
            }
        } else {
            alert('Изображение не выбрано');
        }
    });

    $('#recrop').on('click', function (e) {
        e.preventDefault()
        cropCreate($("#Original").attr('src'))
    });


    function updateCoords(c) {
        [xWidthPercent, yHeigthPercent] = getPercent()
        coordX.val(c.x / xWidthPercent * 100);
        coordY.val(c.y / yHeigthPercent * 100);
        width.val(c.w / xWidthPercent * 100);
        height.val(c.h / yHeigthPercent * 100);
    }

    function getPercent() {
        //размер исходного изображения
        let imgWidth = $("#target")[0].width;
        let imgHegth = $("#target")[0].height;
        //размер текущего изображения
        let targetImgWidth = $("#target").width();
        let targetImgWidthImgHeight = $("#target").height();
        //дефолтный процент
        let xWidthPercent = 100
        let yHeigthPercent = 100

        if (imgWidth !== targetImgWidth || imgHegth !== targetImgWidthImgHeight) {
            //вычисляем разницу между размерами исходного и текущего изображения
            xWidthPercent = targetImgWidth / (imgWidth / 100)
            yHeigthPercent = targetImgWidthImgHeight / (imgHegth / 100)
        }

        return [xWidthPercent, yHeigthPercent]
    }

    function cropCreate(imgSrc) {
        if (JcropAPI != null) {
            $('#target').remove()
            imgContainer.empty();
            coordX.val(null);
            coordY.val(null);
            width.val(null);
            height.val(null);
            targetImgTag.attr('src', imgSrc);
            targetImgTag.removeAttr('style')
            targetImgTag.attr('style', 'max-width: 700px; max-height: 700px')
            targetImgTag.appendTo('.img-container');
        } else {
            $("#target").attr("src", imgSrc);
        }
        JcropAPI = $("#target").Jcrop({
            aspectRatio: 1,
            onSelect: updateCoords
        });
    }
})