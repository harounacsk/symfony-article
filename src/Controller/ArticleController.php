<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\DepotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/article', name: 'app_article')]
class ArticleController extends AbstractController
{
    private  $articleRepository;
    private  $depotRepository;

    private $normalizer;
    function __construct(ArticleRepository $articleRepository, NormalizerInterface $normalizer, DepotRepository $depotRepository)
    {
        $this->articleRepository = $articleRepository;
        $this->depotRepository = $depotRepository;
        $this->normalizer = $normalizer;
    }

    #[Route('/all', name: 'get_articles', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        if (count($this->articleRepository->findAll()) != 0) {
            $articles = $this->articleRepository->findAll();
            $articlesNormalises = $this->normalizer->normalize($articles, null);
            return $this->json($articlesNormalises);
        }
        return $this->json("empty");
    }

    #[Route('/add', name: 'add_article', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $article = new Article();
        $data = json_decode($request->getContent());
        $depot = $this->depotRepository->find($data->depot);

        if (!is_null($depot)) {
            $article->setName($data->name)->setPrice($data->price)
                ->setBackup($data->backup)->setDepot($depot);
            $this->articleRepository->save($article, true);
            return $this->json("article added");

        }
        return $this->json("error");
    }

    #[Route('/update', name: 'update_article', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $article = $this->articleRepository->find($data->id);
        $depot = $this->depotRepository->find($data->depot);

        if (!is_null($article)) {
            if (!is_null($depot)) {
                $article->setName($data->name)
                ->setPrice($data->price)
                ->setBackup($data->backup)
                ->setDepot($depot);

            $this->articleRepository->save($article, true);
            return $this->json("article updated");
            }
            
        }
        return  $this->json("error");
    }

    #[Route('/delete/{id}', name: 'delete_article', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $article = $this->articleRepository->find($id);
        if (!is_null($article)) {
            $this->articleRepository->remove($article, true);
            return  $this->json($article->getName() . " is deleted");
        }

        return  $this->json("error");
    }
}
