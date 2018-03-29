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

        action(canvas, ctx, background, user_id,contract_id,page, submit_url);
    };

    background.src = filePath;

    for(i = 1; i <=totPage; i++) {

        var ahref = "#";
        if(i != page) {
            ahref = "/contracts/"+user_id+"/"+contract_id+"/sign/page/"+i;
        }

        var btn = $('<a/>', {
            text: i,
            id: 'page_btn_'+i,
            class: 'btn btn-default btn-xs spacing',
            href: ahref
        });

        $("#page-buttons").append(btn);
    }

});

function action(canvas, ctx, background, user_id, contract_id, page_num, submit_url) {

        //// Then continue with your code 
        var wrapper     = document.getElementById("signature-pad"),
            backButton  = document.querySelector("[data-action=back]"),
            clearButton = document.querySelector("[data-action=clear]"),
            saveButton  = document.querySelector("[data-action=save]");

        var signaturePad = new SignaturePad(canvas);
        var backg = {
                  img: background,
                  x: (canvas.width/2) - (background.width/2),
                  y: (canvas.height/2) - (background.height/2)
                }
        ctx.drawImage(backg.img, backg.x, backg.y, backg.img.width, backg.img.height);

        backButton.addEventListener("click", function(event) {
            location.href="/contracts/"+user_id;
        });
        clearButton.addEventListener("click", function(event) {
            signaturePad.clear();
            action(canvas, ctx, background, user_id, contract_id, page, submit_url);
        });

        saveButton.addEventListener("click", function(event) {
            if (signaturePad.isEmpty()) {
                alert("signaturePad is empty");
            } else {

                var image_base_64 = signaturePad.toDataURL("image/jpeg");
                $.post(submit_url,{contract_id:contract_id,page_num:page_num,image_base_64:image_base_64}, function(d){
                    if (d.status == "true")
                    {
                        alert("Your signature has been saved, do it again to replace the existing signature\n" + 
                            "You can view the final file on the list of contracts");
                    }
                },"json");

                /*
                var signedImage = new Image();
                signedImage.src = signaturePad.toDataURL("image/jpeg");
                var w = window.open("");
                w.document.write(signedImage.outerHTML);*/

            }
        });
    }