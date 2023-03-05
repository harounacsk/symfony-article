<?php

namespace App\Controller;

use App\Entity\Depot;
use App\Repository\DepotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/depot',name:'app_depot')]
class DepotController extends AbstractController
{
    private $depotRepository;
    private $normalizer;
    private $serializer;

    public  function __construct(DepotRepository $depotRepository,NormalizerInterface $normalizer,SerializerInterface $serializer)
    {
        $this->depotRepository = $depotRepository;
        $this->normalizer = $normalizer;
        $this->serializer = $serializer;
    }

    #[Route('/all', name: 'get_depots', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        if (count($this->depotRepository->findAll()) != 0) {
            $depots = $this->depotRepository->findAll();
            $depotsNormalises = $this->normalizer->normalize($depots, null);
            return $this->json($depotsNormalises);
        }
        return $this->json("empty");
    }

    #[Route('/add', name: 'add_depot', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $obj = $request->getContent();
        #Deserializing in an Existing Object
        $depot = $this->serializer->deserialize($obj, Depot::class, 'json');
        $this->depotRepository->save($depot, true);
        return $this->json("depot added");
    }

    #[Route('/update', name: 'update_depot', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $depot = $this->depotRepository->find($data->id);

        if (!is_null($depot)) {
            $depot->setName($data->name);
            $this->depotRepository->save($depot, true);
            return $this->json("depot updated");
        }
        return  $this->json("error");
    }

    #[Route('/delete/{id}', name: 'delete_depot', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $depot = $this->depotRepository->find($id);
        if (!is_null($depot)) {
            $this->depotRepository->remove($depot, true);
            return  $this->json($depot->getName() . " is deleted");
        }

        return  $this->json("error");
    }
}
