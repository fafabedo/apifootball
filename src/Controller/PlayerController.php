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
    public function index(ProcessCrawler $processCrawler)
    {
//        $crawler = new ContentCrawler('https://www.transfermarkt.co.uk/jumplist/leistungsdaten/spieler/8198');
//        $birthdayHtml = $crawler->filter('//div[@class="dataContent"]//div[@class="dataDaten"]//span[@class="dataValue"]')
//            ->each(function(Crawler $node){
//                $string = strip_tags($node->html());
//                return preg_replace("/[\n\r\t]/","", $string);
//            } );

//        $crawler = new CountryCrawler();
        //
//        $html = $crawler->process();

//        $crawler->processPath('https://www.transfermarkt.co.uk/site/dropDownLaender');
//        $crawler->process();
//        $crawler->saveData();

//        $countries = $countryRepository->findBy(['active' => true]);
//
//        foreach ($countries as $country) {
//            $params = [
//                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
//                'form_params' => [
//                    'land_id' => $country->getCode(),
//                ],
//            ];
//            $crawler->processPath('https://www.transfermarkt.co.uk/site/DropDownWettbewerbe', 'POST', $params);
//            $content = $crawler->getContent();
//            $competitions = $crawler->getCompetitionCodes($content);
//
//            foreach ($competitions as $code => $competition) {
//                $crawler->processPath('https://www.transfermarkt.co.uk/jumplist/startseite/wettbewerb/' . $code);
//                $content = $crawler->getContent();
//            }
//
//            break;
//        }

        $processCrawler->processCompetition();


        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PlayerController.php',
            'html' => [],
        ]);
    }
}
