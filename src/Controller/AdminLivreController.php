<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Livre;
use App\Form\LivreTypeForm;
use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin/livre')]
class AdminLivreController extends AbstractController
{
    // ✅ 1. Add a new book
    #[Route('/add', name: 'admin_livre_add')]
    public function addLivre(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $livre = new Livre();
        $livre->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(LivreTypeForm::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livre);
            $entityManager->flush();

            $this->addFlash('success', 'Livre ajouté avec succès!');
            return $this->redirectToRoute('admin_livre_index');
        }

        return $this->render('admin_livre/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ✅ 2. List all books
    #[Route('/', name: 'admin_livre_index')]
    public function index(LivreRepository $livreRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin_livre/index.html.twig', [
            'livres' => $livreRepository->findAll(),
        ]);
    }

    // ✅ 3. Edit a book
    #[Route('/edit/{id}', name: 'admin_livre_edit')]
    public function editLivre(Livre $livre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(LivreTypeForm::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Livre modifié avec succès!');
            return $this->redirectToRoute('admin_livre_index');
        }

        return $this->render('admin_livre/edit.html.twig', [
            'form' => $form->createView(),
            'livre' => $livre,
        ]);
    }

    // ✅ 4. Delete a book
    #[Route('/delete/{id}', name: 'admin_livre_delete', methods: ['POST'])]
    public function deleteLivre(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $livre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($livre);
            $entityManager->flush();

            $this->addFlash('success', 'Livre supprimé avec succès!');
        }

        return $this->redirectToRoute('admin_livre_index');
    }
    // ajouter du stock
    #[Route('/admin/livre/{id}/add-stock', name: 'admin_livre_add_stock', methods: ['GET', 'POST'])]
    public function addStock(Livre $livre, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $addedStock = (int) $request->request->get('added_stock', 0);

            if ($addedStock > 0) {
                $livre->setStock($livre->getStock() + $addedStock);
                $em->flush();

                $this->addFlash('success', "Stock mis à jour avec succès !");
                return $this->redirectToRoute('admin_livre_index'); // ou la route admin liste livres
            } else {
                $this->addFlash('error', "Veuillez saisir une quantité valide.");
            }
        }

        return $this->render('admin_livre/livre_add_stock.html.twig', [
            'livre' => $livre,
        ]);
    }
}
