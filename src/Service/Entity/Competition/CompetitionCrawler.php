<?php


namespace App\Service\Entity\Competition;


use App\Entity\Competition;
use App\Entity\Country;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;

/**
 * Class CompetitionCrawler
 * @package App\Service\Entity\Competition
 */
class CompetitionCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var Competition[]
     */
    private $competitions = [];

    /**
     * @var array
     */
    private $codes = [];

    /**
     * @var Country
     */
    private $country;

    /**
     * @var string
     */
    private $code;

    /**
     * @return Country
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     * @return $this
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return CompetitionCrawler
     */
    public function setCode(string $code): CompetitionCrawler
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return Country[]|object[]
     */
    public function getCountryList()
    {
        if ($this->getCountry() instanceof Country) {
            $filter = ['country' => $this->getCountry()];
            return $this->getDoctrine()
                ->getRepository(Country::class)
                ->findBy($filter);
        }
        return $this
            ->getDoctrine()
            ->getRepository(Country::class)
            ->findBy(['active' => true]);
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $this->processCodes();

        if (empty($this->codes)) {
            return $this;
        }

        $this->createProgressBar('Crawling country information', count($this->codes));

        foreach ($this->codes as $code => $country) {

            $item = $this
                ->getConfigManager()
                ->getValue('competition.item.url');

            $url = $this->preparePath($item['resource']['url'], [$code]);
            $this->processPath($url, $item['resource']['method']);

            if ($this
                    ->getCrawler()
                    ->filter('h1.spielername-profil')->count() === 0) {
                continue;
            }
            $name = $this
                ->getCrawler()
                ->filter('h1.spielername-profil')
                ->html();

            $table = $this
                ->getCrawler()
                ->filter('table.profilheader')
                ->html();
            $stripped = strip_tags(preg_replace('/\s+/', ' ', $table));
            preg_match('/League\sLevel:[\s]+([a-zA-Z|\s]*)/', $stripped, $matches);

            switch ($matches[1]) {
                case 'First Tier':
                    $level = 1;
                    break;
                case 'Second Tier':
                    $level = 2;
                    break;
                case 'Third Tier':
                    $level = 3;
                    break;
                case 'Fourth Tier':
                    $level = 4;
                    break;
                case 'Fifth Tier':
                    $level = 5;
                    break;
                default:
                    $level = 6;
                    break;
            }

            preg_match('/Number\sof\steams:[\s]+([0-9]*)/', $stripped, $matches);

            if ($level < 5 && isset($matches[1])) {
                $teams = (int)$matches[1];

                $competition = new Competition();
                $competition->setName($name);
                $competition->setLeagueLevel($level);
                $competition->setNumberTeams($teams);
                $competition->setCode($code);
                $competition->setCountry($country);
                $this->competitions[] = $competition;
            }
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();

        return $this;
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function processCodes()
    {
        $collection = $this
            ->getConfigManager()
            ->getValue('competition.collection.url')
        ;

        $countries = $this->getCountryList();
        $this->createProgressBar('Crawling country codes', count($countries));

        foreach ($countries as $country) {
            $params = [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'land_id' => $country->getCode(),
                ],
            ];
            $codes = $this
                ->processPath($collection['resource']['url'], $collection['resource']['method'], $params)
                ->setCountry($country)
                ->getCompetitionCodes();
            $this->codes += $codes;
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();

        return $this;
    }

    /**
     * @return array
     */
    public function getCompetitionCodes()
    {
        $content = $this->getContent();
        preg_match_all('/"([a-zA-Z0-9]+).\>([a-zA-Z|\s|\p{L}]+)/i', $content, $matches);
        $competitions = [];
        foreach ($matches[1] as $key => $item) {
            $competitions[$item] = $this->getCountry();
        }
        return $competitions;
    }


    /**
     * @return Competition[]
     */
    public function getData()
    {
        return $this->competitions;
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        foreach ($this->competitions as $competition) {
            $code = $competition->getCode();
            $existent = $this->getDoctrine()
                ->getRepository(Competition::class)
                ->findOneBy(['code' => $code]);

            if ($existent instanceof Competition) {
                $existent->setLeagueLevel($competition->getLeagueLevel());
                $existent->setNumberTeams($competition->getNumberTeams());
                $existent->setCountry($competition->getCountry());
                $competition = $existent;
            }

            $em = $this->getDoctrine()
                ->getManager();

            $em->persist($competition);
            $em->flush();
        }
        return $this;
    }

}