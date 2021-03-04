<?php
$output = shell_exec("./add-modules.sh");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet/less" type="text/css" href="/assets/css/styles.less" />
    <script src="//cdn.jsdelivr.net/npm/less@3.13"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
</head>

<body class="bg-black">
    <pre class="text-light">
            <?php
            if (isset($output)) {
                echo $output;
            } else {
                echo "<h6> ERROR: NO DATA RETURNED!<h6>";
            }
            ?>
        </pre>
</body>

</html>