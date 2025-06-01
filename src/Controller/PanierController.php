<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Panier;
use App\Repository\LivreRepository;
use App\Repository\PanierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('/add', name: 'panier_add', methods: ['POST'])]
public function add(
    Request $request,
    EntityManagerInterface $em,
    LivreRepository $livreRepository,
    PanierRepository $panierRepository
): Response
{
    $this->denyAccessUnlessGranted('ROLE_USER');

    $livreId = $request->request->get('livreId');
    $quantity = max(1, (int) $request->request->get('quantity', 1));
    $token = $request->request->get('_token');

    if (!$this->isCsrfTokenValid('add-to-cart' . $livreId, $token)) {
        throw $this->createAccessDeniedException('Invalid CSRF token');
    }

    $livre = $livreRepository->find($livreId);
    if (!$livre) {
        throw $this->createNotFoundException('Livre non trouvé');
    }

    // Vérifier le stock
    if ($livre->getStock() < $quantity) {
        $this->addFlash('danger', 'Stock insuffisant pour ce livre.');
        return $this->redirectToRoute('user_livre_index');
    }

    $user = $this->getUser();

    // Vérifier si le livre est déjà dans le panier de l'utilisateur
    $panierItem = $panierRepository->findOneBy(['user' => $user, 'livre' => $livre]);

    if ($panierItem) {
        $newQuantity = $panierItem->getQuantity() + $quantity;

        if ($newQuantity > $livre->getStock()) {
            $this->addFlash('danger', 'Quantité totale demandée dépasse le stock disponible.');
            return $this->redirectToRoute('user_livre_index');
        }

        // Mettre à jour la quantité
        $panierItem->setQuantity($newQuantity);
    } else {
        // Nouveau panier item
        $panierItem = new Panier();
        $panierItem->setUser($user);
        $panierItem->setLivre($livre);
        $panierItem->setQuantity($quantity);
        $em->persist($panierItem);
    }

    $em->flush();

    $this->addFlash('success', 'Livre ajouté au panier avec succès!');

    return $this->redirectToRoute('user_livre_index');
}

    #[Route('/', name: 'panier_index')]
    public function index(PanierRepository $panierRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $panierItems = $panierRepository->findBy(['user' => $user]);

        // Calculer le total du panier
        $total = 0;
        foreach ($panierItems as $item) {
            $total += $item->getLivre()->getPrice() * $item->getQuantity();
        }

        return $this->render('panier/index.html.twig', [
            'panierItems' => $panierItems,
            'total' => $total,
        ]);
    }

    #[Route('/delete/{id}', name: 'panier_delete', methods: ['POST'])]
    public function delete(Request $request, Panier $panier, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Vérifier que l'item appartient bien à l'utilisateur
        if ($panier->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $panier->getId(), $request->request->get('_token'))) {
            $em->remove($panier);
            $em->flush();

            $this->addFlash('success', 'Article supprimé du panier avec succès!');
        }

        return $this->redirectToRoute('panier_index');
    }
    #[Route('/checkout', name: 'panier_checkout')]
public function checkout(EntityManagerInterface $em, PanierRepository $panierRepository): Response
{
    $user = $this->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour finaliser la commande.');
    }

    $panierItems = $panierRepository->findBy(['user' => $user]);

    if (count($panierItems) === 0) {
        $this->addFlash('warning', 'Votre panier est vide.');
        return $this->redirectToRoute('panier_index');
    }

    $commande = new Commande();
    $commande->setUser($user);
    $commande->setDateCommande(new \DateTime()); // compatible avec DateTimeInterface
    $commande->setStatut('En attente');

    $total = 0;
    $em->persist($commande);

    foreach ($panierItems as $item) {
        $livre = $item->getLivre();
        $quantite = $item->getQuantity();

        // Vérification stock
        if ($livre->getStock() < $quantite) {
            $this->addFlash('danger', 'Stock insuffisant pour le livre : ' . $livre->getTitre());
            return $this->redirectToRoute('panier_index');
        }

        // Décrémente le stock
        $livre->setStock($livre->getStock() - $quantite);

        // Création de la ligne commande
        $ligneCommande = new LigneCommande();
        $ligneCommande->setCommande($commande);
        $ligneCommande->setLivre($livre);
        $ligneCommande->setQuantite($quantite);
        $ligneCommande->setPrix($livre->getPrice());

        $total += $quantite * $livre->getPrice();

        $em->persist($ligneCommande);
        $em->remove($item); // Supprimer du panier
    }

    $commande->setTotal($total);
    $em->flush();

    $this->addFlash('success', 'Commande finalisée avec succès !');

    return $this->redirectToRoute('user_home'); // ou une page de confirmation
}

}
