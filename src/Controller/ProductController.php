<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Comment;
use App\Form\ProductForm;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Service\FileUploader;
use App\Form\CommentForm;
use App\Form\ProductSearchForm;
use Doctrine\ORM\EntityManager;

#[Route('/products')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET', 'POST'])]
    public function index(ProductRepository $productRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(ProductSearchForm::class);

        $form->handleRequest($request);

        $query = $form->get('q')->getData();
        $products = [];

        
        if ($query) {
            $products = $manager->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->where('p.title LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->getQuery()
            ->getResult();
        } else {
            $products = $manager->getRepository(Product::class)->findAll();
        }

        dump($products);
        dump($query);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET', 'POST'])]
    public function show(
        Product $product, 
        Comment $comment, 
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        $commentForm = $this->createForm(CommentForm::class, new Comment());

        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {

            $newComment = $commentForm->getData();

            dump($newComment);

            $newComment->setProduct($product);
            $newComment->setUser($this->getUser());
            $newComment->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($newComment);
            $entityManager->flush();

            $this->addFlash('notice', 'Comment added successfully!');

            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()], Response::HTTP_SEE_OTHER);
        }
        
        $comments = $entityManager->getRepository(Comment::class)->findBy(['product' => $product]);

        $avarageRating = 0;

        foreach ($comments as $comment) {
            if ($comment->getRating() !== null) {
                $avarageRating += $comment->getRating();
            }
        }

        if (count($comments) > 0) {
            $avarageRating /= count($comments);
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'comments' => $comments,
            'commentsCount' => count($comments),
            'avarageRating' => $avarageRating,
            'commentForm' => $commentForm,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Product $product, 
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response
    {
        $prevThumbnail = $product->getThumbnail();

        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            

            $newThumbnail = $form->get('thumbnail')->getData();

            if ( $newThumbnail ) {
                $newThumbnailName = $fileUploader->upload($newThumbnail, $this->getParameter('thumbnails_directory'));
                $product->setThumbnail($newThumbnailName);
            } else {   
                $product->setThumbnail($prevThumbnail);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
