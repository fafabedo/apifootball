<?php

namespace App\Controller;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SecurityController extends AbstractController
{
    /**
     * @var JWTTokenManagerInterface
     */
    private $jwtManager;

    private $userPasswordEncoder;

    public function __construct(JWTTokenManagerInterface $jwtManager,
        UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->jwtManager = $jwtManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * @return JWTTokenManagerInterface
     */
    public function getJwtManager(): JWTTokenManagerInterface
    {
        return $this->jwtManager;
    }

    /**
     * @return UserPasswordEncoderInterface
     */
    public function getUserPasswordEncoder(): UserPasswordEncoderInterface
    {
        return $this->userPasswordEncoder;
    }

    /**
     * @Route(
     *     "/v1/user/register.{_format}",
     *     requirements={
     *        "_format": "json|xml",
     *     }
     * )
     * @ParamConverter("user", class="App\Entity\User", converter="user_converter")
     * @param User $user
     */
    public function register(User $user)
    {
        $tmp = $user;
        $tmp = 1;
    }
}
