<?php



namespace App\Controller;

use App\Entity\Commande;
use App\Repository\BoissonRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;




class ServerController extends AbstractController
{
    #[Route('/api/commandes', name: 'create_commande', methods: ['POST'])]
    public function createCommande(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security
    ): JsonResponse {
        if (!$security->isGranted('ROLE_SERVEUR')) {
            throw new AccessDeniedHttpException('Accès refusé');
        }

        $data = json_decode($request->getContent(), true);

        $commande = new Commande();
        $commande->setCreatedDate(new \DateTime());
        $commande->setStatus('en cours de préparation');
        $commande->setServer($this->getUser());

        if (isset($data['tableNuméro'])) {
            $commande->setTableNuméro($data['tableNuméro']);
        } else {
            return new JsonResponse(['error' => 'Le numéro de table est requis'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($commande);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Commande créée', 'id' => $commande->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/commandes/{id}/update-boissons', name: 'update_boissons', methods: ['PATCH'])]
    public function updateBoissons(
        int $id,
        Request $request,
        CommandeRepository $commandeRepository,
        BoissonRepository $boissonRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ): JsonResponse {
        if (!$security->isGranted('ROLE_SERVEUR')) {
            throw new AccessDeniedHttpException('Accès refusé');
        }

        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw new NotFoundHttpException('Commande non trouvée');
        }

        if ($commande->getStatus() === 'payée') {
            throw new AccessDeniedHttpException('Impossible de mettre à jour une commande payée');
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['boissons'])) {
            foreach ($data['boissons'] as $boissonId) {
                $boisson = $boissonRepository->find($boissonId);

                if (!$boisson) {
                    return new JsonResponse(['error' => 'Boisson non trouvée'], JsonResponse::HTTP_BAD_REQUEST);
                }

                if (!$commande->getBoissonCommandé()->contains($boisson)) {
                    $commande->addBoissonCommand($boisson);
                }
            }
        }

        $entityManager->persist($commande);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Boissons mises à jour'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/commandes/{id}/pay', name: 'pay_commande', methods: ['PATCH'])]
    public function payCommande(
        int $id,
        CommandeRepository $commandeRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ): JsonResponse {
        if (!$security->isGranted('ROLE_SERVEUR')) {
            throw new AccessDeniedHttpException('Accès refusé');
        }

        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw new NotFoundHttpException('Commande non trouvée');
        }

        if ($commande->getStatus() === 'payée') {
            return new JsonResponse(['error' => 'Cette commande est déjà payée'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $commande->setStatus('payée');

        $entityManager->persist($commande);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Commande payée'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/commandes/{id}', name: 'view_commande', methods: ['GET'])]
    public function viewCommande(
        int $id,
        CommandeRepository $commandeRepository,
        Security $security
    ): JsonResponse {
        if (!$security->isGranted('ROLE_SERVEUR')) {
            throw new AccessDeniedHttpException('Accès refusé');
        }

        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw new NotFoundHttpException('Commande non trouvée');
        }

        return new JsonResponse($commande);
    }
}