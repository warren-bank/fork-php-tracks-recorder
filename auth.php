<?php
  if (!is_array($_config)) {
    header('HTTP/1.1 500 Internal Server Error', true, 500);
    exit();
  }

  if (!empty($_config['website_pass']) && ($_config['website_pass'] !== $_REQUEST['password'])) {
    header('HTTP/1.1 401 Unauthorized', true, 401);
    exit();
  }
?>
