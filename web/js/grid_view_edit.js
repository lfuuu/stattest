var gve_pencil = "";
var gve_targetElementClass = gve_targetElementName + "-input";
var gve_targetTdElement = "td[data-col-seq='" + gve_targetElementName + "']";

var lastText;
$("html").click(function(e) {
    if (gve_pencil === "") {
        return;
    }
    var inputField = $(gve_pencil).siblings("textarea." + gve_targetElementClass)[0];
    if (!inputField) {
        return;
    }
    var text = inputField.value;

    if (!$(e.target).hasClass(gve_targetElementClass) && !$(e.target).hasClass("edit")) {
        var id = $(gve_pencil).data().id;

        inputField.remove();
        $("<span>" + text + "</span>").appendTo($(gve_pencil).parent());
        if (text !== lastText) {
            sendAjax(id, text);
        }
    }
});

$(gve_targetTdElement + " img.edit").click(function () {
    var targetInput = $(gve_targetTdElement + " ." + gve_targetElementClass)[0];
    var currentTargetInput = $(this).siblings("." + gve_targetElementClass)[0];
    if (targetInput && !currentTargetInput) {
        var text = targetInput.value;
        var id = $(gve_pencil).data().id;

        $("<span>" + text + "</span>").appendTo($(targetInput).parent());
        targetInput.remove();
        sendAjax(id, text);

        return;
    }

    gve_pencil = this;
    toggleElements(gve_pencil);
});

function sendAjax(id, text) {
    $.ajax({
        type: "POST",
        url: gve_targetUrl,
        data: {
            id: id,
            text: text
        },
        complete: function (data) {
            if (data.status !== 200) {
                alert("Произошла ошибка. Код ошибки: " + data.status);
                toggleCheckMarkError(gve_pencil);
            } else {
                toggleCheckMarkSuccess(gve_pencil);
            }
        }
    });
}

function toggleElements(pencil) {
    if ($(pencil).siblings("span").length) {
        var span = $(pencil).siblings("span")[0];
        var text = span.innerText;
        span.remove();
        $(
            "<textarea style=\"width: 80%\" class=\"" +
            gve_targetElementClass +
            " form-control\">" +
            text +
            "</textarea>"
        ).appendTo($(pencil).parent());
        lastText = text;
        $("." + gve_targetElementClass)[0].focus();
    }
}

function toggleCheckMarkSuccess(pencil) {
    $(pencil).hide();
    $("<img src=\"/images/icons/enable.gif\" class=\"apply pull-right\" />").appendTo($(pencil).parent());
    setTimeout(function () {
        $(pencil).siblings("img.apply")[0].remove();
        $(pencil).show();
    }, 2000);
}
function toggleCheckMarkError(pencil) {
    $(pencil).hide();
    $("<img src=\"/images/icons/disable.gif\" class=\"apply pull-right\" />").appendTo($(pencil).parent());
    setTimeout(function () {
        $(pencil).siblings("img.apply")[0].remove();
        $(pencil).show();
        $(pencil).siblings("span")[0].innerText = lastText;
    }, 2000);
}