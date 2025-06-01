<?php

namespace App\Controller;

use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LivreController extends AbstractController
{
    #[Route('/livres/search', name: 'livre_search')]
public function search(Request $request, LivreRepository $livreRepository): Response
{
    $query = $request->query->get('q');
    $author = $request->query->get('author');
    $minPrice = $request->query->get('minPrice');
    $maxPrice = $request->query->get('maxPrice');
    $fromDate = $request->query->get('fromDate');
    $toDate = $request->query->get('toDate');
    $stock = $request->query->get('stock');

    $qb = $livreRepository->createQueryBuilder('l');

    if ($query) {
        $qb->andWhere('LOWER(l.titre) LIKE :query OR LOWER(l.description) LIKE :query')
   ->setParameter('query', '%' . strtolower($query) . '%');

    }

    if ($author) {
        $qb->andWhere('LOWER(l.author) LIKE :author')
   ->setParameter('author', '%' . strtolower($author) . '%');
    }

    if (is_numeric($minPrice)) {
    $qb->andWhere('l.price >= :minPrice')
       ->setParameter('minPrice', (int) $minPrice);
}

if (is_numeric($maxPrice)) {
    $qb->andWhere('l.price <= :maxPrice')
       ->setParameter('maxPrice', (int) $maxPrice);
}

    if ($fromDate) {
    try {
        $qb->andWhere('l.publicationDate >= :fromDate')
           ->setParameter('fromDate', new \DateTime($fromDate));
    } catch (\Exception $e) {}
}

if ($toDate) {
    try {
        $qb->andWhere('l.publicationDate <= :toDate')
           ->setParameter('toDate', new \DateTime($toDate));
    } catch (\Exception $e) {}
}

    if ($stock !== null) {
        $qb->andWhere('l.stock >= :stock')
           ->setParameter('stock', $stock);
    }

    $livres = $qb->getQuery()->getResult();

    return $this->render('livre/search.html.twig', [
        'livres' => $livres,
    ]);

}
}
