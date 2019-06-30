<?php

namespace App\Service\Crawler\Entity\CompetitionSeason;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\Country;
use App\Exception\InvalidCrawlerProcess;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CompetitionMainPageTool;
use Symfony\Component\DomCrawler\Crawler;
use DateTime;

class CompetitionSeasonCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var bool
     */
    private $featured = false;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var array
     */
    private $idCompetitions = [];
    /**
     * @var CompetitionSeason[]
     */
    private $competitionSeasons = [];

    /**
     * @return bool
     */
    public function isFeatured(): bool
    {
        return $this->featured;
    }

    /**
     * @param bool $featured
     * @return CompetitionSeasonCrawler
     */
    public function setFeatured(bool $featured): CompetitionSeasonCrawler
    {
        $this->featured = $featured;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getIdCompetitions()
    {
        return $this->idCompetitions;
    }

    /**
     * @param mixed $idCompetitions
     */
    public function setIdCompetitions($idCompetitions): void
    {
        $this->idCompetitions = $idCompetitions;
    }

    /**
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws InvalidCrawlerProcess
     */
    public function process(): CrawlerInterface
    {
        $competitions = $this
            ->getDoctrine()
            ->getRepository(Competition::class)
            ->findByConfiguration($this->getCountry(), $this->getIdCompetitions(), $this->isFeatured());

        $competitionSchema = $this
            ->getConfigSchema('crawler.competition.item.url');
        $lifetime = $this
            ->getCacheLifetime()
            ->getLifetime(CacheLifetime::CACHE_COMPETITION_SEASON);

        $index = 1;
        foreach ($competitions as $competition) {
            if (!$this->validOffset($index)) {
                $index++;
                continue;
            }
            $tmkCode = $competition->getTmkCode();
            $preparedUrl = $this->preparePath($competitionSchema->getUrl(), [$tmkCode]);
            $this
                ->setLifetime($lifetime)
                ->processPath($preparedUrl, $competitionSchema->getMethod())
            ;
            try {
                $nodeOptions = CompetitionMainPageTool::getSeasonOptions($this->getCrawler());
                foreach ($nodeOptions as $option) {
                    $node = new Crawler();
                    $node->addNode($option);
                    list($startsDate, $endsDate) = CompetitionMainPageTool::getDates($node);
                    $competitionSeason = $this->getDoctrine()
                        ->getRepository(CompetitionSeason::class)
                        ->findByStartEnd($competition, $startsDate, $endsDate);
                    if (!$competitionSeason instanceof CompetitionSeason) {
                        $competitionSeason = new CompetitionSeason();
                        $competitionSeason->setStartSeason($startsDate);
                        $competitionSeason->setEndSeason($endsDate);
                        $competitionSeason->setCompetition($competition);
                        $now = new DateTime();
                        $archive = true;
                        if ($startsDate->format('Y') === $now->format('Y')
                            || $endsDate->format('Y') === $now->format('Y')) {
                            $archive = false;
                        }
                        $competitionSeason->setArchive($archive);
                        $metadata = MetadataSchemaResources::createSchema();
                        $metadata->setUrl($preparedUrl);
                        $competitionSeason->setMetadata($metadata->getSchema());
                        $this->competitionSeasons[] = $competitionSeason;
                    }
                }
            }
            catch (\Exception $e) {
                throw new InvalidCrawlerProcess($e->getMessage());
            }
            $index++;
            if (count($competitions) <= $index) {
                $this->setIsCompleted(true);
            }
        }
        return $this;
    }

    /**
     * @return CompetitionSeason[]
     */
    public function getData()
    {
        return $this->competitionSeasons;
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        if (empty($this->competitionSeasons)) {
            return $this;
        }
        $em = $this->getDoctrine()
            ->getManager();
        foreach ($this->competitionSeasons as $competitionSeason) {
            $em->persist($competitionSeason);
        }
        $em->flush();
        return $this;
    }

}
