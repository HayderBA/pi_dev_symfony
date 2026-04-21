<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

$data = ['medecinId' => 46, 'patientLat' => 36.8065, 'patientLon' => 10.1815, 'distance' => 2.5];

echo json_encode(['success' => true, 'data' => $data]);