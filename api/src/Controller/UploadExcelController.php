<?php

namespace App\Controller;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Entity\ExcelFile;
use App\Entity\MusicGroups;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UploadExcelController {
    public function __invoke(Request $request, ManagerRegistry $doctrine): JsonResponse {
        /** @var UploadedFile $uploadFile */
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }
        $spreadsheet = IOFactory::load($uploadedFile->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();

        // $excel = new ExcelFile();
        // $excel->setName($worksheet[0]);
        // dd($worksheet[0]);
        $rows = $worksheet->toArray();
        // Récupération des données
        $entityManager = $doctrine->getManager();
        $isheader = 0;
        foreach ($rows as $row) {
            if($isheader > 0) {
                $group = new MusicGroups();
                $group->setName($row[0]);
                $group->setCountry($row[1]);
                $group->setCity($row[2]);
                $group->setYearStarted($row[3]);
                $group->setYearEnded($row[4]);
                $group->setFounder($row[5]);
                $group->setMembers($row[6]);
                $group->setStyle($row[7]);
                $group->setPresentation($row[8]);
                // dd($group);
                $entityManager->persist($group);
            }
            $isheader = 1;
        }

        $entityManager->flush();

        return new JsonResponse('Import terminé avec succès');
    }
}