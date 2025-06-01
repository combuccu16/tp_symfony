<?php

namespace App\Controller;

use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/livres')]
class UserLivreController extends AbstractController
{
    #[Route('/', name: 'user_livre_index')]
    public function index(LivreRepository $livreRepository): Response
    {
        return $this->render('user_livre/index.html.twig', [
            'livres' => $livreRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'user_livre_show')]
    public function show(int $id, LivreRepository $livreRepository): Response
    {
        $livre = $livreRepository->find($id);

        if (!$livre) {
            throw $this->createNotFoundException('Livre non trouvé');
        }

        return $this->render('user_livre/show.html.twig', [
            'livre' => $livre,
        ]);
    }


}
