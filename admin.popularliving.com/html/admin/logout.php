<?php


session_start();
session_unset();
session_destroy();

?>
<script language=JavaScript>
document.execCommand("ClearAuthenticationCache"); 
window.location.href('http://www.popularliving.com/admin');
</script>