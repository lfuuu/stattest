function ImmediatelyPrint(element) {
    var windows = window.open(
        element.href,
        'title_windows_print',
        'top=0,width=' + screen.width + 'px,height=' + screen.height + 'px,' +
        'scrollbars=auto,menubar=no,toolbar=no,location=no,status=no,center=yes'
    );
    windows.focus();
    windows.print();

    return false;
}