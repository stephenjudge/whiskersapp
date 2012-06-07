<?php

// RFC4627-compliant header
header('Content-type: application/html');

// Encode data
echo json_encode($this->data);

// header('Content-type: application/json');
// header('HTTP/1.1: ' . 200);
// header('Status: ' . 200);
// header('Content-Length: ' . strlen($output));