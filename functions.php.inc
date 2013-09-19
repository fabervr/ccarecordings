<?
function datetoepoch($day) {
    $tmp = new DateTime($day);
    return date_format($tmp, 'U');
}


function epochtodatetime($epoch) {
    return date('Y-m-d H:i:s', $epoch);
}

?>