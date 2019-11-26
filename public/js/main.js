// AJAX REQUESTS
function generateAuth(id, e) {
    $.get("../../~private/generate/auth/" + id, function (data) {
        e.parent().html(data)
    });
}

function generateAccess(id, source, ctl, e) {
    $.get("../../~private/generate/access/" + source + "/" + ctl + "/" + id, function (data) {
        e.parent().html(data)
    });
}

function requestAccess(id, ctl, source, e) {
    $.get("../../~private/request/access/" + source + "/" + ctl + "/" + id, function (data) {
        e.parent().html(data)
    });
}

function denyRequest(id, e) {
    $.get("../../_secure/requests/deny/" + id, function (data) {
        e.parent().html('<b class="text-danger">' + data + '</b>');
        $(this).remove();
    });
}

function acceptRequest(id, e) {
    $.post("../../_secure/requests/accept/" + id, e.serialize(), function (data) {
        $('#actions-block-' + id).html('<b class="text-success">' + data + '</b>');
        e.parent().html('<b class="text-success">' + data + '</b>')
    });
}

function removeThis(what, id, e) {
    $.get("../../~private/" + what + "/delete/" + id, function () {
        e.remove();
    });
}


// FORM SUBMITTER
function submitter(what) {
    $('form[name="' + what + '"]').submit();
}


// OTHER
function copyToClipboard(item) {
    let copyText = document.getElementById(item);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    /*For mobile devices*/
    document.execCommand("copy");
}

function toggleCheck(e, s = undefined) {
    chk = e.find('input[type=checkbox]');
    sign = e.find('i');
    act = e.parent().find('.action-btn');
    if (chk.is(':checked')) {
        chk.prop("checked", false);
        if (!s) {
            act.removeClass('bg-GET').addClass('bg-danger');
            act.find('i').removeClass('fa-check').addClass('fa-times');
            act.find('input[type=checkbox]').prop("checked", false);
            e.parent().find('input[type=checkbox]').prop("checked", false)
        }
    } else {
        chk.prop("checked", true);
        if (!s) {
            act.removeClass('bg-danger').addClass('bg-GET');
            act.find('i').removeClass('fa-times').addClass('fa-check');
            act.find('input[type=checkbox]').prop("checked", true);
            e.parent().find('input[type=checkbox]').prop("checked", true)
        } else {
            if (e.parent().parent().find('input[type=checkbox]').first().not(':checked')) {
                e.parent().parent().find('input[type=checkbox]').first().prop("checked", true);
            }
        }
    }
    e.toggleClass('bg-GET bg-danger');
    e.parent().find('.actions-ctn').toggleClass('bg-GET bg-danger');
    sign.first().toggleClass('fa-check fa-times')
}