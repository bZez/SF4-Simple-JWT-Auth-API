function generateAuth(id, e) {
    $.get("../~private/generate/auth/" + id, function (data) {
        e.parent().html(data)
    });
}

function generateAccess(id,ctl, e) {
    $.get("../~private/generate/access/" + ctl +"/" + id, function (data) {
        e.parent().html(data)
    });
}

function requestAccess(id,ctl, e) {
    $.get("../~private/request/access/" + ctl +"/" + id, function (data) {
        e.parent().html(data)
    });
}

function submitter(what) {
    $('form[name="' + what + '"]').submit();
}

function copyToClipboard(item) {
    let copyText = document.getElementById(item);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    /*For mobile devices*/
    document.execCommand("copy");
}