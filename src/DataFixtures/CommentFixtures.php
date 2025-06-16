<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;
use App\Entity\Comment;
use App\Entity\User;

class CommentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->find(1);
        $product = $manager->getRepository(Product::class)->find(1);
        // $user = 
        // $product = new Product();
        // $manager->persist($product);

        $comment1 = new Comment();
        $comment1->setText('This is a great product!');
        $comment1->setUser($user);
        $comment1->setProduct($product);
        $comment1->setRating(5);
        $comment1->setCreatedAt(new \DateTimeImmutable());

        $manager->persist($comment1);

        $manager->flush();
    }
}
