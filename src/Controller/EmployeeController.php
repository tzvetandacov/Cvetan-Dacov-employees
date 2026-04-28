<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CsvProcessor;
use App\Service\CollaborationService;
use App\Service\DateFacade;
use Twig\Environment;

final class EmployeeController
{
    private Environment $twig;
    private CsvProcessor $csvProcessor;
    private CollaborationService $collaborationService;
    private DateFacade $dateFacade;

    public function __construct(
        Environment $twig,
        CsvProcessor $csvProcessor,
        CollaborationService $collaborationService,
        DateFacade $dateFacade
    ) {
        $this->twig = $twig;
        $this->csvProcessor = $csvProcessor;
        $this->collaborationService = $collaborationService;
        $this->dateFacade = $dateFacade;
    }

    public function index(): void
    {
        $isRedirect = $_SESSION['is_redirecting'] ?? false;
        unset($_SESSION['is_redirecting']);

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$isRedirect) {
            $this->clearSession();
        }

        $rawEmployees = $_SESSION['rawEmployees'] ?? [];
        $longestPair = $_SESSION['longestPair'] ?? null;
        $pairProjects = $_SESSION['pairProjects'] ?? [];
        $errors = $_SESSION['errors'] ?? [];

        echo $this->twig->render('results.twig', [
            'isCarbonActive' => $this->dateFacade->isCarbonAvailable(),
            'rawEmployees' => $rawEmployees,
            'longestPair' => $longestPair,
            'pairProjects' => $pairProjects,
            'errors' => $errors,
            'serializedData' => !empty($rawEmployees) ? base64_encode(serialize($rawEmployees)) : ''
        ]);
    }

    public function upload(): void
    {
        $this->clearSession();
        try {
            $format = $_POST['date_format'] ?? 'EU';
            $employees = $this->csvProcessor->processFile($_FILES['csv_file']['tmp_name'], $format);
            $_SESSION['rawEmployees'] = $employees;
            $_SESSION['errors'] = $this->csvProcessor->getErrors();
            $_SESSION['is_redirecting'] = true;
        } catch (\Exception $e) {
            $_SESSION['errors'] = [$e->getMessage()];
        }
        $this->redirect();
    }

    public function calculate(): void
    {
        $serializedData = $_POST['serialized_data'] ?? '';
        $employeeDataset = unserialize(base64_decode($serializedData));

        if (!empty($employeeDataset)) {
            $winner = $this->collaborationService->findLongestCollaboration($employeeDataset);

            if ($winner) {
                $_SESSION['longestPair'] = $winner['pair'];
                $_SESSION['pairProjects'] = $winner['projects'];
                $_SESSION['rawEmployees'] = $employeeDataset;
                $_SESSION['is_redirecting'] = true;
            } else {
                $_SESSION['errors'] = [
                    "No common projects found. " .
                    "You need at least two employees working on the same project."
                ];
                $_SESSION['rawEmployees'] = $employeeDataset;
                $_SESSION['is_redirecting'] = true;
            }
        }
        $this->redirect();
    }

    private function clearSession(): void
    {
        unset($_SESSION['rawEmployees'], $_SESSION['longestPair'], $_SESSION['pairProjects'], $_SESSION['errors']);
    }

    private function redirect(): void
    {
        header("Location: /index.php");
        exit;
    }
}
