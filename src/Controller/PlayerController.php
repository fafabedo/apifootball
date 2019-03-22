<?php

namespace App\Controller;

use App\Repository\CountryRepository;
use App\Service\Crawler\ProcessCrawler;
use App\Service\Entity\Competition\CompetitionCrawler;
use App\Service\Entity\Country\CountryCrawler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Annotation\Route;


class PlayerController extends AbstractController
{
    /**
     * @Route("/api/crawler", name="player")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PlayerController.php',
            'html' => [],
        ]);
    }
}
