<?php
namespace App\Twig;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private UserRepository $userRepository
    ) {}

    public function getGlobals(): array
    {
        $session = $this->requestStack->getSession();
        $userId  = $session->get('user_id');
        $user    = $userId ? $this->userRepository->find($userId) : null;

        return ['current_user' => $user];
    }
}