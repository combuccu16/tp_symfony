<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/mes-commandes', name: 'commande_mes_commandes')]
    public function mesCommandes(CommandeRepository $commandeRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $commandes = $commandeRepository->findBy(['user' => $user], ['date_commande' => 'DESC']);

        return $this->render('commande/mes_commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }
    #[Route('/{id}', name: 'commande_show')]
public function show(Commande $commande): Response
{
    $this->denyAccessUnlessGranted('ROLE_USER');

    // Sécurité : l'utilisateur ne peut voir que ses propres commandes
    if ($commande->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException();
    }

    return $this->render('commande/show.html.twig', [
        'commande' => $commande,
    ]);
}
}
