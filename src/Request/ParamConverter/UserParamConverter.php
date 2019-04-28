<?php


namespace App\Request\ParamConverter;


use App\Entity\User;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserParamConverter
 * @package App\Request
 */
class UserParamConverter implements ParamConverterInterface
{

    /**
     * @var ManagerRegistry $registry Manager registry
     */
    private $registry;

    /**
     * @var JsonEncoder
     */
    private $jsonEncoder;

    /**
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(
        ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->jsonEncoder = new JsonEncoder();
    }

    /**
     * @return ManagerRegistry
     */
    public function getRegistry(): ManagerRegistry
    {
        return $this->registry;
    }

    /**
     * @return JsonEncoder
     */
    public function getJsonEncoder(): JsonEncoder
    {
        return $this->jsonEncoder;
    }

    /**
     * @param ParamConverter $configuration
     * @return bool|void
     */
    public function supports(ParamConverter $configuration)
    {
        if (null === $this->registry || !count($this->registry->getManagers())) {
            return false;
        }
        if (null === $configuration->getClass()) {
            return false;
        }

        if ('App\Entity\User' !== $configuration->getClass()) {
            return false;
        }
        return true;
    }

    /**
     * @param Request $request
     * @param ParamConverter $configuration
     * @return bool|void
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $class = $configuration->getClass();
        $user = new $class();
        $format = $request->query->get('_format', 'json');
        $content = $this
            ->getJsonEncoder()
            ->decode($request->getContent(), $format);
        $user = $this->hydrateAssocToClass($user, $content);
        $request->attributes->set($configuration->getName(), $user);
    }

    /**
     * @param $class
     * @param $array
     * @return mixed
     */
    private function hydrateAssocToClass($class, $array)
    {
        foreach ($array as $key => $value)
        {
            $method = 'set' . Inflector::ucwords($key);
            if (method_exists($class, $method) ) {
                $class->$method($value);
            }
        }
        return $class;
    }


}
