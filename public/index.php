<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

use App\Controller\EmployeeController;
use App\Service\CsvProcessor;
use App\Service\CollaborationCalculator;
use App\Service\CollaborationService;
use App\Service\DateFacade;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, ['cache' => __DIR__ . '/../var/cache', 'auto_reload' => true]);

$dateFacade = new DateFacade();
$csvProcessor = new CsvProcessor($dateFacade);
$calculator = new CollaborationCalculator();
$collaborationService = new CollaborationService($calculator);

$employeeController = new EmployeeController($twig, $csvProcessor, $collaborationService, $dateFacade);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $employeeController->upload();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calculate'])) {
    $employeeController->calculate();
} else {
    $employeeController->index();
}
