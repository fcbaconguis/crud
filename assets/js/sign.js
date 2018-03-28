// assets/js/sign.js

$(document).ready(function() {

    var canvas = document.getElementById("my-canvas"),
    ctx = canvas.getContext("2d");

    var background = new Image();
    // The image needs to be in your domain.

    // Make sure the image is loaded first otherwise nothing will draw.
    background.onload = function() {
        
        ctx.clearRect (0, 0, canvas.width, canvas.height);
        var grd = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
                grd.addColorStop(0, "#9db7a0");
                grd.addColorStop(1, "#e6e6e6");
                ctx.fillStyle = grd;
                ctx.fillRect (0, 0, canvas.width, canvas.height);
        
        var originalWidth = background.width;
                background.width = Math.round((50 * document.body.clientWidth) / 100);
                background.height = Math.round((background.width * background.height) / originalWidth);


        var backg = {
                  img: background,
                  x: (canvas.width/2) - (background.width/2),
                  y: (canvas.height/2) - (background.height/2)
                }
        ctx.drawImage(backg.img, backg.x, backg.y, backg.img.width, backg.img.height);

        action(canvas, ctx, background);
    };

    background.src = filePath;

});

function action(canvas, ctx, background) {

        //// Then continue with your code 
        var wrapper = document.getElementById("signature-pad"),
            clearButton = wrapper.querySelector("[data-action=clear]"),
            saveButton = wrapper.querySelector("[data-action=save]");

        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }

        //window.onresize = resizeCanvas;
        resizeCanvas();

        var signaturePad = new SignaturePad(canvas);
        var backg = {
                  img: background,
                  x: (canvas.width/2) - (background.width/2),
                  y: (canvas.height/2) - (background.height/2)
                }
        ctx.drawImage(backg.img, backg.x, backg.y, backg.img.width, backg.img.height);

        clearButton.addEventListener("click", function(event) {
            signaturePad.clear();
            action(canvas, ctx, background);
        });

        saveButton.addEventListener("click", function(event) {
            if (signaturePad.isEmpty()) {
                alert("signaturePad is empty");
            } else {
                //document.getElementById("hfSign").value = signaturePad.toDataURL();
                var signedImage = new Image();
                signedImage.src = signaturePad.toDataURL("image/jpeg");
                var w = window.open("");
                w.document.write(signedImage.outerHTML);

            }
        });
    }