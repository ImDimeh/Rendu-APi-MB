<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Boisson;
use App\Repository\CommandeRepository;
use App\Repository\BoissonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommandController extends AbstractController
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager,  $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('/api/commandes', name: 'create_commande', methods: ['POST'])]
    public function createCommande(Request $request): JsonResponse
    {
        if (!$this->security->isGranted('ROLE_SERVEUR')) {
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

        $this->entityManager->persist($commande);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Commande créée', 'id' => $commande->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/commandes/{id}/update-boissons', name: 'update_boissons', methods: ['PATCH'])]
    public function updateBoissons(int $id, Request $request, CommandeRepository $commandeRepository, BoissonRepository $boissonRepository): JsonResponse
    {
        if (!$this->security->isGranted('ROLE_SERVEUR')) {
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

        $this->entityManager->persist($commande);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Boissons mises à jour'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/commandes/{id}/pay', name: 'pay_commande', methods: ['PATCH'])]
    public function payCommande(int $id, CommandeRepository $commandeRepository): JsonResponse
    {
        if (!$this->security->isGranted('ROLE_SERVEUR')) {
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

        $this->entityManager->persist($commande);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Commande payée'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/commandes/{id}', name: 'view_commande', methods: ['GET'])]
    public function viewCommande(int $id, CommandeRepository $commandeRepository): JsonResponse
    {
        if (!$this->security->isGranted('ROLE_SERVEUR') && !$this->security->isGranted('ROLE_BARMAN') && !$this->security->isGranted('ROLE_PATRON')) {
            throw new AccessDeniedHttpException('Accès refusé');
        }

        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw new NotFoundHttpException('Commande non trouvée');
        }

        return new JsonResponse($commande);
    }

    #[Route('/api/commandes/en-cours', name: 'list_commandes_en_cours', methods: ['GET'])]
    public function listCommandesEnCours(CommandeRepository $commandeRepository): JsonResponse
    {
        if (!$this->security->isGranted('ROLE_BARMAN') && !$this->security->isGranted('ROLE_PATRON')) {
            throw new AccessDeniedHttpException('Accès refusé');
        }

        $commandes = $commandeRepository->findBy(['status' => 'en cours de préparation']);

        return new JsonResponse($commandes);
    }

    #[Route('/api/commandes/{id}/assign', name: 'assign_commande', methods: ['PATCH'])]
    public function assignCommande(int $id, CommandeRepository $commandeRepository): JsonResponse
    {
        if (!$this->security->isGranted('ROLE_BARMAN')) {
            throw new AccessDeniedHttpException('Accès refusé');
        }

        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw new NotFoundHttpException('Commande non trouvée');
        }

        $commande->setBarman($this->getUser());

        $this->entityManager->persist($commande);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Commande assignée'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/commandes/{id}/set-ready', name: 'set_commande_ready', methods: ['PATCH'])]
    public function setCommandeReady(int $id, CommandeRepository $commandeRepository): JsonResponse
    {
        if (!$this->security->isGranted('ROLE_BARMAN')) {
            throw new AccessDeniedHttpException('Accès refusé');
        }

        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw new NotFoundHttpException('Commande non trouvée');
        }

        $commande->setStatus('prête');

        $this->entityManager->persist($commande);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Commande prête'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/commandes', name: 'list_commandes', methods: ['GET'])]
    public function listCommandes(Request $request, CommandeRepository $commandeRepository): JsonResponse
    {
        if (!$this->security->isGranted('ROLE_SERVEUR') && !$this->security->isGranted('ROLE_BARMAN') && !$this->security->isGranted('ROLE_PATRON')) {
            throw new AccessDeniedHttpException('Accès refusé');
        }

        $startDate = new \DateTime($request->query->get('startDate'));
        $endDate = new \DateTime($request->query->get('endDate'));

        $queryBuilder = $commandeRepository->createQueryBuilder('c')
            ->where('c.createdDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $commandes = $queryBuilder->getQuery()->getResult();

        return new JsonResponse($commandes);
    }
}
