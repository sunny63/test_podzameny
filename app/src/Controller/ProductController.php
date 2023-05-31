<?php

namespace App\Controller;

use App\Order\Factory\Order\OrderFiller;
use App\Order\Factory\OrderLine\OrderLineFactory;
use App\Order\Factory\OrderLine\OrderLineFiller;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param string $article
     * @return Response
     */
    #[Route('/products/{article}', name: 'products', methods: ['GET'])]
    public function getProductsAction(string $article): Response
    {
        // чтобы получить записи из бд по артикулу с помощью api tmparts нужно отправить запрос (а чтобы подключиться к их апи нужно иметь ключ)
        // {
        //      'Brand':'',
        //      'Article':'d5471',
        //      'is_main_warehouse': 0 (будут показаны товары в наличии и под заказ)
        //      'Contract' : ''
        // }
        //
        // по данному запросу будут выданы все товары с указанным актикулом по всем брендам
        // тк часть полей не совпадает в выходных данных тз и апи то нужно выбрать только необходимые полz
        $products = $this->productRepository->findBy(['article' => $article]);

        return $this->render('product/index.html.twig', ['products' => $products]);
    }
}