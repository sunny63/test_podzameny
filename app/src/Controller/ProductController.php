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

        $request = [
            'Brand' => '',
            'Article' => $article,
            'is_main_warehouse' => 0,
            'Contract' => ''
        ];

        // после подключения к api tmparts посылаем им $request в запросе StockByArticle
        // api tmparts присылает нам ответ в формате, представленном ниже

        $apiResponse = [
            'brand' => 'JD',
            'brand_alt' => '',
            'article' => '223',
            'article_alt' => '',
            'analog' => 0,
            'article_name' => 'Амортизатор /gas/ FR',
            'min_price' => 805.94,
            'applicability' => '',
            'warehouse_offers' => [
                'id' => '00f1bd04-910d-44e8-aedf-10ad4822559d',
                'price' => 805.94,
                'quantity' => '10',
                'min_part' => '1',
                'delivery_period' => 15,
                'warehouse_code' => 432,
                'warehouse_name' => 'Москва',
                'is_main_warehouse' => 0,
                'branch_name' => 'Москва',
                'branch_code' => '00003',
                'name' => 'Амортизатор /gas/ FR',
            ]
        ];

        // нам нужны только некоторые поля из ответа, поэтому мы отсеиваем лишнее и преобразуем некоторые поля
        // как требуется (цену из рублей в копейки, время доставки видимо из дней (?) в секунды)

        $response['brand'] = $apiResponse['brand'];
        $response['article'] = $apiResponse['article'];
        $response['name'] = $apiResponse['article_name'];

        $warehouseOffers = $apiResponse['warehouse_offers'];

        $response['quantity'] = $warehouseOffers['quantity'];
        $response['price'] = $warehouseOffers['price'] * 100;
        $response['delivery_duration'] = $warehouseOffers['delivery_period'] * 86400;
        $response['vendor_id'] = $warehouseOffers['id'];
        $response['warehouse_alias'] = $warehouseOffers['warehouse_name'];

        // и получаем $response с которым уже дальше можем работать

        $products = $this->productRepository->findBy(['article' => $article]);

        return $this->render('product/index.html.twig', ['products' => $products]);
    }
}