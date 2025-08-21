<?php
session_start();
header('Content-Type: application/json');

echo json_encode(['rol' => $_SESSION['rol'] ?? '']);