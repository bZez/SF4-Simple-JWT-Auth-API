function generateAuth(id, e) {
    $.get("generate/auth/" + id, function (data) {
        e.parent().html(data)
    });
}

function generateAccess(id, e) {
    $.get("generate/access/" + id, function (data) {
        e.parent().html(data)
    });
}

function copyToClipboard(item) {
    var copyText = document.getElementById(item);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    /*For mobile devices*/
    document.execCommand("copy");
}