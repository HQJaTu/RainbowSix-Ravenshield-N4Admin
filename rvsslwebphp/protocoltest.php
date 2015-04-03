<?php
if (!isset($_GET['startip'])) {
    die ("missing start-ip error");
}
?>
<html>
<body onUnLoad="javascript:UnloadingFrame()">
<script>
    var timerDone = false
    var stopped = false
    var data = "<iframe src=\"rvs:-mod=<?php print $currentmod; ?> -ip=<?php print $_GET['startip']; ?>-bc=<?php print $bport; ?>\" frameborder=\"0\" SCROLLING=\"no\" height=\"0\" width=\"0\"></iframe>"
    function rvsTimer()
    {
        if (stopped == false) {
            timerDone = true
            location.reload('installed.html')
        }
    }
    function UnloadingFrame()
    {
        if (timerDone == false) {
            stopped = true
            location.reload('notinstalled.html')
        }
    }
    setTimeout("rvsTimer()", 300)
    document.write(data)
</script>
</body>
</html>
