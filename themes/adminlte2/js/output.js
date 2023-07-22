function requestFullScreen() {
    // Supports most browsers and their versions.

    var element = document.getElementById('output_content');

    var isfs = document.webkitIsFullScreen || document.mozFullScreen || false;
    var xfs = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || false;
    var rfs = element.requestFullScreen || element.webkitRequestFullScreen || element.mozRequestFullScreen || element.msRequestFullScreen || false;

    if (isfs && xfs) {
        xfs.call(document);
        return;
    }

    if (rfs) {
        rfs.call(element);
        return;
    }
}
